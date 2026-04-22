<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Stories;
use Illuminate\Support\Facades\Storage;
use Vinkla\Hashids\Facades\Hashids;

class AdminStoriesController extends Controller
{

    public function index()
    {
        $stories = Stories::orderBy('id', 'desc')->get();

        $stories->transform(function ($story) {
            $story->publication_image_url = Storage::disk('s3')->temporaryUrl(
                $story->publication_image_path,
                now()->addMinutes(30)
            );

            return $story;
        });

        return $stories;
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
        $path = $file->store('Stories', 's3');

        $url = Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(30));

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
            'url' => $url,
        ]);
    }

    public function destroy($id)
    {
        $decodedId = HashIds::decode($id)[0] ?? null;

        if (! $decodedId) {
            return response()->json([
                'message' => 'Invalid story ID'
            ], 400);
        }

        $story = Stories::find($decodedId);

        if (! $story) {
            return response()->json([
                'message' => 'Story not found'
            ], 404);
        }

        if ($story->publication_image_path && Storage::disk('s3')->exists($story->publication_image_path)) {
            Storage::disk('s3')->delete($story->publication_image_path);
        }

        $story->delete();

        return response()->json([
            'message' => 'Story deleted successfully'
        ]);
    }

    public function validateStory(Request $request)
    {
        $decodedId = Hashids::decode($request->id)[0] ?? null;

        if (! $decodedId) {
            return response()->json([
                'error' => 'Invalid story ID',
            ], 400);
        }

        $findStory = Stories::where('id', $decodedId)->first();

        if (! $findStory) {
            return response()->json([
                'error' => 'Cannot find selected story',
            ], 404);
        }

        $findStory->publication_image_url = $findStory->publication_image_path
            ? Storage::disk('s3')->temporaryUrl(
                $findStory->publication_image_path,
                now()->addMinutes(30)
            )
            : null;

        return response()->json([
            'content' => $findStory,
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

        if (! $decodedId) {
            return response()->json([
                'message' => 'Invalid story ID'
            ], 400);
        }

        $story = Stories::find($decodedId);

        if (! $story) {
            return response()->json([
                'message' => 'Story not found'
            ], 404);
        }

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

        if ($request->hasFile('image')) {
            if ($story->publication_image_path && Storage::disk('s3')->exists($story->publication_image_path)) {
                Storage::disk('s3')->delete($story->publication_image_path);
            }

            $file = $request->file('image');
            $path = $file->store('Stories', 's3');
            $story->publication_image_path = $path;
        }

        $story->save();

        $story->publication_image_url = $story->publication_image_path
            ? Storage::disk('s3')->temporaryUrl(
                $story->publication_image_path,
                now()->addMinutes(30)
            )
            : null;

        return response()->json([
            'message' => 'Story updated successfully',
            'story' => $story
        ]);
    }


}
