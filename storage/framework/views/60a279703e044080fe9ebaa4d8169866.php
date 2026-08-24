<?php $__env->startSection('title', translate('Update_restaurant_info')); ?>
<?php $__env->startPush('css_or_js'); ?>
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('/public/assets/admin/css/intlTelInput.css')); ?>" />
    <link href="<?php echo e(dynamicAsset('public/assets/admin/css/tags-input.min.css')); ?>" rel="stylesheet">

<?php $__env->stopPush(); ?>
<?php $__env->startSection('content'); ?>
    <div class="content container-fluid initial-57">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-shop-outlined"></i>
                        <?php echo e(translate('messages.update_restaurant')); ?></h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <?php
        $delivery_time_start =   explode('-',$restaurant->delivery_time)[0] ??  10 ;
        $delivery_time_end =  explode('-',$restaurant->delivery_time)[1] ??  30 ;
        $delivery_time_type =  explode('-',$restaurant->delivery_time)[2] ?? 'min';
    ?>
        <?php ($language=\App\Models\BusinessSetting::where('key','language')->first()); ?>
        <?php ($language = $language->value ?? null); ?>
        <?php ($default_lang = str_replace('_', '-', app()->getLocale())); ?>

        <form action="<?php echo e(route('admin.restaurant.update', [$restaurant['id']])); ?>" method="post"
        class="js-validate" id="res_form" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <?php if(request()->type === 'new_join'): ?>
                            <input type="hidden" name="new_join" value="1">
                        <?php endif; ?>
            <div class="row g-2">
                <div class="col-lg-6">
                    <div class="card shadow--card-2">
                        <div class="card-body">
                            <?php if($language): ?>
                                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                            <ul class="nav nav-tabs mb-4">
                                <li class="nav-item">
                                    <a class="nav-link lang_link active"
                                    href="#"
                                    id="default-link"><?php echo e(translate('Default')); ?></a>
                                </li>
                                <?php $__currentLoopData = json_decode($language); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="nav-item">
                                        <a class="nav-link lang_link"
                                            href="#"
                                            id="<?php echo e($lang); ?>-link"><?php echo e(\App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')'); ?></a>
                                    </li>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </ul>
                                </div>
                            <?php endif; ?>
                            <div class="lang_form" id="default-form">
                                <div class="form-group ">
                                <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.restaurant_name')); ?> (<?php echo e(translate('messages.default')); ?>)</label>
                                    <input type="text" name="name[]" class="form-control"  placeholder="<?php echo e(translate('messages.Ex:_ABC_Company')); ?> " maxlength="191" value="<?php echo e($restaurant?->getRawOriginal('name')); ?>"   >
                                </div>
                                <input type="hidden" name="lang[]" value="default">

                                <div>
                                    <label class="input-label" for="address"><?php echo e(translate('messages.restaurant_address')); ?> (<?php echo e(translate('messages.default')); ?>)</label>
                                    <textarea id="address" name="address[]" class="form-control h-70px" placeholder="<?php echo e(translate('messages.Ex:_House#94,_Road#8,_Abc_City')); ?> "  ><?php echo e($restaurant?->getRawOriginal('address')); ?></textarea>
                                </div>
                            </div>


                                <?php if($language): ?>
                                <?php $__currentLoopData = json_decode($language); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lang): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                                <?php
                                if(count($restaurant['translations'])){
                                    $translate = [];
                                    foreach($restaurant['translations'] as $t){
                                        if($t->locale == $lang && $t->key=="name"){
                                            $translate[$lang]['name'] = $t->value;
                                            }
                                        if($t->locale == $lang && $t->key=="address"){
                                            $translate[$lang]['address'] = $t->value;
                                            }
                                    }
                                    }
                                ?>


                                <div class="d-none lang_form" id="<?php echo e($lang); ?>-form">

                                    <div class="form-group" >
                                        <label class="input-label" for="exampleFormControlInput1"><?php echo e(translate('messages.restaurant_name')); ?> (<?php echo e(strtoupper($lang)); ?>)</label>
                                        <input type="text" name="name[]" class="form-control"  placeholder="<?php echo e(translate('messages.Ex:_ABC_Company')); ?>  maxlength="191" value="<?php echo e($translate[$lang]['name']??''); ?>"  >
                                    </div>
                                    <input type="hidden" name="lang[]" value="<?php echo e($lang); ?>">

                                    <div>
                                        <label class="input-label" for="address"><?php echo e(translate('messages.restaurant_address')); ?> (<?php echo e(strtoupper($lang)); ?>)</label>
                                        <textarea id="address<?php echo e($lang); ?>" name="address[]" class="form-control h-70px" placeholder="<?php echo e(translate('messages.Ex:_House#94,_Road#8,_Abc_City')); ?> "  > <?php echo e($translate[$lang]['address']??''); ?></textarea>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="card shadow--card-2">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-1"><i class="tio-dashboard"></i></span>
                                <span><?php echo e(translate('Restaurant_Logo_&_Covers')); ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap flex-sm-nowrap __gap-12px">
                                <label class="__custom-upload-img mr-lg-5">

                                    <label class="form-label">
                                        <?php echo e(translate('logo')); ?> <span class="text--primary">(<?php echo e(translate('1:1')); ?>)</span>
                                    </label>
                                    <center>
                                        <img class="img--110 min-height-170px min-width-170px onerror-image" id="viewer"
                                             data-onerror-image="<?php echo e(dynamicAsset('public/assets/admin/img/upload.png')); ?>"
                                             src="<?php echo e($restaurant->logo_full_url ?? dynamicAsset('public/assets/admin/img/upload.png')); ?>"
                                             alt="logo image" />
                                    </center>
                                    <input type="file" name="logo" id="customFileEg1" class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*" required>
                                </label>

                                <label class="__custom-upload-img">

                                    <label class="form-label">
                                        <?php echo e(translate('Restaurant_Cover')); ?>  <span class="text--primary">(<?php echo e(translate('2:1')); ?>)</span>
                                    </label>
                                    <center>
                                        <img class="img--vertical min-height-170px min-width-170px onerror-image" id="coverImageViewer"
                                             data-onerror-image="<?php echo e(dynamicAsset('public/assets/admin/img/upload-img.png')); ?>"
                                             src="<?php echo e($restaurant->cover_photo_full_url ?? dynamicAsset('public/assets/admin/img/upload-img.png')); ?>"
                                            alt="Fav icon" />
                                    </center>
                                    <input type="file" name="cover_photo" id="coverImageUpload"  class="custom-file-input"
                                        accept=".jpg, .png, .jpeg, .gif, .bmp, .tif, .tiff|image/*">
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shadow--card-2">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-1"><i class="tio-dashboard"></i></span>
                                <span><?php echo e(translate('Restaurant_Info')); ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="input-label" for="tax"><?php echo e(translate('messages.vat/tax (%)')); ?></label>
                                    <input id="tax" type="number" name="tax" class="form-control h--45px"
                                        placeholder="<?php echo e(translate('messages.Ex:_100')); ?> " min="0" step=".01" required
                                        value="<?php echo e($restaurant->tax); ?>">
                                </div>
                                <div class="col-md-6">
                                    <div class="position-relative">
                                        <label class="input-label" for="tax"><?php echo e(translate('Estimated_Delivery_Time_(_Min_&_Maximum_Time_)')); ?></label>
                                        <input type="text" required id="time_view" value="<?php echo e($delivery_time_start); ?> <?php echo e(translate('messages.to')); ?> <?php echo e($delivery_time_end); ?> <?php echo e($delivery_time_type); ?>"  class="form-control" readonly>
                                        <a href="javascript:void(0)" class="floating-date-toggler">&nbsp;</a>
                                        <span class="offcanvas"></span>
                                        <div class="floating--date" id="floating--date">
                                            <div class="card shadow--card-2">
                                                <div class="card-body">
                                                    <div class="floating--date-inner">
                                                        <div class="item">
                                                            <label class="input-label"
                                                                for="minimum_delivery_time"><?php echo e(translate('Minimum_Time')); ?></label>
                                                            <input id="minimum_delivery_time" type="number" name="minimum_delivery_time" class="form-control h--45px" placeholder="<?php echo e(translate('messages.Ex :')); ?> 30"
                                                                pattern="^[0-9]{2}$" required value="<?php echo e($delivery_time_start); ?>" >
                                                        </div>
                                                        <div class="item">
                                                            <label class="input-label"
                                                                for="maximum_delivery_time"><?php echo e(translate('Maximum_Time')); ?></label>
                                                            <input id="maximum_delivery_time" type="number" name="maximum_delivery_time" class="form-control h--45px" placeholder="<?php echo e(translate('messages.Ex :')); ?> 60"
                                                                pattern="[0-9]{2}" required  value="<?php echo e($delivery_time_end); ?>">
                                                        </div>
                                                        <div class="item smaller">
                                                            <select name="delivery_time_type" id="delivery_time_type" class="custom-select">
                                                                <option value="min" <?php echo e($delivery_time_type=='min'?'selected':''); ?>><?php echo e(translate('messages.minutes')); ?></option>
                                                                <option value="hours" <?php echo e($delivery_time_type=='hours'?'selected':''); ?>><?php echo e(translate('messages.hours')); ?></option>
                                                                
                                                            </select>
                                                        </div>
                                                        <div class="item smaller">
                                                            <button type="button" class="btn btn--primary deliveryTime"><?php echo e(translate('done')); ?></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shadow--card-2">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label" for="cuisine"><?php echo e(translate('messages.cuisine')); ?></label>
                                        <select name="cuisine_ids[]" id="cuisine" class="form-control h--45px min--45 js-select2-custom"
                                        multiple="multiple"  data-placeholder="<?php echo e(translate('messages.select_Cuisine')); ?>" >
                                    </option>
                                    <?php ($cuisine_array = \App\Models\Cuisine::where('status',1 )->get()->toArray()); ?>
                                    <?php ($selected_cuisine =isset($restaurant->cuisine) ? $restaurant->cuisine->pluck('id')->toArray() : []); ?>
                                    <?php $__currentLoopData = $cuisine_array; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cu): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($cu['id']); ?>"
                                            <?php echo e(in_array($cu['id'], $selected_cuisine) ? 'selected' : ''); ?>>
                                            <?php echo e($cu['name']); ?></option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="input-label" for="choice_zones"><?php echo e(translate('messages.zone')); ?>

                                                <span data-toggle="tooltip" data-placement="right" data-original-title="<?php echo e(translate('messages.select_zone_for_map')); ?>"
                                                class="input-label-secondary"><img
                                                    src="<?php echo e(dynamicAsset('/public/assets/admin/img/info-circle.svg')); ?>"
                                                    alt="<?php echo e(translate('messages.restaurant_lat_lng_warning')); ?>"></span>
                                                </label>
                                                <select name="zone_id" id="choice_zones"
                                            data-placeholder="<?php echo e(translate('messages.select_zone')); ?>"
                                            class="form-control h--45px js-select2-custom get_zone_data">
                                            <option value="<?php echo e($restaurant->zone_id); ?>"
                                                selected>
                                                <?php echo e($restaurant->zone->name); ?></option>
                                            <?php $__currentLoopData = \App\Models\Zone::where('status',1 )->get(['id','name']); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $zone): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if(isset(auth('admin')->user()->zone_id)): ?>
                                                    <?php if(auth('admin')->user()->zone_id == $zone->id): ?>

                                                        <option value="<?php echo e($zone->id); ?>"
                                                            <?php echo e($restaurant->zone_id == $zone->id ? 'selected' : ''); ?>>
                                                            <?php echo e($zone->name); ?></option>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?php if($restaurant->zone_id !== $zone->id ): ?>
                                                    <option value="<?php echo e($zone->id); ?>"
                                                        <?php echo e($restaurant->zone_id == $zone->id ? 'selected' : ''); ?>>
                                                        <?php echo e($zone->name); ?></option>
                                                    <?php endif; ?>

                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>



                                    <div class="form-group">
                                        <label class="input-label" for="latitude"><?php echo e(translate('messages.latitude')); ?><span data-toggle="tooltip" data-placement="right" data-original-title="<?php echo e(translate('messages.This_point_marks_the_latitude_of_the_restaurant’s_location_on_the_map')); ?>"
                                                class="input-label-secondary"><img
                                                    src="<?php echo e(dynamicAsset('/public/assets/admin/img/info-circle.svg')); ?>"
                                                    alt="<?php echo e(translate('messages.restaurant_lat_lng_warning')); ?>"></span></label>
                                        <input type="text" id="latitude" name="latitude" class="form-control h--45px disabled"
                                            placeholder="<?php echo e(translate('messages.Ex:_-94.22213')); ?>"  value="<?php echo e($restaurant->latitude); ?>"required readonly>
                                    </div>
                                    <div class="form-group mb-md-0">
                                        <label class="input-label" for="longitude"><?php echo e(translate('messages.longitude')); ?>

                                                <span data-toggle="tooltip" data-placement="right" data-original-title="<?php echo e(translate('messages.This_point_marks_the_longitude_of_the_restaurant’s_location_on_the_map')); ?>"
                                                class="input-label-secondary"><img
                                                    src="<?php echo e(dynamicAsset('/public/assets/admin/img/info-circle.svg')); ?>"
                                                    alt="<?php echo e(translate('messages.restaurant_lat_lng_warning')); ?>"></span>
                                                </label>
                                        <input type="text" name="longitude" class="form-control h--45px disabled" placeholder="<?php echo e(translate('messages.Ex:_103.344322')); ?> "
                                            id="longitude" value="<?php echo e($restaurant->longitude); ?>"  required readonly>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <input id="pac-input" class="controls rounded initial-8" title="<?php echo e(translate('messages.search_your_location_here')); ?>" type="text" placeholder="<?php echo e(translate('messages.search_here')); ?>"/>
                                    <div style="height: 370px !important" id="map"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-12">
                    <div class="card shadow--card-2">
                        <div class="card-header">
                            <h4 class="card-title m-0 d-flex align-items-center"> <span class="card-header-icon mr-2"><i class="tio-user"></i></span> <span><?php echo e(translate('messages.owner_info')); ?></span></h4>
                        </div>
                        <div class="card-body pb-0">
                            <div class="row">
                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="f_name"><?php echo e(translate('messages.first_name')); ?></label>
                                        <input id="f_name" type="text" name="f_name" class="form-control h--45px"
                                            placeholder="<?php echo e(translate('messages.Ex:_Jhon')); ?> "
                                            value="<?php echo e($restaurant->vendor->f_name); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="l_name"><?php echo e(translate('messages.last_name')); ?></label>
                                        <input id="l_name" type="text" name="l_name" class="form-control h--45px"
                                            placeholder="<?php echo e(translate('messages.Ex:_Doe')); ?> "
                                            value="<?php echo e($restaurant->vendor->l_name); ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="phone"><?php echo e(translate('messages.phone')); ?></label>
                                        <input id="phone" type="tel" name="phone" class="form-control h--45px" placeholder="<?php echo e(translate('messages.Ex:_+9XXX-XXX-XXXX')); ?> "
                                        value="<?php echo e($restaurant->phone); ?>"  required>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="col-lg-12">
                    <div class="card shadow--card-2 border-0">
                        <div class="card-header">
                            <h5 class="card-title">
                                <span class="card-header-icon mr-2"><i class="tio-label"></i></span>
                                <span><?php echo e(translate('tags')); ?></span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <input type="text" class="form-control" name="tags"  value="<?php $__currentLoopData = $restaurant->tags; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <?php echo e($c->tag.','); ?> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>" placeholder="Enter tags" data-role="tagsinput">
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card shadow--card-2">
                        <div class="card-header">
                            <h4 class="card-title m-0 d-flex align-items-center"><span class="card-header-icon mr-2"><i class="tio-user"></i></span> <span><?php echo e(translate('messages.account_info')); ?></span></h4>
                        </div>
                        <div class="card-body pb-0">
                            <div class="row">
                                <div class="col-md-4 col-12">
                                    <div class="form-group">
                                        <label class="input-label" for="email"><?php echo e(translate('messages.email')); ?></label>
                                        <input id="email" type="email" name="email" class="form-control h--45px" placeholder="<?php echo e(translate('messages.Ex:_Jhone@company.com')); ?> "
                                        value="<?php echo e($restaurant->email); ?>"
                                            required>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="js-form-message form-group">
                                        <label class="input-label"
                                            for="signupSrPassword"><?php echo e(translate('messages.password')); ?>

                                            <span class="input-label-secondary ps-1" data-toggle="tooltip" title="<?php echo e(translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters')); ?>"><img src="<?php echo e(dynamicAsset('public/assets/admin/img/info-circle.svg')); ?>" alt="<?php echo e(translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters')); ?>"></span>

                                        </label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control h--45px" name="password"
                                                id="signupSrPassword"
                                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="<?php echo e(translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters')); ?>"

                                                placeholder="<?php echo e(translate('messages.Ex:_8+_Character')); ?> "
                                                aria-label="<?php echo e(translate('messages.password_length_8+')); ?>"
                                                 data-msg="Your password is invalid. Please try again."
                                                data-hs-toggle-password-options='{
                                                                                    "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                                                                    "defaultClass": "tio-hidden-outlined",
                                                                                    "showClass": "tio-visible-outlined",
                                                                                    "classChangeTarget": ".js-toggle-passowrd-show-icon-1"
                                                                                    }'>
                                            <div class="js-toggle-password-target-1 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                    <i class="js-toggle-passowrd-show-icon-1 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 col-12">
                                    <div class="js-form-message form-group">
                                        <label class="input-label"
                                            for="signupSrConfirmPassword"><?php echo e(translate('messages.confirm_password')); ?></label>

                                        <div class="input-group input-group-merge">
                                            <input type="password" class="js-toggle-password form-control h--45px" name="confirmPassword"
                                                id="signupSrConfirmPassword"
                                                pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" title="<?php echo e(translate('messages.Must_contain_at_least_one_number_and_one_uppercase_and_lowercase_letter_and_symbol,_and_at_least_8_or_more_characters')); ?>"

                                                placeholder="<?php echo e(translate('messages.Ex:_8+ Character')); ?>"
                                                aria-label="<?php echo e(translate('messages.password_length_8+')); ?>"
                                                 data-msg="Password does not match the confirm password."
                                                data-hs-toggle-password-options='{
                                                                                        "target": [".js-toggle-password-target-1", ".js-toggle-password-target-2"],
                                                                                        "defaultClass": "tio-hidden-outlined",
                                                                                        "showClass": "tio-visible-outlined",
                                                                                        "classChangeTarget": ".js-toggle-passowrd-show-icon-2"
                                                                                        }'>
                                            <div class="js-toggle-password-target-2 input-group-append">
                                                <a class="input-group-text" href="javascript:;">
                                                    <i class="js-toggle-passowrd-show-icon-2 tio-visible-outlined"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="btn--container justify-content-end mt-3">
                <button id="reset_btn" type="button" class="btn btn--reset"><?php echo e(translate('messages.reset')); ?></button>
                <button type="submit" class="btn btn--primary h--45px"><i class="tio-save"></i> <?php echo e(translate('messages.save_information')); ?></button>
            </div>
        </form>

    </div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('script_2'); ?>

    <script src="<?php echo e(dynamicAsset('public/assets/admin/js/spartan-multi-image-picker.js')); ?>"></script>
    <script src="<?php echo e(dynamicAsset('public/assets/admin')); ?>/js/tags-input.min.js"></script>
    <script
    src="https://maps.googleapis.com/maps/api/js?key=<?php echo e(\App\Models\BusinessSetting::where('key', 'map_api_key')->first()->value); ?>&loading=async&libraries=drawing,places&v=3.58&language=<?php echo e(str_replace('_', '-', app()->getLocale())); ?>&callback=initMap&libraries=marker">
