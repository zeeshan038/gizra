<div class="footer">
    <div class="row gy-1 justify-content-between align-items-center">
        <div class="col-md-auto">
            <p class="font-size-sm mb-0">
                &copy; <?php echo e(\App\Models\BusinessSetting::where(['key'=>'business_name'])->first()->value); ?>. <span
                    class="d-none d-sm-inline-block"><?php echo e(\App\Models\BusinessSetting::where(['key'=>'footer_text'])->first()->value); ?></span>
            </p>
        </div>
        <div class="col-md-auto">
            <div class="d-flex justify-content-end">
                <!-- List Dot -->
                <ul class="list-inline list-separator d-flex flex-wrap justify-content-evenly justify-content-md-end flex-grow-1">
                    <li class="list-inline-item py-1">
                        <a class="list-separator-link" href="<?php echo e(route('admin.business-settings.business-setup')); ?>"><?php echo e(translate('messages.business_setup')); ?> <i class="tio-settings-outlined ml-xl-2"></i></a>
                    </li>

                    <li class="list-inline-item py-1">
                        <a class="list-separator-link" href="<?php echo e(route('admin.settings')); ?>">
                            <?php echo e(translate('messages.profile')); ?>

                            <i class="tio-user ml-xl-2"></i>
                        </a>
                    </li>


                    <li class="list-inline-item py-1">
                        <a class="list-separator-link" href="<?php echo e(route('admin.dashboard')); ?>">
                            <?php echo e(translate('messages.dashboard')); ?>

                            <i class="tio-dashboard-vs ml-xl-2"></i>
                        </a>
                    </li>


                    <li class="list-inline-item py-1">
                        <span class="badge app--version"><?php echo e(translate('software_version')); ?> <?php echo e(env('SOFTWARE_VERSION')); ?></span>
                    </li>

                </ul>
                <!-- End List Dot -->
            </div>
        </div>
    </div>
</div>
<?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/layouts/admin/partials/_footer.blade.php ENDPATH**/ ?>