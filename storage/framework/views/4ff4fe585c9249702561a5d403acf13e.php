<div class="footer">
    <div class="row justify-content-between align-items-center">
        <div class="col">
            <p class="font-size-sm mb-0">
                &copy; <?php echo e(Str::limit(\App\CentralLogics\Helpers::get_restaurant_data()->name, 50, '...')); ?>. <span
                    class="d-none d-sm-inline-block"></span>
            </p>
        </div>
        <div class="col-auto">
            <div class="d-flex justify-content-end">
                <!-- List Dot -->
                <ul class="list-inline list-separator">
                    <li class="list-inline-item">
                        <a class="list-separator-link" href="<?php echo e(route('vendor.business-settings.restaurant-setup')); ?>"><?php echo e(translate('messages.restaurant_settings')); ?></a>
                    </li>

                    <li class="list-inline-item">
                        <a class="list-separator-link" href="<?php echo e(route('vendor.shop.view')); ?>"><?php echo e(translate('messages.profile')); ?></a>
                    </li>

                    <li class="list-inline-item">
                        <!-- Keyboard Shortcuts Toggle -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-icon btn-ghost-secondary rounded-circle"
                               href="<?php echo e(route('vendor.dashboard')); ?>">
                                <i class="tio-home-outlined"></i>
                            </a>
                        </div>
                        <!-- End Keyboard Shortcuts Toggle -->
                    </li>
                </ul>
                <!-- End List Dot -->
            </div>
        </div>
    </div>
</div>
<?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/layouts/vendor/partials/_footer.blade.php ENDPATH**/ ?>