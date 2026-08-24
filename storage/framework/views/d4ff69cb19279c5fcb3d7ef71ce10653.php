<div id="headerMain" class="d-none">
    <header id="header"
        class="navbar navbar-expand-lg navbar-fixed navbar-height navbar-flush navbar-container navbar-bordered">
        <div class="navbar-nav-wrap">
            <div class="navbar-brand-wrapper">
                <!-- Logo Div-->
                <?php ($restaurant_logo = \App\CentralLogics\Helpers::get_restaurant_data()?->logo_full_url); ?>
                <a class="navbar-brand" href="<?php echo e(route('vendor.dashboard')); ?>" aria-label="">
                    <img class="navbar-brand-logo logo--design" src="<?php echo e($restaurant_logo); ?>" alt="image">
                    <img class="navbar-brand-logo-mini logo--design" src="<?php echo e($restaurant_logo); ?>" alt="image">
                </a>
                <!-- End Logo -->
            </div>
            <div class="navbar-nav-wrap-content-left ml-auto d--xl-none">
                <!-- Navbar Vertical Toggle -->
                <button type="button" class="js-navbar-vertical-aside-toggle-invoker close">
                    <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                        data-placement="right" title="Collapse"></i>
                    <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                        data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'></i>
                </button>
                <!-- End Navbar Vertical Toggle -->
            </div>






            <!-- Secondary Content -->
            <div class="navbar-nav-wrap-content-right flex-grow-1">
                <!-- Navbar -->
                <ul class="navbar-nav align-items-center flex-row justify-content-end">



                    <li class="nav-item max-sm-m-0">
                        <div class="hs-unfold">
                            <div>
                                <?php ($local = session()->has('vendor_local') ? session('vendor_local') : null); ?>
                                <?php ($lang = \App\CentralLogics\Helpers::getSettingsDataFromConfig(settings: 'system_language')); ?>
                                <?php if($lang): ?>
                                    <div class="topbar-text dropdown disable-autohide text-capitalize d-flex">
                                        <a class="text-dark dropdown-toggle d-flex align-items-center nav-link"
                                            href="#" data-toggle="dropdown">
                                            <?php $__currentLoopData = json_decode($lang['value'], true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($data['code'] == $local): ?>
                                                    <img class="rounded mr-1" width="20"
                                                        src="<?php echo e(dynamicAsset('/public/assets/admin/img/lang.png')); ?>"
                                                        alt="">
                                                    <?php echo e($data['code']); ?>

                                                <?php elseif(!$local && $data['default'] == true): ?>
                                                    <img class="rounded mr-1" width="20"
                                                        src="<?php echo e(dynamicAsset('/public/assets/admin/img/lang.png')); ?>"
                                                        alt="">
                                                    <?php echo e($data['code']); ?>

                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <?php $__currentLoopData = json_decode($lang['value'], true); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $data): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <?php if($data['status'] == 1): ?>
                                                    <li>
                                                        <a class="dropdown-item py-1"
                                                            href="<?php echo e(route('vendor.lang', [$data['code']])); ?>">

                                                            <span class="text-capitalize"><?php echo e($data['code']); ?></span>
                                                        </a>
                                                    </li>
                                                <?php endif; ?>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </li>

                    <li class="nav-item d-none d-sm-inline-block mr-4">
                        <!-- Notification -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-icon btn-soft-secondary rounded-circle"
                                href="<?php echo e(route('vendor.message.list', ['tab' => 'customer'])); ?>">
                                <i class="tio-messages-outlined"></i>
                                <?php (  $message = \App\Models\Conversation::whereUser(\App\CentralLogics\Helpers::get_loggedin_user()->id)->where('unread_message_count', '>', '0')->count()); ?>
                                <?php if($message != 0): ?>
                                    <span class="btn-status btn-sm-status btn-status-danger"></span>
                                <?php endif; ?>
                            </a>
                        </div>
                        <!-- End Notification -->
                    </li>
                    <li class="nav-item">
                        <!-- Notification -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker btn btn-icon navbar--cart btn-soft-secondary rounded-circle"
                                href="<?php echo e(route('vendor.order.list', ['status' => 'pending'])); ?>">
                                <i class="tio-shopping-basket-outlined"></i>
                            </a>
                        </div>
                        <!-- End Notification -->
                    </li>

                    <li class="nav-item nav--item">
                        <!-- Account -->
                        <div class="hs-unfold">
                            <a class="js-hs-unfold-invoker navbar-dropdown-account-wrapper p-0" href="javascript:;"
                                data-hs-unfold-options='{
                                     "target": "#accountNavbarDropdown",
                                     "type": "css-animation"
                                   }'>

                                <div class="cmn--media right-dropdown-icon d-flex align-items-center">
                                    <div class="media-body pl-0 pr-2">
                                        <span class="card-title h5 text-right pr-2">
                                            <?php echo e(\App\CentralLogics\Helpers::get_loggedin_user()->f_name); ?>

                                        </span>
                                        <span
                                            class="card-text card--text"><?php echo e(\App\CentralLogics\Helpers::get_loggedin_user()->email); ?></span>
                                    </div>
                                    <div class="">
                                        <img class="avatar avatar-sm avatar-circle"
                                            src="<?php echo e(\App\CentralLogics\Helpers::get_loggedin_user()?->image_full_url ?? dynamicAsset('public/assets/admin/img/160x160/img1.jpg')); ?>"
                                            alt="image">

                                        <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                    </div>
                                </div>

                            </a>

                            <div id="accountNavbarDropdown"
                                class="hs-unfold-content dropdown-unfold dropdown-menu dropdown-menu-right navbar-dropdown-menu navbar-dropdown-account w-16rem">
                                <div class="dropdown-item-text">
                                    <div class="media cmn--media align-items-center">
                                        <div class="avatar avatar-sm avatar-circle mr-2">
                                            <img class="avatar-img"
                                                src="<?php echo e(\App\CentralLogics\Helpers::get_loggedin_user()?->image_full_url ?? dynamicAsset('public/assets/admin/img/160x160/img1.jpg')); ?>"
                                                alt="image">
                                        </div>
                                        <div class="media-body">
                                            <span
                                                class="card-title h5"><?php echo e(\App\CentralLogics\Helpers::get_loggedin_user()->f_name); ?></span>
                                            <span
                                                class="card-text"><?php echo e(\App\CentralLogics\Helpers::get_loggedin_user()->email); ?></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="<?php echo e(route('vendor.profile.view')); ?>">
                                    <span class="text-truncate pr-2"
                                        title="Settings"><?php echo e(translate('messages.settings')); ?></span>
                                </a>

                                <div class="dropdown-divider"></div>

                                <a class="dropdown-item" href="javascript:"
                                    onclick="Swal.fire({
                                    title: '<?php echo e(translate('Do_You_Want_To_Sign_Out_?')); ?>',
                                    showDenyButton: true,
                                    showCancelButton: true,
                                    confirmButtonColor: '#FC6A57',
                                    cancelButtonColor: '#363636',
                                    confirmButtonText: '<?php echo e(translate('messages.Yes')); ?>',
                                    cancelButtonText: '<?php echo e(translate('messages.cancel')); ?>',
                                    }).then((result) => {
                                    if (result.value) {
                                        location.href='<?php echo e(route('logout')); ?>';
                                    } else{
                                    Swal.fire('<?php echo e(translate('messages.canceled')); ?>', '', 'info')
                                    }
                                    })">
                                    <span class="text-truncate pr-2"
                                        title="Sign out"><?php echo e(translate('messages.sign_out')); ?></span>
                                </a>
                            </div>
                        </div>
                        <!-- End Account -->
                    </li>
                </ul>
                <!-- End Navbar -->
            </div>
            <!-- End Secondary Content -->
        </div>
    </header>
