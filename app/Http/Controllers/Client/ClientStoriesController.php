<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Stories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientStoriesController extends Controller
{
    //

    public function index(){
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
}
