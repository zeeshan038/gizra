@extends('layouts.admin.app')

@section('title', translate('messages.third_party_apis'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <h1 class="page-header-title">
                <span class="page-header-icon">
                    <img src="{{dynamicAsset('public/assets/admin/img/api.png')}}" class="w--26" alt="image">
                </span>
                <span>
                    {{translate('messages.third_party_apis')}}
                </span>
            </h1>
            @include('admin-views.business-settings.partials.third-party-links')
        </div>
        <div class="card">
            @php($map_api_key=\App\Models\BusinessSetting::where(['key'=>'map_api_key'])->first())
            @php($map_api_key=$map_api_key?$map_api_key->value:null)

            @php($map_api_key_server=\App\Models\BusinessSetting::where(['key'=>'map_api_key_server'])->first())
            @php($map_api_key_server=$map_api_key_server?$map_api_key_server->value:null)

            @php($gemini_api_key=\App\Models\BusinessSetting::where(['key'=>'gemini_api_key'])->first())
            @php($gemini_api_key=$gemini_api_key?$gemini_api_key->value:null)

            @php($gemini_ai_enabled=\App\Models\BusinessSetting::where(['key'=>'gemini_ai_enabled'])->first())
            @php($gemini_ai_enabled=$gemini_ai_enabled?$gemini_ai_enabled->value:'1')
            <div class="card-header card-header-shadow border-0 align-items-center">
                <h5 class="card-title align-items-center text--title">
                    {{translate('Google Map API Setup')}}
                </h5>
                <div class="blinkings active lg-top">
                    <i class="tio-info-outined"></i>
                    <div class="business-notes">
                        <h6><img src="{{dynamicAsset('/public/assets/admin/img/notes.png')}}" alt=""> {{translate('Note')}}</h6>
                        <div>
                            {{translate('Without_configuring_this_section_map_functionality_will_not_work_properly._Thus_the_whole_system_will_not_work_as_it_planned')}}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert--primary d-flex" role="alert">
                    <div class="alert--icon">
                        <i class="tio-info"></i>
                    </div>
                    <div>
                        {{translate('messages.map_api_hint_map_api_hint_2')}}
                    </div>
                </div>
                <div class="py-1"></div>
                <form action="{{env('APP_MODE')!='demo'?route('admin.business-settings.config-update'):'javascript:'}}" method="post"
                      enctype="multipart/form-data">
                    @csrf
                    <div class="row gy-3">
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="map_api_key" class="input-label">{{translate('messages.map_api_key')}} ({{translate('messages.client')}})</label>
                                <input type="text" id="map_api_key" placeholder="{{translate('messages.map_api_key')}} ({{translate('messages.client')}})" class="form-control" name="map_api_key"
                                    value="{{env('APP_MODE')!='demo'?$map_api_key??'':''}}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-0">
                                <label for="map_api_key_server" class="input-label">{{translate('messages.map_api_key')}} ({{translate('messages.server')}})</label>
                                <input type="text"  id="map_api_key_server" placeholder="{{translate('messages.map_api_key')}} ({{translate('messages.server')}})" class="form-control" name="map_api_key_server"
                                    value="{{env('APP_MODE')!='demo'?$map_api_key_server??'':''}}" required>
                            </div>
                        </div>
                        <div class="col-md-12 mt-3">
                            <hr>
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="mb-0">{{translate('Google Gemini AI Setup')}} <span class="badge badge-soft-info" style="font-size:11px">Listing Manager AI</span></h5>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="font-size-sm text-muted mr-2">{{translate('Enable AI Feature')}}</span>
                                    <label class="toggle-switch toggle-switch-sm d-flex align-items-center mb-0" style="cursor:pointer">
                                        <input type="hidden" name="gemini_ai_enabled" value="0">
                                        <input type="checkbox" id="gemini_ai_enabled" name="gemini_ai_enabled" value="1"
                                            class="toggle-switch-input"
                                            {{ $gemini_ai_enabled == '1' ? 'checked' : '' }}>
                                        <span class="toggle-switch-label">
                                            <span class="toggle-switch-indicator"></span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group mb-0" id="gemini-key-wrap" style="{{ $gemini_ai_enabled == '1' ? '' : 'opacity:.5;pointer-events:none' }}">
                                <label for="gemini_api_key" class="input-label">{{translate('Gemini API Key')}} <small class="text-muted">({{translate('Get free key at aistudio.google.com/apikey — format: AIzaSy...')}})</small></label>
                                <input type="text" id="gemini_api_key" placeholder="AIzaSy..." class="form-control" name="gemini_api_key"
                                    value="{{env('APP_MODE')!='demo'?$gemini_api_key??'':''}}">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="btn--container justify-content-end">
                                <button type="reset" class="btn btn--reset">{{translate('messages.reset')}}</button>
                                <button type="{{env('APP_MODE')!='demo'?'submit':'button'}}"  class="btn btn--primary call-demo">{{translate('messages.save')}}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('script_2')
<script>
    document.getElementById('gemini_ai_enabled').addEventListener('change', function() {
        var wrap = document.getElementById('gemini-key-wrap');
        wrap.style.opacity = this.checked ? '1' : '0.5';
        wrap.style.pointerEvents = this.checked ? '' : 'none';
    });
</script>
@endpush