</div>
<div id="headerFluid" class="d-none"></div>
<div id="headerDouble" class="d-none"></div>

<?php
$wallet = \App\Models\RestaurantWallet::where('vendor_id', \App\CentralLogics\Helpers::get_vendor_id())->first();
$Payable_Balance = $wallet?->collected_cash > 0 ? 1 : 0;

$cash_in_hand_overflow = \App\Models\BusinessSetting::where('key', 'cash_in_hand_overflow_restaurant')->first()?->value;
$cash_in_hand_overflow_restaurant_amount = \App\Models\BusinessSetting::where('key', 'cash_in_hand_overflow_restaurant_amount')->first()?->value;
$val = round($cash_in_hand_overflow_restaurant_amount - ($cash_in_hand_overflow_restaurant_amount * 10) / 100, 8);
?>

<?php if($Payable_Balance == 1 && $cash_in_hand_overflow && $wallet?->balance < 0 && $val <= abs($wallet?->collected_cash)): ?>
    <div class="alert __alert-2 alert-warning m-0 py-1 px-2" role="alert">
        <img class="rounded mr-1" width="25"
            src="<?php echo e(dynamicAsset('/public/assets/admin/img/header_warning.png')); ?>" alt="">
        <div class="cont">
            <h4 class="m-0"><?php echo e(translate('Attention_Please')); ?> </h4>
            <?php echo e(translate('The_Cash_in_Hand_amount_is_about_to_exceed_the_limit._Please_pay_the_due_amount._If_the_limit_exceeds,_your_account_will_be_suspended.')); ?>

        </div>
    </div>
