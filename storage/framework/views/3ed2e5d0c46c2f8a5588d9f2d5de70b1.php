<!DOCTYPE html>
<?php
    if (env('APP_MODE') == 'demo') {
        $site_direction = session()->get('site_direction_vendor');
    }else{
        $site_direction = session()->has('vendor_site_direction')?session()->get('vendor_site_direction'):'ltr';
    }
    $country=\App\Models\BusinessSetting::where('key','country')->first();
            $countryCode= strtolower($country?$country->value:'auto');
?>
<html dir="<?php echo e($site_direction); ?>" lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>"  class="<?php echo e($site_direction === 'rtl'?'active':''); ?>"><head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <!-- Title -->
    <title><?php echo $__env->yieldContent('title'); ?></title>
    <!-- Favicon -->
    <?php ($logo=\App\Models\BusinessSetting::where(['key'=>'icon'])->first()->value); ?>
    <link rel="shortcut icon" href="">
    <link rel="icon" type="image/x-icon" href="<?php echo e(dynamicStorage('storage/app/public/business/'.$logo??'')); ?>">
    <!-- Font -->
    <link href="<?php echo e(dynamicAsset('public/assets/admin/css/fonts.css')); ?>" rel="stylesheet">
    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('public/assets/admin/css/vendor.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('public/assets/admin/vendor/icon-set/style.css')); ?>">
    <!-- CSS Front Template -->
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('public/assets/admin/css/owl.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('public/assets/admin/css/bootstrap.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('public/assets/admin/css/theme.minc619.css?v=1.0')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('public/assets/admin/css/style.css')); ?>">
    <link  rel="stylesheet" href="<?php echo e(dynamicAsset('/public/assets/admin/plugins/lightbox/css/lightbox.css')); ?>">
    <!-- Provider Panel Update CSS -->
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('public/assets/admin/css/vendor.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('public/assets/admin/intltelinput/css/intlTelInput.css')); ?>">

    <?php echo $__env->yieldPushContent('css_or_js'); ?>

    <script src="<?php echo e(dynamicAsset('public/assets/admin/vendor/hs-navbar-vertical-aside/hs-navbar-vertical-aside-mini-cache.js')); ?>"></script>
    <link rel="stylesheet" href="<?php echo e(dynamicAsset('public/assets/admin/css/toastr.css')); ?>">
</head>

<body class="footer-offset">

    <?php if(env('APP_MODE')=='demo'): ?>
    <div class="direction-toggle">
        <i class="tio-settings"></i>
        <span></span>
    </div>
    <?php endif; ?>

    <div id="pre--loader" class="pre--loader">
    </div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div id="loading" class="initial-hidden">
                <div class="loading--1">
                    <img width="200" src="<?php echo e(dynamicAsset('public/assets/admin/img/loader.gif')); ?>">
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Builder -->
<?php echo $__env->make('layouts.vendor.partials._front-settings', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<!-- End Builder -->

<!-- JS Preview mode only -->
<?php echo $__env->make('layouts.vendor.partials._sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<!-- END ONLY DEV -->

<main id="content" role="main" class="main pointer-event">
<?php echo $__env->make('layouts.vendor.partials._header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    <!-- Content -->
<?php echo $__env->yieldContent('content'); ?>
<!-- End Content -->

    <!-- Footer -->
<?php echo $__env->make('layouts.vendor.partials._footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<!-- End Footer -->

    <div class="modal fade" id="popup-modal">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center">
                                <h2 class="color-8a8a8a">
                                    <i class="tio-shopping-cart-outlined"></i> <?php echo e(translate('messages.You have new order, Check Please.')); ?>

                                </h2>
                                <hr>
                                <button  class="btn btn-primary check-order"><?php echo e(translate('messages.Ok, let me check')); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="popup-modal-msg">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <div class="text-center">
                                <h2 class="8a8a8a">
                                    <i class="tio-messages"></i> <?php echo e(translate('messages.message_description')); ?>

                                </h2>
                                <hr>
                                <button class="btn btn-primary check-message"><?php echo e(translate('messages.Ok, let me check')); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</main>
    <div class="modal fade" id="toggle-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img id="toggle-image" alt="" class="mb-20">
                                <h5 class="modal-title" id="toggle-title"></h5>
                            </div>
                            <div class="text-center" id="toggle-message">
                            </div>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" id="toggle-ok-button" class="btn btn--primary min-w-120 confirm-Toggle" data-dismiss="modal" ><?php echo e(translate('Ok')); ?></button>
                            <button id="reset_btn" type="reset" class="btn btn--cancel min-w-120" data-dismiss="modal">
                                <?php echo e(translate("Cancel")); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="toggle-status-modal">
        <div class="modal-dialog status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img id="toggle-status-image" alt="" class="mb-20">
                                <h5 class="modal-title" id="toggle-status-title"></h5>
                            </div>
                            <div class="text-center" id="toggle-status-message">
                            </div>
                        </div>
                        <div class="btn--container justify-content-center">
                            <button type="button" id="toggle-status-ok-button" class="btn btn--primary min-w-120 confirm-Status-Toggle" data-dismiss="modal" ><?php echo e(translate('Ok')); ?></button>
                            <button id="reset_btn" type="reset" class="btn btn--cancel min-w-120" data-dismiss="modal">
                                <?php echo e(translate("Cancel")); ?>

                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="new-dynamic-submit-model">
        <div class="modal-dialog modal-dialog-centered status-warning-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true" class="tio-clear"></span>
                    </button>
                </div>
                <div class="modal-body pb-5 pt-0">
                    <div class="max-349 mx-auto mb-20">
                        <div>
                            <div class="text-center">
                                <img id="image-src" class="mb-20">
                                <h5 class="modal-title" id="toggle-title"></h5>
                            </div>
                            <div class="text-center" id="toggle-message">
                                <h3 id="modal-title"></h3>
                                <div id="modal-text"></div>
                            </div>

                            </div>
                            <div class="mb-4 d-none" id="note-data">
                                <textarea class="form-control" placeholder="<?php echo e(translate('your_note_here')); ?>" id="get-text-note" cols="5" ></textarea>
                            </div>
                        <div class="btn--container justify-content-center">
                            <div id="hide-buttons">
                                <button data-dismiss="modal" id="cancel_btn_text" class="btn btn-outline-secondary min-w-120" ><?php echo e(translate("Not_Now")); ?></button> &nbsp;
                                <button type="button" id="new-dynamic-ok-button" class="btn btn-outline-danger confirm-model min-w-120"><?php echo e(translate('Yes')); ?></button>
                            </div>

                            <button data-dismiss="modal"  type="button" id="new-dynamic-ok-button-show" class="btn btn--primary  d-none min-w-120"><?php echo e(translate('Okay')); ?></button>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script src="<?php echo e(dynamicAsset('public/assets/admin/js/custom.js')); ?>"></script>
<script src="<?php echo e(dynamicAsset('public/assets/admin/js/jquery.min.js')); ?>"></script>

    <script>
        "use strict";
    setTimeout(hide_loader, 1000);
        function hide_loader(){
        $('#pre--loader').removeClass("pre--loader");;
    }
</script>
<script src="<?php echo e(dynamicAsset('public/assets/admin/js/firebase.min.js')); ?>"></script>

<!-- JS Implementing Plugins -->

<?php echo $__env->yieldPushContent('script'); ?>
<script src="<?php echo e(dynamicAsset('public/assets/admin/js/vendor.min.js')); ?>"></script>
<script src="<?php echo e(dynamicAsset('public/assets/admin/js/theme.min.js')); ?>"></script>
<script src="<?php echo e(dynamicAsset('public/assets/admin/js/sweet_alert.js')); ?>"></script>
<script src="<?php echo e(dynamicAsset('public/assets/admin/js/toastr.js')); ?>"></script>
<script src="<?php echo e(dynamicAsset('public/assets/admin/js/owl.min.js')); ?>"></script>
<script src="<?php echo e(dynamicAsset('/public/assets/admin/plugins/lightbox/js/lightbox.min.js')); ?>"></script>

<script src="<?php echo e(dynamicAsset('public/assets/admin/intltelinput/js/intlTelInput.min.js')); ?>"></script>

<?php echo Toastr::message(); ?>


<?php if($errors->any()): ?>
    <script>
            "use strict";
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        toastr.error('<?php echo e(translate($error)); ?>', Error, {
            CloseButton: true,
            ProgressBar: true
        });
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </script>
<?php endif; ?>

<script>
    "use strict";

    $('.blinkings').on('mouseover', ()=> $('.blinkings').removeClass('active'))
    $('.blinkings').addClass('open-shadow')
    setTimeout(() => {
        $('.blinkings').removeClass('active')
    }, 10000);
    setTimeout(() => {
        $('.blinkings').removeClass('open-shadow')
    }, 5000);

    $(function(){
        var owl = $('.single-item-slider');
        owl.owlCarousel({
            autoplay: false,
            items:1,
            onInitialized  : counter,
            onTranslated : counter,
            autoHeight: true,
            dots: true
        });

        function counter(event) {
            var element   = event.target;         // DOM element, in this example .owl-carousel
                var items     = event.item.count;     // Number of items
                var item      = event.item.index + 1;     // Position of the current item

            // it loop is true then reset counter from 1
            if(item > items) {
                item = item - items
            }
            $('.slide-counter').html(+item+"/"+items)
        }
    });
    $(document).on('ready', function(){
        $(".direction-toggle").on("click", function () {
            if($('html').hasClass('active')){
                $('html').removeClass('active')
                setDirection(1);
            }else {
                setDirection(0);
                $('html').addClass('active')
            }
        });
        if ($('html').attr('dir') == "rtl") {
            $(".direction-toggle").find('span').text('Toggle LTR')
        } else {
            $(".direction-toggle").find('span').text('Toggle RTL')
        }

        function setDirection(status) {
            if (status == 1) {
                $("html").attr('dir', 'ltr');
                $(".direction-toggle").find('span').text('Toggle RTL')
            } else {
                $("html").attr('dir', 'rtl');
                $(".direction-toggle").find('span').text('Toggle LTR')
            }
            $.get({
                    url: '<?php echo e(route('vendor.business-settings.site_direction_vendor')); ?>',
                    dataType: 'json',
                    data: {
                        status: status,
                    },
                    success: function() {
                        alert(ok);
                    },

                });
            }
        });

    $(document).on('ready', function () {
        if (window.localStorage.getItem('hs-builder-popover') === null) {
            $('#builderPopover').popover('show')
                .on('shown.bs.popover', function () {
                    $('.popover').last().addClass('popover-dark')
                });

            $(document).on('click', '#closeBuilderPopover', function () {
                window.localStorage.setItem('hs-builder-popover', true);
                $('#builderPopover').popover('dispose');
            });
        } else {
            $('#builderPopover').on('show.bs.popover', function () {
                return false
            });
        }

        // BUILDER TOGGLE INVOKER
        // =======================================================
        $('.js-navbar-vertical-aside-toggle-invoker').click(function () {
            $('.js-navbar-vertical-aside-toggle-invoker i').tooltip('hide');
        });


        // INITIALIZATION OF NAVBAR VERTICAL NAVIGATION
        // =======================================================
        var sidebar = $('.js-navbar-vertical-aside').hsSideNav();


        // INITIALIZATION OF TOOLTIP IN NAVBAR VERTICAL MENU
        // =======================================================
        $('.js-nav-tooltip-link').tooltip({boundary: 'window'})

        $(".js-nav-tooltip-link").on("show.bs.tooltip", function (e) {
            if (!$("body").hasClass("navbar-vertical-aside-mini-mode")) {
                return false;
            }
        });


        // INITIALIZATION OF UNFOLD
        // =======================================================
        $('.js-hs-unfold-invoker').each(function () {
            var unfold = new HSUnfold($(this)).init();
        });


        // INITIALIZATION OF FORM SEARCH
        // =======================================================
        $('.js-form-search').each(function () {
            new HSFormSearch($(this)).init()
        });


        // INITIALIZATION OF SELECT2
        // =======================================================
        $('.js-select2-custom').each(function () {
            var select2 = $.HSCore.components.HSSelect2.init($(this));
        });


        // INITIALIZATION OF DATERANGEPICKER
        // =======================================================
        $('.js-daterangepicker').daterangepicker();

        $('.js-daterangepicker-times').daterangepicker({
            timePicker: true,
            startDate: moment().startOf('hour'),
            endDate: moment().startOf('hour').add(32, 'hour'),
            locale: {
                format: 'M/DD hh:mm A'
            }
        });

        var start = moment();
        var end = moment();

        function cb(start, end) {
            $('#js-daterangepicker-predefined .js-daterangepicker-predefined-preview').html(start.format('MMM D') + ' - ' + end.format('MMM D, YYYY'));
        }

        $('#js-daterangepicker-predefined').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(start, end);

        $('.js-clipboard').each(function () {
            var clipboard = $.HSCore.components.HSClipboard.init(this);
        });
    });
</script>

<?php echo $__env->yieldPushContent('script_2'); ?>
    <script src="<?php echo e(dynamicAsset('public/assets/admin/js/view-pages/common.js')); ?>"></script>
<audio id="myAudio">
    <source src="<?php echo e(dynamicAsset('public/assets/admin/sound/notification.mp3')); ?>" type="audio/mpeg">
</audio>

<script>
        "use strict";
    let audio = document.getElementById("myAudio");

    function unlockAudio() {
        audio.play().then(function () {
            audio.pause();
            audio.currentTime = 0;
        }).catch(function () {});
        document.removeEventListener('click', unlockAudio);
        document.removeEventListener('keydown', unlockAudio);
    }
    document.addEventListener('click', unlockAudio);
    document.addEventListener('keydown', unlockAudio);

    function playAudio() {
        audio.play().catch(function (err) {
            console.warn('Buzzer blocked by browser autoplay policy:', err);
        });
    }

    function pauseAudio() {
        audio.pause();
    }


    function route_alert(route, message) {
        Swal.fire({
            title: '<?php echo e(translate('messages.Are you sure ?')); ?>',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#FC6A57',
            cancelButtonText: '<?php echo e(translate('messages.No')); ?>',
            confirmButtonText: '<?php echo e(translate('messages.Yes')); ?>',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                location.href = route;
            }
        })
    }

    $('.form-alert').on('click',function (){
        let id = $(this).data('id')
        let message = $(this).data('message')
        Swal.fire({
            title: '<?php echo e(translate('messages.Are you sure?')); ?>',
            text: message,
            type: 'warning',
            showCancelButton: true,
            cancelButtonColor: 'default',
            confirmButtonColor: '#FC6A57',
            cancelButtonText: '<?php echo e(translate('messages.no')); ?>',
            confirmButtonText: '<?php echo e(translate('messages.Yes')); ?>',
            reverseButtons: true
        }).then((result) => {
            if (result.value) {
                $('#'+id).submit()
            }
        })
    })


    <?php ($fcm_credentials = \App\CentralLogics\Helpers::get_business_settings('fcm_credentials')); ?>
    var firebaseConfig = {
        apiKey: "<?php echo e(isset($fcm_credentials['apiKey']) ? $fcm_credentials['apiKey'] : ''); ?>",
        authDomain: "<?php echo e(isset($fcm_credentials['authDomain']) ? $fcm_credentials['authDomain'] : ''); ?>",
        projectId: "<?php echo e(isset($fcm_credentials['projectId']) ? $fcm_credentials['projectId'] : ''); ?>",
        storageBucket: "<?php echo e(isset($fcm_credentials['storageBucket']) ? $fcm_credentials['storageBucket'] : ''); ?>",
        messagingSenderId: "<?php echo e(isset($fcm_credentials['messagingSenderId']) ? $fcm_credentials['messagingSenderId'] : ''); ?>",
        appId: "<?php echo e(isset($fcm_credentials['appId']) ? $fcm_credentials['appId'] : ''); ?>",
        measurementId: "<?php echo e(isset($fcm_credentials['measurementId']) ? $fcm_credentials['measurementId'] : ''); ?>"
    };
    firebase.initializeApp(firebaseConfig);
    const messaging = firebase.messaging();

        function startFCM() {
            messaging
                .requestPermission()
                .then(function() {
                    return messaging.getToken();
                })
                .then(function(token) {
                    // console.log('FCM Token:', token);
                    <?php ($restaurant_id=\App\CentralLogics\Helpers::get_restaurant_id()); ?>
                    // Send the token to your backend to subscribe to topic
                    subscribeTokenToBackend(token, 'restaurant_panel_<?php echo e($restaurant_id); ?>_message');
                }).catch(function(error) {
                console.error('Error getting permission or token:', error);
            });
        }

        function subscribeTokenToBackend(token, topic) {
            fetch('<?php echo e(url('/')); ?>/subscribeToTopic', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                },
                body: JSON.stringify({ token: token, topic: topic })
            }).then(response => {
                if (response.status < 200 || response.status >= 400) {
                    return response.text().then(text => {
                        throw new Error(`Error subscribing to topic: ${response.status} - ${text}`);
                    });
                }
                console.log(`Subscribed to "${topic}"`);
            }).catch(error => {
                console.error('Subscription error:', error);
            });
        }

    

    
    
    
    

    
    
    
    
    
    
    

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    function getUrlParameter(sParam) {
        var sPageURL = window.location.search.substring(1);
        var sURLVariables = sPageURL.split('&');
        for (var i = 0; i < sURLVariables.length; i++) {
            var sParameterName = sURLVariables[i].split('=');
            if (sParameterName[0] === sParam) {
                return sParameterName[1];
            }
        }
    }

    function converationList() {
        var tab = getUrlParameter('tab');
        $.ajax({
            url: "<?php echo e(route('vendor.message.list')); ?>"+ '?tab=' + tab,
            success: function(data) {
                $('#conversation-list').empty();
                $('#admin-conversation-list').empty();
                $("#conversation-list").append(data.html);
                $("#admin-conversation-list").append(data.admin_html);
                var user_id = getUrlParameter('user');
                $('.customer-list').removeClass('conv-active');
                $('#customer-' + user_id).addClass('conv-active');
            }
        })
    }

    function conversationView() {
        var conversation_id = getUrlParameter('conversation');
        var user_id = getUrlParameter('user');
        var url= '<?php echo e(url('/')); ?>/restaurant-panel/message/view/'+conversation_id+'/' + user_id;
        $.ajax({
            url: url,
            success: function(data) {
                $('#view-conversation').html(data.view);
            }
        })
    }
    <?php ($order_notification_type = \App\Models\BusinessSetting::where('key', 'order_notification_type')->first()); ?>
    <?php ($order_notification_type = $order_notification_type ? $order_notification_type->value : 'firebase'); ?>
    var order_type = 'all';
    messaging.onMessage(function (payload) {
        console.log(payload.data);

        if(payload.data.order_id && payload.data.type === 'new_order'){
            <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('order') && $order_notification_type == 'firebase'): ?>
            order_type = payload.data.order_type
            playAudio();
            $('#popup-modal').appendTo("body").modal('show');
            <?php endif; ?>
        }else if(payload.data.type === 'message'){
            var conversation_id = getUrlParameter('conversation');
            var user_id = getUrlParameter('user');
            var url= '<?php echo e(url('/')); ?>/restaurant-panel/message/view/'+conversation_id+'/' + user_id;
            $.ajax({
                url: url,
                success: function(data) {
                    $('#view-conversation').html(data.view);
                }
            })
            toastr.success('<?php echo e(translate('messages.New message arrived')); ?>', {
                        CloseButton: true,
                        ProgressBar: true
                    });

            if($('#conversation-list').scrollTop() == 0){
                converationList();
            }
        }
    });
    <?php if(\App\CentralLogics\Helpers::employee_module_permission_check('order')): ?>
        setInterval(function () {
            $.get({
                url: '<?php echo e(route('vendor.get-restaurant-data')); ?>',
                dataType: 'json',
                success: function (response) {
                    let data = response.data;
                    if (data.new_pending_order > 0) {
                        order_type = 'pending';
                        playAudio();
                        $('#popup-modal').appendTo("body").modal('show');
                    }
                    else if(data.new_confirmed_order > 0)
                    {
                        order_type = 'confirmed';
                        playAudio();
                        $('#popup-modal').appendTo("body").modal('show');
                    }
                },
            });
        }, 10000);
        <?php endif; ?>

        $(document).on('click', '.check-order', function () {
            location.href = '<?php echo e(url('/')); ?>/restaurant-panel/order/list/all';
        });
        $(document).on('click', '.check-message', function () {
            var tab = getUrlParameter('tab');
            location.href = '<?php echo e(route('vendor.message.list')); ?>'+ '?tab=' + tab;
        });

    startFCM();
    converationList();

    if(getUrlParameter('conversation')){
        conversationView();
    }

    $(document).on('click', '.call-demo', function () {
            <?php if(env('APP_MODE') =='demo'): ?>
            toastr.info('<?php echo e(translate('Update option is disabled for demo!')); ?>', {
                CloseButton: true,
                ProgressBar: true
            });
            <?php endif; ?>
        });


    if (/MSIE \d|Trident.*rv:/.test(navigator.userAgent)) document.write('<script src="<?php echo e(dynamicAsset('public/assets/admin')); ?>/vendor/babel-polyfill/polyfill.min.js"><\/script>');

    $(window).on('load', ()=> $('.pre--loader').fadeOut(600))

    $('.log-out').on('click',function (){
        Swal.fire({
        title: '<?php echo e(translate('Do you want to logout?')); ?>',
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonColor: '#FC6A57',
        cancelButtonColor: '#363636',
        confirmButtonText: `<?php echo e(translate('yes')); ?>`,
        cancelButtonText: `<?php echo e(translate('cancel')); ?>`,
        }).then((result) => {
        if (result.value) {
            location.href='<?php echo e(route('logout')); ?>';
            } else{
            Swal.fire('<?php echo e(translate('messages.canceled')); ?>', '', 'info')
            }
        })
    });


    const inputs = document.querySelectorAll('input[type="tel"]');
            inputs.forEach(input => {
                window.intlTelInput(input, {
                    initialCountry: "<?php echo e($countryCode); ?>",
                    utilsScript: "<?php echo e(dynamicAsset('public/assets/admin/intltelinput/js/utils.js')); ?>",
                    autoInsertDialCode: true,
                    nationalMode: false,
                    formatOnDisplay: false,
                    strictMode: true,
                    // allowDropdown: false,
                    <?php if(\App\Models\BusinessSetting::where('key', 'country_picker_status')->first()?->value  != 1): ?>
                    onlyCountries: ["<?php echo e($countryCode); ?>"],
                    <?php endif; ?>
                });
            });


            function keepNumbersAndPlus(inputString) {
                let regex = /[0-9+]/g;
                let filteredString = inputString.match(regex);
            return filteredString ? filteredString.join('') : '';
            }

            $(document).on('keyup', 'input[type="tel"]', function () {
                $(this).val(keepNumbersAndPlus($(this).val()));
                });






</script>


</body>
</html>
<?php /**PATH /Users/zeeshan/Desktop/Desktop/Clients/Gizra/Gizra_full_backup_2026-08-19/deliverymain/resources/views/layouts/vendor/app.blade.php ENDPATH**/ ?>