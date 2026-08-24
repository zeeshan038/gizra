@extends('layouts.admin.app')

@section('title',translate('messages.promotional_banner'))


@section('content')
<div class="content container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-sm mb-2 mb-sm-0">
                <h1 class="page-header-title"><i class="tio-edit"></i>{{translate('messages.promotional_banner')}}</h1>
            </div>
        </div>
    </div>

        <div class="col-lg-12 mb-3 mb-lg-12">
            <div class="card h-100">
                <div class="card-body">
                    <form action="{{route('admin.banner.promotional_banner_update')}}" enctype="multipart/form-data" method="post" id="promo_banner_form">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    @php($language=\App\Models\BusinessSetting::where('key','language')->first())
                                    @php($language = $language->value ?? null)
                                    @php($default_lang = str_replace('_', '-', app()->getLocale()))
                                    @if($language)
                                <div class="js-nav-scroller hs-nav-scroller-horizontal">
                                    <ul class="nav nav-tabs mb-4">
                                        <li class="nav-item">
                                            <a class="nav-link lang_link active" href="#"
                                                id="default-link">{{translate('messages.default')}}</a>
                                        </li>
                                        @foreach (json_decode($language) as $lang)
                                        <li class="nav-item">
                                            <a class="nav-link lang_link" href="#" id="{{ $lang }}-link">{{
                                                \App\CentralLogics\Helpers::get_language_name($lang) . '(' .
                                                strtoupper($lang) . ')' }}</a>
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                                    <div class="lang_form" id="default-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="default_title">{{translate('Prompt')}}
                                                ({{translate('messages.default')}})</label>
                                            <textarea name="promotional_banner_title[]" id="default_title" rows="6" class="form-control"
                                                placeholder="{{translate('messages.new_banner')}}"
                                                 >{{ $banner_title?->getRawOriginal('value') ?? null }}</textarea>
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                    @foreach(json_decode($language) as $lang)

                                    <?php
                                                    if($banner_title?->translations){
                                                        $translate = [];
                                                        foreach($banner_title['translations'] as $t)
                                                        {
                                                            if($t->locale == $lang && $t->key=="promotional_banner_title"){
                                                                $translate[$lang]['promotional_banner_title'] = $t->value;
                                                            }
                                                        }
                                                    }
                                                ?>
                                    <div class="d-none lang_form" id="{{$lang}}-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="{{$lang}}_title">{{translate('Prompt')}}
                                                ({{strtoupper($lang)}})</label>
                                            <textarea name="promotional_banner_title[]" id="{{$lang}}_title" rows="6" class="form-control"
                                                placeholder="{{translate('messages.new_banner')}}"
                                                 >{{$translate[$lang]['promotional_banner_title']??''}}</textarea>
                                        </div>
                                        <input type="hidden" name="lang[]" value="{{$lang}}">
                                    </div>
                                    @endforeach
                                    @else
                                    <div id="default-form">
                                        <div class="form-group">
                                            <label class="input-label"
                                                for="exampleFormControlInput1">{{translate('Prompt')}} ({{
                                                translate('messages.default') }})</label>
                                            <textarea name="promotional_banner_title[]" rows="6" class="form-control"
                                                placeholder="{{translate('messages.new_banner')}}"
                                                  >{{$banner_title?->value}}</textarea>
                                        </div>
                                        <input type="hidden" name="lang[]" value="default">
                                    </div>
                                    @endif
                                </div>

                            </div>

                        </div>
                </div>
            </div>
        </div>

        <div class="col-lg-12 mb-3 mb-lg-12">
            <div class="card h-100">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12 d-flex justify-content-between">
                                <span class="d-flex g-1">
                                    <img src="{{dynamicAsset('public/assets/admin/img/other-banner.png')}}" class="h-85"
                                        alt="">
                                </span>
                                <div>
                                    <div class="blinkings">
                                        <div>
                                            <i class="tio-info-outined"></i>
                                        </div>
                                        <div class="business-notes">
                                            <h6><img src="{{dynamicAsset('/public/assets/admin/img/notes.png')}}" alt="">
                                                {{translate('Note')}}</h6>
                                            <div>
                                                {{translate('messages.this_banner_is_only_for_web.')}}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <h3 class="form-label d-block mb-5 text-center">
                                    {{translate('Upload_Banner')}}
                                </h3>
                                <label class="__upload-img aspect-5-1 m-auto d-block">
                                    <div class="img">
                                        <img class="onerror-image" style="object-fit: cover"
                                             src="{{ \App\CentralLogics\Helpers::get_full_url('banner',$banner_image?->value,$banner_image?->storage[0]?->value ?? 'public','upload_image') }}"
                                             data-onerror-image="{{dynamicAsset('/public/assets/admin/img/upload-placeholder.png')}}" alt="">
                                    </div>
                                    <input type="file" name="promotional_banner_image" hidden>
                                </label>

                                <div class="text-center mt-3">
                                    <input type="hidden" id="promo-banner-ai-base64" value="">
                                    <button type="button" id="promo-banner-ai-img-btn" class="btn btn-outline-primary btn-sm mt-1" style="font-size:12px">
                                        ✨ {{ translate('AI Generate Image') }}
                                    </button>
                                </div>

                                <div class="text-center mt-5">
                                    <h3 class="form-label d-block mt-2">
                                        {{translate('Min_Size_for_Better_Resolution_5:1')}}
                                    </h3>
                                    <p>{{translate('image_format_:_jpg_,_png_,_jpeg_|_maximum_size:_2_MB')}}</p>

                                </div>
                            </div>
                        </div>

                    </div>
            </div>

        </div>
        <div class="btn--container justify-content-end mt-3">
            <button type="submit" class="btn btn--primary">{{translate('messages.Save')}}</button>
        </div>
        </form>

    </div>

