<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;

class AdminController extends Controller
{
   public function index(){
        return view('admin.index');
    }
    public function brands() {
        $brands = Brand::orderBy('id', 'DESC')->paginate(10);
        return view('admin.brands', compact('brands'));
    }
    public function add_brand(){
        return view('admin.brand-add');
    }

    public function brand_store(Request $request){
        $request->validate([
            'name'=>'required',
            'slug'=>'required',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);
        $brand = new Brand();
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extention;
        $this->GenerateBrandThumbnailImage($image,$file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status','Brand has been added successfully');

    }

    public function brand_edit($id){
        $brand = Brand::find($id);
        return view('admin.brand-edit', compact('brand'));
    }

    public function brand_update(Request $request){
        $request->validate([
            'name'=>'required',
            'slug'=>'required',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);
        $brand = Brand::find($request->id);
        $brand->name = $request->name;
        $brand->slug = Str::slug($request->name);
        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/brands').'/'.$brand->image)){
                File::delete(public_path('uploads/brands').'/'.$brands->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp.'.'.$file_extention;
            $this->GenerateBrandThumbnailImage($image,$file_name);
            $brand->image = $file_name;
        }
      
        $brand->save();
        return redirect()->route('admin.brands')->with('status','Brand has been added successfully');

    }

    public function GenerateBrandThumbnailImage($image, $imageName){
        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function brand_delete($id){
        $brand = Brand::find($id);
        if(File::exists(public_path('uploads/brands').'/'.$brand->image)){
            File::delete(public_path('uploads/brands').'/'.$brand->image);
        }
        $brand->delete();
        return redirect()->route('admin.brands')->with('status','Brand has been deleted successfully!!!');
    }

    public function categories(){
        $categories = Category::orderBy('id', 'DESC')->paginate(10);
        return view('admin.categories', compact('categories'));
    }
    public function category_add() {
        return view('admin.category-add');
    }
    public function category_store(Request $request){
        $request->validate([
            'name'=>'required',
            'slug'=>'required',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);
        $category = new Category();
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp.'.'.$file_extention;
        $this->GenerateCategoryThumbnailImage($image,$file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status','category has been added successfully');

    }
    public function GenerateCategoryThumbnailImage($image, $imageName){
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);
    }
    public function category_edit($id){
        $category = Category::find($id);
        return view('admin.category-edit', compact('category'));
    }
    public function category_update(Request $request){
        $request->validate([
            'name'=>'required',
            'slug'=>'required',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);
     
       
        $category = Category::find($request->id);       
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/categories').'/'.$category->image)){
                File::delete(public_path('uploads/categories').'/'.$category->image);
            }
            $image = $request->file('image');
            $file_extention = $request->file('image')->extension();
            $file_name = Carbon::now()->timestamp.'.'.$file_extention;
            $this->GenerateCategoryThumbnailImage($image,$file_name);
            $category->image = $file_name;
        }
      
        $category->save();
        return redirect()->route('admin.categories')->with('status','Category has been added successfully');

    }
    public function category_delete($id) {
        $category = Category::find($id);
        if(File::exists(public_path('uploads/categories').'/'.$category->image));
        {
            File::delete(public_path('uploads/categories').'/'.$category->image);
        }
        $category->delete();
        return redirect()->route('admin.categories')->with('success','Category has been deleted successfully!!!');
    }

    public function products(){
        $products = Product::orderBy('created_at','DESC')->paginate(10);
        return view('admin.products',compact('products'));
    }
    public function product_add(){
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-add',compact('categories','brands'));
    }

    public function product_store(Request $request){

        $request->validate([
            'name' =>'required',
            'slug' =>'required|unique:products,slug',
            'short_description' =>'required',
            'description' =>'required',
            'regular_price' =>'required',
            'sale_price' =>'required',
            'sku' =>'required',
            'stock_status' =>'required',
            'feature' =>'required',
            'quantity' =>'required',
            'image' =>'required|mimes:png,jpg,jpeg|max:2048',
            'category_id' =>'required',
            'brand_id' =>'required'
        ]);
        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->sku = $request->sku;
        $product->stock_status = $request->stock_status;
        $product->feature = $request->feature;
        $product->quantity = $request->quantity;
        $product->image = $request->image;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image')){
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailImage($image,$imageName);
            $product->image = $imageName;
        }
        $gallery_arr = array();
        $gallary_images="";
        $counter=1;

        if($request->hasFile('images')){
            $allowedFileExtension = ['jpg', 'png', 'jpeg'];
            $files= $request->file('images');
           foreach($files as $file){
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedFileExtension);
                if($gcheck){
                    $gfileName = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->GenerateProductThumbnailImage($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter++;
                }
            }

            $gallary_images = implode(',',$gallery_arr);
        }
        $product->images = $gallary_images;
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Product has been added successfully!!!');
    }

    public function GenerateProductThumbnailImage($image, $imageName){
        $destinationPathThumbnail = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image->path());

        $img->cover(540,689,"top");
        $img->resize(540,689,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);

        $img->resize(104,104,function($constraint){
            $constraint->aspectRatio();
        })->save($destinationPathThumbnail.'/'.$imageName);
    }

    public function product_edit($id){
        $product = Product::find($id);
        $categories = Category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admin.product-edit',compact('product','categories','brands'));
    }

    public function product_update(Request $request){
         $request->validate([
            'name' =>'required',
            'slug' =>'required|unique:products,slug,'.$request->id,
            'short_description' =>'required',
            'description' =>'required',
            'regular_price' =>'required',
            'sale_price' =>'required',
            'sku' =>'required',
            'stock_status' =>'required',
            'feature' =>'required',
            'quantity' =>'required',
            'image' =>'mimes:png,jpg,jpeg|max:2048',
            'category_id' =>'required',
            'brand_id' =>'required'
        ]);
        $product = Product::find($request->id);

        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->sku = $request->sku;
        $product->stock_status = $request->stock_status;
        $product->feature = $request->feature;
        $product->quantity = $request->quantity;
        $product->image = $request->image;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image')){
            if(File::exists(public_path('uploads/products').'/'.$product->image)){
                File::delete(public_path('uploads/products').'/'.$product->image);
            }
            if(File::exists(public_path('uploads/products/thumbnails').'/'.$product->images)){
                File::delete(public_path('uploads/products/thumbnails').'/'.$product->images);
            }
            $image = $request->file('image');
            $imageName = $current_timestamp . '.' . $image->extension();
            $this->GenerateProductThumbnailImage($image,$imageName);
            $product->image = $imageName;
        }
        $gallery_arr = array();
        $gallary_images="";
        $counter=1;

        if($request->hasFile('images')){
            foreach(explode(',',$product->image) as $ofile)
            {
                if(File::exists(public_path('uploads/products').'/'.$ofile)){
                    File::delete(public_path('uploads/products').'/'.$ofile);
                }
                if(File::exists(public_path('uploads/products/thumbnails').'/'.$ofile)){
                    File::delete(public_path('uploads/products').'/'.$ofile);
                }
            }
            $allowedFileExtension = ['jpg', 'png', 'jpeg'];
            $files= $request->file('images');
           foreach($files as $file){
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array($gextension, $allowedFileExtension);
                if($gcheck){
                    $gfileName = $current_timestamp . "-" . $counter . "." . $gextension;
                    $this->GenerateProductThumbnailImage($file, $gfileName);
                    array_push($gallery_arr, $gfileName);
                    $counter++;
                }
            }

            $gallary_images = implode(',',$gallery_arr);
            $product->images = $gallary_images;
        }
        
        $product->save();
        return redirect()->route('admin.products')->with('status', 'Product has been update successfuly!!!');
    }

    public function product_delete($id){
        $product = Product::find($id);
        if ($product) {
        // Delete main image
        if (File::exists(public_path('uploads/products/') . '/' . $product->image)) {
            File::delete(public_path('uploads/products/') . '/' . $product->image);
        }

        // Delete thumbnail
        if (File::exists(public_path('uploads/products/thumbnails/') . '/' . $product->image)) {
            File::delete(public_path('uploads/products/thumbnails/') . '/' . $product->image);
        }

        // Delete multiple images
        $images = explode(',', $product->images); // Assumes images are comma-separated
        foreach ($images as $file) {
            if (File::exists(public_path('uploads/products/') . '/' . $file)) {
                File::delete(public_path('uploads/products/') . '/' . $file);
            }

            if (File::exists(public_path('uploads/products/thumbnails/') . '/' . $file)) {
                File::delete(public_path('uploads/products/thumbnails/') . '/' . $file);
            }
        }

        // Delete product from DB
        $product->delete();
        return redirect()->route('admin.products')->with('status', 'Product has been update successfuly!!!');
        } 
    }

    public function coupons(){
        // Placeholder for coupons management
        $coupons = Coupon::orderBy('expiry_date', 'DESC')->paginate(12);
        return view('admin.coupons', compact('coupons'));
    }
    public function coupon_add(){
        return view('admin.coupon-add');
    }
    public function coupon_store(Request $request){
        $request->validate([
            'code' => 'required',
            'type' => 'required',
            'value' => 'required|numeric',
            'cart_value' => 'required|numeric',
            'expiry_date' => 'required|date'
        ]);

        $coupon = new Coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();

        return redirect()->route('admin.coupons')->with('status', 'Coupon has been added successfully!');
    }
}
