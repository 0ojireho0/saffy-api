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

    public function archive($id){
        $decodedId = Hashids::decode($id)[0] ?? null;
        $gallery = Gallery::find($decodedId);

        if(!$gallery){
            return response()->json([
                'message' => 'Product not found'
            ]);
        }

        $gallery->update([
            'isArchive' => 1
        ]);

        return response()->json([
            'message' => 'Product archived successfully'
        ]);
    }

    public function unarchive($id){
        $decodedId = Hashids::decode($id)[0] ?? null;
        $gallery = Gallery::find($decodedId);

        if(!$gallery){
            return response()->json([
                'message' => 'Product not found'
            ]);
        }

        $gallery->update([
            'isArchive' => 0
        ]);

        return response()->json([
            'message' => 'Product archived successfully'
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

    public function validateGallery(Request $request){
        $decodedId = Hashids::decode($request->id)[0] ?? null;

        $findGallery = Gallery::where('id', $decodedId)->first();

        if(!$findGallery){
            return response()->json([
                'error' => "Cannot find selected gallery"
            ], 404);
        }

        return response()->json([
            'content' => $findGallery
        ], 200);
    }

    public function update(Request $request, $id){
        $request->validate([
            'color' => 'nullable|string',
            'category' => 'nullable|in:fashion,gifts,home,kitchen,stationaries,supported,christmas,toys',
            'description' => 'nullable|string',
            'material' => 'nullable|string',
            'product_id' => 'nullable|integer|unique:'.Gallery::class,
            'shape' => 'nullable|string',
            'size' => 'nullable|string',
            'title' => 'nullable|string',
            'weight' => 'nullable|string',
            'image' => 'nullable|image',
        ]);

        $decodedId = HashIds::decode($id)[0] ?? null;
        $gallery = Gallery::findOrFail($decodedId);

        if ($request->filled('color')) {
            $gallery->color = $request->color;
        }
        if ($request->filled('category')) {
            $gallery->category = $request->category;
        }
        if ($request->filled('description')) {
            $gallery->description = $request->description;
        }
        if ($request->filled('material')) {
            $gallery->material = $request->material;
        }
        if ($request->filled('product_id')) {
            $gallery->product_id = $request->product_id;
        }
        if ($request->filled('shape')) {
            $gallery->shape = $request->shape;
        }
        if ($request->filled('size')) {
            $gallery->size = $request->size;
        }
        if ($request->filled('title')) {
            $gallery->title = $request->title;
        }
        if ($request->filled('weight')) {
            $gallery->weight = $request->weight;
        }
        // Handle image only if new one uploaded
        if ($request->hasFile('image')) {
            // Delete old image
            if ($gallery->img_path) {
                Storage::disk('public')->delete($gallery->img_path);
            }

            // Store new image
            $file = $request->file('image');
            $path = $file->store('Gallery', 'public');
            $gallery->img_path = $path;
        }

        $gallery->save();

        return response()->json([
            'message' => 'Gallery updated successfully',
            'gallery' => $gallery
        ]);

    }
}
