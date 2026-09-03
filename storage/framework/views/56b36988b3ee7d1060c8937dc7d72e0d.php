<?php $__env->startSection('title',translate('Restaurant_List')); ?>

<?php $__env->startPush('css_or_js'); ?>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>

    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-header-title"><i class="tio-filter-list"></i> <?php echo e(translate('messages.restaurants')); ?> <span class="badge badge-soft-dark ml-2" id="itemCount"><?php echo e($restaurants->total()); ?></span></h1>
        </div>
        <!-- End Page Header -->


        <!-- Filters -->
        <div class="card shadow--card p-0 mb-4">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6 col-md-3">
                        <div class="select-item">
                        <!-- Veg/NonVeg filter -->
                        <select name="type"
                        data-url="<?php echo e(url()->full()); ?>" data-filter="type"
                        data-placeholder="<?php echo e(translate('messages.all')); ?>" class="form-control js-select2-custom set-filter">
                            <option value="all" <?php echo e($type=='all'?'selected':''); ?>><?php echo e(translate('messages.all')); ?></option>
                            <?php if($toggle_veg_non_veg): ?>
                            <option value="veg" <?php echo e($type=='veg'?'selected':''); ?>><?php echo e(translate('messages.veg')); ?></option>
                            <option value="non_veg" <?php echo e($type=='non_veg'?'selected':''); ?>><?php echo e(translate('messages.non_veg')); ?></option>
                            <?php endif; ?>
                            <option value="home_delivery" <?php echo e($type=='home_delivery'?'selected':''); ?>><?php echo e(translate('messages.Home_Delivery')); ?></option>
                            <option value="take_away" <?php echo e($type=='take_away'?'selected':''); ?>><?php echo e(translate('messages.Take_Away')); ?></option>
                            <option value="dine_in" <?php echo e($type=='dine_in'?'selected':''); ?>><?php echo e(translate('messages.Dine_In')); ?></option>
                        </select>
                        <!-- End Veg/NonVeg filter -->
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="select-item">
                        <!-- Veg/NonVeg filter -->
                        <select name="restaurant_model"
                                data-url="<?php echo e(url()->full()); ?>" data-filter="restaurant_model"
                        data-placeholder="<?php echo e(translate('messages.all')); ?>" class="form-control js-select2-custom set-filter">
                            <option selected disabled><?php echo e(translate('messages.Business_model')); ?></option>
                            <option value="all" <?php echo e($typ=='all'?'selected':''); ?>><?php echo e(translate('messages.all')); ?></option>
                            <option value="commission" <?php echo e($typ=='commission'?'selected':''); ?>><?php echo e(translate('messages.Commission')); ?></option>
                            <option value="subscribed" <?php echo e($typ=='subscribed'?'selected':''); ?>><?php echo e(translate('messages.Subscribed')); ?></option>
                            <option value="unsubscribed" <?php echo e($typ=='unsubscribed'?'selected':''); ?>><?php echo e(translate('messages.Unsubscribed')); ?></option>
                            <option value="none" <?php echo e($typ=='none'?'selected':''); ?>><?php echo e(translate('messages.Incomplete_Business_Model')); ?></option>

                        </select>

                    <!-- End Veg/NonVeg filter -->
                    </div>
                </div>

                <div class="col-sm-6 col-md-3">
                    <div class="select-item">
                        <select name="cuisine_id" id="cuisine"
                                data-url="<?php echo e(url()->full()); ?>" data-filter="cuisine_id"
                        data-placeholder="<?php echo e(translate('messages.select_Cuisine')); ?>"
                        class="form-control h--45px js-select2-custom set-filter">
                        <option value="all" selected ><?php echo e(translate('messages.select_Cuisine')); ?></option>
                        <?php $__currentLoopData = \App\Models\Cuisine::orderBy('name')->get(['id','name']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cu['id']); ?>"
                                <?php echo e($cuisine_id ==  $cu['id']? 'selected' : ''); ?>>
                                <?php echo e($cu['name']); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    </div>
                </div>
                <?php if(!isset(auth('admin')->user()->zone_id)): ?>
                    <div class="col-sm-6 col-md-3">
                        <div class="select-item">
                            <select name="zone_id" class="form-control js-select2-custom set-filter"
                                data-url="<?php echo e(url()->full()); ?>" data-filter="zone_id">
                                <option selected disabled><?php echo e(translate('messages.select_zone')); ?></option>
                                <option value="all"><?php echo e(translate('messages.all_zones')); ?></option>
                                <?php $__currentLoopData = \App\Models\Zone::orderBy('name')->get(['id','name']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $z): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option
                                        value="<?php echo e($z['id']); ?>" <?php echo e(isset($zone) && $zone->id == $z['id']?'selected':''); ?>>
                                        <?php echo e($z['name']); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <!-- Filters -->


        <!-- Resturent Card Wrapper -->
        <div class="row g-3 mb-3">
            <div class="col-xl-3 col-sm-6">
                <div class="resturant-card bg--1">
                    <h4 class="title" id="itemCount" ><?php echo e($restaurants->total()); ?></h4>
                    <span class="subtitle"><?php echo e(translate('messages.total_restaurants')); ?></span>
                    <img class="resturant-icon" src="<?php echo e(dynamicAsset('/public/assets/admin/img/resturant/map-pin.png')); ?>" alt="resturant">
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">

                <div class="resturant-card bg--2">
                    <?php ($active_restaurants = \App\Models\Restaurant::where(['status'=>1])
                     ->whereHas('vendor', function($q){
                            $q->where('status',1);
                        })
                    ->when( isset($zone) && ($zone->id), function ($query) use ($zone) {
                                    return $query->where('zone_id', $zone->id);
                                    })
                    ->type($type)->RestaurantModel($typ)
                    ->count()); ?>
                    <?php ($active_restaurants = isset($active_restaurants) ? $active_restaurants : 0); ?>
                    <h4 class="title"><?php echo e($active_restaurants); ?></h4>
                    <span class="subtitle"><?php echo e(translate('messages.active_restaurants')); ?></span>
                    <img class="resturant-icon" src="<?php echo e(dynamicAsset('/public/assets/admin/img/resturant/active-rest.png')); ?>" alt="resturant">
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="resturant-card bg--3">
                    <?php ($inactive_restaurants = \App\Models\Restaurant::where(['status'=>0])
                     ->whereHas('vendor', function($q){
                            $q->where('status',1);
                        })
                    ->when( isset($zone) && ($zone->id), function ($query) use ($zone) {
                                    return $query->where('zone_id', $zone->id);
                                    })
                    ->type($type)->RestaurantModel($typ)
                    ->count()); ?>
                    <?php ($inactive_restaurants = isset($inactive_restaurants) ? $inactive_restaurants : 0); ?>
                    <h4 class="title"><?php echo e($inactive_restaurants); ?></h4>
                    <span class="subtitle"><?php echo e(translate('messages.inactive_restaurants')); ?></span>
                    <img class="resturant-icon" src="<?php echo e(dynamicAsset('/public/assets/admin/img/resturant/inactive-rest.png')); ?>" alt="resturant">
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="resturant-card bg--4">
                    <?php ($data = \App\Models\Restaurant::where('created_at', '>=', now()->subDays(30)->toDateTimeString())
                    ->whereHas('vendor', function($q){
                            $q->where('status',1);
                        })
                    ->when( isset($zone) && ($zone->id), function ($query) use ($zone) {
                                    return $query->where('zone_id', $zone->id);
                                    })
                    ->type($type)->RestaurantModel($typ)
                    ->count()); ?>
                    <h4 class="title"><?php echo e($data); ?></h4>
                    <span class="subtitle"><?php echo e(translate('messages.newly_joined_restaurants')); ?></span>
                    <img class="resturant-icon" src="<?php echo e(dynamicAsset('/public/assets/admin/img/resturant/new-rest.png')); ?>" alt="resturant">
                </div>
            </div>
        </div>
        <!-- Resturent Card Wrapper -->
        <!-- Transaction Information -->
        <ul class="transaction--information text-uppercase">
            <li class="text--info">
                <i class="tio-document-text-outlined"></i>
                <div>
                    <?php ($total_transaction = \App\Models\OrderTransaction::count()); ?>
                    <?php ($total_transaction = isset($total_transaction) ? $total_transaction : 0); ?>
                    <span><?php echo e(translate('messages.total_transactions')); ?></span> <strong><?php echo e($total_transaction); ?></strong>
                </div>
            </li>
            <li class="seperator"></li>
            <li class="text--success">
                <i class="tio-checkmark-circle-outlined success--icon"></i>
                <div>
                    <?php ($comission_earned = \App\Models\AdminWallet::sum('total_commission_earning')); ?>
                    <?php ($comission_earned = isset($comission_earned) ? $comission_earned : 0); ?>
                    <span><?php echo e(translate('messages.commission_earned')); ?></span> <strong><?php echo e(\App\CentralLogics\Helpers::format_currency($comission_earned)); ?></strong>
                </div>
            </li>
            <li class="seperator"></li>
            <li class="text--danger">
                <i class="tio-atm"></i>
                <div>
                    <?php ($restaurant_withdraws = \App\Models\WithdrawRequest::where(['approved'=>1])->sum('amount')); ?>
                    <?php ($restaurant_withdraws = isset($restaurant_withdraws) ? $restaurant_withdraws : 0); ?>
                    <span><?php echo e(translate('messages.total_restaurant_withdraws')); ?></span> <strong><?php echo e(\App\CentralLogics\Helpers::format_currency($restaurant_withdraws)); ?></strong>
                </div>
            </li>
        </ul>
        <!-- Transaction Information -->
        <!-- Resturent List -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <!-- Card -->
                <div class="card">
                    <!-- Card Header -->

                    <div class="card-header py-2 border-0">
                        <div class="search--button-wrapper">
                            <h3 class="card-title"><?php echo e(translate('messages.restaurants_list')); ?></h3>

                            <div class="d-flex align-items-center mr-3">
                                <label class="toggle-switch toggle-switch-sm mb-0 mr-2" for="activeOnlyToggle">
                                    <input type="checkbox" id="activeOnlyToggle"
                                        data-url="<?php echo e(url()->full()); ?>"
                                        class="toggle-switch-input" <?php echo e($active_only == '1' ? 'checked' : ''); ?>>
                                    <span class="toggle-switch-label">
                                        <span class="toggle-switch-indicator"></span>
                                    </span>
                                </label>
                                <span><?php echo e(translate('messages.active')); ?></span>
                            </div>

                            <form class="my-2 ml-auto mr-sm-2 mr-xl-4 ml-sm-auto flex-grow-1 flex-grow-sm-0">

                                <div class="input--group input-group input-group-merge input-group-flush">
                                    <input id="datatableSearch_" type="search" name="search" class="form-control"
                                          value="<?php echo e(request()?->search  ?? null); ?>"  placeholder="<?php echo e(translate('Ex:_search_by_Restaurant_name_of_Phone_number')); ?>" aria-label="<?php echo e(translate('messages.search')); ?>" required>
                                    <button type="submit" class="btn btn--secondary"><i class="tio-search"></i></button>

                                </div>
                                <!-- End Search -->
                            </form>

                            <!-- Export Button Static -->
                            <div class="hs-unfold ml-3">
                                <a class="js-hs-unfold-invoker btn btn-sm btn-white dropdown-toggle btn export-btn btn-outline-primary btn--primary font--sm" href="javascript:"
                                    data-hs-unfold-options='{
                                        "target": "#usersExportDropdown",
                                        "type": "css-animation"
                                    }'>
                                    <i class="tio-download-to mr-1"></i> <?php echo e(translate('messages.export')); ?>

                                </a>

                                <div id="usersExportDropdown"
                                        class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-sm-right">

                                    <span class="dropdown-header"><?php echo e(translate('messages.download_options')); ?></span>
                                    <a target="__blank" id="export-excel" class="dropdown-item" href="<?php echo e(route('admin.restaurant.restaurants-export', ['type'=>'excel',
                   request()->getQueryString()])); ?>">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                        src="<?php echo e(dynamicAsset('public/assets/admin')); ?>/svg/components/excel.svg"
                                        alt="Image Description">
                                        <?php echo e(translate('messages.excel')); ?>

                                    </a>

                                    <a id="export-excel" class="dropdown-item" href="<?php echo e(route('admin.restaurant.restaurants-export', ['type'=>'csv',request()->getQueryString()])); ?>">
                                        <img class="avatar avatar-xss avatar-4by3 mr-2"
                                                    src="<?php echo e(dynamicAsset('public/assets/admin')); ?>/svg/components/placeholder-csv-format.svg"
                                                    alt="Image Description">
                                                    <?php echo e(translate('messages.csv')); ?>

                                    </a>
                                </div>
                            </div>
                            <!-- Export Button Static -->
                        </div>
                    </div>
                    <!-- Card Header -->

                    <!-- Table -->
                    <div class="table-responsive datatable-custom resturant-list-table">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                 "order": [],
                                 "orderCellsTop": true,
                                 "paging":false

                               }'>
                            <thead class="thead-light">
                            <tr>
                                <th class="text-uppercase w-90px"><?php echo e(translate('messages.sl')); ?></th>
                                <th class="initial-58"><?php echo e(translate('messages.restaurant_info')); ?></th>
                                <th class="w-230px text-center"><?php echo e(translate('messages.owner_info')); ?> </th>
                                <th class="w-130px"><?php echo e(translate('messages.zone')); ?></th>
                                <th class="w-130px"><?php echo e(translate('messages.cuisine')); ?></th>
                                <th class="w-100px"><?php echo e(translate('messages.status')); ?></th>
                                <th class="text-center w-60px"><?php echo e(translate('messages.action')); ?></th>
                            </tr>
                            </thead>

                            <tbody id="set-rows">
                            <?php $__currentLoopData = $restaurants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>$dm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($key+$restaurants->firstItem()); ?></td>
                                    <td>
                                        <a href="<?php echo e(route('admin.restaurant.view', $dm->id)); ?>" alt="view restaurant" class="table-rest-info">
                                        <img class="onerror-image" data-onerror-image="<?php echo e(dynamicAsset('public/assets/admin/img/100x100/food-default-image.png')); ?>"

                                             src="<?php echo e($dm['logo_full_url'] ?? dynamicAsset('public/assets/admin/img/100x100/food-default-image.png')); ?>">
                                            <div class="info">
                                                <span class="d-block text-body">
                                                    <?php echo e(Str::limit($dm->name,20,'...')); ?><br>
                                                    <!-- Rating -->
                                                    <span class="rating">
                                                        <?php if($dm->reviews_count): ?>
                                                        <?php ($reviews_count = $dm->reviews_count); ?>
                                                        <?php ($reviews = 1); ?>
                                                        <?php else: ?>
                                                        <?php ($reviews = 0); ?>
                                                        <?php ($reviews_count = 1); ?>
                                                        <?php endif; ?>
                                                    <i class="tio-star"></i> <?php echo e(round($dm->reviews_sum_rating /$reviews_count,1)); ?>

                                                </span>
                                                    <!-- Rating -->
                                                </span>
                                            </div>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="d-block owner--name text-center">
                                            <?php echo e($dm->vendor->f_name.' '.$dm->vendor->l_name); ?>

                                        </span>
                                        <span class="d-block font-size-sm text-center">
                                            <?php echo e($dm['phone']); ?>

                                        </span>
                                    </td>
                                    <td>
                                        <?php echo e($dm->zone?$dm->zone->name:translate('messages.zone_deleted')); ?>

                                    </td>
                                    <td>

                                        <?php if($dm->cuisine): ?>
                                        <div class="white-space-initial" data-toggle="tooltip" data-placement="bottom" title="<?php $__currentLoopData = $dm->cuisine; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key  => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($c->name); ?><?php echo e(!$loop->last ? ',' : '.'); ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>">
                                            <span  >
                                            <?php $__empty_1 = true; $__currentLoopData = $dm->cuisine; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key  => $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                                <?php echo e($c->name); ?><?php echo e($key == 3 ? ' ....' :  (!$loop->last ? ',' : '.')); ?>

                                                <?php if($key == 3) break; ?>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                                <?php echo e(translate('Cuisine_not_found')); ?>

                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <?php endif; ?>
                                </td>
                                    <td>
                                        <?php if(isset($dm->vendor->status)): ?>
                                            <?php if($dm->vendor->status): ?>
                                            <label class="toggle-switch toggle-switch-sm" for="stocksCheckbox<?php echo e($dm->id); ?>">
                                                <input type="checkbox" data-url="<?php echo e(route('admin.restaurant.status',[$dm->id,$dm->status?0:1])); ?>" data-message="<?php echo e(translate('messages.you_want_to_change_this_restaurant_status')); ?>" class="toggle-switch-input status_change_alert" id="stocksCheckbox<?php echo e($dm->id); ?>" <?php echo e($dm->status?'checked':''); ?>>
                                                <span class="toggle-switch-label">
                                                    <span class="toggle-switch-indicator"></span>
                                                </span>
                                            </label>
                                            <?php else: ?>
                                            <span class="badge badge-soft-danger"><?php echo e(translate('messages.denied')); ?></span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge badge-soft-danger"><?php echo e(translate('messages.not_approved')); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn"
                                                href="<?php echo e(route('admin.restaurant.edit',[$dm['id']])); ?>" title="<?php echo e(translate('messages.edit_restaurant')); ?>"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn btn-sm btn--warning btn-outline-warning action-btn"
                                                href="<?php echo e(route('admin.restaurant.view',[$dm['id']])); ?>" title="<?php echo e(translate('messages.view_restaurant')); ?>"><i class="tio-invisible"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                        <?php if(count($restaurants) === 0): ?>
                        <div class="empty--data">
                            <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/empty.png')); ?>" alt="public">
                            <h5>
                                <?php echo e(translate('no_data_found')); ?>

                            </h5>
                        </div>
                        <?php endif; ?>
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    <?php echo $restaurants->appends(request()->all())->links(); ?>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- End Table -->
                </div>
                <!-- End Card -->
            </div>
        </div>
        <!-- Resturent List -->
    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>
    <script>
        "use strict";
        $('.status_change_alert').on('click', function (event) {
            let url = $(this).data('url');
            let message = $(this).data('message');
            status_change_alert(url, message, event)
        })

        function status_change_alert(url, message, e) {
            e.preventDefault();
            Swal.fire({
                title: '<?php echo e(translate('Are_you_sure?')); ?>',
                text: message,
                type: 'warning',
                showCancelButton: true,
                cancelButtonColor: 'default',
                confirmButtonColor: '#FC6A57',
                cancelButtonText: '<?php echo e(translate('no')); ?>',
                confirmButtonText: '<?php echo e(translate('yes')); ?>',
                reverseButtons: true
            }).then((result) => {
                if (result.value) {
                    location.href=url;
                }
            })
        }
        $(document).on('ready', function () {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function () {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });

            $('#column2_search').on('keyup', function () {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });

            $('#column3_search').on('keyup', function () {
                datatable
                    .columns(3)
                    .search(this.value)
                    .draw();
            });

            $('#column4_search').on('keyup', function () {
                datatable
                    .columns(4)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $('#activeOnlyToggle').on('change', function () {
            let nurl = new URL($(this).data('url'));
            nurl.searchParams.delete('page');
            nurl.searchParams.set('active_only', this.checked ? '1' : '0');
            location.href = nurl;
        });
    </script>

<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/admin-views/vendor/list.blade.php ENDPATH**/ ?>