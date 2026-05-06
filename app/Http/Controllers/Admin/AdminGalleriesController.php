<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gallery;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Support\Facades\Storage;

class AdminGalleriesController extends Controller
{
    //


    public function index($search){

        $gallery = Gallery::where('product_id', $search)->first();

        return response()->json([
            'item' => $gallery
        ], 200);

    }

    public function store(Request $request){


        $request->validate([
            'product_id' => 'required|integer|unique:'.Gallery::class,
            'title' => 'required|string',
            'description' => 'required|string',
            'material' => 'required|string',
            'color' => 'required|string',
            'shape' => 'required|string',
            'size' => 'required|string',
            'weight' => 'required|string',
            'category' => 'required|in:featured,fashion,gifts,home,kitchen,stationaries,supported,christmas,toys',
            'image' => 'required|image',
        ]);

        try {
            $image = $request->file('image');

            $path = $image->store('Gallery', 'public');

            Gallery::create([
                'product_id' => $request->product_id,
                'title' => $request->title,
                'description' => $request->description,
                'material' => $request->material,
                'color' => $request->color,
                'shape' => $request->shape,
                'size' => $request->size,
                'weight' => $request->weight,
                'category' => $request->category,
                'img_path' => $path
            ]);

            return response()->json([
                'message' => 'Product Added Successfully',
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage()
            ], 400);
        }
    }

    public function delete($id){


        $decodedId = Hashids::decode($id)[0] ?? null;
        $gallery = Gallery::find($decodedId);

        if (!$gallery) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        // Delete image from storage
        if ($gallery->img_path) {
            Storage::disk('public')->delete($gallery->img_path);
        }

        // Delete database record
        $gallery->delete();

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);

    }

    public function feature($id){

        $decodedId = Hashids::decode($id)[0] ?? null;
        $gallery = Gallery::find($decodedId);

        if(!$gallery){
            return response()->json([
                'message' => 'Product not found'
            ]);
        }

        $gallery->update([
            'isFeatured' => 1
        ]);

        return response()->json([
            'message' => 'Product featured successfully'
        ]);

    }

    public function unfeature($id){

        $decodedId = Hashids::decode($id)[0] ?? null;
        $gallery = Gallery::find($decodedId);

        if(!$gallery){
            return response()->json([
                'message' => 'Product not found'
            ]);
        }

        $gallery->update([
            'isFeatured' => 0
        ]);

        return response()->json([
            'message' => 'Product unfeature successfully'
        ]);

    }
}