</script>
<script>
    "use strict";
    $(document).on('ready', function() {
        $('.offcanvas').on('click', function(){
            $('.offcanvas, .floating--date').removeClass('active')
        })
        $('.floating-date-toggler').on('click', function(){
            $('.offcanvas, .floating--date').toggleClass('active')
        })
    });

    $(function() {
        $("#coba").spartanMultiImagePicker({
            fieldName: 'identity_image[]',
            maxCount: 5,
            rowHeight: '120px',
            groupClassName: 'col-lg-2 col-md-4 col-sm-4 col-6',
            maxFileSize: '',
            placeholderImage: {
                image: '<?php echo e(dynamicAsset('public/assets/admin/img/400x400/img2.jpg')); ?>',
                width: '100%'
            },
            dropFileLabel: "Drop Here",
            onAddRow: function(index, file) {

            },
            onRenderedPreview: function(index) {

            },
            onRemoveRow: function(index) {

            },
            onExtensionErr: function(index, file) {
                toastr.error('<?php echo e(translate('messages.please_only_input_png_or_jpg_type_file')); ?>', {
                    CloseButton: true,
                    ProgressBar: true
                });
            },
            onSizeErr: function(index, file) {
                toastr.error('<?php echo e(translate('messages.file_size_too_big')); ?>', {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        });
    });


        $("#customFileEg1").change(function() {
            readURL(this, 'viewer');
        });

        $("#coverImageUpload").change(function() {
            readURL(this, 'coverImageViewer');
        });
        $('#res_form').on('keyup keypress', function(e) {
            var keyCode = e.keyCode || e.which;
            if (keyCode === 13) {
                e.preventDefault();
                return false;
            }
        });

        let zonePolygon = null;
        let map = null;
        let bounds = null;
        let infoWindow = null;


        function initMap() {

            let myLatlng = {
                lat: <?php echo e($restaurant->latitude); ?>,
                lng: <?php echo e($restaurant->longitude); ?>

            };
            map = new google.maps.Map(document.getElementById("map"), {
                zoom: 10,
                center: myLatlng,
                 mapId: "map"
            });
                zonePolygon = null;
            infoWindow = new google.maps.InfoWindow({
                content: "<?php echo e(translate('Click_the_map_to_get_Lat/Lng!')); ?>",
                position: myLatlng,
            });
            bounds = new google.maps.LatLngBounds();


            new google.maps.marker.AdvancedMarkerElement({
                position: {
                    lat: <?php echo e($restaurant->latitude); ?>,
                    lng: <?php echo e($restaurant->longitude); ?>

                },
                map,
                title: "<?php echo e($restaurant->name); ?>",
            });
            infoWindow.open(map);
            // Create the search box and link it to the UI element.
            const input = document.getElementById("pac-input");
            //console.log(input);
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_CENTER].push(input);
            let markers = [];
            searchBox.addListener("places_changed", () => {
                        const places = searchBox.getPlaces();
                        if (places.length == 0) {
                        return;
                        }
                        // Clear out the old markers.
                        markers.forEach((marker) => {
                        marker.setMap(null);
                        });
                        markers = [];
                        // For each place, get the icon, name and location.
                        const bounds = new google.maps.LatLngBounds();
                        places.forEach((place) => {
                        if (!place.geometry || !place.geometry.location) {
                            console.log("Returned place contains no geometry");
                            return;
                        }
                        const icon = {
                            url: place.icon,
                            size: new google.maps.Size(71, 71),
                            origin: new google.maps.Point(0, 0),
                            anchor: new google.maps.Point(17, 34),
                            scaledSize: new google.maps.Size(25, 25),
                        };
                        // Create a marker for each place.
                        markers.push(
                            new google.maps.Marker({
                            map,
                            icon,
                            title: place.name,
                            position: place.geometry.location,
                            })
                        );
                        if (place.geometry.viewport) {
                            // Only geocodes have viewport.
                            bounds.union(place.geometry.viewport);
                        } else {
                            bounds.extend(place.geometry.location);
                        }
                        });
                        map.fitBounds(bounds);
                    });
        }

    $('.get_zone_data').on('change',function (){
        let id = $(this).val();
        get_zone_data(id);
    })


    function get_zone_data(id){
        $.get({
                url: '<?php echo e(url('/')); ?>/admin/zone/get-coordinates/' + id,
                dataType: 'json',
                success: function(data) {
                    if (zonePolygon) {
                        zonePolygon.setMap(null);
                    }

                    zonePolygon = new google.maps.Polygon({
                        paths: data.coordinates,
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: 'white',
                        fillOpacity: 0,
                    });
                    zonePolygon.setMap(map);
                    // map.setCenter(data.center);


                    bounds = new google.maps.LatLngBounds();
                            zonePolygon.getPaths().forEach(function(path) {
                                path.forEach(function(latlng) {
                                    bounds.extend(latlng);
                                });
                            });
                            map.fitBounds(bounds);


                    google.maps.event.addListener(zonePolygon, 'click', function(mapsMouseEvent) {
                        infoWindow.close();
                        // Create a new InfoWindow.
                        infoWindow = new google.maps.InfoWindow({
                            position: mapsMouseEvent.latLng,
                            content: JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2),
                        });
                        var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null, 2);
                        var coordinates = JSON.parse(coordinates);

                        document.getElementById('latitude').value = coordinates['lat'];
                        document.getElementById('longitude').value = coordinates['lng'];
                        infoWindow.open(map);
                    });
                },
            });
            }

        $(document).on('ready', function() {
            var id = $('#choice_zones').val();
            $.get({
                url: '<?php echo e(url('/')); ?>/admin/zone/get-coordinates/' + id,
                dataType: 'json',
                success: function(data) {
                    if (zonePolygon) {
                        zonePolygon.setMap(null);
                    }
                    zonePolygon = new google.maps.Polygon({
                        paths: data.coordinates,
                        strokeColor: "#FF0000",
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: 'white',
                        fillOpacity: 0,
                    });
                    zonePolygon.setMap(map);
                    zonePolygon.getPaths().forEach(function(path) {
                        path.forEach(function(latlng) {
                            bounds.extend(latlng);
                            map.fitBounds(bounds);
                        });
                    });
                    map.setCenter(data.center);
                    google.maps.event.addListener(zonePolygon, 'click', function(mapsMouseEvent) {
                        infoWindow.close();
                        // Create a new InfoWindow.
                        infoWindow = new google.maps.InfoWindow({
                            position: mapsMouseEvent.latLng,
                            content: JSON.stringify(mapsMouseEvent.latLng.toJSON(),
                                null, 2),
                        });
                        var coordinates = JSON.stringify(mapsMouseEvent.latLng.toJSON(), null,
                            2);
                        var coordinates = JSON.parse(coordinates);

                        document.getElementById('latitude').value = coordinates['lat'];
                        document.getElementById('longitude').value = coordinates['lng'];
                        infoWindow.open(map);
                    });
                },
            });
        });

        $(document).on('ready', function() {
            get_zone_data(<?php echo e($restaurant->zone_id); ?>);
        });
		$("#vendor_form").on('keydown', function(e){
            if (e.keyCode === 13) {
                e.preventDefault();
            }
        })

        $('#reset_btn').click(function(){
            $('#name').val("<?php echo e($restaurant->name); ?>");
            $('#tax').val("<?php echo e($restaurant->tax); ?>");
            $('#address').val("<?php echo e($restaurant->address); ?>");
            $('#minimum_delivery_time').val("<?php echo e(explode('-', $restaurant->delivery_time)[0]); ?>");
            $('#maximum_delivery_time').val("<?php echo e(explode('-', $restaurant->delivery_time)[1]); ?>");
            $('#viewer').attr('src', "<?php echo e(dynamicStorage('storage/app/public/restaurant/' . $restaurant->logo)); ?>");
            $('#customFileEg1').val(null);
            $('#coverImageViewer').attr('src', "<?php echo e(dynamicStorage('storage/app/public/restaurant/cover/' . $restaurant->cover_photo)); ?>");
            $('#coverImageUpload').val(null);
            $('#choice_zones').val(<?php echo e($restaurant->zone_id); ?>).trigger('change');
            $('#f_name').val("<?php echo e($restaurant->vendor->f_name); ?>");
            $('#l_name').val("<?php echo e($restaurant->vendor->l_name); ?>");
            $('#phone').val("<?php echo e($restaurant->phone); ?>");
            $('#email').val("<?php echo e($restaurant->email); ?>");


        })

    $('.deliveryTime').click(function(){
        var min = $("#minimum_delivery_time").val();
        var max = $("#maximum_delivery_time").val();
        var type = $("#delivery_time_type").val();
        $("#floating--date").removeClass('active');
        $("#time_view").val(min+' to '+max+' '+type);

    })
    </script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.admin.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/admin-views/vendor/edit.blade.php ENDPATH**/ ?>