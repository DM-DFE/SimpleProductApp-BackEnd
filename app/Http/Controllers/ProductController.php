<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProductController extends Controller
{

    public function index()
    {
        return Product::select('id', 'title', 'description', 'image')->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => 'required|image'
        ]);

        $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('product/image', $request->image, $imageName);

        Product::create($request->post()+ ['image' => $imageName]);

        return response()->json(['message' => 'item created successfully']);
    }

    public function show(Product $product)
    {
        return response()->json(['product' => $product]);
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'image' => 'nullable'
        ]);

        $product->fill($request->post())->update();

        //check if product image was updated
        if ($request->hasFile('image')) {
            if ($product->image) {
                $exist = Storage::disk('public')->exists("product/image/{$product->image}");
                if ($exist) {
                    // remove old product image
                    Storage::disk('public')->delete("product/image/{$product->image}");
                }
            }
        }

        $imageName = Str::random().'.'.$request->image->getClientOriginalExtension();
        Storage::disk('public')->putFileAs('product/image', $request->image, $imageName);

        $product->image = $imageName;
        $product->save();

        return response()->json(['message' => 'item updated successfully']);
    }

    public function destroy(Product $product)
    {
        //check if product image exists and delete it
        if ($product->image) {
            $exist = Storage::disk('public')->exists("product/image/{$product->image}");
            if ($exist) {
                // remove old product image
                Storage::disk('public')->delete("product/image/{$product->image}");
            }
        }

        $product->delete();

        return response()->json(['message' => 'item deleted successfully']);
    }
}
