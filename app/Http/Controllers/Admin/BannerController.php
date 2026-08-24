<?php

namespace App\Http\Controllers\Admin;

use App\Models\Banner;
use App\Models\DataSetting;
use App\Models\Translation;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    function index()
    {
        $banners = Banner::latest()->paginate(config('default_pagination'));
        return view('admin-views.banner.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'image' => 'required|max:2048',
            'banner_type' => 'required',
            'zone_id' => 'required',
            'restaurant_id' => 'required_if:banner_type,restaurant_wise',
            'item_id' => 'required_if:banner_type,item_wise',
        ], [
            'zone_id.required' => translate('messages.select_a_zone'),
            'restaurant_id.required_if'=> translate('messages.Restaurant is required when banner type is restaurant wise'),
            'item_id.required_if'=> translate('messages.Food is required when banner type is food wise'),
        ]);

        if($request->title[array_search('default', $request->lang)] == '' ){
            $validator->getMessageBag()->add('title', translate('messages.default_title_is_required'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
            }
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $banner = new Banner;
        $banner->title = $request->title[array_search('default', $request->lang)];
        $banner->type = $request->banner_type;
        $banner->zone_id = $request->zone_id;
        $banner->image = Helpers::upload(dir:'banner/',  format:'png', image: $request->file('image'));
        $banner->data = ($request->banner_type == 'restaurant_wise')?$request->restaurant_id:$request->item_id;
        $banner->save();
        $data=[];
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if($default_lang == $key && !($request->title[$index])){
                if ($key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Banner',
                        'translationable_id' => $banner->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $banner->title,
                    ));
                }
            }else{
                if ($request->title[$index] && $key != 'default') {
                    array_push($data, array(
                        'translationable_type' => 'App\Models\Banner',
                        'translationable_id' => $banner->id,
                        'locale' => $key,
                        'key' => 'title',
                        'value' => $request->title[$index],
                    ));
                }
            }
        }
        Translation::insert($data);
        return response()->json([], 200);
    }

    public function edit(Banner $banner)
    {
        return view('admin-views.banner.edit', compact('banner'));
    }


    public function status(Request $request)
    {
        $banner = Banner::findOrFail($request->id);
        $banner->status = $request->status;
        $banner->save();
        Toastr::success(translate('messages.banner_status_updated'));
        return back();
    }

    public function update(Request $request, Banner $banner)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'banner_type' => 'required',
            'zone_id' => 'required',
            'image' => 'nullable|max:2048',
            'restaurant_id' => 'required_if:banner_type,restaurant_wise',
            'item_id' => 'required_if:banner_type,item_wise',
        ], [
            'zone_id.required' => translate('messages.select_a_zone'),
            'restaurant_id.required_if'=> translate('messages.Restaurant is required when banner type is restaurant wise'),
            'item_id.required_if'=> translate('messages.Food is required when banner type is food wise'),
        ]);


        if($request->title[array_search('default', $request->lang)] == '' ){
            $validator->getMessageBag()->add('title', translate('messages.default_title_is_required'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
            }

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $banner->title = $request->title[array_search('default', $request->lang)];;
        $banner->type = $request->banner_type;
        $banner->zone_id = $request->zone_id;
        $banner->image = $request->has('image') ? Helpers::update(dir:'banner/',old_image: $banner->image, format:'png', image: $request->file('image')) : $banner->image;
        $banner->data = $request->banner_type=='restaurant_wise'?$request->restaurant_id:$request->item_id;
        $banner->save();
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if($default_lang == $key && !($request->title[$index])){
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Banner',
                            'translationable_id' => $banner->id,
                            'locale' => $key,
                            'key' => 'title'
                        ],
                        ['value' => $banner->title]
                    );
                }
            }else{

                if ($request->title[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\Banner',
                            'translationable_id' => $banner->id,
                            'locale' => $key,
                            'key' => 'title'
                        ],
                        ['value' => $request->title[$index]]
                    );
                }
            }
        }
        return response()->json([], 200);
    }

    public function delete(Banner $banner)
    {
        Helpers::check_and_delete('banner/' , $banner['image']);
        $banner?->translations()?->delete();
        $banner->delete();
        Toastr::success(translate('messages.banner_deleted_successfully'));
        return back();
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $banners=Banner::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->orWhere('title', 'like', "%{$value}%");
            }
        })->limit(50)->get();
        return response()->json([
            'view'=>view('admin-views.banner.partials._table',compact('banners'))->render(),
            'count'=>$banners->count()
        ]);
    }





    public function promotional_banner(){
        $banner_title =  DataSetting::where('type','promotional_banner')->where('key' ,'promotional_banner_title')->withoutGlobalScope('translate')->with('translations')->first();
        $banner_image =  DataSetting::where('type','promotional_banner')->where('key', 'promotional_banner_image')->withoutGlobalScope('translate')->with('translations')->first();
        return view('admin-views.banner.promotional_banner', compact('banner_title','banner_image'));
    }

    public function promotional_banner_update(Request $request){

        $request->validate([
            'promotional_banner_title.0'=>'required',
            'promotional_banner_image' => 'nullable|max:2048',
        ], [
            'promotional_banner_title.required' => translate('messages.Title is required!'),
            'promotional_banner_title.0.required'=>translate('default_Title_is_required'),
        ]);

        if ($request->hasFile('promotional_banner_image')) {
            if (!$request->file('promotional_banner_image')->isValid()) {
                return response()->json(['errors' => [
                    ['code' => 'promotional_banner_image', 'message' => translate('messages.Image upload failed — file may be too large for the server. Please try a smaller image.')]
                ]], 422);
            }
            $banner = DataSetting::firstOrNew(
                ['key' =>  'promotional_banner_image',
                'type' =>  'promotional_banner'],
            );
            $banner->value=   Helpers::update(dir:'banner/',old_image: $banner->value, format:'png', image: $request->file('promotional_banner_image'));
            $banner->save();
        }

        // dd($request->all());
        $this->update_data($request , 'promotional_banner_title','promotional_banner_title' );
        Toastr::success(translate('messages.banner_updated_successfully'));
        return back();

    }


    private function update_data($request, $key_data, $name_field , $type = 'promotional_banner' ){
        $data = DataSetting::firstOrNew(
            ['key' =>  $key_data,
            'type' =>  $type],
        );
// dd($request->{$name_field}[array_search('default', $request->lang)]);
        $data->value = $request->{$name_field}[array_search('default', $request->lang)];
        $data->save();
        $default_lang = str_replace('_', '-', app()->getLocale());
        foreach ($request->lang as $index => $key) {
            if ($default_lang == $key && !($request->{$name_field}[$index])) {
                if ($key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\DataSetting',
                            'translationable_id' => $data->id,
                            'locale' => $key,
                            'key' => $key_data
                        ],
                        ['value' => $data->value]
                    );
                }
            } else {
                if ($request->{$name_field}[$index] && $key != 'default') {
                    Translation::updateOrInsert(
                        [
                            'translationable_type' => 'App\Models\DataSetting',
                            'translationable_id' => $data->id,
                            'locale' => $key,
                            'key' => $key_data
                        ],
                        ['value' => $request->{$name_field}[$index]]
                    );
                }
            }
        }

        return true;
    }

    public function aiImage(Request $request)
    {
        $title = trim($request->title ?? '');
        if (!$title) return response()->json(['error' => 'Title required'], 422);

        $apiKey = \App\Models\BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) return response()->json(['error' => 'No Gemini API key set in Admin → Business Settings → Third Party APIs'], 400);

        $isHebrew = (bool) preg_match('/[\x{0590}-\x{05FF}]/u', $title);
        $languageInstruction = $isHebrew
            ? " CRITICAL TEXT REQUIREMENT: Render ONLY the following Hebrew text on the banner, spelled exactly letter-for-letter, correct Hebrew orthography, right-to-left: \"{$title}\". Every Hebrew letter must be correctly formed and correctly spelled — do not invent, distort, mirror, or substitute any letters. Do not translate or add any English text. Keep the on-image text short and large so each letter is rendered clearly and legibly. Triple-check the spelling matches the given text exactly before finalizing."
            : " Render any text inside the image in the same language as the description above, using the exact wording given.";

        $prompt = "Generate a professional, eye-catching restaurant food delivery app banner image for \"{$title}\".{$languageInstruction} The image must be exactly 3:1 aspect ratio (landscape, very wide), vibrant colors, appetizing food photography style, suitable for a mobile app promotional banner. Compose the scene to fill the full wide 3:1 frame.";

        $models    = ['gemini-2.5-flash-image', 'gemini-3.1-flash-image', 'gemini-3-pro-image'];
        $lastError = '';
        foreach ($models as $model) {
            try {
                $r = \Illuminate\Support\Facades\Http::timeout(45)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                    ['contents' => [['parts' => [['text' => $prompt]]]], 'generationConfig' => ['responseModalities' => ['IMAGE', 'TEXT']]]
                );
                if ($r->successful()) {
                    foreach ($r->json('candidates.0.content.parts') ?? [] as $part) {
                        if (isset($part['inlineData']['data'])) {
                            $mime = $part['inlineData']['mimeType'] ?? 'image/png';
                            return response()->json(['image' => "data:{$mime};base64,{$part['inlineData']['data']}"]);
                        }
                    }
                }
                $lastError = $r->json('error.message') ?? ('HTTP ' . $r->status());
                if ($r->status() === 403) return response()->json(['error' => $lastError], 500);
            } catch (\Exception $e) { $lastError = $e->getMessage(); }
        }
        return response()->json(['error' => "AI image error: {$lastError}"], 500);
    }
}