<?php endif; ?>

<?php if(
    $Payable_Balance == 1 &&
        $cash_in_hand_overflow &&
        $wallet?->balance < 0 &&
        $cash_in_hand_overflow_restaurant_amount < $wallet?->collected_cash): ?>
    <div class="alert __alert-2 alert-warning m-0 py-1 px-2" role="alert">
        <img class="mr-1" width="25" src="<?php echo e(dynamicAsset('/public/assets/admin/img/header_warning.png')); ?>"
            alt="">
        <div class="cont">
            <h4 class="m-0"><?php echo e(translate('Attention_Please')); ?> </h4>
            <?php echo e(translate('The_Cash_in_Hand_amount_limit_is_exceeded._Your_account_is_now_suspended._Please_pay_the_due_amount_to_receive_new_order_requests_again.')); ?><a
                href="<?php echo e(route('vendor.wallet.index')); ?>" class="alert-link"> &nbsp;
                <?php echo e(translate('Pay_the_due')); ?></a>
        </div>
    </div>
<?php endif; ?>

<?php
$restaurant_data = \App\CentralLogics\Helpers::get_restaurant_data();
$subscription_deadline_warning_days = \App\Models\BusinessSetting::where('key', 'subscription_deadline_warning_days')->first()?->value ?? 7;
$subscription_deadline_warning_message = \App\Models\BusinessSetting::where('key', 'subscription_deadline_warning_message')->first()?->value ?? null;
?>


<div id="hide-subscription-warnings">



    <?php if(
        !in_array($restaurant_data->restaurant_model, ['none', 'commission']) &&
            !Request::is('restaurant-panel/subscription/*')): ?>

        <?php
        $pers = 10;
        if ($restaurant_data?->restaurant_sub) {
            $validity = $restaurant_data?->restaurant_sub?->validity;
            $remaining_days = Carbon\Carbon::now()->diffInDays($restaurant_data?->restaurant_sub?->expiry_date_parsed->format('Y-m-d'), false);
            $pers = $validity - $remaining_days > 0 ? (($validity - $remaining_days) / $validity) * 100 : 1;
            $pers = (439.6 * $pers) / 100;
        }
        ?>
<?php if(
    $restaurant_data?->restaurant_sub?->is_trial == 0 &&
        $restaurant_data?->restaurant_sub?->expiry_date_parsed &&
        $restaurant_data?->restaurant_sub->expiry_date_parsed->subDays($subscription_deadline_warning_days)->isBefore(now()) &&
        Request::is('restaurant-panel')): ?>

    <!--Always in header Renew -->
    <div class="renew-badge mx-3 mt-3" id="renew-badge">
        <div class="renew-content d-flex align-items-center">

            <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/timer.svg')); ?>" alt="">
            <div class="txt">
                <?php echo e($subscription_deadline_warning_message != null ? $subscription_deadline_warning_message : translate('Your subscription ending soon. Please renew to continue access')); ?>

            </div>
        </div>
        <div>
            <a href="<?php echo e(route('vendor.subscriptionackage.subscriberDetail', ['renew_now' => true])); ?>"
                class="btn btn--danger"><?php echo e(translate('Renew')); ?></a>
        </div>
    </div>
