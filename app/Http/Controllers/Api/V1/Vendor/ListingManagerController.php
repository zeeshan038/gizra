<?php

namespace App\Http\Controllers\Api\V1\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Allergy;
use App\Models\Nutrition;
use App\Models\Tag;
use App\Models\Food;
use App\Models\Category;
use App\Models\Variation;
use App\Models\Translation;
use App\Models\AddOn;
use App\Models\VariationOption;
use App\Models\BusinessSetting;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ListingManagerController extends Controller
{
    // Helper to get authenticated restaurant
    protected function getRestaurant($request)
    {
        $vendor = $request['vendor'];
        return $vendor?->restaurants[0] ?? null;
    }

    // ── Food Items ────────────────────────────────────────────────────────────

    public function getItems(Request $request)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $filter = $request->filter ?? 'all';
        $category_id = $request->category_id ?? null;

        $query = Food::with(['newVariations' => function ($q) {
            $q->with('variationOptions');
        }])->where('restaurant_id', $restaurant->id);

        if ($filter === 'disabled') {
            $query->where('status', 0);
        } elseif ($filter === 'no_category') {
            $query->where(function ($q) {
                $q->whereNull('category_id')->orWhere('category_id', 0);
            });
        } elseif ($filter === 'category' && $category_id && is_numeric($category_id)) {
            $query->whereHas('category', function ($q) use ($category_id) {
                $q->where('id', $category_id)->orWhere('parent_id', $category_id);
            });
        }

        $foods = $query->latest()->get();

        $items = $foods->map(function ($food) {
            $hasVariations = $food->newVariations->count() > 0;
            $firstOptionPrice = null;
            if ($hasVariations) {
                $firstVar = $food->newVariations->first();
                if ($firstVar && $firstVar->variationOptions->count() > 0) {
                    $firstOptionPrice = $firstVar->variationOptions->first()->option_price;
                }
            }
            $catIds = json_decode($food->category_ids, true) ?? [];
            $mainCat = collect($catIds)->firstWhere('position', 1);
            $parentCategoryId = $mainCat['id'] ?? $food->category_id;
            return [
                'id' => $food->id,
                'name' => $food->name,
                'price' => $food->price,
                'display_price' => $hasVariations ? ($firstOptionPrice ?? 0) : $food->price,
                'image_full_url' => $food->image_full_url,
                'status' => $food->status,
                'veg' => $food->veg,
                'has_variations' => $hasVariations,
                'category_id' => $food->category_id,
                'parent_category_id' => $parentCategoryId,
            ];
        });

        return response()->json(['items' => $items]);
    }

    public function getItem(Request $request, $id)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $food = Food::with(['newVariations' => function ($q) {
            $q->with('variationOptions');
        }])->where('restaurant_id', $restaurant->id)->findOrFail($id);

        $categoryIds = json_decode($food->category_ids, true) ?? [];
        $mainCat = collect($categoryIds)->firstWhere('position', 1);
        $subCat = collect($categoryIds)->firstWhere('position', 2);

        $variations = $food->newVariations->map(function ($variation) {
            return [
                'variation_id' => $variation->id,
                'name' => $variation->name,
                'type' => $variation->type,
                'min' => $variation->min,
                'max' => $variation->max,
                'required' => $variation->is_required ? 'on' : 'off',
                'values' => $variation->variationOptions->map(function ($opt) {
                    return [
                        'option_id' => $opt->id,
                        'label' => $opt->option_name,
                        'optionPrice' => $opt->option_price,
                    ];
                })->values()->toArray(),
            ];
        })->values()->toArray();

        // Include simple array format for translations to be easy to digest by the app
        $translations = Translation::where('translationable_type', 'App\Models\Food')
            ->where('translationable_id', $food->id)
            ->get();

        return response()->json([
            'id' => $food->id,
            'name' => $food->name,
            'description' => $food->description,
            'price' => $food->price,
            'image_full_url' => $food->image_full_url,
            'image' => $food->image,
            'status' => $food->status,
            'veg' => $food->veg,
            'category_id' => $mainCat['id'] ?? $food->category_id,
            'sub_category_id' => $subCat['id'] ?? null,
            'discount' => $food->discount,
            'discount_type' => $food->discount_type,
            'stock_type' => $food->stock_type ?? 'unlimited',
            'item_stock' => $food->item_stock ?? 0,
            'available_time_starts' => $food->available_time_starts,
            'available_time_ends' => $food->available_time_ends,
            'variations' => $variations,
            'translations' => $translations,
            'addon_ids' => json_decode($food->add_ons, true) ?? [],
        ]);
    }

    public function store(Request $request)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant || !$restaurant->food_section) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => translate('messages.permission_denied')]]], 403);
        }

        // Support both direct array/JSON options format
        $options = $request->options;
        if (is_string($options)) {
            $options = json_decode($options, true);
        }
        $hasOptions = is_array($options) && count($options) > 0;

        $rules = [
            'category_id'  => 'required',
            'discount'     => 'nullable|numeric|min:0',
        ];

        // Name and description validation (either via translations field or direct name array)
        if ($request->has('translations')) {
            $rules['translations'] = 'required';
        } else {
            $rules['name'] = 'required';
        }

        if (!$hasOptions) {
            $rules['price'] = 'required|numeric|between:.01,999999999999.99';
        } else {
            $rules['price'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        // Subscription Limit check
        if ($restaurant->restaurant_model == 'subscription') {
            $rest_sub = $restaurant?->restaurant_sub;
            if (isset($rest_sub)) {
                if ($rest_sub->max_product != 'unlimited' && $rest_sub->max_product > 0) {
                    $total_food = Food::where('restaurant_id', $restaurant->id)->count() + 1;
                    if ($total_food >= $rest_sub->max_product) {
                        $restaurant->food_section = 0;
                        $restaurant->save();
                    }
                }
            } else {
                return response()->json(['errors' => [['code' => 'unauthorized', 'message' => translate('messages.you_are_not_subscribed_to_any_package')]]], 403);
            }
        } elseif ($restaurant->restaurant_model == 'unsubscribed') {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => translate('messages.you_are_not_subscribed_to_any_package')]]], 403);
        }

        if ($hasOptions) {
            foreach ($options as $option) {
                if (!isset($option['values'])) {
                    return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.please_add_options_for') . ($option['name'] ?? '')]]], 422);
                }
                if (isset($option['min']) && isset($option['max']) && $option['min'] > 0 && $option['min'] > $option['max']) {
                    return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.minimum_value_can_not_be_greater_then_maximum_value')]]], 422);
                }
            }
        }

        $food = new Food;

        // Parse Name and Description
        $nameDefault = '';
        $descDefault = '';
        $translationsToSave = [];

        if ($request->has('translations')) {
            $transData = is_string($request->translations) ? json_decode($request->translations, true) : $request->translations;
            foreach ($transData as $item) {
                if ($item['key'] === 'name' && ($item['locale'] === 'default' || $item['locale'] === 'en' || !$nameDefault)) {
                    $nameDefault = $item['value'];
                }
                if ($item['key'] === 'description' && ($item['locale'] === 'default' || $item['locale'] === 'en' || !$descDefault)) {
                    $descDefault = $item['value'];
                }
                $translationsToSave[] = [
                    'locale' => $item['locale'],
                    'key' => $item['key'],
                    'value' => $item['value'],
                ];
            }
        } else {
            $nameDefault = is_array($request->name) ? ($request->name['default'] ?? reset($request->name)) : $request->name;
            $descDefault = is_array($request->description) ? ($request->description['default'] ?? reset($request->description) ?? '') : ($request->description ?? '');
            
            // Build default translations
            $translationsToSave[] = ['locale' => 'default', 'key' => 'name', 'value' => $nameDefault];
            $translationsToSave[] = ['locale' => 'default', 'key' => 'description', 'value' => $descDefault];
        }

        $food->name = $nameDefault;
        $food->description = $descDefault;

        $category = [];
        if ($request->category_id) {
            array_push($category, ['id' => $request->category_id, 'position' => 1]);
        }
        if ($request->sub_category_id) {
            array_push($category, ['id' => $request->sub_category_id, 'position' => 2]);
        }

        $food->category_id = $request->sub_category_id ?? $request->category_id;
        $food->category_ids = json_encode($category);
        $food->choice_options = json_encode([]);
        $food->variations = json_encode([]);

        $price = $request->price;
        $food->price = ($hasOptions && (!$price || $price == 0)) ? 0 : max(0.01, (float)($price ?? 0.01));

        $food->veg = $request->veg ?? 0;
        
        // Handle multipart or base64 image
        if ($request->hasFile('image')) {
            $food->image = Helpers::upload(dir: 'product/', format: 'png', image: $request->file('image'));
        } elseif ($request->filled('image_base64')) {
            $base64  = preg_replace('/^data:image\/\w+;base64,/', '', $request->image_base64);
            $decoded = base64_decode($base64);
            if ($decoded) {
                $fname = 'product/' . now()->toDateString() . '-' . uniqid() . '.png';
                Storage::disk('public')->put($fname, $decoded);
                $food->image = basename($fname);
            }
        }

        $food->available_time_starts = $request->available_time_starts ?? '00:00:00';
        $food->available_time_ends = $request->available_time_ends ?? '23:59:00';
        $food->discount = $request->discount ?? 0;
        $food->discount_type = $request->discount_type ?? 'percent';
        $food->attributes = json_encode([]);
        
        // Addons handling: comma separated or array
        $addonIds = $request->addon_ids;
        if (is_string($addonIds)) {
            $addonIds = explode(',', $addonIds);
        }
        $food->add_ons = is_array($addonIds) ? json_encode($addonIds) : json_encode([]);

        $food->restaurant_id = $restaurant->id;
        $food->maximum_cart_quantity = $request->maximum_cart_quantity ?? null;
        $food->is_halal = $request->is_halal ?? 0;
        $food->item_stock = 0;
        $food->stock_type = 'unlimited';
        $food->status = $request->status ?? 1;
        $food->save();

        if ($hasOptions) {
            foreach ($options as $option) {
                $variation = new Variation();
                $variation->food_id = $food->id;
                $variation->name = $option['name'];
                $variation->type = $option['type'] ?? 'single';
                $variation->min = $option['min'] ?? 0;
                $variation->max = $option['max'] ?? 0;
                $variation->is_required = ($option['required'] ?? 'off') === 'on' || ($option['required'] ?? false) === true;
                $variation->save();

                foreach ($option['values'] ?? [] as $value) {
                    $varOption = new VariationOption();
                    $varOption->food_id = $food->id;
                    $varOption->variation_id = $variation->id;
                    $varOption->option_name = $value['label'];
                    $varOption->option_price = $value['optionPrice'];
                    $varOption->stock_type = 'unlimited';
                    $varOption->total_stock = 0;
                    $varOption->save();
                }
            }
        }

        // Save translations
        foreach ($translationsToSave as $t) {
            Translation::updateOrInsert(
                [
                    'translationable_type' => 'App\Models\Food',
                    'translationable_id' => $food->id,
                    'locale' => $t['locale'],
                    'key' => $t['key']
                ],
                ['value' => $t['value']]
            );
        }

        return response()->json(['id' => $food->id, 'message' => translate('Item saved successfully')]);
    }

    public function update(Request $request, $id)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant || !$restaurant->food_section) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => translate('messages.permission_denied')]]], 403);
        }

        $food = Food::where('restaurant_id', $restaurant->id)->findOrFail($id);

        $options = $request->options;
        if (is_string($options)) {
            $options = json_decode($options, true);
        }
        $hasOptions = is_array($options) && count($options) > 0;

        $rules = [
            'category_id'  => 'required',
            'discount'     => 'nullable|numeric|min:0',
        ];

        if ($request->has('translations')) {
            $rules['translations'] = 'required';
        } else {
            $rules['name'] = 'required';
        }

        if (!$hasOptions) {
            $rules['price'] = 'required|numeric|between:.01,999999999999.99';
        } else {
            $rules['price'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        if ($hasOptions) {
            foreach ($options as $option) {
                if (!isset($option['values'])) {
                    return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.please_add_options_for') . ($option['name'] ?? '')]]], 422);
                }
                if (isset($option['min']) && isset($option['max']) && $option['min'] > 0 && $option['min'] > $option['max']) {
                    return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.minimum_value_can_not_be_greater_then_maximum_value')]]], 422);
                }
            }
        }

        // Parse Name and Description
        $nameDefault = '';
        $descDefault = '';
        $translationsToSave = [];

        if ($request->has('translations')) {
            $transData = is_string($request->translations) ? json_decode($request->translations, true) : $request->translations;
            foreach ($transData as $item) {
                if ($item['key'] === 'name' && ($item['locale'] === 'default' || $item['locale'] === 'en' || !$nameDefault)) {
                    $nameDefault = $item['value'];
                }
                if ($item['key'] === 'description' && ($item['locale'] === 'default' || $item['locale'] === 'en' || !$descDefault)) {
                    $descDefault = $item['value'];
                }
                $translationsToSave[] = [
                    'locale' => $item['locale'],
                    'key' => $item['key'],
                    'value' => $item['value'],
                ];
            }
        } else {
            $nameDefault = is_array($request->name) ? ($request->name['default'] ?? reset($request->name)) : $request->name;
            $descDefault = is_array($request->description) ? ($request->description['default'] ?? reset($request->description) ?? '') : ($request->description ?? '');
            
            $translationsToSave[] = ['locale' => 'default', 'key' => 'name', 'value' => $nameDefault];
            $translationsToSave[] = ['locale' => 'default', 'key' => 'description', 'value' => $descDefault];
        }

        $food->name = $nameDefault;
        $food->description = $descDefault;

        $slug = Str::slug($food->name);
        $food->slug = $food->slug ?: "{$slug}-{$food->id}";

        $category = [];
        if ($request->category_id) {
            array_push($category, ['id' => $request->category_id, 'position' => 1]);
        }
        if ($request->sub_category_id) {
            array_push($category, ['id' => $request->sub_category_id, 'position' => 2]);
        }

        $food->category_id = $request->sub_category_id ?? $request->category_id;
        $food->category_ids = json_encode($category);

        $price = $request->price;
        if ($hasOptions && (!$price || $price == 0)) {
            $food->price = 0;
        } elseif ($price && (float)$price > 0) {
            $food->price = (float)$price;
        }

        $food->veg = $request->veg ?? $food->veg;
        
        if ($request->hasFile('image')) {
            $food->image = Helpers::update(dir: 'product/', old_image: $food->image, format: 'png', image: $request->file('image'));
        } elseif ($request->filled('image_base64')) {
            $base64  = preg_replace('/^data:image\/\w+;base64,/', '', $request->image_base64);
            $decoded = base64_decode($base64);
            if ($decoded) {
                $fname = 'product/' . now()->toDateString() . '-' . uniqid() . '.png';
                Storage::disk('public')->put($fname, $decoded);
                $food->image = basename($fname);
            }
        }

        $food->available_time_starts = $request->available_time_starts ?? $food->available_time_starts;
        $food->available_time_ends = $request->available_time_ends ?? $food->available_time_ends;
        $food->discount = $request->discount ?? 0;
        $food->discount_type = $request->discount_type ?? 'percent';
        $food->maximum_cart_quantity = $request->maximum_cart_quantity ?? $food->maximum_cart_quantity;
        $food->is_halal = $request->is_halal ?? $food->is_halal;
        $food->status = $request->has('status') ? (int)$request->status : $food->status;

        $addonIds = $request->addon_ids;
        if (is_string($addonIds)) {
            $addonIds = explode(',', $addonIds);
        }
        if (is_array($addonIds)) {
            $food->add_ons = json_encode($addonIds);
        }

        if ($hasOptions) {
            foreach ($options as $option) {
                $variation = Variation::updateOrCreate(
                    ['id' => $option['variation_id'] ?? null, 'food_id' => $food->id],
                    [
                        'name' => $option['name'],
                        'type' => $option['type'] ?? 'single',
                        'min' => $option['min'] ?? 0,
                        'max' => $option['max'] ?? 0,
                        'is_required' => ($option['required'] ?? 'off') === 'on' || ($option['required'] ?? false) === true,
                    ]
                );

                foreach ($option['values'] ?? [] as $value) {
                    VariationOption::updateOrCreate(
                        ['id' => $value['option_id'] ?? null, 'food_id' => $food->id, 'variation_id' => $variation->id],
                        [
                            'option_name' => $value['label'],
                            'option_price' => $value['optionPrice'],
                            'total_stock' => 0,
                            'stock_type' => 'unlimited',
                            'sell_count' => 0,
                        ]
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

        // Save translations
        foreach ($translationsToSave as $t) {
            Translation::updateOrInsert(
                [
                    'translationable_type' => 'App\Models\Food',
                    'translationable_id' => $food->id,
                    'locale' => $t['locale'],
                    'key' => $t['key']
                ],
                ['value' => $t['value']]
            );
        }

        return response()->json(['id' => $food->id, 'message' => translate('Item updated successfully')]);
    }

    public function delete(Request $request, $id)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $food = Food::where('restaurant_id', $restaurant->id)->findOrFail($id);
        $food->newVariations()->each(function ($v) {
            $v->variationOptions()->delete();
            $v->delete();
        });
        $food->delete();
        return response()->json(['message' => translate('Item deleted successfully')]);
    }

    public function toggleStatus(Request $request)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $food = Food::where('restaurant_id', $restaurant->id)->findOrFail($request->id);
        $food->status = $request->status;
        $food->save();
        if ($food->status != 1) {
            $food->carts()?->delete();
        }
        return response()->json(['status' => $food->status]);
    }

    public function getCategories(Request $request)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $parentId = $request->parent_id ?? 0;
        $categories = Category::where('parent_id', $parentId)
            ->where('restaurant_id', $restaurant->id)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($categories);
    }

    // ── AI Helpers ────────────────────────────────────────────────────────────

    public function aiDescription(Request $request)
    {
        $name = trim($request->name ?? '');
        if (!$name) return response()->json(['error' => 'Item name required'], 422);

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) return response()->json(['error' => 'No Gemini API key set in Admin settings'], 400);

        $prompt = "Write a short, appetizing menu description for a food item called \"{$name}\". 1-2 sentences only, focus on taste and appeal. Plain text, no markdown.";

        foreach (['gemini-2.5-flash', 'gemini-2.0-flash-001', 'gemini-flash-latest'] as $model) {
            try {
                $r = Http::timeout(15)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                    ['contents' => [['parts' => [['text' => $prompt]]]], 'generationConfig' => ['thinkingConfig' => ['thinkingBudget' => 0]]]
                );
                if ($r->successful()) {
                    $text = trim($r->json('candidates.0.content.parts.0.text') ?? '');
                    if ($text) return response()->json(['description' => $text]);
                }
            } catch (\Exception $e) {}
        }
        return response()->json(['error' => 'Description generation failed'], 500);
    }

    public function aiImage(Request $request)
    {
        $name = trim($request->name ?? '');
        if (!$name) return response()->json(['error' => 'Item name required'], 422);

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) return response()->json(['error' => 'No Gemini API key set in Admin settings'], 400);

        $prompt = "Generate a professional food photograph of {$name}. Restaurant quality, appetizing, clean white plate, high quality food menu photo.";

        foreach (['gemini-2.5-flash-image', 'gemini-3.1-flash-image', 'gemini-3-pro-image'] as $model) {
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
            } catch (\Exception $e) {}
        }
        return response()->json(['error' => 'Gemini image generation failed'], 500);
    }

    // ── Restaurant-Specific Category CRUD ─────────────────────────────────────

    public function categoryList(Request $request)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $cats = Category::where('restaurant_id', $restaurant->id)
            ->where('position', 0)
            ->orderBy('name')
            ->get(['id', 'name', 'image', 'status']);

        return response()->json($cats->map(function ($c) {
            return [
                'id'        => $c->id,
                'name'      => $c->name,
                'status'    => $c->status,
                'image_url' => $c->image_full_url,
            ];
        }));
    }

    public function categoryStore(Request $request)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'  => 'required|max:191',
            'image' => 'nullable|image|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $cat = new Category();
        $cat->restaurant_id = $restaurant->id;
        $cat->name          = $request->name;
        $cat->image         = 'def.png';
        $cat->parent_id     = 0;
        $cat->position      = 0;
        $cat->status        = 1;
        $cat->save();

        if ($request->hasFile('image')) {
            $imageName = Helpers::upload(dir: 'category/', format: 'png', image: $request->file('image'));
            $cat->image = $imageName;
            $cat->save();
            Helpers::updateStorageTable(Category::class, $cat->id, $imageName);
        } elseif ($request->filled('image_base64')) {
            $base64  = preg_replace('/^data:image\/\w+;base64,/', '', $request->image_base64);
            $decoded = base64_decode($base64);
            if ($decoded) {
                $fname = 'category/' . now()->toDateString() . '-' . uniqid() . '.png';
                Storage::disk('public')->put($fname, $decoded);
                $imageName = basename($fname);
                $cat->image = $imageName;
                $cat->save();
                Helpers::updateStorageTable(Category::class, $cat->id, $imageName);
            }
        }

        return response()->json([
            'id'        => $cat->id,
            'name'      => $cat->name,
            'status'    => $cat->status,
            'image_url' => $cat->refresh()->image_full_url,
        ]);
    }

    public function categoryUpdate(Request $request, $id)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $cat = Category::where('id', $id)->where('restaurant_id', $restaurant->id)->first();
        if (!$cat) return response()->json(['error' => 'Category not found'], 404);

        $validator = Validator::make($request->all(), [
            'name'  => 'required|max:191',
            'image' => 'nullable|image|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $cat->name = $request->name;

        if ($request->hasFile('image')) {
            $cat->image = Helpers::update(dir: 'category/', old_image: $cat->image, format: 'png', image: $request->file('image'));
        } elseif ($request->filled('image_base64')) {
            $base64  = preg_replace('/^data:image\/\w+;base64,/', '', $request->image_base64);
            $decoded = base64_decode($base64);
            if ($decoded) {
                $fname = 'category/' . now()->toDateString() . '-' . uniqid() . '.png';
                Storage::disk('public')->put($fname, $decoded);
                $cat->image = basename($fname);
                Helpers::updateStorageTable(Category::class, $cat->id, $cat->image);
            }
        }

        $cat->save();
        return response()->json([
            'id'        => $cat->id,
            'name'      => $cat->name,
            'status'    => $cat->status,
            'image_url' => $cat->refresh()->image_full_url,
        ]);
    }

    public function categoryDelete(Request $request, $id)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $cat = Category::where('id', $id)->where('restaurant_id', $restaurant->id)->first();
        if (!$cat) return response()->json(['error' => 'Category not found'], 404);

        Food::where('restaurant_id', $restaurant->id)->where('category_id', $id)->update(['category_id' => null]);
        $cat->delete();
        return response()->json(['success' => true]);
    }

    public function categoryAiImage(Request $request)
    {
        $name = trim($request->name ?? '');
        if (!$name) return response()->json(['error' => 'Category name required'], 422);

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) return response()->json(['error' => 'No Gemini API key set in Admin settings'], 400);

        $prompt = "Generate a professional food category banner photo for a restaurant menu category called \"{$name}\". Clean, appetizing, high quality, suitable as a menu category header image.";

        foreach (['gemini-2.5-flash-image', 'gemini-3.1-flash-image', 'gemini-3-pro-image'] as $model) {
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
            } catch (\Exception $e) {}
        }
        return response()->json(['error' => 'AI image generation failed'], 500);
    }

    // ── Addon CRUD (Following Listing Manager Flow) ───────────────────────────

    public function addonList(Request $request)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $addons = AddOn::where('restaurant_id', $restaurant->id)->latest()->get();

        return response()->json(Helpers::addon_data_formatting($addons, true, true, app()->getLocale()), 200);
    }

    public function addonStore(Request $request)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant || !$restaurant->food_section) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => translate('messages.permission_denied')]]], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'stock_type' => 'required|max:20',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $nameDefault = '';
        $translationsToSave = [];

        if ($request->has('translations')) {
            $transData = is_string($request->translations) ? json_decode($request->translations, true) : $request->translations;
            foreach ($transData as $item) {
                if ($item['key'] === 'name' && ($item['locale'] === 'default' || $item['locale'] === 'en' || !$nameDefault)) {
                    $nameDefault = $item['value'];
                }
                $translationsToSave[] = [
                    'locale' => $item['locale'],
                    'key' => $item['key'],
                    'value' => $item['value'],
                ];
            }
        } else {
            $nameDefault = is_array($request->name) ? ($request->name['default'] ?? reset($request->name)) : $request->name;
            $translationsToSave[] = ['locale' => 'default', 'key' => 'name', 'value' => $nameDefault];
        }

        $addon = new AddOn();
        $addon->name = $nameDefault;
        $addon->price = $request->price;
        $addon->restaurant_id = $restaurant->id;
        $addon->stock_type = $request->stock_type ?? 'unlimited';
        $addon->addon_stock = $request->stock_type != 'unlimited' ?  $request->addon_stock : 0;
        $addon->save();

        foreach ($translationsToSave as $t) {
            Translation::updateOrInsert(
                [
                    'translationable_type' => 'App\Models\AddOn',
                    'translationable_id' => $addon->id,
                    'locale' => $t['locale'],
                    'key' => $t['key']
                ],
                ['value' => $t['value']]
            );
        }

        return response()->json(['id' => $addon->id, 'message' => translate('messages.addon_added_successfully')], 200);
    }

    public function addonUpdate(Request $request, $id)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant || !$restaurant->food_section) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => translate('messages.permission_denied')]]], 403);
        }

        $addon = AddOn::where('restaurant_id', $restaurant->id)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'price' => 'required|numeric|min:0',
            'stock_type' => 'required|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $nameDefault = '';
        $translationsToSave = [];

        if ($request->has('translations')) {
            $transData = is_string($request->translations) ? json_decode($request->translations, true) : $request->translations;
            foreach ($transData as $item) {
                if ($item['key'] === 'name' && ($item['locale'] === 'default' || $item['locale'] === 'en' || !$nameDefault)) {
                    $nameDefault = $item['value'];
                }
                $translationsToSave[] = [
                    'locale' => $item['locale'],
                    'key' => $item['key'],
                    'value' => $item['value'],
                ];
            }
        } else {
            $nameDefault = is_array($request->name) ? ($request->name['default'] ?? reset($request->name)) : $request->name;
            $translationsToSave[] = ['locale' => 'default', 'key' => 'name', 'value' => $nameDefault];
        }

        $addon->name = $nameDefault;
        $addon->price = $request->price;
        $addon->stock_type = $request->stock_type ?? 'unlimited';
        $addon->addon_stock = $request->stock_type != 'unlimited' ?  $request->addon_stock : 0;
        $addon->save();

        foreach ($translationsToSave as $t) {
            Translation::updateOrInsert(
                [
                    'translationable_type' => 'App\Models\AddOn',
                    'translationable_id' => $addon->id,
                    'locale' => $t['locale'],
                    'key' => $t['key']
                ],
                ['value' => $t['value']]
            );
        }

        return response()->json(['id' => $addon->id, 'message' => translate('messages.addon_updated_successfully')], 200);
    }

    public function addonDelete(Request $request, $id)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $addon = AddOn::where('restaurant_id', $restaurant->id)->findOrFail($id);
        $addon->translations()->delete();
        $addon->delete();

        return response()->json(['message' => translate('messages.addon_deleted_successfully')], 200);
    }

    public function addonToggleStatus(Request $request)
    {
        $restaurant = $this->getRestaurant($request);
        if (!$restaurant) {
            return response()->json(['errors' => [['code' => 'not-found', 'message' => 'Restaurant not found']]], 404);
        }

        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $addon = AddOn::where('restaurant_id', $restaurant->id)->findOrFail($request->id);
        $addon->status = $request->status;
        $addon->save();

        return response()->json(['message' => translate('messages.addon_status_updated')], 200);
    }
}
