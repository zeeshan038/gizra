<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Manual driver dispatch for orders a restaurant takes by phone.
 *
 * Creates an ordinary delivery order so the existing driver queries, admin
 * panel and commission engine pick it up with no changes of their own. Nothing
 * in the customer, POS or delivery man flows is touched by this controller.
 */
class DispatchController extends Controller
{
    /** Restaurant ids allowed to use the feature. Empty means nobody. */
    private function allowed_restaurants(): array
    {
        $raw = BusinessSetting::where('key', 'manual_dispatch_restaurants')->first()?->value ?? '';

        $ids = [];
        foreach (explode(',', $raw) as $id) {
            $id = (int) trim($id);
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        return $ids;
    }

    public function request_driver(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:30',
            'address' => 'required|string|max:1000',
            'order_amount' => 'required|numeric|min:0.01',
            'delivery_fee' => 'required|numeric|min:0',
            'payment_method' => 'required|in:prepaid,cash_on_delivery',
            'idempotency_key' => 'required|string|max:100',
            'zone_id' => 'nullable|integer',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }

        $restaurant = $request->vendor->restaurants[0];

        if (!(int) $restaurant->manual_dispatch && !in_array((int) $restaurant->id, $this->allowed_restaurants(), true)) {
            return response()->json([
                'errors' => [
                    ['code' => 'manual_dispatch', 'message' => 'Manual driver dispatch is not enabled for this restaurant.'],
                ]
            ], 403);
        }

        // A repeated submit must not put a second driver on the road.
        $existing = Order::where('manual_dispatch_key', $request['idempotency_key'])->first();
        if ($existing) {
            return response()->json($this->summary($existing), 200);
        }

        try {
            DB::beginTransaction();

            $customer = $this->walk_in_customer($restaurant);

            $order = new Order();
            // Always above the current maximum. The stock POS routine derives
            // the id from a row count, which can land below max once any order
            // has been deleted.
            $order->id = max(100000, (int) Order::max('id')) + 1;

            $order->user_id = $customer->id;
            $order->restaurant_id = $restaurant->id;
            $order->order_type = 'delivery';
            $order->order_status = 'confirmed';
            $order->pending = now();
            $order->confirmed = now();
            $order->accepted = now();
            $order->schedule_at = now();

            // The restaurant absorbs the delivery fee, so the customer pays the
            // order total only and that is what the driver collects.
            // Calculate tax (10% by default, or use what's sent in the request). Applied on item price only.
            $tax_percentage = $request->has('tax') ? (float) $request['tax'] : 10;
            $item_price = (float) $request['order_amount'];
            $delivery_fee = (float) $request['delivery_fee'];
            
            $tax_amount = round($item_price * ($tax_percentage / 100), 2);

            // Final total is Item Price + Delivery Fee + Tax
            $order->order_amount = round($item_price + $delivery_fee + $tax_amount, 2);
            $order->delivery_charge = round($delivery_fee, 2);
            $order->original_delivery_charge = round($delivery_fee, 2);

            // Set explicitly rather than left to the column defaults.
            $order->total_tax_amount = $tax_amount;
            $order->restaurant_discount_amount = 0;
            $order->coupon_discount_amount = 0;
            $order->dm_tips = 0;
            $order->extra_packaging_amount = 0;
            $order->additional_charge = 0;
            $order->ref_bonus_amount = 0;

            $prepaid = $request['payment_method'] === 'prepaid';

            // The commission engine keys the cash-handling branch off this
            // string, and in both cases somebody is holding physical money for
            // GIZRA. Which side holds it is decided at delivery time by
            // received_by, not here.
            $order->payment_method = 'cash_on_delivery';
            $order->payment_status = $prepaid ? 'paid' : 'unpaid';

            // Driver matching runs off the restaurant's zone; the submitted zone
            // is recorded for reporting only.
            $order->zone_id = $request['zone_id'] ? (int) $request['zone_id'] : $restaurant->zone_id;

            $order->delivery_address = json_encode([
                'contact_person_name' => $request['customer_name'],
                'contact_person_number' => $request['customer_phone'],
                'address' => $request['address'],
                'address_type' => 'others',
                'latitude' => (string) ($request['latitude'] ?? $restaurant->latitude),
                'longitude' => (string) ($request['longitude'] ?? $restaurant->longitude),
            ]);

            $order->is_manual_dispatch = 1;
            $order->manual_dispatch_key = $request['idempotency_key'];
            $order->order_note = 'Manual dispatch - phone order';
            $order->checked = 0;
            $order->created_at = now();
            $order->updated_at = now();
            $order->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            info('manual dispatch failed: ' . $e->getMessage());

            return response()->json([
                'errors' => [
                    ['code' => 'manual_dispatch', 'message' => 'Could not create the dispatch order.'],
                ]
            ], 403);
        }

        // Best effort only - a push failure must not lose an order that exists.
        try {
            Helpers::send_push_notif_to_topic([
                'title' => 'New order',
                'description' => 'Order #' . $order->id,
                'order_id' => $order->id,
                'image' => '',
                'type' => 'order_request',
                'order_type' => $order->order_type,
            ], 'zone_' . $order->zone_id . '_delivery_man', 'order_request');
        } catch (\Exception $e) {
            info('manual dispatch notification failed: ' . $e->getMessage());
        }

        return response()->json($this->summary($order), 200);
    }

    /**
     * One hidden customer per restaurant. Phone orders have no app account, and
     * a null user_id risks the driver app dereferencing a customer that is not
     * there.
     */
    private function walk_in_customer($restaurant): User
    {
        return User::firstOrCreate(
            ['email' => 'walkin+restaurant' . $restaurant->id . '@gizra.app'],
            [
                'f_name' => 'Walk-in',
                'l_name' => 'Customer',
                'phone' => '+00000000' . str_pad((string) $restaurant->id, 3, '0', STR_PAD_LEFT),
                'password' => bcrypt(Str::random(32)),
            ]
        );
    }

    /**
     * The figures shown back to the restaurant. Commission is quoted here but
     * only becomes a ledger entry when the order is delivered, through the
     * existing OrderLogic::create_transaction.
     */
    private function summary(Order $order): array
    {
        $rate = (float) (BusinessSetting::where('key', 'manual_dispatch_commission')->first()?->value ?? 10);

        $commission = round($order->order_amount * $rate / 100, 2);
        $fee = round($order->original_delivery_charge, 2);

        return [
            'order_id' => $order->id,
            'commission_rate' => $rate,
            'commission' => $commission,
            'delivery_fee' => $fee,
            'gizra_charge' => round($commission + $fee, 2),
            'restaurant_net' => $order->payment_status === 'paid'
                ? round(-1 * ($commission + $fee), 2)
                : round($order->order_amount - $commission - $fee, 2),
            'payment_status' => $order->payment_status,
            'message' => 'Driver requested successfully',
        ];
    }
}
