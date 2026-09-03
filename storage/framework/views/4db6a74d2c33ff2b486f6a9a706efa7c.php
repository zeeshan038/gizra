<!-- Header -->
<div class="card-header">
    <h5 class="card-header-title">
        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/dashboard/top-resturant.png')); ?>" alt="dashboard" class="card-header-icon">
        <span><?php echo e(translate('Top_Restaurants')); ?></span>
    </h5>
    <?php ($params=session('dash_params')); ?>
    <?php if($params['zone_id']!='all'): ?>
        <?php ($zone_name=\App\Models\Zone::where('id',$params['zone_id'])->first()->name); ?>
    <?php else: ?>
    <?php ($zone_name=translate('All')); ?>
    <?php endif; ?>
    <span class="badge badge-soft--info my-2"><?php echo e(translate('messages.zone')); ?> : <?php echo e($zone_name); ?></span>
</div>
<!-- End Header -->

<!-- Body -->
<div class="card-body">
    <ul class="top--resturant">
    <?php $__currentLoopData = $top_restaurants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <li>
            <div class="top--resturant-item redirect-url" data-url="<?php echo e(route('admin.restaurant.view', $item->id)); ?>">
                <img class="onerror-image" data-onerror-image="<?php echo e(dynamicAsset('public/assets/admin/img/100x100/1.png')); ?>"
                     src="<?php echo e($item['logo_full_url'] ?? dynamicAsset('public/assets/admin/img/100x100/1.png')); ?>">
                <div class="top--resturant-item-content">
                    <h5 class="name m-0">
                            <?php echo e(Str::limit($item->name??translate('messages.Restaurant_deleted!'), 20, '...')); ?>

                    </h5>
                    <h5 class="info m-0"><span class="text-warning"><?php echo e($item['order_count']); ?></span> <small><?php echo e(translate('Orders')); ?></small></h5>
                </div>
            </div>
        </li>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </ul>
</div>
<!-- End Body -->
<?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/admin-views/partials/_top-restaurants.blade.php ENDPATH**/ ?>