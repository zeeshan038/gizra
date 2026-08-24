<div id="sidebarMain" class="d-none">
    <aside
        class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-bordered">
        <div class="navbar-vertical-container">
            <div class="navbar-brand-wrapper justify-content-between">
                <!-- Logo -->
                <div class="sidebar-logo-container">
                    <?php ($restaurant_data=\App\CentralLogics\Helpers::get_restaurant_data()); ?>
                    <a class="navbar-brand pt-0 pb-0" href="<?php echo e(route('vendor.dashboard')); ?>" aria-label="Front">
                            <img class="navbar-brand-logo"
                            src="<?php echo e($restaurant_data?->logo_full_url); ?>"
                            alt="image">
                            <img class="navbar-brand-logo-mini"
                            src="<?php echo e($restaurant_data?->logo_full_url); ?>"
                            alt="image">

                        <div class="ps-2">
                            <h6>
                                <?php echo e(\Illuminate\Support\Str::limit($restaurant_data->name,15)); ?>

                            </h6>
                        </div>
                    </a>
                    <!-- End Logo -->

                    <!-- Navbar Vertical Toggle -->
                    <button type="button"
                            class="js-navbar-vertical-aside-toggle-invoker navbar-vertical-aside-toggle btn btn-icon btn-xs btn-ghost-dark">
                        <i class="tio-clear tio-lg"></i>
                    </button>
                    <!-- End Navbar Vertical Toggle -->
                </div>
                <div class="navbar-nav-wrap-content-left ml-auto d-none d-xl-block">
                    <!-- Navbar Vertical Toggle -->
                    <button type="button" class="js-navbar-vertical-aside-toggle-invoker close">
                        <i class="tio-first-page navbar-vertical-aside-toggle-short-align" data-toggle="tooltip"
                        data-placement="right" title="Collapse"></i>
                        <i class="tio-last-page navbar-vertical-aside-toggle-full-align"
                        data-template='<div class="tooltip d-none d-sm-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'></i>
                    </button>
                    <!-- End Navbar Vertical Toggle -->
                </div>

            </div>

            <!-- Content -->
            <div class="navbar-vertical-content text-capitalize bg-334257">
                <ul class="navbar-nav navbar-nav-lg nav-tabs">
                    <!-- Dashboards -->
                    <li class="pt-4"></li>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="<?php echo e(route('vendor.dashboard')); ?>" title="<?php echo e(translate('messages.dashboard')); ?>">
                            <i class="tio-home-vs-1-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                <?php echo e(translate('messages.dashboard')); ?>

                            </span>
                        </a>
                    </li>
                    <!-- End Dashboards -->

                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('pos')): ?>
                    <!-- POS -->
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/pos')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="<?php echo e(route('vendor.pos.index')); ?>" title="<?php echo e(translate('POS')); ?>"
                        >
                            <i class="tio-shopping nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('messages.pos')); ?></span>
                        </a>
                    </li>
                    <!-- End POS -->
                    <?php endif; ?>

                    <li class="nav-item">
                        <small class="nav-subtitle"><?php echo e(translate('Listing Manager')); ?></small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>
                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('food')): ?>
                    <li class="nav-item <?php echo e(Request::is('restaurant-panel/listing-manager*')?'active':''); ?>">
                        <a class="nav-link" href="<?php echo e(route('vendor.listing-manager.index')); ?>" title="<?php echo e(translate('Listing Manager')); ?>">
                            <i class="tio-layers-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('Listing Manager')); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <small
                            class="nav-subtitle"><?php echo e(translate('Promotions')); ?></small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>
                    <!-- Campaign -->
                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('campaign')): ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/campaign*')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            title="<?php echo e(translate('Campaign')); ?>">
                            <i class="tio-image nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('Campaign')); ?></span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: <?php echo e(Request::is('restaurant-panel/campaign*') ? 'block' : 'none'); ?>">
                            <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/campaign/list') ? 'active' : ''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.campaign.list')); ?>" title="<?php echo e(translate('messages.basic_campaign')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate text-capitalize"><?php echo e(translate('messages.basic_campaign')); ?></span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/campaign/item/list') ? 'active' : ''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.campaign.itemlist')); ?>" title="<?php echo e(translate('messages.food_campaign')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate text-capitalize"><?php echo e(translate('messages.food_campaign')); ?></span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <?php endif; ?>
                    <!-- End Campaign -->


                <!-- Coupon -->
                <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('coupon')): ?>
                <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/coupon*') ? 'active' : ''); ?>">
                <a class="js-navbar-vertical-aside-menu-link nav-link"
                    href="<?php echo e(route('vendor.coupon.add-new')); ?>"
                    title="<?php echo e(translate('messages.coupons')); ?>">
                    <i class="tio-ticket nav-icon"></i>
                    <span
                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('messages.coupons')); ?></span>
                </a>
                </li>
                <?php endif; ?>
                <!-- End Coupon -->


                <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('coupon')): ?>

                <li class="nav-item">
                    <small
                        class="nav-subtitle"><?php echo e(translate('Advertisement Management')); ?></small>
                    <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                </li>

                <li class="navbar-vertical-aside-has-menu <?php echo $__env->yieldContent('advertisement_create'); ?>">
                <a class="js-navbar-vertical-aside-menu-link nav-link"
                    href="<?php echo e(route('vendor.advertisement.create')); ?>"
                    title="<?php echo e(translate('messages.New_Advertisement')); ?>">
                    <i class="tio-tv-old nav-icon"></i>
                    <span
                        class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('messages.New_Advertisement')); ?></span>
                </a>
                </li>


                <li class="navbar-vertical-aside-has-menu <?php echo $__env->yieldContent('advertisement'); ?>">
                    <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                        href="javascript:" title="<?php echo e(translate('messages.Advertisement_List')); ?>"
                    >
                        <i class="tio-format-bullets nav-icon"></i>
                        <span
                            class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('messages.Advertisement_List')); ?></span>
                    </a>
                    <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                    style="display: <?php echo e(!Request::is('restaurant-panel/advertisement/create*') && Request::is('restaurant-panel/advertisement*')?'block':'none'); ?>">
                        <li class="nav-item <?php echo $__env->yieldContent('advertisement_pending_list'); ?>">
                            <a class="nav-link " href="<?php echo e(route('vendor.advertisement.index',['type'=> 'pending'])); ?>"
                                title="<?php echo e(translate('messages.Pending')); ?>">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate"><?php echo e(translate('messages.Pending')); ?></span>
                            </a>
                        </li>

                        <li class="nav-item <?php echo $__env->yieldContent('advertisement_list'); ?>">
                            <a class="nav-link " href="<?php echo e(route('vendor.advertisement.index')); ?>"
                                title="<?php echo e(translate('messages.Ad_List')); ?>">
                                <span class="tio-circle nav-indicator-icon"></span>
                                <span class="text-truncate"><?php echo e(translate('messages.Ad_List')); ?></span>
                            </a>
                        </li>
                    </ul>
                </li>

                <?php endif; ?>

                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('order')): ?>
                    <li class="nav-item">
                        <small class="nav-subtitle" title="<?php echo e(translate('messages.order_section')); ?>"><?php echo e(translate('messages.order_management')); ?></small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <!-- Order -->
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/order*') && (Request::is('restaurant-panel/order/subscription*') == false ) ?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            title="<?php echo e(translate('messages.orders')); ?>">
                            <i class="tio-shopping-cart nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                <?php echo e(translate('messages.orders')); ?>

                            </span>
                        </a>


                        <?php ($data =0); ?>
                        <?php ($restaurant =\App\CentralLogics\Helpers::get_restaurant_data()); ?>
                        <?php if(($restaurant->restaurant_model == 'subscription' && isset($restaurant->restaurant_sub) && $restaurant->restaurant_sub->self_delivery == 1)  || ($restaurant->restaurant_model == 'commission' && $restaurant->self_delivery_system == 1) ): ?>
                        <?php ($data =1); ?>
                        <?php endif; ?>

                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                        style="display:  <?php echo e(Request::is('restaurant-panel/order*') && (Request::is('restaurant-panel/order/subscription*') == false )?'block':'none'); ?>">
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/all')?'active':''); ?> <?php echo $__env->yieldContent('all_order'); ?> ">
                                <a class="nav-link" href="<?php echo e(route('vendor.order.list',['all'])); ?>" title="<?php echo e(translate('messages.all_order')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.all')); ?>

                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::where('restaurant_id', \App\CentralLogics\Helpers::get_restaurant_id())
                                                ->where(function($query) use($data){
                                                    return $query->whereNotIn('order_status',(config('order_confirmation_model') == 'restaurant'|| $data)?['failed','canceled', 'refund_requested', 'refunded']:['pending','failed','canceled', 'refund_requested', 'refunded'])
                                                    ->orWhere(function($query){
                                                        return $query->where('order_status','pending')->whereIn('order_type', ['take_away','dine_in']);
                                                    });
                                            })->Notpos()->HasSubscriptionToday()->NotDigitalOrder()
                                            ->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/pending')?'active':''); ?> <?php echo $__env->yieldContent('pending'); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.order.list',['pending'])); ?>" title="<?php echo e(translate('messages.pending')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.pending')); ?>

                                            <span class="badge badge-soft-success badge-pill ml-1">
                                            <?php if(config('order_confirmation_model') == 'restaurant' || $data): ?>
                                            <?php echo e(\App\Models\Order::where(['order_status'=>'pending','restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->Notpos()->NotDigitalOrder()->HasSubscriptionToday()->OrderScheduledIn(30)->count()); ?>

                                            <?php else: ?>
                                            <?php echo e(\App\Models\Order::where(['order_status'=>'pending','restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->whereIn('order_type',['take_away','dine_in'])->NotDigitalOrder()->Notpos()->HasSubscriptionToday()->OrderScheduledIn(30)->count()); ?>

                                            <?php endif; ?>
                                        </span>
                                    </span>
                                </a>
                            </li>

                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/confirmed')?'active':''); ?> <?php echo $__env->yieldContent('confirmed'); ?> ">
                                <a class="nav-link " href="<?php echo e(route('vendor.order.list',['confirmed'])); ?>" title="<?php echo e(translate('messages.confirmed')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.confirmed')); ?>

                                            <span class="badge badge-soft-success badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::whereIn('order_status',['confirmed',])->NotDigitalOrder()->Notpos()->whereNotNull('confirmed')->where('restaurant_id', \App\CentralLogics\Helpers::get_restaurant_id())->HasSubscriptionToday()->OrderScheduledIn(30)->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/accepted')?'active':''); ?> <?php echo $__env->yieldContent('accepted'); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.order.list',['accepted'])); ?>"  title="<?php echo e(translate('accepted')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.accepted')); ?>

                                            <span class="badge badge-soft-success badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::whereIn('order_status',['accepted'])->NotDigitalOrder()->hasSubscriptionToday()->where(['restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->Notpos()->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>

                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/cooking')?'active':''); ?> <?php echo $__env->yieldContent('processing'); ?>">
                                <a class="nav-link" href="<?php echo e(route('vendor.order.list',['cooking'])); ?>" title="<?php echo e(translate('messages.cooking')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.cooking')); ?>

                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::where(['order_status'=>'processing', 'restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/ready_for_delivery')?'active':''); ?> <?php echo $__env->yieldContent('handover'); ?>">
                                <a class="nav-link" href="<?php echo e(route('vendor.order.list',['ready_for_delivery'])); ?>" title="<?php echo e(translate('Ready For Delivery')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.ready_for_delivery')); ?>

                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::where(['order_status'=>'handover', 'restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/food_on_the_way')?'active':''); ?> <?php echo $__env->yieldContent('picked_up'); ?>">
                                <a class="nav-link" href="<?php echo e(route('vendor.order.list',['food_on_the_way'])); ?>" title="<?php echo e(translate('Food On The Way')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.food_on_the_way')); ?>

                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::where(['order_status'=>'picked_up', 'restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/delivered')?'active':''); ?> <?php echo $__env->yieldContent('delivered'); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.order.list',['delivered'])); ?>"  title="<?php echo e(translate('Delivered')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.delivered')); ?>

                                            <span class="badge badge-soft-success badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::where(['order_status'=>'delivered','restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/dine_in')?'active':''); ?> <?php echo $__env->yieldContent('dine_in'); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.order.list',['dine_in'])); ?>"  title="<?php echo e(translate('dine_in')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.dine_in')); ?>

                                            <span class="badge badge-soft-success badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::where(['order_type'=>'dine_in','restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/refunded')?'active':''); ?> <?php echo $__env->yieldContent('refunded'); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.order.list',['refunded'])); ?>"  title="<?php echo e(translate('Refunded')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.refunded')); ?>

                                            <span class="badge badge-soft-danger bg-light badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::Refunded()->where(['restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->NotDigitalOrder()->HasSubscriptionToday()->Notpos()->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/refund_requested')?'active':''); ?> <?php echo $__env->yieldContent('refund_requested'); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.order.list',['refund_requested'])); ?>"  title="<?php echo e(translate('refund_requested')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.refund_requested')); ?>

                                            <span class="badge badge-soft-danger bg-light badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::Refund_requested()->NotDigitalOrder()->where(['restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->Notpos()->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/scheduled')?'active':''); ?> <?php echo $__env->yieldContent('scheduled'); ?>">
                                <a class="nav-link" href="<?php echo e(route('vendor.order.list',['scheduled'])); ?>" title="<?php echo e(translate('messages.scheduled')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.scheduled')); ?>

                                        <span class="badge badge-soft-info badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::where('restaurant_id',\App\CentralLogics\Helpers::get_restaurant_id())->NotDigitalOrder()->Notpos()->HasSubscriptionToday()->Scheduled()->where(function($q) use($data){
                                                if(config('order_confirmation_model') == 'restaurant' || $data)
                                                {
                                                    $q->whereNotIn('order_status',['failed','canceled', 'refund_requested', 'refunded']);
                                                }
                                                else
                                                {
                                                    $q->whereNotIn('order_status',['pending','failed','canceled', 'refund_requested', 'refunded'])->orWhere(function($query){
                                                        $query->where('order_status','pending')->whereIn('order_type', ['take_away','dine_in']);
                                                    });
                                                }

                                            })->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>


                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/payment_failed')?'active':''); ?> <?php echo $__env->yieldContent('failed'); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.order.list',['payment_failed'])); ?>"  title="<?php echo e(translate('payment_failed')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.payment_failed')); ?>

                                            <span class="badge badge-soft-success badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::where('order_status','failed')->NotDigitalOrder()->where(['restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->Notpos()->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/order/list/canceled')?'active':''); ?> <?php echo $__env->yieldContent('canceled'); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.order.list',['canceled'])); ?>"  title="<?php echo e(translate('canceled')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate sidebar--badge-container">
                                        <?php echo e(translate('messages.canceled')); ?>

                                            <span class="badge badge-soft-success badge-pill ml-1">
                                            <?php echo e(\App\Models\Order::where('order_status','canceled')->NotDigitalOrder()->where(['restaurant_id'=>\App\CentralLogics\Helpers::get_restaurant_id()])->Notpos()->count()); ?>

                                        </span>
                                    </span>
                                </a>
                            </li>



                        </ul>
                    </li>

                    
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/order/subscription*') ? 'active' : ''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="<?php echo e(route('vendor.order.subscription.index')); ?>" title="<?php echo e(translate('messages.order_subscriptiona')); ?>">
                            <i class="tio-appointment nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                <?php echo e(translate('messages.order')); ?>  <?php echo e(translate('messages.subscription')); ?> </span>
                        </a>
                    </li>
                    

                    <!-- End Order -->
                    <?php endif; ?>
                    <?php if(false): ?>
                    <li class="nav-item">
                        <small
                            class="nav-subtitle"><?php echo e(translate('messages.food_management')); ?></small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>
                    <!-- End AddOn -->
                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('food')): ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/category*')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle"
                            href="javascript:" title="<?php echo e(translate('messages.categories')); ?>"
                        >
                            <i class="tio-category nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('messages.categories')); ?></span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                        style="display: <?php echo e(Request::is('restaurant-panel/category*')?'block':'none'); ?>">
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/category/list')?'active':''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.category.add')); ?>"
                                    title="<?php echo e(translate('messages.category')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate"><?php echo e(translate('messages.category')); ?></span>
                                </a>
                            </li>

                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/category/sub-category-list')?'active':''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.category.add-sub-category')); ?>"
                                    title="<?php echo e(translate('messages.sub_category')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate"><?php echo e(translate('messages.sub_category')); ?></span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <!-- Food -->
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/food*')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:" title="<?php echo e(translate('Food')); ?>"
                        >
                            <i class="tio-premium-outlined nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('messages.foods')); ?></span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                        style="display:<?php echo e(Request::is('restaurant-panel/food*')?'block':'none'); ?>">
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/food/add-new')?'active':''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.food.add-new')); ?>"
                                     title="<?php echo e(translate('Add New Food')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span
                                        class="text-truncate"><?php echo e(translate('messages.add_new')); ?></span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/food/list')?'active':''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.food.list')); ?>"  title="<?php echo e(translate('Food List')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate"><?php echo e(translate('messages.list')); ?></span>
                                </a>
                            </li>
                            <?php if(\App\CentralLogics\Helpers::get_restaurant_data()->food_section): ?>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/food/bulk-import')?'active':''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.food.bulk-import')); ?>"
                                     title="<?php echo e(translate('Bulk Import')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate text-capitalize"><?php echo e(translate('messages.bulk_import')); ?></span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/food/bulk-export')?'active':''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.food.bulk-export-index')); ?>"
                                     title="<?php echo e(translate('Bulk Export')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate text-capitalize"><?php echo e(translate('messages.bulk_export')); ?></span>
                                </a>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <!-- End Food -->
                    <?php endif; ?>
                    <!-- AddOn -->
                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('addon')): ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/addon*')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="<?php echo e(route('vendor.addon.add-new')); ?>" title="<?php echo e(translate('messages.addons')); ?>"
                        >
                            <i class="tio-add-circle-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                <?php echo e(translate('messages.addons')); ?>

                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php endif; ?>

                    <!-- DeliveryMan -->
                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('deliveryman')): ?>
                        <li class="nav-item">
                            <small class="nav-subtitle"
                                   title="<?php echo e(translate('messages.deliveryman_section')); ?>"><?php echo e(translate('messages.deliveryman_management')); ?></small>
                            <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                        </li>
                        <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/delivery-man/add')?'active':''); ?>">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="<?php echo e(route('vendor.delivery-man.add')); ?>"
                               title="<?php echo e(translate('messages.add_delivery_man')); ?>"
                            >
                                <i class="tio-running nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    <?php echo e(translate('messages.add_delivery_man')); ?>

                                </span>
                            </a>
                        </li>

                        <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/delivery-man/list')?'active':''); ?>">
                            <a class="js-navbar-vertical-aside-menu-link nav-link"
                               href="<?php echo e(route('vendor.delivery-man.list')); ?>"
                               title="<?php echo e(translate('messages.deliveryman_list')); ?>"
                            >
                                <i class="tio-filter-list nav-icon"></i>
                                <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                    <?php echo e(translate('messages.deliverymen_list')); ?>

                                </span>
                            </a>
                        </li>

                        
                    <?php endif; ?>
                <!-- End DeliveryMan -->


                    <!-- Business Section-->
                    <li class="nav-item">
                        <small class="nav-subtitle"
                                title="<?php echo e(translate('messages.business_section')); ?>"><?php echo e(translate('messages.business_management')); ?></small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('restaurant_setup')): ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/business-settings/restaurant-setup')?'active':''); ?>">
                        <a class="nav-link " href="<?php echo e(route('vendor.business-settings.restaurant-setup')); ?>" title="<?php echo e(translate('messages.restaurant_config')); ?>"
                        >
                            <span class="tio-settings nav-icon"></span>
                            <span
                                class="text-truncate"><?php echo e(translate('messages.restaurant_config')); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/business-settings/notification-setup')?'active':''); ?>">
                        <a class="nav-link " href="<?php echo e(route('vendor.business-settings.notification-setup')); ?>" title="<?php echo e(translate('messages.notification_setup')); ?>"
                        >
                            <span class="tio-notifications nav-icon"></span>
                            <span
                                class="text-truncate"><?php echo e(translate('messages.notification_setup')); ?></span>
                        </a>
                    </li>

                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('my_shop')): ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/restaurant/view')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="<?php echo e(route('vendor.shop.view')); ?>"
                            title="<?php echo e(translate('My Resturant')); ?>">
                            <i class="tio-home nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                <?php echo e(translate('messages.my_shop')); ?>

                            </span>
                        </a>
                    </li>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/restaurant/qr-view')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="<?php echo e(route('vendor.shop.qr-view')); ?>"
                            title="<?php echo e(translate('My Resturant')); ?>">
                            <i class="tio-qr-code nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                <?php echo e(translate('messages.my_qr_code')); ?>

                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo $__env->yieldContent('subscriberList'); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="<?php echo e(route('vendor.subscriptionackage.subscriberDetail')); ?>"
                            title="<?php echo e(translate('messages.My_Subscription')); ?>">
                            <i class="tio-crown nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                <?php echo e(translate('messages.My_Business_Plan')); ?>

                            </span>
                        </a>
                    </li>




                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('wallet')): ?>
                    <!-- RestaurantWallet -->
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/wallet*')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="<?php echo e(route('vendor.wallet.index')); ?>" title="<?php echo e(translate('messages.my_wallet')); ?>"
                        >
                            <i class="tio-table nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('messages.my_wallet')); ?></span>
                        </a>
                    </li>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/withdraw-method*')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="<?php echo e(route('vendor.wallet-method.index')); ?>" title="<?php echo e(translate('messages.my_wallet')); ?>"
                        >
                            <i class="tio-museum nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('messages.wallet_method')); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <!-- End RestaurantWallet -->
                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('reviews')): ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/reviews')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="<?php echo e(route('vendor.reviews')); ?>" title="<?php echo e(translate('messages.reviews')); ?>"
                        >
                            <i class="tio-star-outlined nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                <?php echo e(translate('messages.reviews')); ?>

                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <!-- End RestaurantWallet -->
                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('chat')): ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/message*')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link"
                            href="<?php echo e(route('vendor.message.list', ['tab' => 'customer'])); ?>" title="<?php echo e(translate('messages.chat')); ?>"
                        >
                            <i class="tio-chat nav-icon"></i>
                            <span class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate">
                                <?php echo e(translate('messages.chat')); ?>

                            </span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <!-- End Business Settings -->
                    <!-- Employee-->
                    <li class="nav-item">
                        <small class="nav-subtitle" title="<?php echo e(translate('messages.Report_section')); ?>"><?php echo e(translate('messages.Report_section')); ?></small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('report')): ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/report/expense-report') ? 'active' : ''); ?>">
                        <a class="nav-link " href="<?php echo e(route('vendor.report.expense-report')); ?>" title="<?php echo e(translate('messages.expense_report')); ?>">
                            <span class="tio-money nav-icon"></span>
                            <span class="text-truncate"><?php echo e(translate('messages.expense_report')); ?></span>
                        </a>
                    </li>


                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/report/transaction-report') ? 'active' : ''); ?>">
                    <a class="nav-link " href="<?php echo e(route('vendor.report.day-wise-report')); ?>"
                        title="<?php echo e(translate('messages.transaction_report')); ?>">
                        <span class="tio-chart-pie-1 nav-icon"></span>
                        <span class="text-truncate"><?php echo e(translate('messages.transaction_report')); ?></span>
                    </a>
                    </li>

                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/report/disbursement-report') ? 'active' : ''); ?>">
                    <a class="nav-link " href="<?php echo e(route('vendor.report.disbursement-report')); ?>"
                        title="<?php echo e(translate('messages.disbursement_report')); ?>">
                        <span class="tio-saving nav-icon"></span>
                        <span class="text-truncate"><?php echo e(translate('messages.disbursement_report')); ?></span>
                    </a>
                    </li>


                    <li class="navbar-vertical-aside-has-menu  <?php echo e(Request::is('restaurant-panel/report/order-report') || Request::is('restaurant-panel/report/campaign-order-report') ? 'active' : ''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                            title="<?php echo e(translate('messages.Order_Report')); ?>">
                            <i class="tio-user nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('messages.Order_Report')); ?></span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                            style="display: <?php echo e(Request::is('restaurant-panel/report/order-report') || Request::is('restaurant-panel/report/campaign-order-report') ? 'block' : 'none'); ?>">
                            <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/report/order-report') ? 'active' : ''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.report.order-report')); ?>" title="<?php echo e(translate('messages.order_report')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate text-capitalize"><?php echo e(translate('messages.Regular_order_report')); ?></span>
                                </a>
                            </li>
                            <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/report/campaign-order-report') ? 'active' : ''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.report.campaign_order-report')); ?>" title="<?php echo e(translate('messages.Campaign_Order_Report')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate text-capitalize"><?php echo e(translate('messages.Campaign_Order_Report')); ?></span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/report/food-wise-report') ? 'active' : ''); ?>">
                        <a class="nav-link " href="<?php echo e(route('vendor.report.food-wise-report')); ?>"
                            title="<?php echo e(translate('messages.food_report')); ?>">
                            <span class="tio-fastfood nav-icon"></span>
                            <span class="text-truncate"><?php echo e(translate('messages.food_report')); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>
                    <!-- Employee-->
                    <li class="nav-item">
                        <small class="nav-subtitle" title="<?php echo e(translate('messages.employee_section')); ?>"><?php echo e(translate('messages.employee_section')); ?></small>
                        <small class="tio-more-horizontal nav-subtitle-replacer"></small>
                    </li>

                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('custom_role')): ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/custom-role*')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link" href="<?php echo e(route('vendor.custom-role.create')); ?>"
                        title="<?php echo e(translate('messages.employee_Role')); ?>">
                            <i class="tio-incognito nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('messages.employee_Role')); ?></span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('employee')): ?>
                    <li class="navbar-vertical-aside-has-menu <?php echo e(Request::is('restaurant-panel/employee*')?'active':''); ?>">
                        <a class="js-navbar-vertical-aside-menu-link nav-link nav-link-toggle" href="javascript:"
                        title="<?php echo e(translate('messages.employees')); ?>">
                            <i class="tio-user nav-icon"></i>
                            <span
                                class="navbar-vertical-aside-mini-mode-hidden-elements text-truncate"><?php echo e(translate('messages.employees')); ?></span>
                        </a>
                        <ul class="js-navbar-vertical-aside-submenu nav nav-sub"
                        style="display: <?php echo e(Request::is('restaurant-panel/employee*')?'block':'none'); ?>">
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/employee/add-new')?'active':''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.employee.add-new')); ?>" title="<?php echo e(translate('messages.add_new_Employee')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate"><?php echo e(translate('messages.add_new_employee')); ?></span>
                                </a>
                            </li>
                            <li class="nav-item <?php echo e(Request::is('restaurant-panel/employee/list')?'active':''); ?>">
                                <a class="nav-link " href="<?php echo e(route('vendor.employee.list')); ?>" title="<?php echo e(translate('messages.Employee_list')); ?>">
                                    <span class="tio-circle nav-indicator-icon"></span>
                                    <span class="text-truncate"><?php echo e(translate('messages.list')); ?></span>
                                </a>
                            </li>

                        </ul>
                    </li>
                    <?php endif; ?>
                    <!-- End Employee -->

                    <li class="nav-item px-20 pb-5">
                        <div class="promo-card">
                            <div class="position-relative">
                                <img src="<?php echo e(dynamicAsset('public/assets/admin/img/promo.png')); ?>" class="mw-100" alt="">
                                <h4 class="mb-2 mt-3"><?php echo e(translate('Want_to_get_highlighted?')); ?></h4>
                                <p class="mb-4">
                                    <?php echo e(translate('Create_ads_to_get_highlighted_on_the_app_and_web_browser')); ?>

                                </p>
                                <a href="<?php echo e(route('vendor.advertisement.create')); ?>" class="btn btn--primary"><?php echo e(translate('Create_Ads')); ?></a>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
            <!-- End Content -->
        </div>
    </aside>
</div>

<div id="sidebarCompact" class="d-none">

</div>



<?php $__env->startPush('script_2'); ?>
<script>
    "use strict";
    $(window).on('load' , function() {
        if($(".navbar-vertical-content li.active").length) {
            $('.navbar-vertical-content').animate({
                scrollTop: $(".navbar-vertical-content li.active").offset().top - 150
            }, 100);
        }
        });
</script>
<?php $__env->stopPush(); ?>
<?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/layouts/vendor/partials/_sidebar.blade.php ENDPATH**/ ?>