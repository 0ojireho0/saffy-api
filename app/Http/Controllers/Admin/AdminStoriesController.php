<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stories;
use Illuminate\Support\Facades\Storage;
use Vinkla\Hashids\Facades\Hashids;

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
        $decodedId = HashIds::decode($id)[0] ?? null;
        $story = Stories::find($decodedId);

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

    public function archive($id){
        $decodedId = HashIds::decode($id)[0] ?? null;
        $story = Stories::find($decodedId);

        if (!$story) {
            return response()->json([
                'message' => 'Story not found'
            ], 404);
        }

        $story->update([
            'isArchive' => 1
        ]);

        return response()->json([
            'message' => 'Story archive successfully'
        ]);
    }

    public function unarchive($id){
        $decodedId = HashIds::decode($id)[0] ?? null;
        $story = Stories::find($decodedId);

        if (!$story) {
            return response()->json([
                'message' => 'Story not found'
            ], 404);
        }

        $story->update([
            'isArchive' => 0
        ]);

        return response()->json([
            'message' => 'Story restored successfully'
        ]);
    }

    public function validateStory(Request $request){


        $decodedId = Hashids::decode($request->id)[0] ?? null;

        $findStory = Stories::where('id', $decodedId)->first();

        if(!$findStory){
            return response()->json([
                'error' => "Cannot find selected story"
            ], 404);
        }

        return response()->json([
            'content' => $findStory
        ], 200);
    }

    public function update(Request $request, $id)
    {


        $request->validate([
            'author' => 'nullable|string',
            'category' => 'nullable|in:news,stories',
            'content' => 'nullable|string',
            'date' => 'nullable|date',
            'timeRange' => 'nullable|string',
            'title' => 'nullable|string',
            'image' => 'nullable|image',
        ]);


        $decodedId = HashIds::decode($id)[0] ?? null;
        $story = Stories::findOrFail($decodedId);

        // Update only provided fields
        if ($request->filled('category')) {
            $story->type = $request->category;
        }

        if ($request->filled('title')) {
            $story->title = $request->title;
        }

        if ($request->filled('author')) {
            $story->author = $request->author;
        }

        if ($request->filled('date')) {
            $story->publish_date = $request->date;
        }

        if ($request->filled('timeRange')) {
            $story->reading_time = $request->timeRange;
        }

        if ($request->filled('content')) {
            $story->content = $request->content;
        }

        // Handle image only if new one uploaded
        if ($request->hasFile('image')) {
            // Delete old image
            if ($story->publication_image_path) {
                Storage::disk('public')->delete($story->publication_image_path);
            }

            // Store new image
            $file = $request->file('image');
            $path = $file->store('Stories', 'public');
            $story->publication_image_path = $path;
        }

        $story->save();

        return response()->json([
            'message' => 'Story updated successfully',
            'story' => $story
        ]);
    }


}
