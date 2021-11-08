<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;

class ProductController extends Controller
{

    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'image' => 'required|image|mimes:jpg,jpeg,png',
            'price' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'validation_errors' => $validator->errors()
            ]);
        } else {
            $product = new Product;
            $product->name = $request->input('name');
            $product->description = $request->input('description');
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '.' . $extension;
                $file->move('uploads/product/', $filename);
                $product->image = 'uploads/product/' . $filename;
            }
            $product->price = $request->input('price');
            $product->save();

            return response()->json([
                'status' => 200,
                'message' => 'Product saved successfully!'
            ]);
        }
    }


    public function getAll()
    {

        $products = Product::all();
        return response()->json([
            'status' => 200,
            'products' => $products
        ]);
    }


    public function edit($id)
    {
        $product = Product::find($id);

        if ($product) {
            return response()->json([
                'status' => 200,
                'product' => $product
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Product does not exist!'
            ]);
        }
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'description' => 'required',
            'price' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation error',
                'validation_errors' => $validator->errors()
            ]);
        } else {
            $product = Product::find($id);
            if ($product) {
                $product->name = $request->input('name');
                $product->description = $request->input('description');
                if ($request->hasFile('image')) {
                    $path = $product->image;
                    if (File::exists($path)) {
                        File::delete($path);
                    }
                    $file = $request->file('image');
                    $extension = $file->getClientOriginalExtension();
                    $filename = time() . '.' . $extension;
                    $file->move('uploads/product/', $filename);
                    $product->image = 'uploads/product/' . $filename;
                }
                $product->price = $request->input('price');
                $product->update();

                return response()->json([
                    'status' => 200,
                    'message' => 'Product updated successfully!'
                ]);
            } else {
                return response()->json([
                    'status' => 404,
                    'message' => 'Product does not exist!'
                ]);
            }
        }
    }

    public function delete($id)
    {
        $product = Product::find($id);
        if ($product) {
            Product::destroy($id);
            return response()->json([
                'status' => 200,
                'message' => 'Product deleted successfully!'
            ]);
        } else {
            return response()->json([
                'status' => 404,
                'message' => 'Product does not exist!'
            ]);
        }
    }
    public function search($key)
    {
        return Product::where('name', 'like', "%$key%")->get();
    }
}
