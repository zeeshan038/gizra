
            <!-- Nav -->
            <ul class="nav nav-tabs page-header-tabs">
                <li class="nav-item">
                    <a class="nav-link <?php echo e(request('tab')==null?'active':''); ?>"  href="<?php echo e(route('admin.restaurant.view', $restaurant->id)); ?>"><?php echo e(translate('messages.overview')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='order'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'order'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.orders')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='product'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'product'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.foods')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='reviews'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'reviews'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.reviews')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='discount'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'discount'])); ?>"  aria-disabled="true"><?php echo e(translate('discounts')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='transaction'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'transaction'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.transactions')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='settings'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'settings'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.settings')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='conversations'?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'conversations'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.conversations')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab') =='business_plan' || request('tab') =='subscriptions-transactions' ?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'business_plan'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.Business Plan')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='meta-data' ?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'meta-data'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.meta-data')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='qr-code' ?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'qr-code'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.QR_code')); ?></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link  <?php echo e(request('tab')=='disbursements' ?'active':''); ?>" href="<?php echo e(route('admin.restaurant.view', ['restaurant'=>$restaurant->id, 'tab'=> 'disbursements'])); ?>"  aria-disabled="true"><?php echo e(translate('messages.disbursements')); ?></a>
                </li>
            </ul>
            
            <!-- End Nav -->
<?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/admin-views/vendor/view/partials/_header.blade.php ENDPATH**/ ?>