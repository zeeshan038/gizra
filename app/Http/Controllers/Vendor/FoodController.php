<?php

namespace App\Http\Controllers\Vendor;

use App\Models\Allergy;
use App\Models\Nutrition;
use DateTime;
use Carbon\Carbon;
use App\Models\Tag;
use App\Models\Food;
use App\Models\Review;
use App\Models\Category;
use App\Models\Variation;
use App\Models\Translation;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
use App\Models\VariationOption;
use Illuminate\Support\Facades\DB;
use App\CentralLogics\ProductLogic;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use App\Models\BusinessSetting;
use Illuminate\Support\Facades\Http;
use Rap2hpoutre\FastExcel\FastExcel;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class FoodController extends Controller
{
    public function index()
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $categories = Category::where(['position' => 0])->get();
        return view('vendor-views.product.index', compact('categories'));
    }

    public function store(Request $request)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            return response()->json([
                    'errors'=>[
                        ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                    ]
                ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'array',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'image' => 'nullable|max:2048',
            'price' => 'required|numeric|between:.01,999999999999.99',
            'description.*' => 'max:1000',
            'discount' => 'required|numeric|min:0',
        ], [
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
            'veg.required'=>translate('messages.item_type_is_required'),
            'description.*.max' => translate('messages.description_length_warning'),
        ]);


        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }

        if ($request['price'] <= $dis || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }


        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if(isset($tags)){
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => $value]
                );
                $tag->save();
                array_push($tag_ids,$tag->id);
            }
        }

        $nutrition_ids = [];
        if ($request->nutritions != null) {
            $nutritions = $request->nutritions;
        }
        if (isset($nutritions)) {
            foreach ($nutritions as $key => $value) {
                $nutrition = Nutrition::firstOrNew(
                    ['nutrition' => $value]
                );
                $nutrition->save();
                array_push($nutrition_ids, $nutrition->id);
            }
        }
        $allergy_ids = [];
        if ($request->allergies != null) {
            $allergies = $request->allergies;
        }
        if (isset($allergies)) {
            foreach ($allergies as $key => $value) {
                $allergy = Allergy::firstOrNew(
                    ['allergy' => $value]
                );
                $allergy->save();
                array_push($allergy_ids, $allergy->id);
            }
        }

        $food = new Food;
        $food->name = $request->name[array_search('default', $request->lang)];

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }
        $food->category_id = $request->sub_category_id ?? $request->category_id;
        $food->category_ids = json_encode($category);
        $food->description = $request->description[array_search('default', $request->lang)];

        $food->choice_options = json_encode([]);


        $food->variations = json_encode([]);
        $food->price = $request->price;
        $food->veg = $request->veg;
        $food->image = Helpers::upload(dir:'product/', format:'png', image:$request->file('image'));
        $food->available_time_starts = $request->available_time_starts;
        $food->available_time_ends = $request->available_time_ends;
        $food->discount =  $request->discount ?? 0;
        $food->discount_type = $request->discount_type;
        $food->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $food->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $food->restaurant_id = Helpers::get_restaurant_id();
        $food->maximum_cart_quantity = $request->maximum_cart_quantity;
        $food->is_halal =  $request->is_halal ?? 0;
        $food->stock_type = $request->stock_type ?? $food->stock_type ?? 'unlimited';
        $food->item_stock = ($food->stock_type !== 'unlimited') ? ($request->item_stock ?? $food->item_stock ?? 0) : 0;

        $restaurant= Helpers::get_restaurant_data();
        if ( $restaurant->restaurant_model == 'subscription' ) {
            $rest_sub = $restaurant?->restaurant_sub;
            if (isset($rest_sub)) {
                if ($rest_sub->max_product != "unlimited" && $rest_sub->max_product > 0 ) {
                    $total_food= Food::where('restaurant_id', $restaurant->id)->count()+1;
                    if ( $total_food >= $rest_sub->max_product){
                        $restaurant->food_section = 0;
                        $restaurant->save();
                    }
                }
            } else{
                return response()->json([
                    'errors'=>[
                        ['code'=>'unauthorized', 'message'=>translate('messages.you_are_not_subscribed_to_any_package')]
                    ]
                ]);
            }
        }elseif( $restaurant->restaurant_model == 'unsubscribed'){
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.you_are_not_subscribed_to_any_package')]
                ]
            ]);
        }

        if(isset($request->options))
        {
            foreach(array_values($request->options) as $key=>$option)
            {
                if($option['min'] > 0 &&  $option['min'] > $option['max']  ){
                    $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if(!isset($option['values'])){
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for').$option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if($option['max'] > count($option['values'])  ){
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').$option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
            }

            $food->save();

            foreach(array_values($request->options) as $key=>$option)
            {
                $variation=  New Variation ();
                $variation->food_id =$food->id;
                $variation->name = $option['name'];
                $variation->type = $option['type'];
                $variation->min = $option['min'] ?? 0;
                $variation->max = $option['max'] ?? 0;
                $variation->is_required =   data_get($option, 'required') == 'on' ? true : false;
                $variation->save();

                foreach(array_values($option['values']) as $value)
                {
                    $VariationOption=  New VariationOption ();
                    $VariationOption->food_id =$food->id;
                    $VariationOption->variation_id =$variation->id;
                    $VariationOption->option_name = $value['label'];
                    $VariationOption->option_price = $value['optionPrice'];
                    $VariationOption->stock_type = $request?->stock_type ?? 'unlimited' ;
                    $VariationOption->total_stock = data_get($value, 'total_stock') == null || $VariationOption->stock_type == 'unlimited' ? 0 : data_get($value, 'total_stock');
                    $VariationOption->save();
                }
            }
        }
        else{
            $food->save();
        }
        $food->tags()->sync($tag_ids);
        $food->nutritions()->sync($nutrition_ids);
        $food->allergies()->sync($allergy_ids);

        Helpers::add_or_update_translations(request: $request, key_data:'name' , name_field:'name' , model_name: 'Food' ,data_id: $food->id,data_value: $food->name);
        Helpers::add_or_update_translations(request: $request, key_data:'description' , name_field:'description' , model_name: 'Food' ,data_id: $food->id,data_value: $food->description);

        return response()->json([], 200);
    }

    public function view($id)
    {
        $product = Food::findOrFail($id);
        $reviews=Review::where(['food_id'=>$id])->latest()->paginate(config('default_pagination'));
        return view('vendor-views.product.view', compact('product','reviews'));
    }

    public function edit($id)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }

        $product = Food::withoutGlobalScope('translate')->findOrFail($id);
        $product_category = json_decode($product->category_ids);
        $categories = Category::where(['parent_id' => 0])->get();
        return view('vendor-views.product.edit', compact('product', 'product_category', 'categories'));
    }

    public function status(Request $request)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $product = Food::find($request->id);
        $product->status = $request->status;
        $product->save();
        if($request->status != 1){
            $product?->carts()?->delete();
        }
        Toastr::success(translate('Food status updated!'));
        return back();
    }
    public function recommended(Request $request)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $product = Food::find($request->id);
        $product->recommended = $request->status;
        $product->save();
        Toastr::success(translate('Food recommendation updated!'));
        return back();
    }

    public function update(Request $request, $id)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            return response()->json([
                'errors'=>[
                    ['code'=>'unauthorized', 'message'=>translate('messages.permission_denied')]
                ]
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'array',
            'name.0' => 'required',
            'name.*' => 'max:191',
            'category_id' => 'required',
            'price' => 'required|numeric|between:0.01,999999999999.99',
            'description.*' => 'max:1000',
            'discount' => 'required|numeric|min:0',
            'image' => 'nullable|max:2048',
        ], [
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
            'veg.required'=>translate('messages.item_type_is_required'),
            'description.*.max' => translate('messages.description_length_warning'),
        ]);

        if ($request['discount_type'] == 'percent') {
            $dis = ($request['price'] / 100) * $request['discount'];
        } else {
            $dis = $request['discount'];
        }

        if ($request['price'] <= $dis) {
            $validator->getMessageBag()->add('unit_price', translate('messages.discount_can_not_be_more_than_or_equal'));
        }

        if ($request['price'] <= $dis || $validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $tag_ids = [];
        if ($request->tags != null) {
            $tags = explode(",", $request->tags);
        }
        if(isset($tags)){
            foreach ($tags as $key => $value) {
                $tag = Tag::firstOrNew(
                    ['tag' => $value]
                );
                $tag->save();
                array_push($tag_ids,$tag->id);
            }
        }

        $nutrition_ids = [];
        if ($request->nutritions != null) {
            $nutritions = $request->nutritions;
        }
        if (isset($nutritions)) {
            foreach ($nutritions as $key => $value) {
                $nutrition = Nutrition::firstOrNew(
                    ['nutrition' => $value]
                );
                $nutrition->save();
                array_push($nutrition_ids, $nutrition->id);
            }
        }
        $allergy_ids = [];
        if ($request->allergies != null) {
            $allergies = $request->allergies;
        }
        if (isset($allergies)) {
            foreach ($allergies as $key => $value) {
                $allergy = Allergy::firstOrNew(
                    ['allergy' => $value]
                );
                $allergy->save();
                array_push($allergy_ids, $allergy->id);
            }
        }

        $p = Food::find($id);

        $p->name = $request->name[array_search('default', $request->lang)];

        $slug = Str::slug($request->name[array_search('default', $request->lang)]);
        $p->slug = $p->slug? $p->slug :"{$slug}-{$p->id}";

        $category = [];
        if ($request->category_id != null) {
            array_push($category, [
                'id' => $request->category_id,
                'position' => 1,
            ]);
        }
        if ($request->sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_category_id,
                'position' => 2,
            ]);
        }
        if ($request->sub_sub_category_id != null) {
            array_push($category, [
                'id' => $request->sub_sub_category_id,
                'position' => 3,
            ]);
        }

        $p->category_id = $request->sub_category_id?$request->sub_category_id:$request->category_id;
        $p->category_ids = json_encode($category);
        $p->description = $request->description[array_search('default', $request->lang)];
        $p->choice_options = json_encode([]);
        $p->variations = json_encode([]);

        if(isset($request->options))
        {
            foreach(array_values($request->options) as $key=>$option)
            {
                if($option['min'] > 0 &&  $option['min'] > $option['max']  ){
                    $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if(!isset($option['values'])){
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for').$option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if($option['max'] > count($option['values'])  ){
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for').$option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }

                $variation=Variation::updateOrCreate([
                    'id'=> $option['variation_id'] ?? null,
                    'food_id'=> $p->id,
                    ],[
                        "name" => $option['name'],
                        "type" => $option['type'],
                        "min" => $option['min'] ?? 0,
                        "max" => $option['max'] ?? 0,
                        "is_required" => data_get($option, 'required') == 'on' ? true : false,
                    ]);

                foreach(array_values($option['values']) as $value)
                {
                    VariationOption::updateOrCreate([
                        'id'=> $value['option_id'] ?? null,
                        'food_id'=> $p->id,
                        'variation_id'=> $variation->id,
                    ],[
                        "option_name" =>$value['label'],
                        "option_price" => $value['optionPrice'],
                        "total_stock" =>data_get($value, 'total_stock') == null ||  $request?->stock_type == 'unlimited' ? 0 : data_get($value, 'total_stock'),
                        "stock_type" => $request?->stock_type ?? 'unlimited' ,
                        "sell_count" =>0 ,
                    ]);
                }
            }

        }
        if($request?->removedVariationOptionIDs && is_string($request?->removedVariationOptionIDs)){
            VariationOption::whereIn('id',explode(',',$request->removedVariationOptionIDs))->delete();
        }
        if($request?->removedVariationIDs && is_string($request?->removedVariationIDs)){
            VariationOption::whereIn('variation_id',explode(',',$request->removedVariationIDs))->delete();
            Variation::whereIn('id',explode(',',$request->removedVariationIDs))->delete();
        }

        $p->item_stock = $request?->item_stock ?? 0;
        $p->stock_type = $request->stock_type;

        $p->price = $request->price;
        $p->veg = $request->veg;
        $p->image = $request->has('image') ? Helpers::update(dir:'product/',old_image: $p->image,format: 'png', image:$request->file('image')) : $p->image;
        $p->available_time_starts = $request->available_time_starts;
        $p->available_time_ends = $request->available_time_ends;
        $p->discount = $request->discount ?? 0;
        $p->discount_type = $request->discount_type;
        $p->attributes = $request->has('attribute_id') ? json_encode($request->attribute_id) : json_encode([]);
        $p->add_ons = $request->has('addon_ids') ? json_encode($request->addon_ids) : json_encode([]);
        $p->maximum_cart_quantity = $request->maximum_cart_quantity;
        $p->is_halal =  $request->is_halal ?? 0;
        $p->sell_count = 0;

        $p->save();
        $p->tags()->sync($tag_ids);
        $p->nutritions()->sync($nutrition_ids);
        $p->allergies()->sync($allergy_ids);


        Helpers::add_or_update_translations(request: $request, key_data:'name' , name_field:'name' , model_name: 'Food' ,data_id: $p->id,data_value: $p->name);
        Helpers::add_or_update_translations(request: $request, key_data:'description' , name_field:'description' , model_name: 'Food' ,data_id: $p->id,data_value: $p->description);

        return response()->json([], 200);
    }

    public function delete(Request $request)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $product = Food::find($request->id);

        if($product->image)
        {
            Helpers::check_and_delete('product/' , $product['image']);
        }
        $product?->carts()?->delete();
        $product?->newVariationOptions()?->delete();
        $product?->newVariations()?->delete();
        $product?->translations()?->delete();

        $product->delete();
        Toastr::success(translate('Food removed!'));
        return back();
    }

    public function get_categories(Request $request)
    {
        $cat = Category::where(['parent_id' => $request->parent_id])->get();
        $res = '<option value="' . 0 . '" disabled selected>---Select---</option>';
        foreach ($cat as $row) {
            if ($row->id == $request->sub_category) {
                $res .= '<option value="' . $row->id . '" selected >' . $row->name . '</option>';
            } else {
                $res .= '<option value="' . $row->id . '">' . $row->name . '</option>';
            }
        }
        return response()->json([
            'options' => $res,
        ]);
    }

    public function list(Request $request)
    {
        $category_id = $request->query('category_id', 'all');
        $type = $request->query('type', 'all');
        $foods = Food::
        when(is_numeric($category_id), function($query)use($category_id){
            return $query->whereHas('category',function($q)use($category_id){
                return $q->whereId($category_id)->orWhere('parent_id', $category_id);
            });
        })
        ->type($type)->latest()->paginate(config('default_pagination'));
        $category =$category_id !='all'? Category::findOrFail($category_id):null;
        return view('vendor-views.product.list', compact('foods', 'category', 'type'));
    }

    public function search(Request $request){
        $key = explode(' ', $request['search']);
        $foods=Food::where(function ($q) use ($key) {
            foreach ($key as $value) {
                $q->where('name', 'like', "%{$value}%");
            }
        })->limit(50)->get();
        return response()->json([
            'view'=>view('vendor-views.product.partials._table',compact('foods'))->render()
        ]);
    }

    public function bulk_import_index()
    {
        return view('vendor-views.product.bulk-import');
    }

    public function bulk_import_data(Request $request)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $request->validate([
            'products_file' => 'required|max:2048',
        ]);

        try {
            $collections = (new FastExcel)->import($request->file('products_file'));
        } catch (\Exception $exception) {
            Toastr::error(translate('messages.you_have_uploaded_a_wrong_format_file'));
            return back();
        }

        $data = [];
        if($request->button == 'import'){

            try {
                foreach ($collections as $collection) {
                    if ($collection['Id'] === "" || $collection['Name'] === "" || $collection['CategoryId'] === "" || $collection['SubCategoryId'] === "" || $collection['Price'] === "" || empty($collection['AvailableTimeStarts'])  || empty($collection['AvailableTimeEnds'])  || $collection['Discount'] === "") {
                        Toastr::error(translate('messages.please_fill_all_required_fields'));
                        return back();
                    }
                    if(isset($collection['Price']) && ($collection['Price'] < 0  )  ) {
                        Toastr::error(translate('messages.Price_must_be_greater_then_0_on_id').' '.$collection['Id']);
                        return back();
                    }
                    if(isset($collection['Discount']) && ($collection['Discount'] < 0  )  ) {
                        Toastr::error(translate('messages.Discount_must_be_greater_then_0_on_id').' '.$collection['Id']);
                        return back();
                    }

                    try{
                            $t1= Carbon::parse($collection['AvailableTimeStarts']);
                            $t2= Carbon::parse($collection['AvailableTimeEnds']) ;
                            if($t1->gt($t2)   ) {
                                Toastr::error(translate('messages.AvailableTimeEnds_must_be_greater_then_AvailableTimeStarts_on_id').' '.$collection['Id']);
                                return back();
                            }
                        }catch(\Exception $e){
                            info(["line___{$e->getLine()}",$e->getMessage()]);
                            Toastr::error(translate('messages.Invalid_AvailableTimeEnds_or_AvailableTimeStarts_on_id').' '.$collection['Id']);
                            return back();
                        }


                    array_push($data, [
                        'name' => $collection['Name'],
                        'description' => $collection['Description'],
                        'image' => $collection['Image'],
                        'category_id' => $collection['SubCategoryId']?$collection['SubCategoryId']:$collection['CategoryId'],
                        'category_ids' => json_encode([['id' => $collection['CategoryId'], 'position' => 1], ['id' => $collection['SubCategoryId'], 'position' => 2]]),
                        'restaurant_id' => Helpers::get_restaurant_id(),
                        'price' => $collection['Price'],
                        'discount' => $collection['Discount'] ?? 0,
                        'discount_type' => $collection['DiscountType'] ??  'percent',
                        'available_time_starts' => $collection['AvailableTimeStarts'],
                        'available_time_ends' => $collection['AvailableTimeEnds'],
                        'variations' => $collection['Variations'] ?? json_encode([]),
                        'add_ons' => $collection['Addons'] ?($collection['Addons']==""?json_encode([]):$collection['Addons']): json_encode([]),
                        'veg' => $collection['Veg'] == 'yes' ? 1 : 0,
                        'recommended' => $collection['Recommended'] == 'yes' ? 1 : 0,
                        'status' => $collection['Status'] == 'active' ? 1 : 0,
                        'created_at'=>now(),
                        'updated_at'=>now()
                    ]);
                }

            }catch(\Exception $e){
                info(["line___{$e->getLine()}",$e->getMessage()]);
                Toastr::error(translate('messages.failed_to_import_data'));
                return back();

            }

            try{
                DB::beginTransaction();
                $total_food= count($data);

                $restaurant= Helpers::get_restaurant_data();
                if ( $restaurant->restaurant_model == 'subscription' ) {
                    $rest_sub=$restaurant?->restaurant_sub;
                    if (isset($rest_sub)) {
                        if ($rest_sub->max_product != "unlimited" && $rest_sub->max_product > 0  &&  $rest_sub->max_product >= $total_food ) {
                            $rest_sub->decrement('max_product' , $total_food);
                            if (  $rest_sub->max_product <= 0 ){
                                $restaurant->update(['food_section' => 0]);
                            }
                        } else{
                            Toastr::error(translate('messages.you_have_reached_the_maximum_limit_of_food'));
                            return back();
                        }


                        if ($rest_sub->max_product != "unlimited" && $rest_sub->max_product > 0 ) {
                            $total_all_foods= Food::where('restaurant_id', $restaurant->id)->count();

                            $available_food_uploads= $total_all_foods + $total_food;
                            if ($available_food_uploads > $rest_sub->max_product){
                                Toastr::error(translate('messages.you_have_reached_the_maximum_limit_of_food'));
                                return back();
                            }
                        }

                    } else{
                        return response()->json([
                            'errors'=>[
                                ['code'=>'unauthorized', 'message'=>translate('messages.you_are_not_subscribed_to_any_package')]
                            ]
                        ]);
                    }
                }

                    $chunkSize = 100;
                    $chunk_items= array_chunk($data,$chunkSize);
                    foreach($chunk_items as $key=> $chunk_item){
//                        DB::table('food')->insert($chunk_item);
                        foreach ($chunk_item as $item) {
                            $insertedId = DB::table('food')->insertGetId($item);
                            Helpers::updateStorageTable(get_class(new Food), $insertedId, $item['image']);
                        }
                    }

                DB::commit();
            }catch(\Exception $e){
                DB::rollBack();
                info(["line___{$e->getLine()}",$e->getMessage()]);
                Toastr::error(translate('messages.failed_to_import_data'));
                return back();

            }

            Toastr::success(translate('messages.product_imported_successfully', ['count'=>count($data)]));
            return back();
        }

            try{
                foreach ($collections as $collection) {
                    if ($collection['Id'] === "" || $collection['Name'] === "" || $collection['CategoryId'] === "" || $collection['SubCategoryId'] === "" || $collection['Price'] === "" || empty($collection['AvailableTimeStarts'])  || empty($collection['AvailableTimeEnds'])  || $collection['Discount'] === "") {
                        Toastr::error(translate('messages.please_fill_all_required_fields'));
                        return back();
                    }
                    if(isset($collection['Price']) && ($collection['Price'] < 0  )  ) {
                        Toastr::error(translate('messages.Price_must_be_greater_then_0_on_id').' '.$collection['Id']);
                        return back();
                    }
                    if(isset($collection['Discount']) && ($collection['Discount'] < 0  )  ) {
                        Toastr::error(translate('messages.Discount_must_be_greater_then_0_on_id').' '.$collection['Id']);
                        return back();
                    }

                    try{
                            $t1= Carbon::parse($collection['AvailableTimeStarts']);
                            $t2= Carbon::parse($collection['AvailableTimeEnds']) ;
                            if($t1->gt($t2)   ) {
                                Toastr::error(translate('messages.AvailableTimeEnds_must_be_greater_then_AvailableTimeStarts_on_id').' '.$collection['Id']);
                                return back();
                            }
                        }catch(\Exception $e){
                            info(["line___{$e->getLine()}",$e->getMessage()]);
                            Toastr::error(translate('messages.Invalid_AvailableTimeEnds_or_AvailableTimeStarts_on_id').' '.$collection['Id']);
                            return back();
                        }

                    array_push($data, [
                        'id' => $collection['Id'],
                        'name' => $collection['Name'],
                        'description' => $collection['Description'],
                        'image' => $collection['Image'],
                        'category_id' => $collection['SubCategoryId']?$collection['SubCategoryId']:$collection['CategoryId'],
                        'category_ids' => json_encode([['id' => $collection['CategoryId'], 'position' => 1], ['id' => $collection['SubCategoryId'], 'position' => 2]]),
                        'restaurant_id' => Helpers::get_restaurant_id(),
                        'price' => $collection['Price'],
                        'discount' => $collection['Discount'] ?? 0,
                        'discount_type' => $collection['DiscountType'] ??  'percent',
                        'available_time_starts' => $collection['AvailableTimeStarts'],
                        'available_time_ends' => $collection['AvailableTimeEnds'],
                        'variations' => $collection['Variations'] ?? json_encode([]),
                        'add_ons' => $collection['Addons'] ?($collection['Addons']==""?json_encode([]):$collection['Addons']): json_encode([]),
                        'veg' => $collection['Veg'] == 'yes' ? 1 : 0,
                        'recommended' => $collection['Recommended'] == 'yes' ? 1 : 0,
                        'status' => $collection['Status'] == 'active' ? 1 : 0,
                        'updated_at'=>now()
                    ]);
                }
            }catch(\Exception $e)
            {
                info(["line___{$e->getLine()}",$e->getMessage()]);
                Toastr::error(translate('messages.failed_to_import_data'));
                return back();
            }

        try{
            DB::beginTransaction();
            $chunkSize = 100;
            $chunk_items= array_chunk($data,$chunkSize);
            foreach($chunk_items as $key=> $chunk_item){
//                DB::table('food')->upsert($chunk_item,['id'],['name','description','image','category_id','category_ids','price','discount','discount_type','available_time_starts','available_time_ends','variations','add_ons','status','veg','recommended']);
                foreach ($chunk_item as $item) {
                    if (isset($item['id']) && DB::table('food')->where('id', $item['id'])->exists()) {
                        DB::table('food')->where('id', $item['id'])->update($item);
                        Helpers::updateStorageTable(get_class(new Food), $item['id'], $item['image']);
                    } else {
                        $insertedId = DB::table('food')->insertGetId($item);
                        Helpers::updateStorageTable(get_class(new Food), $insertedId, $item['image']);
                    }
                }
            }
            DB::commit();
        }catch(\Exception $e)
        {
            DB::rollBack();
            info(["line___{$e->getLine()}",$e->getMessage()]);
            Toastr::error(translate('messages.failed_to_import_data'));
            return back();
        }

        Toastr::success(translate('messages.Food_imported_successfully', ['count' => count($data)]));
        return back();



    }

    public function bulk_export_index()
    {
        return view('vendor-views.product.bulk-export');
    }

    public function bulk_export_data(Request $request)
    {
        if(!Helpers::get_restaurant_data()->food_section)
        {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }

        $request->validate([
            'type'=>'required',
            'start_id'=>'required_if:type,id_wise',
            'end_id'=>'required_if:type,id_wise',
            'from_date'=>'required_if:type,date_wise',
            'to_date'=>'required_if:type,date_wise'
        ]);
        $products = Food::when($request['type']=='date_wise', function($query)use($request){
            $query->whereBetween('created_at', [$request['from_date'].' 00:00:00', $request['to_date'].' 23:59:59']);
        })
        ->when($request['type']=='id_wise', function($query)use($request){
            $query->whereBetween('id', [$request['start_id'], $request['end_id']]);
        })
        ->where('restaurant_id', Helpers::get_restaurant_id())
        ->get();

        return (new FastExcel(ProductLogic::format_export_foods($products)))->download('Foods.xlsx');
    }

    public function food_variation_generator(Request $request){
        $validator = Validator::make($request->all(), [
            'options' => 'required',
        ]);

        $food_variations = [];
        if (isset($request->options)) {
            foreach (array_values($request->options) as $key => $option) {

                $temp_variation['name'] = $option['name'];
                $temp_variation['type'] = $option['type'];
                $temp_variation['min'] = $option['min'] ?? 0;
                $temp_variation['max'] = $option['max'] ?? 0;
                $temp_variation['required'] = $option['required'] ?? 'off';
                if ($option['min'] > 0 &&  $option['min'] > $option['max']) {
                    $validator->getMessageBag()->add('name', translate('messages.minimum_value_can_not_be_greater_then_maximum_value'));
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if (!isset($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_options_for') . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                if ($option['max'] > count($option['values'])) {
                    $validator->getMessageBag()->add('name', translate('messages.please_add_more_options_or_change_the_max_value_for') . $option['name']);
                    return response()->json(['errors' => Helpers::error_processor($validator)]);
                }
                $temp_value = [];

                foreach (array_values($option['values']) as $value) {
                    if (isset($value['label'])) {
                        $temp_option['label'] = $value['label'];
                    }
                    $temp_option['optionPrice'] = $value['optionPrice'];
                    array_push($temp_value, $temp_option);
                }
                $temp_variation['values'] = $temp_value;
                array_push($food_variations, $temp_variation);
            }
        }

        return response()->json([
            'variation' => json_encode($food_variations)
        ]);
    }


        public function stockOutList(Request $request){


            $category_id = $request->query('category_id', 'all');
            $type = $request->query('type', 'all');
            $foods =
            Food::where('stock_type','!=' ,'unlimited' )->where(function($query){
            $query->whereRaw('item_stock - sell_count <= 0')->orWhereHas('newVariationOptions',function($query){
                $query->whereRaw('total_stock - sell_count <= 0');
            });

            })

            ->when(is_numeric($category_id), function($query)use($category_id){
                return $query->whereHas('category',function($q)use($category_id){
                    return $q->whereId($category_id)->orWhere('parent_id', $category_id);
                });
            })
            ->type($type)->latest()->paginate(config('default_pagination'));
            $category =$category_id !='all'? Category::findOrFail($category_id):null;
            return view('vendor-views.product.out_of_stock_list', compact('foods', 'category', 'type'));


        }

    public function updateStock(Request $request){
        $product = Food::findOrFail($request->food_id);
        $product->item_stock = $request->item_stock;
        $product->sell_count =0;
        $product->save() ;
        if($request->option){
                foreach($request->option  as $key => $value ){
                    VariationOption::where('food_id',$product->id)->where('id',$key)->update([
                        'sell_count' => 0,
                        'total_stock'=> $value
                    ]);
                }
        }
        Toastr::success(translate('Stock_updated_successfully'));
        return back();
    }

    public function addToSession(Request $request)
    {
        Session::put($request->value, true);
        return response()->json(['success' => true]);
    }

    // ─── Listing Manager ────────────────────────────────────────────────────────

    public function listingManager()
    {
        if (!Helpers::get_restaurant_data()->food_section) {
            Toastr::warning(translate('messages.permission_denied'));
            return back();
        }
        $rid = Helpers::get_restaurant_id();
        $categories = Category::where(['position' => 0])->where('restaurant_id', $rid)->orderBy('name')->get();
        $subcategories = Category::where('position', '!=', 0)->where('restaurant_id', $rid)->orderBy('name')->get();
        $restaurant_id = Helpers::get_restaurant_id();
        $all_count = Food::where('restaurant_id', $restaurant_id)->where('is_draft', 0)->count();
        $disabled_count = Food::where('restaurant_id', $restaurant_id)->where('is_draft', 0)->where('status', 0)->count();
        $drafts_count = Food::where('restaurant_id', $restaurant_id)->where('is_draft', 1)->count();
        $gemini_ai_enabled = BusinessSetting::where('key', 'gemini_ai_enabled')->first()?->value ?? '1';
        return response()->view('vendor-views.product.listing-manager', compact(
            'categories', 'subcategories', 'all_count', 'disabled_count', 'drafts_count', 'gemini_ai_enabled'
        ))->header('X-LiteSpeed-Cache-Control', 'no-cache')
          ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }

    public function listingManagerGetItems(Request $request)
    {
        $filter = $request->filter ?? 'all';
        $category_id = $request->category_id ?? null;
        $restaurant_id = Helpers::get_restaurant_id();

        $query = Food::with(['newVariations' => function ($q) {
            $q->with('variationOptions');
        }])->where('restaurant_id', $restaurant_id);

        if ($filter === 'drafts') {
            $query->where('is_draft', 1);
        } else {
            $query->where('is_draft', 0);
            
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
                'is_draft' => $food->is_draft,
            ];
        });

        return response()->json(['items' => $items]);
    }

    public function listingManagerGetItem($id)
    {
        $food = Food::with(['newVariations' => function ($q) {
            $q->with('variationOptions');
        }])->findOrFail($id);

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
        ]);
    }

    public function listingManagerStore(Request $request)
    {
        if (!Helpers::get_restaurant_data()->food_section) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => translate('messages.permission_denied')]]]);
        }

        $hasOptions = $request->has('options') && is_array($request->options) && count($request->options) > 0;

        $rules = [
            'name'         => 'array',
            'name.0'       => 'required|max:191',
            'name.*'       => 'max:191',
            'category_id'  => 'required',
            'image'        => 'nullable|max:2048',
            'description.*' => 'max:1000',
            'discount'     => 'nullable|numeric|min:0',
        ];
        if (!$hasOptions) {
            $rules['price'] = 'required|numeric|between:.01,999999999999.99';
        } else {
            $rules['price'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules, [
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        $restaurant = Helpers::get_restaurant_data();
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
                return response()->json(['errors' => [['code' => 'unauthorized', 'message' => translate('messages.you_are_not_subscribed_to_any_package')]]]);
            }
        } elseif ($restaurant->restaurant_model == 'unsubscribed') {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => translate('messages.you_are_not_subscribed_to_any_package')]]]);
        }

        // Validate variation options if present
        if ($hasOptions) {
            foreach (array_values($request->options) as $option) {
                if (!isset($option['values'])) {
                    return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.please_add_options_for') . ($option['name'] ?? '')]]]);
                }
                if (isset($option['min']) && isset($option['max']) && $option['min'] > 0 && $option['min'] > $option['max']) {
                    return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.minimum_value_can_not_be_greater_then_maximum_value')]]]);
                }
            }
        }

        $food = new Food;
        $food->name = $request->name[array_search('default', $request->lang ?? ['default'])];

        $category = [];
        if ($request->category_id) {
            array_push($category, ['id' => $request->category_id, 'position' => 1]);
        }
        if ($request->sub_category_id) {
            array_push($category, ['id' => $request->sub_category_id, 'position' => 2]);
        }

        $food->category_id = $request->sub_category_id ?? $request->category_id;
        $food->category_ids = json_encode($category);
        $food->description = $request->description[array_search('default', $request->lang ?? ['default'])] ?? '';
        $food->choice_options = json_encode([]);
        $food->variations = json_encode([]);

        $price = $request->price;
        // Variable item: base price = 0; app adds variation option price on top
        $food->price = ($hasOptions && (!$price || $price == 0)) ? 0 : max(0.01, (float)($price ?? 0.01));

        $food->veg = $request->veg ?? 0;
        $food->image = Helpers::upload(dir: 'product/', format: 'png', image: $request->file('image'));
        $food->available_time_starts = $request->available_time_starts ?? '00:00:00';
        $food->available_time_ends = $request->available_time_ends ?? '23:59:00';
        $food->discount = $request->discount ?? 0;
        $food->discount_type = $request->discount_type ?? 'percent';
        $food->attributes = json_encode([]);
        $food->add_ons = json_encode([]);
        $food->restaurant_id = Helpers::get_restaurant_id();
        $food->maximum_cart_quantity = $request->maximum_cart_quantity ?? null;
        $food->is_halal = $request->is_halal ?? 0;
        $food->item_stock = 0;
        $food->stock_type = 'unlimited';
        $food->status = $request->status ?? 1;
        $food->save();

        if ($hasOptions) {
            foreach (array_values($request->options) as $option) {
                $variation = new Variation();
                $variation->food_id = $food->id;
                $variation->name = $option['name'];
                $variation->type = $option['type'] ?? 'single';
                $variation->min = $option['min'] ?? 0;
                $variation->max = $option['max'] ?? 0;
                $variation->is_required = data_get($option, 'required') == 'on';
                $variation->save();

                foreach (array_values($option['values'] ?? []) as $value) {
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

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Food', data_id: $food->id, data_value: $food->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'Food', data_id: $food->id, data_value: $food->description);

        return response()->json(['id' => $food->id, 'message' => translate('Item saved successfully')]);
    }

    public function listingManagerUpdate(Request $request, $id)
    {
        if (!Helpers::get_restaurant_data()->food_section) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => translate('messages.permission_denied')]]]);
        }

        $hasOptions = $request->has('options') && is_array($request->options) && count($request->options) > 0;

        $rules = [
            'name'         => 'array',
            'name.0'       => 'required|max:191',
            'name.*'       => 'max:191',
            'category_id'  => 'required',
            'image'        => 'nullable|max:2048',
            'description.*' => 'max:1000',
            'discount'     => 'nullable|numeric|min:0',
        ];
        if (!$hasOptions) {
            $rules['price'] = 'required|numeric|between:.01,999999999999.99';
        } else {
            $rules['price'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules, [
            'name.0.required' => translate('messages.item_name_required'),
            'category_id.required' => translate('messages.category_required'),
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)]);
        }

        if ($hasOptions) {
            foreach (array_values($request->options) as $option) {
                if (!isset($option['values'])) {
                    return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.please_add_options_for') . ($option['name'] ?? '')]]]);
                }
                if (isset($option['min']) && isset($option['max']) && $option['min'] > 0 && $option['min'] > $option['max']) {
                    return response()->json(['errors' => [['code' => 'variation', 'message' => translate('messages.minimum_value_can_not_be_greater_then_maximum_value')]]]);
                }
            }
        }

        $food = Food::findOrFail($id);
        $food->name = $request->name[array_search('default', $request->lang ?? ['default'])];

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
        $food->description = $request->description[array_search('default', $request->lang ?? ['default'])] ?? '';
        $food->choice_options = json_encode([]);
        $food->variations = json_encode([]);

        $price = $request->price;
        if ($hasOptions && (!$price || $price == 0)) {
            $food->price = 0; // base 0; app adds variation price
        } elseif ($price && (float)$price > 0) {
            $food->price = (float)$price;
        }

        $food->veg = $request->veg ?? $food->veg;
        if ($request->hasFile('image')) {
            $food->image = Helpers::update(dir: 'product/', old_image: $food->image, format: 'png', image: $request->file('image'));
        }
        $food->available_time_starts = $request->available_time_starts ?? $food->available_time_starts;
        $food->available_time_ends = $request->available_time_ends ?? $food->available_time_ends;
        $food->discount = $request->discount ?? 0;
        $food->discount_type = $request->discount_type ?? 'percent';
        $food->maximum_cart_quantity = $request->maximum_cart_quantity ?? $food->maximum_cart_quantity;
        $food->is_halal = $request->is_halal ?? $food->is_halal;
        $food->status = $request->has('status') ? (int)$request->status : $food->status;

        if ($hasOptions) {
            foreach (array_values($request->options) as $option) {
                $variation = Variation::updateOrCreate(
                    ['id' => $option['variation_id'] ?? null, 'food_id' => $food->id],
                    [
                        'name' => $option['name'],
                        'type' => $option['type'] ?? 'single',
                        'min' => $option['min'] ?? 0,
                        'max' => $option['max'] ?? 0,
                        'is_required' => data_get($option, 'required') == 'on',
                    ]
                );

                foreach (array_values($option['values'] ?? []) as $value) {
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

        Helpers::add_or_update_translations(request: $request, key_data: 'name', name_field: 'name', model_name: 'Food', data_id: $food->id, data_value: $food->name);
        Helpers::add_or_update_translations(request: $request, key_data: 'description', name_field: 'description', model_name: 'Food', data_id: $food->id, data_value: $food->description);

        return response()->json(['id' => $food->id, 'message' => translate('Item updated successfully')]);
    }

    public function listingManagerDelete($id)
    {
        $food = Food::findOrFail($id);
        if ($food->restaurant_id != Helpers::get_restaurant_id()) {
            return response()->json(['errors' => [['code' => 'unauthorized', 'message' => translate('messages.permission_denied')]]], 403);
        }
        $food->newVariations()->each(function ($v) {
            $v->variationOptions()->delete();
            $v->delete();
        });
        $food->delete();
        return response()->json(['message' => translate('Item deleted successfully')]);
    }

    public function listingManagerToggleStatus(Request $request)
    {
        $food = Food::findOrFail($request->id);
        if ($food->restaurant_id != Helpers::get_restaurant_id()) {
            return response()->json(['errors' => [['code' => 'unauthorized']]], 403);
        }
        $food->status = $request->status;
        $food->save();
        if ($food->status != 1) {
            $food->carts()?->delete();
        }
        return response()->json(['status' => $food->status]);
    }

    public function listingManagerGetCategories(Request $request)
    {
        $rid = Helpers::get_restaurant_id();
        $parentId = $request->parent_id ?? 0;
        $categories = Category::where('parent_id', $parentId)
            ->where('restaurant_id', $rid)
            ->orderBy('name')
            ->get(['id', 'name']);
        return response()->json($categories);
    }

    // ── Restaurant Category CRUD ──────────────────────────────────────────────

    public function listingManagerCategoryList(Request $request)
    {
        $rid = Helpers::get_restaurant_id();
        $cats = Category::where('restaurant_id', $rid)
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

    public function listingManagerCategoryStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required|max:191',
            'image' => 'nullable|image|max:2048',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 422);
        }

        $rid = Helpers::get_restaurant_id();

        $cat = new Category();
        $cat->restaurant_id = $rid;
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
                \Illuminate\Support\Facades\Storage::disk('public')->put($fname, $decoded);
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

    public function listingManagerCategoryUpdate(Request $request, $id)
    {
        $rid = Helpers::get_restaurant_id();
        $cat = Category::where('id', $id)->where('restaurant_id', $rid)->first();
        if (!$cat) return response()->json(['error' => 'Not found'], 404);

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
                $fname = 'category/' . \Carbon\Carbon::now()->toDateString() . '-' . uniqid() . '.png';
                \Illuminate\Support\Facades\Storage::disk('public')->put($fname, $decoded);
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

    public function listingManagerCategoryDelete($id)
    {
        $rid = Helpers::get_restaurant_id();
        $cat = Category::where('id', $id)->where('restaurant_id', $rid)->first();
        if (!$cat) return response()->json(['error' => 'Not found'], 404);

        // Unset category_id on foods that used this category
        Food::where('restaurant_id', $rid)->where('category_id', $id)->update(['category_id' => null]);

        $cat->delete();
        return response()->json(['success' => true]);
    }

    public function listingManagerAiImageForCategory(Request $request)
    {
        $name = trim($request->name ?? '');
        if (!$name) return response()->json(['error' => 'Category name required'], 422);

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) return response()->json(['error' => 'No Gemini API key set in Admin → Business Settings → Third Party APIs'], 400);

        $prompt = "Generate a professional food category banner photo for a restaurant menu category called \"{$name}\". Clean, appetizing, high quality, suitable as a menu category header image.";

        $models    = ['gemini-2.5-flash-image', 'gemini-3.1-flash-image', 'gemini-3-pro-image'];
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

    public function listingManagerAiDescription(Request $request)
    {
        $name = trim($request->name ?? '');
        if (!$name) return response()->json(['error' => 'Item name required'], 422);

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) return response()->json(['error' => 'No Gemini API key set in Admin → Business Settings → Third Party APIs'], 400);

        // Detect if Hebrew text is present in the item name
        $isHebrew = (bool) preg_match('/[\x{0590}-\x{05FF}]/u', $name);
        $englishName = $name;
        if ($isHebrew) {
            foreach (['gemini-3.6-flash', 'gemini-2.0-flash-001', 'gemini-flash-latest'] as $model) {
                try {
                    $r = Http::timeout(10)->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                        ['contents' => [['parts' => [['text' => "Translate this Hebrew food item name to a simple, descriptive English food name. Output ONLY the English translation, no other text: {$name}"]]]], 'generationConfig' => ['thinkingConfig' => ['thinkingBudget' => 0]]]
                    );
                    if ($r->successful()) {
                        $translated = trim($r->json('candidates.0.content.parts.0.text') ?? '');
                        if ($translated) {
                            $englishName = $translated;
                            break;
                        }
                    }
                } catch (Exception $e) {}
            }
        }

        $prompt = $isHebrew
            ? "Write a short, appetizing menu description for the food item \"{$name}\" (English: {$englishName}). The description MUST be written in Hebrew. 1-2 sentences only, focus on taste and appeal. Plain text, no markdown."
            : "Write a short, appetizing menu description for a food item called \"{$name}\". 1-2 sentences only, focus on taste and appeal. Plain text, no markdown.";

        $lastErr = '';
        foreach (['gemini-3.6-flash', 'gemini-2.0-flash-001', 'gemini-flash-latest'] as $model) {
            try {
                $r = Http::timeout(15)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                    ['contents' => [['parts' => [['text' => $prompt]]]], 'generationConfig' => ['thinkingConfig' => ['thinkingBudget' => 0]]]
                );
                if ($r->successful()) {
                    $text = trim($r->json('candidates.0.content.parts.0.text') ?? '');
                    if ($text) return response()->json(['description' => $text]);
                }
                $lastErr = $r->json('error.message') ?? ('HTTP ' . $r->status());
            } catch (\Exception $e) { $lastErr = $e->getMessage(); }
        }

        return response()->json(['error' => 'Description generation failed: ' . $lastErr], 500);
    }

    public function listingManagerAiImage(Request $request)
    {
        $name = trim($request->name ?? '');
        if (!$name) return response()->json(['error' => 'Item name required'], 422);

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) return response()->json(['error' => 'No Gemini API key set in Admin → Business Settings → Third Party APIs'], 400);

        // Detect if Hebrew text is present in the item name
        $isHebrew = (bool) preg_match('/[\x{0590}-\x{05FF}]/u', $name);
        $englishName = $name;
        if ($isHebrew) {
            foreach (['gemini-3.6-flash', 'gemini-2.0-flash-001', 'gemini-flash-latest'] as $model) {
                try {
                    $r = Http::timeout(10)->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                        ['contents' => [['parts' => [['text' => "Translate this Hebrew food item name to a simple, descriptive English food name. Output ONLY the English translation, no other text: {$name}"]]]], 'generationConfig' => ['thinkingConfig' => ['thinkingBudget' => 0]]]
                    );
                    if ($r->successful()) {
                        $translated = trim($r->json('candidates.0.content.parts.0.text') ?? '');
                        if ($translated) {
                            $englishName = $translated;
                            break;
                        }
                    }
                } catch (Exception $e) {}
            }
        }

        $prompt = "Generate a professional food photograph of {$englishName}. Restaurant quality, appetizing, clean white plate, high quality food menu photo.";

        $models = [
            'gemini-2.5-flash-image',
            'gemini-3.1-flash-image',
            'gemini-3-pro-image',
        ];

        $lastError = '';
        foreach ($models as $model) {
            try {
                $r = Http::timeout(45)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                    [
                        'contents'         => [['parts' => [['text' => $prompt]]]],
                        'generationConfig' => ['responseModalities' => ['IMAGE', 'TEXT']],
                    ]
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
                // Key invalid — no point trying other models
                if ($r->status() === 403 || str_contains(strtolower($lastError), 'api key')) {
                    return response()->json(['error' => $lastError], 500);
                }
            } catch (\Exception $e) {
                $lastError = $e->getMessage();
            }
        }

        return response()->json(['error' => "Gemini image generation error: {$lastError}"], 500);
    }


    public function listingManagerApprove($id)
    {
        $restaurant_id = Helpers::get_restaurant_id();
        $food = Food::where('restaurant_id', $restaurant_id)->findOrFail($id);
        $food->is_draft = 0;
        $food->status = 1;
        $food->save();
        return response()->json(['message' => translate('messages.item_approved_successfully')]);
    }

    public function listingManagerParseCSV(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file'
        ]);

        $file = $request->file('csv_file');
        
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return response()->json(['errors' => [['code' => 'import', 'message' => 'Could not read CSV file.']]], 422);
        }

        // Get header
        $header = fgetcsv($handle);
        if (!$header) {
            fclose($handle);
            return response()->json(['errors' => [['code' => 'import', 'message' => 'CSV file is empty.']]], 422);
        }
        
        // Normalize header (lowercase, trim)
        $header = array_map(function($h) { return strtolower(trim($h)); }, $header);

        $categoryIdx = array_search('category', $header);
        $nameIdx = array_search('name', $header);
        $descIdx = array_search('description', $header);
        $priceIdx = array_search('price', $header);
        $typeIdx = array_search('item type', $header);
        $vegIdx = array_search('veg', $header);

        if ($nameIdx === false || $priceIdx === false || $categoryIdx === false) {
            fclose($handle);
            return response()->json(['errors' => [['code' => 'import', 'message' => 'CSV must contain Category, Name, and Price columns.']]], 422);
        }

        $items = [];
        while (($row = fgetcsv($handle)) !== false) {
            $catName = trim($row[$categoryIdx] ?? '');
            $itemName = trim($row[$nameIdx] ?? '');
            $itemDesc = trim($row[$descIdx] ?? '');
            $itemPrice = (float)trim($row[$priceIdx] ?? 0);
            $vegVal = strtolower(trim($row[$vegIdx] ?? 'non-veg'));

            if (!$itemName || !$catName) continue;

            $items[] = [
                'category' => $catName,
                'name' => $itemName,
                'description' => $itemDesc,
                'price' => max(0.01, $itemPrice),
                'veg' => (str_contains($vegVal, 'veg') && !str_contains($vegVal, 'non')) ? 1 : 0,
                'item_type' => 'simple'
            ];
        }
        fclose($handle);

        return response()->json(['items' => $items]);
    }

    public function listingManagerParsePDF(Request $request)
    {
        @set_time_limit(240);
        @ini_set('max_execution_time', 240);

        $request->validate([
            'menu_file' => 'required|file'
        ]);

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) {
            return response()->json(['errors' => [['code' => 'import', 'message' => 'No Gemini API key configured.']]], 400);
        }

        $file = $request->file('menu_file');
        $mimeType = $file->getMimeType();
        $base64Data = base64_encode(file_get_contents($file->getRealPath()));

        $prompt = "You are a menu parsing expert. Extract all menu items, descriptions, prices, and categories from the provided menu image/PDF. IGNORE ANY VARIATIONS OR OPTIONS (like size, add-ons, etc.) and only extract the base item details.\nOutput a JSON object with a single key 'categories' containing an array of categories.\nEach category has:\n- 'name': category name\n- 'items': array of items. Each item must have:\n  - 'name': item name\n  - 'description': item description\n  - 'price': item base price (numeric)\nOutput ONLY valid raw JSON conforming to this schema, without markdown formatting or code blocks.";

        $lastErr = '';
        $models = ['gemini-3.6-flash', 'gemini-2.5-flash', 'gemini-2.0-flash-001', 'gemini-flash-latest'];
        $parsedData = null;

        foreach ($models as $model) {
            try {
                $response = Http::timeout(60)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                    [
                                        'inlineData' => [
                                            'mimeType' => $mimeType,
                                            'data' => $base64Data
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json'
                        ]
                    ]
                );

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text');
                    $parsedData = json_decode($text, true);
                    if (isset($parsedData['categories'])) {
                        break;
                    }
                }
                $lastErr = $response->json('error.message') ?? ('HTTP ' . $response->status());
            } catch (\Exception $e) {
                $lastErr = $e->getMessage();
            }
        }

        if (!$parsedData || !isset($parsedData['categories'])) {
            return response()->json(['errors' => [['code' => 'import', 'message' => 'Failed to parse menu: ' . $lastErr]]], 500);
        }

        $items = [];
        foreach ($parsedData['categories'] as $catData) {
            $catName = trim($catData['name'] ?? '');
            if (!$catName) continue;

            foreach ($catData['items'] ?? [] as $itemData) {
                $itemName = trim($itemData['name'] ?? '');
                if (!$itemName) continue;

                $items[] = [
                    'category' => $catName,
                    'name' => $itemName,
                    'description' => $itemData['description'] ?? '',
                    'price' => max(0.01, (float)($itemData['price'] ?? 0.01)),
                    'veg' => 0,
                    'item_type' => 'simple'
                ];
            }
        }

        return response()->json(['items' => $items]);
    }

    public function listingManagerSaveImportedItem(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'veg' => 'required|integer',
            'item_type' => 'required|string'
        ]);

        $restaurant_id = Helpers::get_restaurant_id();

        // Find or create category
        $catName = trim($request->category);
        $category = Category::where('name', $catName)->where('position', 0)->where('restaurant_id', $restaurant_id)->first();
        if (!$category) {
            $category = new Category();
            $category->name = $catName;
            $category->parent_id = 0;
            $category->position = 0;
            $category->status = 1;
            $category->restaurant_id = $restaurant_id;
            $category->priority = 0;
            $category->save();
        }

        // Create food draft
        $description = trim($request->description ?? '');
        $imageName = null;

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if ($apiKey) {
            // ─── Auto AI Description if empty ───
            if (!$description) {
                $isHebrew = (bool) preg_match('/[\x{0590}-\x{05FF}]/u', $request->name);
                $englishName = $request->name;
                if ($isHebrew) {
                    foreach (['gemini-3.6-flash', 'gemini-2.0-flash-001', 'gemini-flash-latest'] as $model) {
                        try {
                            $r = Http::timeout(10)->post(
                                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                                ['contents' => [['parts' => [['text' => "Translate this Hebrew food item name to a simple, descriptive English food name. Output ONLY the English translation, no other text: {$request->name}"]]]], 'generationConfig' => ['thinkingConfig' => ['thinkingBudget' => 0]]]
                            );
                            if ($r->successful()) {
                                $translated = trim($r->json('candidates.0.content.parts.0.text') ?? '');
                                if ($translated) {
                                    $englishName = $translated;
                                    break;
                                }
                            }
                        } catch (\Exception $e) {}
                    }
                }

                $descPrompt = $isHebrew
                    ? "Write a short, appetizing menu description for the food item \"{$request->name}\" (English: {$englishName}). The description MUST be written in Hebrew. 1-2 sentences only, focus on taste and appeal. Plain text, no markdown."
                    : "Write a short, appetizing menu description for a food item called \"{$request->name}\". 1-2 sentences only, focus on taste and appeal. Plain text, no markdown.";

                foreach (['gemini-3.6-flash', 'gemini-2.0-flash-001', 'gemini-flash-latest'] as $model) {
                    try {
                        $r = Http::timeout(15)->post(
                            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                            ['contents' => [['parts' => [['text' => $descPrompt]]]], 'generationConfig' => ['thinkingConfig' => ['thinkingBudget' => 0]]]
                        );
                        if ($r->successful()) {
                            $text = trim($r->json('candidates.0.content.parts.0.text') ?? '');
                            if ($text) {
                                $description = $text;
                                break;
                            }
                        }
                    } catch (\Exception $e) {}
                }
            }

            // ─── Auto AI Image ───
            $isHebrew = (bool) preg_match('/[\x{0590}-\x{05FF}]/u', $request->name);
            $englishName = $request->name;
            if ($isHebrew) {
                foreach (['gemini-3.6-flash', 'gemini-2.0-flash-001', 'gemini-flash-latest'] as $model) {
                    try {
                        $r = Http::timeout(10)->post(
                            "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                            ['contents' => [['parts' => [['text' => "Translate this Hebrew food item name to a simple, descriptive English food name. Output ONLY the English translation, no other text: {$request->name}"]]]], 'generationConfig' => ['thinkingConfig' => ['thinkingBudget' => 0]]]
                        );
                        if ($r->successful()) {
                            $translated = trim($r->json('candidates.0.content.parts.0.text') ?? '');
                            if ($translated) {
                                $englishName = $translated;
                                break;
                            }
                        }
                    } catch (\Exception $e) {}
                }
            }

            $imgPrompt = "Generate a professional food photograph of {$englishName}. Restaurant quality, appetizing, clean white plate, high quality food menu photo.";
            $models = ['gemini-2.5-flash-image', 'gemini-3.1-flash-image', 'gemini-3-pro-image'];
            foreach ($models as $model) {
                try {
                    $r = Http::timeout(35)->post(
                        "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                        [
                            'contents'         => [['parts' => [['text' => $imgPrompt]]]],
                            'generationConfig' => ['responseModalities' => ['IMAGE', 'TEXT']],
                        ]
                    );

                    if ($r->successful()) {
                        foreach ($r->json('candidates.0.content.parts') ?? [] as $part) {
                            if (isset($part['inlineData']['data'])) {
                                $imageName = $this->saveBase64Image($part['inlineData']['data']);
                                break 2;
                            }
                        }
                    }
                } catch (\Exception $e) {}
            }
        }

        $food = new Food();
        $food->name = $request->name;
        $food->description = $description;
        if ($imageName) {
            $food->image = $imageName;
        }
        $food->price = max(0.01, (float)$request->price);
        $food->restaurant_id = $restaurant_id;
        $food->category_id = $category->id;
        $food->category_ids = json_encode([['id' => $category->id, 'position' => 1]]);
        $food->choice_options = json_encode([]);
        $food->variations = json_encode([]);
        $food->attributes = json_encode([]);
        $food->add_ons = json_encode([]);
        $food->status = 0;
        $food->is_draft = 1;
        $food->veg = (int)$request->veg;
        $food->available_time_starts = '00:00:00';
        $food->available_time_ends = '23:59:00';
        $food->item_stock = 0;
        $food->stock_type = 'unlimited';
        $food->save();

        return response()->json(['success' => true]);
    }

    public function listingManagerBulkPublish(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:food,id'
        ]);

        $restaurant_id = Helpers::get_restaurant_id();
        
        Food::where('restaurant_id', $restaurant_id)
            ->whereIn('id', $request->ids)
            ->update([
                'is_draft' => 0,
                'status' => 1
            ]);

        return response()->json(['message' => 'Selected draft items published successfully.']);
    }

    public function listingManagerBulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:food,id'
        ]);

        $restaurant_id = Helpers::get_restaurant_id();

        Food::where('restaurant_id', $restaurant_id)
            ->whereIn('id', $request->ids)
            ->delete();

        return response()->json(['message' => 'Selected items deleted successfully.']);
    }

    public function listingManagerImportPDF(Request $request)
    {
        $request->validate([
            'menu_file' => 'required|file'
        ]);

        $apiKey = BusinessSetting::where('key', 'gemini_api_key')->first()?->value ?? '';
        if (!$apiKey) {
            return response()->json(['errors' => [['code' => 'import', 'message' => 'No Gemini API key configured.']]], 400);
        }

        $restaurant_id = Helpers::get_restaurant_id();
        $file = $request->file('menu_file');
        $mimeType = $file->getMimeType();
        $base64Data = base64_encode(file_get_contents($file->getRealPath()));

        $prompt = "You are a menu parsing expert. Extract all menu items, descriptions, prices, and categories from the provided menu image/PDF. IGNORE ANY VARIATIONS OR OPTIONS (like size, add-ons, etc.) and only extract the base item details.\nCRITICAL INSTRUCTION: If an item or category name is provided in multiple languages (e.g. Hebrew and English separated by a slash '/'), you MUST split them and create a separate item/category entry for each language. For example, if a menu item is 'Black Tea / תה שחור', you must create two separate items in the JSON: one named 'Black Tea' and another named 'תה שחור', both with the same price and details.\nOutput a JSON object with a single key 'categories' containing an array of categories.\nEach category has:\n- 'name': category name\n- 'items': array of items. Each item must have:\n  - 'name': item name\n  - 'description': item description\n  - 'price': item base price (numeric)\nOutput ONLY valid raw JSON conforming to this schema, without markdown formatting or code blocks.";

        $lastErr = '';
        $models = ['gemini-3.6-flash', 'gemini-2.5-flash', 'gemini-2.0-flash-001', 'gemini-flash-latest'];
        $parsedData = null;

        foreach ($models as $model) {
            try {
                $response = Http::timeout(60)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    ['text' => $prompt],
                                    [
                                        'inlineData' => [
                                            'mimeType' => $mimeType,
                                            'data' => $base64Data
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        'generationConfig' => [
                            'responseMimeType' => 'application/json'
                        ]
                    ]
                );

                if ($response->successful()) {
                    $text = $response->json('candidates.0.content.parts.0.text');
                    $parsedData = json_decode($text, true);
                    if (isset($parsedData['categories'])) {
                        break;
                    }
                }
                $lastErr = $response->json('error.message') ?? ('HTTP ' . $response->status());
            } catch (\Exception $e) {
                $lastErr = $e->getMessage();
            }
        }

        if (!$parsedData || !isset($parsedData['categories'])) {
            return response()->json(['errors' => [['code' => 'import', 'message' => 'Failed to parse menu: ' . $lastErr]]], 500);
        }

        $imported = 0;
        foreach ($parsedData['categories'] as $catData) {
            $catName = trim($catData['name'] ?? '');
            if (!$catName) continue;

            // Find or create category
            $category = Category::where('name', $catName)->where('position', 0)->where('restaurant_id', $restaurant_id)->first();
            if (!$category) {
                $category = new Category();
                $category->name = $catName;
                $category->parent_id = 0;
                $category->position = 0;
                $category->status = 1;
                $category->restaurant_id = $restaurant_id;
                $category->priority = 0;
                $category->save();
            }

            foreach ($catData['items'] ?? [] as $itemData) {
                $itemName = trim($itemData['name'] ?? '');
                if (!$itemName) continue;

                $food = new Food();
                $food->name = $itemName;
                $food->description = $itemData['description'] ?? '';
                $food->price = max(0.01, (float)($itemData['price'] ?? 0.01));
                $food->restaurant_id = $restaurant_id;
                $food->category_id = $category->id;
                $food->category_ids = json_encode([['id' => $category->id, 'position' => 1]]);
                $food->choice_options = json_encode([]);
                $food->variations = json_encode([]);
                $food->attributes = json_encode([]);
                $food->add_ons = json_encode([]);
                $food->status = 0;
                $food->is_draft = 1;
                $food->veg = 0;
                $food->available_time_starts = '00:00:00';
                $food->available_time_ends = '23:59:00';
                $food->item_stock = 0;
                $food->stock_type = 'unlimited';

                // Try to generate AI image (best effort)
                $imgBase64 = $this->generateFoodImage($itemName, $apiKey);
                if ($imgBase64) {
                    $food->image = $this->saveBase64Image($imgBase64, 'product/');
                }

                $food->save();

                $imported++;
            }
        }

        return response()->json(['message' => "Successfully parsed and imported $imported items as drafts."]);
    }

    private function generateFoodImage($name, $apiKey)
    {
        $prompt = "Generate a professional food photograph of {$name}. Restaurant quality, appetizing, clean white plate, high quality food menu photo.";
        $models = [
            'gemini-2.5-flash-image',
            'gemini-3.1-flash-image',
            'gemini-3-pro-image',
        ];
        foreach ($models as $model) {
            try {
                $r = Http::timeout(25)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}",
                    [
                        'contents'         => [['parts' => [['text' => $prompt]]]],
                        'generationConfig' => ['responseModalities' => ['IMAGE', 'TEXT']],
                    ]
                );

                if ($r->successful()) {
                    foreach ($r->json('candidates.0.content.parts') ?? [] as $part) {
                        if (isset($part['inlineData']['data'])) {
                            return $part['inlineData']['data'];
                        }
                    }
                }
            } catch (\Exception $e) {
                // ignore
            }
        }
        return null;
    }

    private function saveBase64Image($base64Data, $dir = 'product/')
    {
        $data = base64_decode($base64Data);
        $fileName = Carbon::now()->todateString() . '-' . uniqid() . '.png';
        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
        }
        Storage::disk('public')->put($dir . $fileName, $data);
        return $fileName;
    }

}