<?php elseif(Session::get('subscription_renew_close_btn') !== true &&
        $restaurant_data?->restaurant_sub?->is_trial == 0 &&
        $restaurant_data?->restaurant_sub?->expiry_date_parsed &&
        $restaurant_data?->restaurant_sub->expiry_date_parsed->subDays($subscription_deadline_warning_days)->isBefore(now()) &&
        !Request::is('restaurant-panel')): ?>
    <div class="renew-badge mx-3 mt-3 hide-warning" id="renew-badge">
        <div class="renew-content d-flex align-items-center">

            <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/timer.svg')); ?>" alt="">
            <div class="txt">
                <?php echo e($subscription_deadline_warning_message != null ? $subscription_deadline_warning_message : translate('Your subscription ending soon. Please renew to continue access')); ?>

            </div>
        </div>
        <div>
            <?php if($restaurant_data?->restaurant_sub?->is_canceled == 1): ?>
                <a href="<?php echo e(route('vendor.subscriptionackage.subscriberDetail', ['open_plans' => true])); ?>"
                    class="btn btn--danger"><?php echo e(translate('Change_Subscription')); ?></a>
            <?php else: ?>
                <a href="<?php echo e(route('vendor.subscriptionackage.subscriberDetail', ['renew_now' => true])); ?>"
                    class="btn btn--danger"><?php echo e(translate('Renew')); ?></a>
            <?php endif; ?>
            <button data-id="subscription_renew_close_btn" id="subs-hide-warning"
                class="btn btn-sm btn-primary add-to-session"><?php echo e(translate('remind_me_later')); ?></button>
        </div>
    </div>
    <!-- Renew -->


