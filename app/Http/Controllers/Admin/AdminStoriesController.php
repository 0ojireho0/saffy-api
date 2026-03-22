<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stories;
use Illuminate\Support\Facades\Storage;

class AdminStoriesController extends Controller
{

    public function index(){
        return Stories::orderBy('id', 'desc')->get();
    }



    public function addStories(Request $request)
    {
        $request->validate([
            'author' => 'required|string',
            'category' => 'required|in:news,stories',
            'content' => 'required|string',
            'date' => 'required|date',
            'timeRange' => 'required|string',
            'title' => 'required|string',
            'image' => 'required|image',
        ]);

        $file = $request->file('image');

        $path = $file->store('Stories', 'public');

        Stories::create([
            'type' => $request->category,
            'title' => $request->title,
            'author' => $request->author,
            'publish_date' => $request->date,
            'reading_time' => $request->timeRange,
            'publication_image_path' => $path,
            'content' => $request->content,
        ]);

        return response()->json([
            'message' => 'Story created successfully.',
            'path' => $path,
            'url' => asset('storage/' . $path),
        ]);
    }

    public function destroy($id)
    {
        $story = Stories::find($id);

        if (!$story) {
            return response()->json([
                'message' => 'Story not found'
            ], 404);
        }

        // Delete image from storage
        if ($story->publication_image_path) {
            Storage::disk('public')->delete($story->publication_image_path);
        }

        // Delete database record
        $story->delete();

        return response()->json([
            'message' => 'Story deleted successfully'
        ]);
    }
}
