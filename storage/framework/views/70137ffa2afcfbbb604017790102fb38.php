<!-- Header -->
<div class="card-header">
    <h5 class="card-header-title align-items-center d-flex">
        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/dashboard/most-rated.png')); ?>" alt="dashboard" class="card-header-icon mr-2 mb-1">
        <span><?php echo e(translate('messages.top_rated_foods')); ?></span>
    </h5>
</div>
<!-- End Header -->

<!-- Body -->
<div class="card-body">
    <div class="row g-2">
        <?php $__currentLoopData = $most_rated_foods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="col-md-4 col-6">
            <a href="<?php echo e(route('vendor.food.view',[$item['id']])); ?>" class="grid-card top--rated-food pb-4">
                <center>
                    <img class="initial-42 onerror-image" src="<?php echo e($item['image_full_url']); ?>"
                         data-onerror-image="<?php echo e(dynamicAsset('public/assets/admin/img/100x100/2.png')); ?>" alt="<?php echo e($item->name); ?> image">
                </center>
                <div class="text-center mt-3">
                    <h5 class="name m-0 mb-1">
                        <?php echo e(Str::limit($item->name??translate('messages.Food_deleted!'),20,'...')); ?>

                    </h5>
                    <div class="rating">
                        <span class="text-warning"><i class="tio-star"></i> <?php echo e(round($item['avg_rating'],1)); ?></span>
                        <span class="text--title">(<?php echo e($item['rating_count']); ?>  <?php echo e(translate('Reviews')); ?>)</span>
                    </div>
                </div>
            </a>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
</div>
<!-- End Body -->
<?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/vendor-views/partials/_most-rated-foods.blade.php ENDPATH**/ ?>