<?php endif; ?>




        <?php if(Session::get('subscription_free_trial_close_btn') !== true &&
                $restaurant_data?->restaurant_sub?->status == 1 &&
                $restaurant_data?->restaurant_sub?->is_trial == 1 &&
                $restaurant_data?->restaurant_sub?->is_canceled == 0): ?>
            <div class="free-trial trial success-bg">
                <div class="inner-div">
                    <div class="left">
                        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/icon-puck.svg')); ?>" alt="">
                        <div class="left-content">
                            <h6><?php echo e(translate('Get the best experience of your business')); ?></h6>
                            <div><?php echo e(translate('Run your business with the most popular platform')); ?></div>
                        </div>
                    </div>
                    <div class="right">
                        <a href="#" class="btn btn-2">
                            <span class="circle-progress-container">
                                <svg width="40" viewBox="0 0 160 160">
                                    <circle r="70" cx="80" cy="80" fill="transparent"
                                        stroke="#ffffff20" stroke-width="12px"></circle>
                                    <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff"
                                        stroke-width="12px" stroke-dasharray="439.6px"
                                        stroke-dashoffset="<?php echo e($pers); ?>px"></circle>
                                </svg>
                                <?php echo e(1+ Carbon\Carbon::now()->diffInDays($restaurant_data?->restaurant_sub?->expiry_date_parsed->format('Y-m-d'), false)); ?>

                            </span>
                            <?php echo e(translate('Days_left_in_free_trial')); ?>

                        </a>
                        <a href="<?php echo e(route('vendor.subscriptionackage.subscriberDetail', ['open_plans' => true])); ?>"
                            class="btn btn-light"><?php echo e(translate('Choose_Subscription_Plan')); ?> <i
                                class="tio-arrow-forward"></i></a>
                    </div>

                    <button type="button" data-id="subscription_free_trial_close_btn"
                        class="trial-close add-to-session ">
                        <i class="tio-clear-circle"></i>
                    </button>
                </div>
            </div>
        <?php elseif($restaurant_data?->restaurant_sub == null && $restaurant_data?->restaurant_sub_update_application?->is_trial == 1): ?>
            <div class="modal fade show trial-ended-modal" id="free-trial-modal">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body p-0">
                            <div class="trial-ended-modal-wrapper">
                                
                                <div class="trial-ended-modal-content align-self-center">
                                    <h3 class="title"><?php echo e(translate('Your_Free_Trial_Has_Been_Ended')); ?></h3>
                                    <p class="mb-4">
                                        <?php echo e(translate('Purchase a subscription plan or contact with the admin to settle the payment and unblock the access to service.')); ?>

                                    </p>
                                    <a href="<?php echo e(route('vendor.subscriptionackage.subscriberDetail', ['open_plans' => true])); ?>"
                                        class="btn btn--primary"><?php echo e(translate('Choose Subscription Plan')); ?> <i
                                            class="tio-arrow-forward"></i></a>
                                    <div class="blocked-subscription mt-5">
                                        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/WarningOctagon.svg')); ?>"
                                            alt="">
                                        <span><?php echo e(translate('All Access to service has been blocked due to no active subscription')); ?></span>
                                    </div>
                                </div>
                                <div class="trial-ended-modal-img d-none d-md-block">
                                    <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/trial-ended-bg.png')); ?>"
                                        alt="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div class="free-trial trial danger-bg">
                <div class="inner-div">
                    <div class="left">
                        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/timer-2.svg')); ?>" alt="">
                        <div class="left-content">
                            <h6><?php echo e(translate('Free_Trial_Has_Been_Ended')); ?></h6>
                            <div><?php echo e(translate('Get_a_subscription_plan_to_continue_with_your_business')); ?></div>
                        </div>
                    </div>
                    <div class="right">
                        <a href="<?php echo e(route('vendor.subscriptionackage.subscriberDetail', ['open_plans' => true])); ?>"
                            class="btn btn-light"><?php echo e(translate('Choose_Subscription_Plan')); ?> <i
                                class="tio-arrow-forward"></i></a>
                    </div>
                    
                </div>
            </div>
        <?php elseif(Session::get('subscription_cancel_close_btn') !== true &&
                $restaurant_data?->restaurant_sub &&
                $restaurant_data?->restaurant_sub?->is_canceled == 1): ?>
            <div class="free-trial trial danger-bg">
                <div class="inner-div">
                    <div class="left">
                        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/timer-2.svg')); ?>" alt="">
                        <div class="left-content">
                            <h6><?php echo e(translate('Your_Subscription_Has_Been_Cnaceled_by')); ?>

                                <?php echo e($restaurant_data?->restaurant_sub?->canceled_by == 'admin' ? translate($restaurant_data?->restaurant_sub?->canceled_by) : translate('Yourself')); ?>

                            </h6>
                            <div><?php echo e(translate('You_can_not_consume_your_subscription_after')); ?>

                                <?php echo e(\App\CentralLogics\Helpers::date_format($restaurant_data?->restaurant_sub?->expiry_date_parsed)); ?>

                            </div>
                        </div>
                    </div>
                    <div class="right">
                        <a href="#" class="btn btn-2">
                            <span class="circle-progress-container">
                                <svg width="40" viewBox="0 0 160 160">
                                    <circle r="70" cx="80" cy="80" fill="transparent"
                                        stroke="#ffffff20" stroke-width="12px"></circle>
                                    <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff"
                                        stroke-width="12px" stroke-dasharray="439.6px"
                                        stroke-dashoffset="<?php echo e($pers); ?>px"></circle>
                                </svg>
                                <?php echo e(1+ Carbon\Carbon::now()->diffInDays($restaurant_data?->restaurant_sub?->expiry_date_parsed->format('Y-m-d'), false)); ?>

                            </span>
                            <?php echo e(translate('Days_left_in_this_subscription')); ?>

                        </a>
                        <a href="<?php echo e(route('vendor.subscriptionackage.subscriberDetail', ['open_plans' => true])); ?>"
                            class="btn btn-light"><?php echo e(translate('Change_Subscription_Plan')); ?> <i
                                class="tio-arrow-forward"></i></a>
                    </div>

                    <button type="button" data-id="subscription_cancel_close_btn"
                        class="trial-close add-to-session ">
                        <i class="tio-clear-circle"></i>
                    </button>
                </div>
            </div>
        <?php elseif(Session::get('subscription_plan_update_close_btn') !== true &&
                $restaurant_data?->restaurant_sub &&
                $restaurant_data?->restaurant_sub?->package?->status != 1): ?>
            <div class="free-trial trial danger-bg">
                <div class="inner-div">
                    <div class="left">
                        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/timer-2.svg')); ?>" alt="">
                        <div class="left-content">
                            <h6><?php echo e(translate('Your_Current_Subscription_Package_has_been_Disable_By_Admin.')); ?> </h6>
                            <div><?php echo e(translate('You_can_not_renew_this_Package_after')); ?>

                                <?php echo e(\App\CentralLogics\Helpers::date_format($restaurant_data?->restaurant_sub?->expiry_date_parsed)); ?>.
                                <?php echo e(translate('to_continue_your_subscription_please_chose_another_package.')); ?></div>
                        </div>
                    </div>
                    <div class="right">
                        <a href="#" class="btn btn-2">
                            <span class="circle-progress-container">
                                <svg width="40" viewBox="0 0 160 160">
                                    <circle r="70" cx="80" cy="80" fill="transparent"
                                        stroke="#ffffff20" stroke-width="12px"></circle>
                                    <circle r="70" cx="80" cy="80" fill="transparent" stroke="#ffffff"
                                        stroke-width="12px" stroke-dasharray="439.6px"
                                        stroke-dashoffset="<?php echo e($pers); ?>px"></circle>
                                </svg>
                                <?php echo e(1+ Carbon\Carbon::now()->diffInDays($restaurant_data?->restaurant_sub?->expiry_date_parsed->format('Y-m-d'), false)); ?>

                            </span>
                            <?php echo e(translate('Days_left_in_this_subscription')); ?>

                        </a>
                        <a href="<?php echo e(route('vendor.subscriptionackage.subscriberDetail', ['open_plans' => true])); ?>"
                            class="btn btn-light"><?php echo e(translate('Change_Subscription_Plan')); ?> <i
                                class="tio-arrow-forward"></i></a>
                    </div>

                    <button type="button" data-id="subscription_plan_update_close_btn"
                        class="trial-close add-to-session ">
                        <i class="tio-clear-circle"></i>
                    </button>
                </div>
            </div>
        <?php elseif($restaurant_data?->restaurant_model == 'unsubscribed' && !$restaurant_data?->restaurant_sub_update_application ): ?>
            <div class="free-trial trial danger-bg">
                <div class="inner-div">
                    <div class="left">
                        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/timer-2.svg')); ?>" alt="">
                        <div class="left-content">
                            <h6><?php echo e(translate('Your_are_not_subscribed')); ?>

                                
                            </h6>
                            <div>
                                <?php echo e(translate('Purchase a subscription plan or contact with the admin to settle the payment and unblock the access to service')); ?>

                            </div>
                        </div>
                    </div>
                    <div class="right">

                        <a href="<?php echo e(route('vendor.subscriptionackage.subscriberDetail', ['open_plans' => true])); ?>"
                            class="btn btn-light"><?php echo e(translate('Choose Subscription_Plan')); ?> <i
                                class="tio-arrow-forward"></i></a>
                    </div>

                </div>
            </div>

        <?php elseif($restaurant_data?->restaurant_sub == null): ?>
            <div class="free-trial trial danger-bg">
                <div class="inner-div">
                    <div class="left">
                        <img src="<?php echo e(dynamicAsset('/public/assets/admin/img/timer-2.svg')); ?>" alt="">
                        <div class="left-content">
                            <h6><?php echo e(translate('Your_Subscription_Has_Been_Expired_on')); ?>

                                <?php echo e(\App\CentralLogics\Helpers::date_format($restaurant_data?->restaurant_sub_update_application?->expiry_date_parsed)); ?>

                            </h6>
                            <div>
                                <?php echo e(translate('Purchase a subscription plan or contact with the admin to settle the payment and unblock the access to service')); ?>

                            </div>
                        </div>
                    </div>
                    <div class="right">

                        <a href="<?php echo e(route('vendor.subscriptionackage.subscriberDetail', ['open_plans' => true])); ?>"
                            class="btn btn-light"><?php echo e(translate('Change/Renew Subscription_Plan')); ?> <i
                                class="tio-arrow-forward"></i></a>
                    </div>

                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        $(document).on('click', '.add-to-session', function() {
            var session_data = $(this).data("id");
            $.ajax({
                url: '<?php echo e(route('vendor.subscriptionackage.addToSession')); ?>',
                method: 'POST',
                data: {
                    value: session_data,
                    _token: '<?php echo e(csrf_token()); ?>'
                },
                success: function(response) {
                    $('#hide-subscription-warnings').addClass('d-none')
                }
            });
        });
    });
</script>
<?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/layouts/vendor/partials/_header.blade.php ENDPATH**/ ?>