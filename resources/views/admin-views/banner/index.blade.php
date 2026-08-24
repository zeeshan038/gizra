@extends('layouts.admin.app')

@section('title',translate('Banner'))


@section('content')
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title"><i class="tio-add-circle-outlined"></i> {{translate('messages.add_new_banner')}}</h1>
                </div>
            </div>
        </div>
        <!-- End Page Header -->
        <div class="row gx-2 gx-lg-3">
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-body">
                        <form action="{{route('admin.banner.store')}}" method="post" id="banner_form">
                            @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                            @php($language = $language->value ?? null)
                            @php($default_lang = str_replace('_', '-', app()->getLocale()))
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    @if ($language)
                                    <ul class="nav nav-tabs mb-3 border-0">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active"
                                            href="#"
                                            id="default-link">{{translate('messages.default')}}</a>
                                        </li>
                                        @foreach (json_decode($language) as $lang)
                                            <li class="nav-item">
                                                <a class="nav-link lang_link"
                                                    href="#"
                                                    id="{{ $lang }}-link">{{ \App\CentralLogics\Helpers::get_language_name($lang) . '(' . strtoupper($lang) . ')' }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                    <div class="lang_form" id="default-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="default_title">{{ translate('Prompt') }}
                                                (Default)
                                            </label>
                                            <textarea name="title[]" id="default_title" rows="6"
                                                class="form-control" placeholder="{{ translate('messages.new_banner') }}"
                                                 ></textarea>
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                    @foreach (json_decode($language) as $lang)
                                    <div class="d-none lang_form"
                                        id="{{ $lang }}-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="{{ $lang }}_title">{{ translate('Prompt') }}
                                                ({{ strtoupper($lang) }})
                                            </label>
                                            <textarea name="title[]" id="{{ $lang }}_title" rows="6"
                                                class="form-control" placeholder="{{ translate('messages.new_banner') }}"
                                                 ></textarea>
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{ $lang }}">
                                    </div>
                                @endforeach
                            @else
                            <div id="default-form">
                                <div class="form-group">
                                    <label class="input-label"
                                        for="exampleFormControlInput1">{{ translate('Prompt') }} ({{ translate('messages.default') }})</label>
                                    <textarea name="title[]" rows="6" class="form-control"
                                        placeholder="{{ translate('messages.new_banner') }}" ></textarea>
                                </div>
                                <input type="hidden" name="lang[]" value="default">
                            </div>
                        @endif
                                    <div class="form-group">
                                        <label class="input-label" for="title">{{translate('messages.zone')}}</label>
                                        <select name="zone_id" id="zone" class="form-control js-select2-custom get-request" data-url="{{url('/')}}/admin/food/get-foods?zone_id=" data-id="choice_item">
                                            <option disabled selected value="">---{{translate('messages.select')}}---</option>
                                            @php($zones=\App\Models\Zone::active()->get(['id','name']))
                                            @foreach($zones as $zone)
                                                @if(isset(auth('admin')->user()->zone_id))
                                                    @if(auth('admin')->user()->zone_id == $zone->id)
                                                        <option value="{{$zone->id}}" selected>{{$zone->name}}</option>
                                                    @endif
                                                @else
                                                    <option value="{{$zone['id']}}">{{$zone['name']}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.banner_type')}}</label>
                                        <select name="banner_type" id="banner_type" class="form-control banner_type_change">
                                            <option value="restaurant_wise">{{translate('messages.restaurant_wise')}}</option>
                                            <option value="item_wise">{{translate('messages.food_wise')}}</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="restaurant_wise">
                                        <label class="input-label" for="exampleFormControlSelect1">{{translate('messages.restaurant')}}<span
                                                class="input-label-secondary"></span></label>
                                        <select name="restaurant_id" class="js-data-example-ajax form-control"  title="Select Restaurant">
                                            <option selected disabled>{{ translate('messages.Select') }}</option>
                                        </select>
                                    </div>
                                    <div class="form-group" id="item_wise">
                                        <label class="input-label" for="exampleFormControlInput1">{{translate('messages.select_food')}}</label>
                                        <select name="item_id" id="choice_item" class="form-control js-select2-custom get-foods" placeholder="{{translate('messages.select_food')}}">
                                            <option selected disabled>{{ translate('select_food') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">

                                    <div class="d-flex flex-column align-items-center gap-3">
                                        <p class="mb-0">{{ translate('Banner_image') }}</p>

                                        <div class="image-box banner2">
                                            <label for="image-input" class="d-flex flex-column align-items-center justify-content-center h-100 cursor-pointer gap-2">
                                                <img width="30" class="upload-icon" src="{{dynamicAsset('public/assets/admin/img/upload-icon.png')}}" alt="Upload Icon">
                                                <span class="upload-text">{{ translate('Upload Image')}}</span>
                                                <img src="#" alt="Preview Image" class="preview-image">
                                            </label>
                                            <button type="button" class="delete_image">
                                                <i class="tio-delete"></i>
                                            </button>
                                            <input type="file" id="image-input" name="image" accept="image/*" hidden>
                                        </div>

                                        <input type="hidden" id="banner-ai-base64" value="">

                                        <button type="button" id="banner-ai-img-btn" class="btn btn-outline-primary btn-sm mt-1" style="font-size:12px">
                                            ✨ {{ translate('AI Generate Image') }}
                                        </button>

                                        <p class="opacity-75 max-w220 mx-auto text-center">
                                            {{ translate('Image format - jpg png jpeg gif Image Size -maximum size 2 MB Image Ratio - 3:1')}}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <div class="btn--container justify-content-end">
                                <button id="reset_btn" type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                <button type="submit" class="btn btn--primary">{{translate('messages.submit')}}</button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>

            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">{{translate('messages.banner_list')}}<span class="badge badge-soft-dark ml-2" id="itemCount">{{$banners->count()}}</span></h5>
                        <form id="search-form">
                            @csrf
                            <!-- Search -->
                            <div class="input--group input-group input-group-merge input-group-flush">
                                <input id="datatableSearch" type="search" name="search" class="form-control" placeholder="{{ translate('Ex_:_Search_by_title_...') }}" aria-label="{{translate('messages.search_here')}}">
                                <button type="submit" class="btn btn--secondary">
                                    <i class="tio-search"></i>
                                </button>
                            </div>
                            <!-- End Search -->
                        </form>
                    </div>
                    <!-- Table -->
                    <div class="table-responsive datatable-custom">
                        <table id="columnSearchDatatable"
                               class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                "order": [],
                                "orderCellsTop": true,
                                "search": "#datatableSearch",
                                "entries": "#datatableEntries",
                                "isResponsive": false,
                                "isShowPaging": false,
                                "paging": false,
                               }'>
                            <thead class="thead-light">
                                <tr>
                                    <th>{{ translate('messages.sl') }}</th>
                                    <th>{{translate('messages.title')}}</th>
                                    <th>{{translate('messages.type')}}</th>
                                    <th>{{translate('messages.status')}}</th>
                                    <th class="text-center">{{translate('messages.action')}}</th>
                                </tr>
                            </thead>

                            <tbody id="set-rows">
                            @foreach($banners as $key=>$banner)
                                <tr>
                                    <td>{{$key+$banners->firstItem()}}</td>
                                    <td>
                                        <span class="media align-items-center">
                                                            <img class="avatar avatar-lg mr-3 avatar--3-1 onerror-image" src="{{ $banner['image_full_url'] }}"
                                                                 data-onerror-image="{{dynamicAsset('/public/assets/admin/img/900x400/img1.jpg')}}" alt="{{$banner->name}} image">
                                            <div class="media-body">
                                                <h5 class="text-hover-primary mb-0">{{Str::limit($banner['title'], 25, '...')}}</h5>
                                            </div>
                                        </span>
                                    <span class="d-block font-size-sm text-body">

                                    </span>
                                    </td>
                                    <td>{{translate('messages.'.$banner['type'])}}</td>
                                    <td>
                                        <label class="toggle-switch toggle-switch-sm" for="statusCheckbox{{$banner->id}}">
                                            <input type="checkbox" data-url="{{route('admin.banner.status',[$banner['id'],$banner->status?0:1])}}" class="toggle-switch-input redirect-url" id="statusCheckbox{{$banner->id}}" {{$banner->status?'checked':''}}>
                                            <span class="toggle-switch-label">
                                                <span class="toggle-switch-indicator"></span>
                                            </span>
                                        </label>
                                    </td>
                                    <td>
                                        <div class="btn--container justify-content-center">
                                            <a class="btn btn-sm btn--primary btn-outline-primary action-btn" href="{{route('admin.banner.edit',[$banner['id']])}}"title="{{translate('messages.edit_banner')}}"><i class="tio-edit"></i>
                                            </a>
                                            <a class="btn btn-sm btn--danger btn-outline-danger action-btn form-alert" href="javascript:" data-id="banner-{{$banner['id']}}" data-message="{{translate('messages.Want_to_delete_this_banner')}}" title="{{translate('messages.delete_banner')}}"><i class="tio-delete-outlined"></i>
                                            </a>
                                            <form action="{{route('admin.banner.delete',[$banner['id']])}}"
                                                        method="post" id="banner-{{$banner['id']}}">
                                                    @csrf @method('delete')
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                        @if(count($banners) === 0)
                        <div class="empty--data">
                            <img src="{{dynamicAsset('/public/assets/admin/img/empty.png')}}" alt="public">
                            <h5>
                                {{translate('messages.no_data_found')}}
                            </h5>
                        </div>
                        @endif
                        <div class="page-area px-4 pb-3">
                            <div class="d-flex align-items-center justify-content-end">
                                <div>
                                    {!! $banners->links() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Table -->
        </div>
    </div>
@endsection

@push('script_2')
<script src="{{dynamicAsset('public/assets/admin')}}/js/view-pages/banner-index.js"></script>
<script>
"use strict";
let zone_id ={{ auth('admin')?->user()?->zone_id ? auth('admin')->user()->zone_id : '[]' }} ;
$(document).on('ready', function () {
        let select_control = $('#banner_type, #restaurant_wise select, #item_wise select');
        $('#zone').on('change', function(){
            if($(this).val())
            {
                zone_id = $(this).val();
            }
            else
            {
                zone_id = [];
            }
            if($('#zone').val() == undefined) {
                select_control.attr('disabled', '')
            } else {
                select_control.removeAttr('disabled')
            }
        });
        if($('#zone').val() == undefined) {
            select_control.attr('disabled', '')
        } else {
            select_control.removeAttr('disabled')
        }

        $('.js-data-example-ajax').select2({

            ajax: {
                url: '{{url('/')}}/admin/restaurant/get-restaurants',
                data: function (params) {
                    return {
                        q: params.term, // search term
                        zone_ids: [zone_id],
                        page: params.page
                    };
                },
                processResults: function (data) {
                    return {
                    results: data
                    };
                },
                __port: function (params, success, failure) {
                    let $request = $.ajax(params);

                    $request.then(success);
                    $request.fail(failure);

                    return $request;
                }
            }
        });
            // INITIALIZATION OF DATATABLES
            // =======================================================
            let datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'), {
                select: {
                    style: 'multi',
                    classMap: {
                        checkAll: '#datatableCheckAll',
                        counter: '#datatableCounter',
                        counterInfo: '#datatableCounterInfo'
                    }
                },
                language: {
                    zeroRecords: '<div class="text-center p-4">' +
                    '<img class="w-7rem mb-3" src="{{dynamicAsset('public/assets/admin/svg/illustrations/sorry.svg')}}" alt="Image Description">' +
                    '<p class="mb-0">{{ translate('messages.No_data_to_show') }}</p>' +
                    '</div>'
                }
            });

            $('#datatableSearch').on('mouseup', function (e) {
                let $input = $(this),
                    oldValue = $input.val();

                if (oldValue == "") return;

                setTimeout(function(){
                    let newValue = $input.val();

                    if (newValue == ""){
                    // Gotcha
                    datatable.search('').draw();
                    }
                }, 1);
            });

            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function () {
                let select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });
        $('#item_wise').hide();
        $('.banner_type_change').on('change', function (){
            let order_type = $(this).val();
            banner_type_change(order_type);
        })
        function banner_type_change(order_type) {
           if(order_type=='item_wise')
            {
                $('#restaurant_wise').hide();
                $('#item_wise').show();
                let route ='{{url('/')}}/admin/food/get-foods?zone_id='+zone_id;
                let id ='choice_item';

            $.get({
                url: route,
                dataType: 'json',
                success: function (data) {
                    $('#' + id).empty().append(data.options);
                },
            });


            }
            else if(order_type=='restaurant_wise')
            {
                $('#restaurant_wise').show();
                $('#item_wise').hide();
            }
            else{
                $('#item_wise').hide();
                $('#restaurant_wise').hide();
            }
        }

        $('#banner_form').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.banner.store')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                    } else {
                        toastr.success('{{ translate('messages.Banner_uploaded_successfully!') }}', {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        setTimeout(function () {
                            location.href = '{{route('admin.banner.add-new')}}';
                        }, 2000);
                    }
                }
            });
        });

        $('#search-form').on('submit', function (e) {
            e.preventDefault();
            let formData = new FormData(this);
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: '{{route('admin.banner.search')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $('#loading').show();
                },
                success: function (data) {
                    $('#set-rows').html(data.view);
                    $('#itemCount').html(data.count);
                    $('.page-area').hide();
                },
                complete: function () {
                    $('#loading').hide();
                },
            });
        });

        $('#reset_btn').click(function(){
            $('#zone').val(null).trigger('change');
            $('#choice_item').val(null).trigger('change');
            $('#viewer').attr('src','{{dynamicAsset('public/assets/admin/img/900x400/img1.jpg')}}');
            $('#banner-ai-base64').val('');
        });

        // ── AI Banner Image ───────────────────────────────────────────────────
        let bannerAiBase64 = null;

        $('#banner-ai-img-btn').on('click', function () {
            const title = $('textarea[name="title[]"]:visible').first().val().trim();
            if (!title) {
                toastr.warning('{{ translate('messages.enter_title_first') }}');
                return;
            }
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="tio-sync" style="animation:spin 1s linear infinite"></i> {{ translate('messages.generating') }}...');

            $.ajax({
                url: '{{ route('admin.banner.ai-image') }}',
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { title: title },
                success: function (res) {
                    if (res.error) { toastr.error(res.error); return; }
                    bannerAiBase64 = res.image;
                    $('#banner-ai-base64').val(res.image);
                    // Show preview in the image box
                    $('.preview-image').attr('src', res.image).show();
                    $('.upload-icon, .upload-text').hide();
                    toastr.success('{{ translate('messages.ai_image_generated') }}');
                },
                error: function (xhr) {
                    let msg = '{{ translate('messages.ai_image_failed') }}';
                    try { msg = xhr.responseJSON.error || msg; } catch (e) {}
                    toastr.error(msg);
                },
                complete: function () {
                    btn.prop('disabled', false).html('✨ {{ translate('AI Generate Image') }}');
                }
            });
        });

        // Resize/compress an image file down to a max width + JPEG quality before upload
        function compressImageFile(file, maxWidth = 1200, quality = 0.8) {
            return new Promise(function (resolve) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = new Image();
                    img.onload = function () {
                        let w = img.width, h = img.height;
                        if (w > maxWidth) {
                            h = Math.round(h * (maxWidth / w));
                            w = maxWidth;
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = w;
                        canvas.height = h;
                        canvas.getContext('2d').drawImage(img, 0, 0, w, h);
                        canvas.toBlob(function (blob) {
                            resolve(blob || file);
                        }, 'image/jpeg', quality);
                    };
                    img.onerror = function () { resolve(file); };
                    img.src = e.target.result;
                };
                reader.onerror = function () { resolve(file); };
                reader.readAsDataURL(file);
            });
        }

        // Inject AI base64 (or the manually chosen file) into FormData, compressed, on banner form submit
        $('#banner_form').off('submit').on('submit', function (e) {
            e.preventDefault();
            const formEl = this;
            const base64 = $('#banner-ai-base64').val();
            const fileInput = $('#image-input')[0];
            const hasFileInput = fileInput.files.length > 0;
            const submitBtn = $(formEl).find('button[type="submit"]').prop('disabled', true);

            function finish(compressedBlob, filename) {
                const fd = new FormData(formEl);
                fd.set('image', compressedBlob, filename);
                submitBannerForm(fd, submitBtn);
            }

            if (base64 && !hasFileInput) {
                // Convert base64 to Blob first, then compress
                const byteStr = atob(base64.split(',')[1]);
                const mime = base64.match(/data:([^;]+);/)[1];
                const ab = new ArrayBuffer(byteStr.length);
                const ia = new Uint8Array(ab);
                for (let i = 0; i < byteStr.length; i++) ia[i] = byteStr.charCodeAt(i);
                const blob = new Blob([ab], { type: mime });
                const aiFile = new File([blob], 'ai-banner.png', { type: mime });
                compressImageFile(aiFile).then(function (compressed) {
                    finish(compressed, 'ai-banner.jpg');
                });
            } else if (hasFileInput) {
                compressImageFile(fileInput.files[0]).then(function (compressed) {
                    finish(compressed, 'banner.jpg');
                });
            } else {
                submitBannerForm(new FormData(formEl), submitBtn);
            }
        });

        function submitBannerForm(formData, submitBtn) {
            $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
            $.post({
                url: '{{route('admin.banner.store')}}',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function (data) {
                    if (data.errors) {
                        for (let i = 0; i < data.errors.length; i++) {
                            toastr.error(data.errors[i].message, { CloseButton: true, ProgressBar: true });
                        }
                    } else {
                        toastr.success('{{ translate('messages.Banner_uploaded_successfully!') }}', { CloseButton: true, ProgressBar: true });
                        setTimeout(function () { location.href = '{{route('admin.banner.add-new')}}'; }, 2000);
                    }
                },
                error: function (xhr) {
                    let msg = 'Upload failed';
                    try { msg = xhr.responseJSON.message || xhr.responseJSON.error || msg; } catch (e) {}
                    toastr.error(msg + (xhr.status ? ' (HTTP ' + xhr.status + ')' : ''), { CloseButton: true, ProgressBar: true });
                },
                complete: function () {
                    if (submitBtn) submitBtn.prop('disabled', false);
                }
            });
        }
    </script>
@endpush
