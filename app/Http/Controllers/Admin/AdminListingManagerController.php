<?php

namespace App\Http\Controllers\Admin;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\BusinessSetting;
use App\Models\Category;
use App\Models\Food;
use App\Models\Restaurant;
use App\Models\Variation;
use App\Models\VariationOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminListingManagerController extends Controller
{
    private function restaurant($rid): ?Restaurant
    {
        return Restaurant::find($rid);
    }

    private function logAction(string $action, array $context = []): void
    {
        $admin = auth('admin')->user();
        Log::channel('daily')->info("[ADMIN LISTING] {$action}", array_merge([
            'admin_id'   => $admin?->id,
            'admin_name' => $admin?->name,
            'admin_email'=> $admin?->email,
            'ip'         => request()->ip(),
            'time'       => now()->toDateTimeString(),
        ], $context));
    }

    public function index($rid)
    {
        $restaurant = $this->restaurant($rid);
        if (!$restaurant) abort(404);

        $categories    = Category::where('position', 0)->where('restaurant_id', $rid)->orderBy('name')->get();
        $subcategories = Category::where('position', '!=', 0)->where('restaurant_id', $rid)->orderBy('name')->get();
        $all_count     = Food::where('restaurant_id', $rid)->count();
        $disabled_count = Food::where('restaurant_id', $rid)->where('status', 0)->count();
        $gemini_ai_enabled = BusinessSetting::where('key', 'gemini_ai_enabled')->first()?->value ?? '1';

        $lm_base              = route('admin.restaurant.listing-manager.index', $rid);
        $admin_mode           = true;
        $admin_restaurant_name = $restaurant->name;
        $admin_back_url       = route('admin.restaurant.view', ['restaurant' => $rid]);

        $this->logAction('Opened listing manager', ['restaurant_id' => $rid, 'restaurant' => $restaurant->name]);

        return view('vendor-views.product.listing-manager', compact(
            'categories', 'subcategories', 'all_count', 'disabled_count',
            'gemini_ai_enabled', 'lm_base', 'admin_mode', 'admin_restaurant_name', 'admin_back_url'
        ));
    }

    public function getItems(Request $request, $rid)
    {
        $filter      = $request->filter ?? 'all';
        $category_id = $request->category_id ?? null;

        $query = Food::with(['newVariations' => fn($q) => $q->with('variationOptions')])
            ->where('restaurant_id', $rid);

        if ($filter === 'disabled') {
            $query->where('status', 0);
        } elseif ($filter === 'no_category') {
            $query->where(fn($q) => $q->whereNull('category_id')->orWhere('category_id', 0));
        } elseif ($filter === 'category' && $category_id && is_numeric($category_id)) {
            $query->whereHas('category', fn($q) => $q->where('id', $category_id)->orWhere('parent_id', $category_id));
        }

        $items = $query->latest()->get()->map(function ($food) {
            $hasVariations = $food->newVariations->count() > 0;
            $firstOptionPrice = $hasVariations
                ? optional(optional($food->newVariations->first())->variationOptions->first())->option_price
                : null;
            $catIds = json_decode($food->category_ids, true) ?? [];
            $mainCat = collect($catIds)->firstWhere('position', 1);
            return [
                'id'               => $food->id,
                'name'             => $food->name,
                'price'            => $food->price,
                'display_price'    => $hasVariations ? ($firstOptionPrice ?? 0) : $food->price,
                'image_full_url'   => $food->image_full_url,
                'status'           => $food->status,
                'veg'              => $food->veg,
                'has_variations'   => $hasVariations,
                'category_id'      => $food->category_id,
                'parent_category_id' => $mainCat['id'] ?? $food->category_id,
            ];
        });

        return response()->json(['items' => $items]);
    }

    public function getItem($rid, $id)
    {
        $food = Food::with(['newVariations' => fn($q) => $q->with('variationOptions')])->findOrFail($id);

        $categoryIds = json_decode($food->category_ids, true) ?? [];
        $mainCat     = collect($categoryIds)->firstWhere('position', 1);
        $subCat      = collect($categoryIds)->firstWhere('position', 2);

        $variations = $food->newVariations->map(function ($v) {
            return [
                'variation_id' => $v->id,
                'name'         => $v->name,
                'type'         => $v->type,
                'required'     => $v->is_required ? 'on' : '',
                'min'          => $v->min,
                'max'          => $v->max,
                'values'       => $v->variationOptions->map(fn($o) => [
                    'option_id'    => $o->id,
                    'label'        => $o->option_name,
                    'optionPrice'  => $o->option_price,
                ])->values()->toArray(),
            ];
        })->values()->toArray();

        return response()->json([
            'id'                  => $food->id,
            'name'                => $food->name,
            'description'         => $food->description,
            'price'               => $food->price,
            'image_full_url'      => $food->image_full_url,
            'status'              => $food->status,
            'veg'                 => $food->veg,
            'category_id'         => $mainCat['id'] ?? $food->category_id,
            'sub_category_id'     => $subCat['id'] ?? null,
            'available_time_starts' => $food->available_time_starts,
            'available_time_ends'   => $food->available_time_ends,
            'discount'            => $food->discount,
            'discount_type'       => $food->discount_type,
            'stock_type'          => $food->stock_type ?? 'unlimited',
            'item_stock'          => $food->item_stock ?? 0,
            'variations'          => $variations,
        ]);
    }

    public function store(Request $request, $rid)
    {
        $restaurant = $this->restaurant($rid);
        if (!$restaurant) return response()->json(['errors' => [['code' => 'not_found', 'message' => 'Restaurant not found']]], 404);

        $hasOptions = $request->has('options') && is_array($request->options) && count($request->options) > 0;

        $rules = [
            'name'          => 'array',
            'name.0'        => 'required|max:191',
            'name.*'        => 'max:191',
            'category_id'   => 'required',
            'image'         => 'nullable|max:2048',
            'description.*' => 'max:1000',
            'discount'      => 'nullable|numeric|min:0',
        ];
        $rules['price'] = $hasOptions ? 'nullable|numeric|min:0' : 'required|numeric|between:.01,999999999999.99';

        $validator = Validator::make($request->all(), $rules, [
            'name.0.required'     => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
        ]);
        if ($validator->fails()) return response()->json(['errors' => Helpers::error_processor($validator)]);

        if ($hasOptions) {
            foreach (array_values($request->options) as $option) {
                if (!isset($option['values'])) return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.please_add_options_for') . ($option['name'] ?? '')]]]);
                if (isset($option['min'], $option['max']) && $option['min'] > 0 && $option['min'] > $option['max']) {
                    return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.minimum_value_can_not_be_greater_then_maximum_value')]]]);
                }
            }
        }

        $food = new Food;
        $food->name = $request->name[array_search('default', $request->lang ?? ['default'])];

        $category = [];
        if ($request->category_id) $category[] = ['id' => $request->category_id, 'position' => 1];
        if ($request->sub_category_id) $category[] = ['id' => $request->sub_category_id, 'position' => 2];

        $food->category_id    = $request->sub_category_id ?? $request->category_id;
        $food->category_ids   = json_encode($category);
        $food->description    = $request->description[array_search('default', $request->lang ?? ['default'])] ?? '';
        $food->choice_options = json_encode([]);
        $food->variations     = json_encode([]);
        $price = $request->price;
        $food->price = ($hasOptions && (!$price || $price == 0)) ? 0 : max(0.01, (float)($price ?? 0.01));
        $food->veg = $request->veg ?? 0;
        $food->image = $request->hasFile('image')
            ? Helpers::upload(dir: 'product/', format: 'png', image: $request->file('image'))
            : 'def.png';
        $food->available_time_starts = $request->available_time_starts ?? '00:00:00';
        $food->available_time_ends   = $request->available_time_ends ?? '23:59:00';
        $food->discount       = $request->discount ?? 0;
        $food->discount_type  = $request->discount_type ?? 'percent';
        $food->attributes     = json_encode([]);
        $food->add_ons        = json_encode([]);
        $food->restaurant_id  = $rid;
        $food->maximum_cart_quantity = $request->maximum_cart_quantity ?? null;
        $food->is_halal       = $request->is_halal ?? 0;
        $food->item_stock     = 0;
        $food->stock_type     = 'unlimited';
        $food->status         = $request->status ?? 1;
        $food->save();

        if ($hasOptions) {
            foreach (array_values($request->options) as $option) {
                $variation = new Variation();
                $variation->food_id     = $food->id;
                $variation->name        = $option['name'];
                $variation->type        = $option['type'] ?? 'single';
                $variation->min         = $option['min'] ?? 0;
                $variation->max         = $option['max'] ?? 0;
                $variation->is_required = data_get($option, 'required') == 'on';
                $variation->save();

                foreach (array_values($option['values'] ?? []) as $value) {
                    $vo = new VariationOption();
                    $vo->food_id      = $food->id;
                    $vo->variation_id = $variation->id;
                    $vo->option_name  = $value['label'];
                    $vo->option_price = $value['optionPrice'];
                    $vo->stock_type   = 'unlimited';
                    $vo->total_stock  = 0;
                    $vo->save();
                }
            }
        }

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Food', data_id: $food->id, data_value: $food->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'Food', data_id: $food->id, data_value: $food->description);

        $this->logAction('Added food item', ['restaurant_id' => $rid, 'food_id' => $food->id, 'name' => $food->name]);

        return response()->json(['id' => $food->id, 'message' => translate('Item saved successfully')]);
    }

    public function update(Request $request, $rid, $id)
    {
        $hasOptions = $request->has('options') && is_array($request->options) && count($request->options) > 0;

        $rules = [
            'name'          => 'array',
            'name.0'        => 'required|max:191',
            'name.*'        => 'max:191',
            'category_id'   => 'required',
            'image'         => 'nullable|max:2048',
            'description.*' => 'max:1000',
            'discount'      => 'nullable|numeric|min:0',
        ];
        $rules['price'] = $hasOptions ? 'nullable|numeric|min:0' : 'required|numeric|between:.01,999999999999.99';

        $validator = Validator::make($request->all(), $rules, [
            'name.0.required'      => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
        ]);
        if ($validator->fails()) return response()->json(['errors' => Helpers::error_processor($validator)]);

        if ($hasOptions) {
            foreach (array_values($request->options) as $option) {
                if (!isset($option['values'])) return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.please_add_options_for') . ($option['name'] ?? '')]]]);
                if (isset($option['min'], $option['max']) && $option['min'] > 0 && $option['min'] > $option['max']) {
                    return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.minimum_value_can_not_be_greater_then_maximum_value')]]]);
                }
            }
        }

        $food = Food::where('id', $id)->where('restaurant_id', $rid)->firstOrFail();
        $food->name = $request->name[array_search('default', $request->lang ?? ['default'])];

        $slug = Str::slug($food->name);
        $food->slug = $food->slug ?: "{$slug}-{$food->id}";

        $category = [];
        if ($request->category_id) $category[] = ['id' => $request->category_id, 'position' => 1];
        if ($request->sub_category_id) $category[] = ['id' => $request->sub_category_id, 'position' => 2];

        $food->category_id    = $request->sub_category_id ?? $request->category_id;
        $food->category_ids   = json_encode($category);
        $food->description    = $request->description[array_search('default', $request->lang ?? ['default'])] ?? '';
        $food->choice_options = json_encode([]);
        $food->variations     = json_encode([]);

        $price = $request->price;
        if ($hasOptions && (!$price || $price == 0)) {
            $food->price = 0;
        } elseif ($price && (float)$price > 0) {
            $food->price = (float)$price;
        }

        $food->veg = $request->veg ?? $food->veg;
        if ($request->hasFile('image')) {
            $food->image = Helpers::update(dir: 'product/', old_image: $food->image, format: 'png', image: $request->file('image'));
        }
        $food->available_time_starts = $request->available_time_starts ?? $food->available_time_starts;
        $food->available_time_ends   = $request->available_time_ends ?? $food->available_time_ends;
        $food->discount      = $request->discount ?? 0;
        $food->discount_type = $request->discount_type ?? 'percent';
        $food->maximum_cart_quantity = $request->maximum_cart_quantity ?? $food->maximum_cart_quantity;
        $food->is_halal = $request->is_halal ?? $food->is_halal;
        $food->status = $request->has('status') ? (int)$request->status : $food->status;

        if ($hasOptions) {
            foreach (array_values($request->options) as $option) {
                $variation = Variation::updateOrCreate(
                    ['id' => $option['variation_id'] ?? null, 'food_id' => $food->id],
                    ['name' => $option['name'], 'type' => $option['type'] ?? 'single', 'min' => $option['min'] ?? 0, 'max' => $option['max'] ?? 0, 'is_required' => data_get($option, 'required') == 'on']
                );
                foreach (array_values($option['values'] ?? []) as $value) {
                    VariationOption::updateOrCreate(
                        ['id' => $value['option_id'] ?? null, 'food_id' => $food->id, 'variation_id' => $variation->id],
                        ['option_name' => $value['label'], 'option_price' => $value['optionPrice'], 'total_stock' => 0, 'stock_type' => 'unlimited', 'sell_count' => 0]
                    );
                }
            }
        }

        if ($request->removedVariationOptionIDs && is_string($request->removedVariationOptionIDs)) {
            VariationOption::whereIn('id', explode(',', $request->removedVariationOptionIDs))->delete();
        }
        if ($request->removedVariationIDs && is_string($request->removedVariationIDs)) {
            VariationOption::whereIn('variation_id', explode(',', $request->removedVariationIDs))->delete();
            Variation::whereIn('id', explode(',', $request->removedVariationIDs))->delete();
        }

        $food->save();
        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Food', data_id: $food->id, data_value: $food->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'Food', data_id: $food->id, data_value: $food->description);

        $this->logAction('Updated food item', ['restaurant_id' => $rid, 'food_id' => $food->id, 'name' => $food->name]);

        return response()->json(['id' => $food->id, 'message' => translate('Item updated successfully')]);
    }

    public function delete($rid, $id)
    {
        $food = Food::where('id', $id)->where('restaurant_id', $rid)->firstOrFail();
        $name = $food->name;
        $food->newVariations()->each(function ($v) { $v->variationOptions()->delete(); $v->delete(); });
        $food->delete();
        $this->logAction('Deleted food item', ['restaurant_id' => $rid, 'food_id' => $id, 'name' => $name]);
        return response()->json(['message' => translate('Item deleted successfully')]);
    }

    public function toggleStatus(Request $request, $rid)
    {
        $food = Food::where('id', $request->id)->where('restaurant_id', $rid)->firstOrFail();
        $food->status = $request->status;
        $food->save();
        if ($food->status != 1) $food->carts()?->delete();
        $this->logAction('Toggled item status', ['restaurant_id' => $rid, 'food_id' => $food->id, 'status' => $food->status]);
        return response()->json(['status' => $food->status]);
    }

    public function getCategories(Request $request, $rid)
    {
        $parentId = $request->parent_id ?? 0;
        $categories = Category::where('parent_id', $parentId)
            ->where('restaurant_id', $rid)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($categories);
    }

    public function categoryList($rid)
    {
        $cats = Category::where('restaurant_id', $rid)->where('position', 0)->orderBy('name')->get(['id', 'name', 'image', 'status']);
        return response()->json($cats->map(fn($c) => ['id' => $c->id, 'name' => $c->name, 'status' => $c->status, 'image_url' => $c->image_full_url]));
    }

    public function categoryStore(Request $request, $rid)
    {
        $validator = Validator::make($request->all(), ['name' => 'required|max:191', 'image' => 'nullable|image|max:2048']);
        if ($validator->fails()) return response()->json(['errors' => Helpers::error_processor($validator)], 422);

        $cat = new Category();
        $cat->restaurant_id = $rid;
        $cat->name = $request->name;
        $cat->image = 'def.png';
        $cat->parent_id = 0;
        $cat->position  = 0;
        $cat->status    = 1;
        $cat->save();

        if ($request->hasFile('image')) {
            $img = Helpers::upload(dir: 'category/', format: 'png', image: $request->file('image'));
            $cat->image = $img; $cat->save();
            Helpers::updateStorageTable(Category::class, $cat->id, $img);
        } elseif ($request->filled('image_base64')) {
            $decoded = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $request->image_base64));
            if ($decoded) {
                $fname = 'category/' . now()->toDateString() . '-' . uniqid() . '.png';
                Storage::disk('public')->put($fname, $decoded);
                $cat->image = basename($fname); $cat->save();
                Helpers::updateStorageTable(Category::class, $cat->id, $cat->image);
            }
        }

        $this->logAction('Added category', ['restaurant_id' => $rid, 'category_id' => $cat->id, 'name' => $cat->name]);
        return response()->json(['id' => $cat->id, 'name' => $cat->name, 'status' => $cat->status, 'image_url' => $cat->refresh()->image_full_url]);
    }

    public function categoryUpdate(Request $request, $rid, $id)
    {
        $cat = Category::where('id', $id)->where('restaurant_id', $rid)->firstOrFail();
        $validator = Validator::make($request->all(), ['name' => 'required|max:191', 'image' => 'nullable|image|max:2048']);
        if ($validator->fails()) return response()->json(['errors' => Helpers::error_processor($validator)], 422);

        $cat->name = $request->name;
        if ($request->hasFile('image')) {
            $cat->image = Helpers::update(dir: 'category/', old_image: $cat->image, format: 'png', image: $request->file('image'));
        } elseif ($request->filled('image_base64')) {
            $decoded = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $request->image_base64));
            if ($decoded) {
                $fname = 'category/' . now()->toDateString() . '-' . uniqid() . '.png';
                Storage::disk('public')->put($fname, $decoded);
                $cat->image = basename($fname);
                Helpers::updateStorageTable(Category::class, $cat->id, $cat->image);
            }
        }
        $cat->save();
        $this->logAction('Updated category', ['restaurant_id' => $rid, 'category_id' => $cat->id, 'name' => $cat->name]);
        return response()->json(['id' => $cat->id, 'name' => $cat->name, 'status' => $cat->status, 'image_url' => $cat->refresh()->image_full_url]);
    }

    public function categoryDelete($rid, $id)
    {
        $cat = Category::where('id', $id)->where('restaurant_id', $rid)->firstOrFail();
        Food::where('restaurant_id', $rid)->where('category_id', $id)->update(['category_id' => null]);
        $cat->delete();
        $this->logAction('Deleted category', ['restaurant_id' => $rid, 'category_id' => $id]);
        return response()->json(['success' => true]);
    }

    public function categoryAiImage(Request $request, $rid)
    {
        $name = trim($request->name ?? '');
        if (!$name) return response()->json(['error' => 'Category name required'], 422);

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) return response()->json(['error' => 'No Gemini API key in Business Settings'], 400);

        $prompt = "Generate a professional food category banner photo for a restaurant menu category called \"{$name}\". Clean, appetizing, high quality, suitable as a menu category header image.";
        $models = ['gemini-2.5-flash-image', 'gemini-3.1-flash-image', 'gemini-3-pro-image'];
        $lastError = '';
        foreach ($models as $model) {
            try {
                $r = Http::timeout(45)->post(
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

    public function aiDescription(Request $request, $rid)
    {
        $name = trim($request->name ?? '');
        if (!$name) return response()->json(['error' => 'Item name required'], 422);

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) return response()->json(['error' => 'No Gemini API key in Business Settings'], 400);

        $restaurant = $this->restaurant($rid);
        $prompt = "Write a short, appetizing 1-2 sentence menu description for a food item called \"{$name}\" at {$restaurant?->name}. Be concise and enticing.";

        $models = ['gemini-2.5-flash', 'gemini-2.0-flash-001', 'gemini-flash-latest'];
        $lastError = '';
        foreach ($models as $model) {
            try {
                $r = Http::timeout(30)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                    ['contents' => [['parts' => [['text' => $prompt]]]], 'generationConfig' => ['thinkingConfig' => ['thinkingBudget' => 0]]]
                );
                if ($r->successful()) {
                    $text = $r->json('candidates.0.content.parts.0.text') ?? '';
                    if ($text) return response()->json(['description' => trim($text)]);
                }
                $lastError = $r->json('error.message') ?? ('HTTP ' . $r->status());
            } catch (\Exception $e) { $lastError = $e->getMessage(); }
        }
        return response()->json(['error' => "AI error: {$lastError}"], 500);
    }

    public function aiImage(Request $request, $rid)
    {
        $name = trim($request->name ?? '');
        if (!$name) return response()->json(['error' => 'Item name required'], 422);

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) return response()->json(['error' => 'No Gemini API key in Business Settings'], 400);

        $prompt = "Generate a professional food photography image of \"{$name}\" for a restaurant menu. Clean white background, top-down or 45-degree angle, appetizing presentation.";
        $models = ['gemini-2.5-flash-image', 'gemini-3.1-flash-image', 'gemini-3-pro-image'];
        $lastError = '';
        foreach ($models as $model) {
            try {
                $r = Http::timeout(45)->post(
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
