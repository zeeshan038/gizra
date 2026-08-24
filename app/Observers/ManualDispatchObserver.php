<?php

namespace App\Observers;

use App\Models\AccountTransaction;
use App\Models\AdminWallet;
use App\Models\Admin;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\RestaurantWallet;

/**
 * Settles manual dispatch orders to the terms agreed for phone orders.
 *
 * The stock commission engine already runs for these orders and does most of
 * the work. Two things differ from a normal delivery and are corrected here
 * once, when the order is delivered:
 *
 *  1. commission is the manual dispatch rate, not the restaurant's usual rate;
 *  2. the restaurant absorbs the delivery fee, where normally the customer
 *     pays it on top.
 *
 * Everything is additive. Orders that are not manual dispatches return on the
 * first line, and the whole body is wrapped so a fault here can never break an
 * order save anywhere else in the system.
 */
class ManualDispatchObserver
{
    public function updated(Order $order): void
    {
        try {
            if ((int) ($order->is_manual_dispatch ?? 0) !== 1) {
                return;
            }
            if (!$order->wasChanged('order_status') || $order->order_status !== 'delivered') {
                return;
            }

            $ref = 'manual_dispatch_' . $order->id;

            // The stock engine can be invoked more than once for one order.
            // The ledger line is the guard: if it exists, this is settled.
            if (AccountTransaction::where('ref', $ref)->exists()) {
                return;
            }

            $restaurant = $order->restaurant;
            $vendorId   = $restaurant?->vendor?->id;
            if (!$vendorId) {
                info('manual dispatch settlement skipped, no vendor for order ' . $order->id);
                return;
            }

            $manualRate = (float) (BusinessSetting::where('key', 'manual_dispatch_commission')->first()?->value ?? 10);
            $chargedRate = $restaurant?->comission !== null
                ? (float) $restaurant->comission
                : (float) (BusinessSetting::where('key', 'admin_commission')->first()?->value ?? 0);

            // These orders carry no tax, packaging or customer-paid delivery, so
            // the commissionable base the engine used is the order amount.
            $base = (float) $order->order_amount;
            $fee  = (float) $order->original_delivery_charge;

            // Positive when the engine took more than the manual dispatch rate,
            // which is money owed back to the restaurant.
            $commissionAdjustment = round($base * ($chargedRate - $manualRate) / 100, 2);

            $vendorDelta = round($commissionAdjustment - $fee, 2);

            $vendorWallet = RestaurantWallet::firstOrNew(['vendor_id' => $vendorId]);
            $adminWallet  = AdminWallet::firstOrNew(['admin_id' => Admin::where('role_id', 1)->first()?->id]);

            $vendorWallet->total_earning = (float) $vendorWallet->total_earning + $vendorDelta;
            $adminWallet->total_commission_earning = (float) $adminWallet->total_commission_earning - $vendorDelta;

            // Pre-paid means the restaurant took the customer's money at the
            // counter, so it is holding cash that belongs to GIZRA.
            $prepaid = $order->payment_method === 'digital_payment';
            if ($prepaid) {
                $vendorWallet->collected_cash = (float) $vendorWallet->collected_cash + $base;
            }

            $vendorWallet->save();
            $adminWallet->save();

            $this->log(
                ref: $ref,
                vendorId: $vendorId,
                amount: round($base * $manualRate / 100, 2),
                method: 'manual_dispatch_commission',
                balance: (float) $vendorWallet->total_earning
            );

            if ($fee > 0) {
                $this->log(
                    ref: $ref . '_fee',
                    vendorId: $vendorId,
                    amount: $fee,
                    method: 'manual_dispatch_delivery_fee',
                    balance: (float) $vendorWallet->total_earning
                );
            }
        } catch (\Throwable $e) {
            // Never propagate. An accounting fault must not stop a delivery
            // being recorded.
            info('manual dispatch settlement failed for order ' . $order->id . ': ' . $e->getMessage());
        }
    }

    private function log(string $ref, int $vendorId, float $amount, string $method, float $balance): void
    {
        try {
            $t = new AccountTransaction();
            $t->from_type       = 'restaurant';
            $t->from_id         = $vendorId;
            $t->amount          = $amount;
            $t->current_balance = $balance;
            $t->method          = $method;
            $t->ref             = $ref;
            $t->type            = 'cash_out';
            $t->created_by      = 'admin';
            $t->save();
        } catch (\Throwable $e) {
            info('manual dispatch ledger line failed (' . $ref . '): ' . $e->getMessage());
        }
    }
}
