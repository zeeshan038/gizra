<?php ($params = session('dash_params')); ?>
<?php if($params['zone_id']!='all'): ?>
<?php ($zone_name=\App\Models\Zone::where('id',$params['zone_id'])->first()->name); ?>
<?php else: ?>
<?php ($zone_name=translate('All')); ?>
<?php endif; ?>
<span class="badge badge-soft--info my-2"><?php echo e(translate('messages.zone')); ?> : <?php echo e($zone_name); ?></span>
<?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/admin-views/partials/_zone-change.blade.php ENDPATH**/ ?>