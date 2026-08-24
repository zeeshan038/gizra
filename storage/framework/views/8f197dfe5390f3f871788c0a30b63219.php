<div class="col-xl-3 col-sm-6">
    <a class="resturant-card dashboard--card bg--2" href="<?php echo e(route('vendor.order.list',['confirmed'])); ?>">
        <h4 class="title"><?php echo e($data['confirmed']); ?></h4>
        <span class="subtitle"><?php echo e(translate('messages.confirmed')); ?></span>
        <img class="resturant-icon max-width-34px" src="<?php echo e(dynamicAsset('public/assets/admin/img/resturant-panel/dashboard/confirmed.png')); ?>" alt="dashboard">
    </a>
</div>

<div class="col-xl-3 col-sm-6">
    <!-- Card -->
    <a class="resturant-card dashboard--card bg--3" href="<?php echo e(route('vendor.order.list',['cooking'])); ?>">
        <h4 class="title"><?php echo e($data['cooking']); ?></h4>
        <span class="subtitle"><?php echo e(translate('messages.cooking')); ?></span>
        <img class="resturant-icon max-width-34px" src="<?php echo e(dynamicAsset('public/assets/admin/img/resturant-panel/dashboard/cooking.png')); ?>" alt="dashboard">
    </a>
    <!-- End Card -->
</div>

<div class="col-xl-3 col-sm-6">
    <!-- Card -->
    <a class="resturant-card dashboard--card bg--5" href="<?php echo e(route('vendor.order.list',['ready_for_delivery'])); ?>">
        <h4 class="title"><?php echo e($data['ready_for_delivery']); ?></h4>
        <span class="subtitle"><?php echo e(translate('messages.ready_for_delivery')); ?></span>
        <img class="resturant-icon max-width-34px" src="<?php echo e(dynamicAsset('public/assets/admin/img/resturant-panel/dashboard/ready.png')); ?>" alt="dashboard">
    </a>
    <!-- End Card -->
</div>

<div class="col-xl-3 col-sm-6">
    <!-- Card -->
    <a class="resturant-card dashboard--card bg--14" href="<?php echo e(route('vendor.order.list',['food_on_the_way'])); ?>">
        <h4 class="title"><?php echo e($data['food_on_the_way']); ?></h4>
        <span class="subtitle"><?php echo e(translate('messages.food_on_the_way')); ?></span>
        <img class="resturant-icon max-width-34px" src="<?php echo e(dynamicAsset('public/assets/admin/img/resturant-panel/dashboard/on-the-way.png')); ?>" alt="dashboard">
    </a>
    <!-- End Card -->
</div>

<div class="col-12">
    <div class="row g-2 mt-2">
        <div class="col-sm-6 col-lg-3">
            <a href="<?php echo e(route('vendor.order.list',['delivered'])); ?>" class="order--card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/resturant-panel/dashboard/all.png')); ?>" alt="">
                        <span><?php echo e(translate('messages.delivered')); ?></span>
                    </h6>
                    <span class="card-title h3">
                        <?php echo e($data['delivered']); ?>

                    </span>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a href="<?php echo e(route('vendor.order.list',['refunded'])); ?>" class="order--card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/resturant-panel/dashboard/refunded.png')); ?>" alt="">
                        <span><?php echo e(translate('messages.refunded')); ?></span>
                    </h6>
                    <span
                        class="card-title h3"><?php echo e($data['refunded']); ?></span>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a href="<?php echo e(route('vendor.order.list',['scheduled'])); ?>" class="order--card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/resturant-panel/dashboard/scheduled.png')); ?>" alt="">
                        <span><?php echo e(translate('messages.scheduled')); ?></span>
                    </h6>
                    <span
                        class="card-title h3"><?php echo e($data['scheduled']); ?></span>
                </div>
            </a>
        </div>

        <div class="col-sm-6 col-lg-3">
            <a href="<?php echo e(route('vendor.order.list',['all'])); ?>" class="order--card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="card-subtitle d-flex justify-content-between m-0 align-items-center">
                        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/resturant-panel/dashboard/top-resturant.png')); ?>" alt="">
                        <span><?php echo e(translate('messages.all')); ?></span>
                    </h6>
                    <span
                        class="card-title h3"><?php echo e($data['all']); ?></span>
                </div>
            </a>
        </div>
    </div>
</div>
<?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/vendor-views/partials/_dashboard-order-stats.blade.php ENDPATH**/ ?>