</div>


@endsection

@push('script_2')
<script>
    $(document).ready(function() {
        "use strict"
        $(".__upload-img, .upload-img-4, .upload-img-2, .upload-img-5, .upload-img-1, .upload-img").each(function(){
            var targetedImage = $(this).find('.img');
            var targetedImageSrc = $(this).find('.img img');
            function proPicURL(input) {
                if (input.files && input.files[0]) {
                    var uploadedFile = new FileReader();
                    uploadedFile.onload = function (e) {
                        targetedImageSrc.attr('src', e.target.result);
                        targetedImage.addClass('image-loaded');
                        targetedImage.hide();
                        targetedImage.fadeIn(650);
                    }
                    uploadedFile.readAsDataURL(input.files[0]);
                }
            }
            $(this).find('input').on('change', function () {
                proPicURL(this);
            })
        })

        // ── AI Banner Image ───────────────────────────────────────────────────
        $('#promo-banner-ai-img-btn').on('click', function () {
            const title = $('textarea[name="promotional_banner_title[]"]:visible').first().val().trim();
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
                    $('#promo-banner-ai-base64').val(res.image);
                    $('.__upload-img .img img').attr('src', res.image);
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

        // Resize/compress an image file down to a max width + JPEG quality before upload.
        // If still over maxBytes after the first pass, step quality down further.
        const MAX_UPLOAD_BYTES = 1.7 * 1024 * 1024; // stay safely under the server's hard 2M upload_max_filesize cutoff
        function compressImageFile(file, maxWidth = 1200, quality = 0.8) {
            function renderAt(q) {
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
                            }, 'image/jpeg', q);
                        };
                        img.onerror = function () { resolve(file); };
                        img.src = e.target.result;
                    };
                    reader.onerror = function () { resolve(file); };
                    reader.readAsDataURL(file);
                });
            }

            return renderAt(quality).then(function tryShrink(blob) {
                if (blob.size <= MAX_UPLOAD_BYTES || quality <= 0.3) {
                    if (blob.size > MAX_UPLOAD_BYTES) {
                        toastr.warning('Image is still over 2MB after compression — uploading anyway, save may be rejected.');
                    }
                    return blob;
                }
                quality -= 0.15;
                return renderAt(quality).then(tryShrink);
            });
        }

        // Submit via ajax (same proven approach as banner add-new): compress the
        // chosen/AI image, build FormData, post it, then reload so the
        // server-side Toastr flash message shows on the fresh page.
        $('#promo_banner_form').on('submit', function (e) {
            e.preventDefault();
            try {
                submitPromoBannerForm(this);
            } catch (err) {
                console.error('promo_banner_form submit failed:', err);
                const diag = 'forms#promo_banner_form=' + document.querySelectorAll('#promo_banner_form').length
                    + ' inputs[name=promotional_banner_image]=' + document.querySelectorAll('input[name="promotional_banner_image"]').length
                    + ' thisFormHasInput=' + (this.querySelector('input[name="promotional_banner_image"]') ? 'yes' : 'no')
                    + ' thisFormOuterHTML.length=' + this.outerHTML.length;
                console.error('DIAG:', diag);
                toastr.error('Save failed before request was sent: ' + (err && err.message ? err.message : err) + ' | ' + diag, { CloseButton: true, ProgressBar: true, timeOut: 15000 });
            }
        });

        function submitPromoBannerForm(formEl) {
            // Look inside the form first, but fall back to a page-wide lookup —
            // theme JS can reparent this input at runtime so it isn't reliably
            // nested inside the form in the live DOM.
            const fileInput = formEl.querySelector('input[name="promotional_banner_image"]')
                || document.querySelector('input[name="promotional_banner_image"]');
            const base64 = $('#promo-banner-ai-base64').val();
            const hasFileInput = !!(fileInput && fileInput.files && fileInput.files.length > 0);
            const submitBtn = $(formEl).find('button[type="submit"]').prop('disabled', true);

            function send(blob, filename) {
                const fd = new FormData(formEl);
                if (blob) {
                    fd.set('promotional_banner_image', blob, filename);
                }
                $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
                $.ajax({
                    url: formEl.action,
                    method: 'POST',
                    data: fd,
                    cache: false,
                    contentType: false,
                    processData: false,
                    success: function () {
                        location.href = '{{ route('admin.banner.promotional_banner') }}';
                    },
                    error: function (xhr) {
                        let msg = 'Save failed';
                        try {
                            const errors = xhr.responseJSON.errors;
                            if (Array.isArray(errors)) {
                                // Helpers::error_processor()-style: [{code, message}, ...]
                                msg = errors.map(function (e) { return typeof e === 'string' ? e : e.message; }).join(' ');
                            } else if (errors && typeof errors === 'object') {
                                // Laravel default validate()-style: {field: [msg, ...]}
                                msg = Object.values(errors).flat().join(' ');
                            } else {
                                msg = xhr.responseJSON.message || msg;
                            }
                        } catch (e) {}
                        toastr.error(msg + (xhr.status ? ' (HTTP ' + xhr.status + ')' : ''), { CloseButton: true, ProgressBar: true });
                        submitBtn.prop('disabled', false);
                    }
                });
            }

            function fail(err) {
                console.error('promo banner image processing failed:', err);
                toastr.error('Image processing failed: ' + (err && err.message ? err.message : err), { CloseButton: true, ProgressBar: true });
                submitBtn.prop('disabled', false);
            }

            if (base64 && !hasFileInput) {
                try {
                    const byteStr = atob(base64.split(',')[1]);
                    const mime = base64.match(/data:([^;]+);/)[1];
                    const ab = new ArrayBuffer(byteStr.length);
                    const ia = new Uint8Array(ab);
                    for (let i = 0; i < byteStr.length; i++) ia[i] = byteStr.charCodeAt(i);
                    const blob = new Blob([ab], { type: mime });
                    const aiFile = new File([blob], 'ai-banner.png', { type: mime });
                    compressImageFile(aiFile).then(function (compressed) {
                        send(compressed, 'ai-banner.jpg');
                    }).catch(fail);
                } catch (err) {
                    fail(err);
                }
            } else if (hasFileInput) {
                compressImageFile(fileInput.files[0]).then(function (compressed) {
                    send(compressed, 'banner.jpg');
                }).catch(fail);
            } else {
                send(null, null);
            }
        }
    });
</script>
@endpush
