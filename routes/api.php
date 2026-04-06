<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminStoriesController;
use App\Http\Controllers\Client\ClientStoriesController;



Route::middleware(['auth:sanctum'])->group(function(){

    Route::get('/user', function(Request $request){
        return $request->user();
    });


    // Stories ADMIN
    Route::post('/admin/stories/add', [AdminStoriesController::class, 'addStories']);
    Route::get('/admin/stories', [AdminStoriesController::class, 'index']);
    Route::delete('/admin/stories/{id}', [AdminStoriesController::class, 'destroy']);
    Route::post('/admin/stories/{id}', [AdminStoriesController::class, 'update']);

});

// Client
Route::get('/client/stories', [ClientStoriesController::class, 'index']);
Route::get('/validate-story', [AdminStoriesController::class, 'validateStory']);

Route::get('/session-test', function (Request $request) {
    $request->session()->put('test', 'hello');
    return $request->session()->get('test');
});

Route::get('/test', function(){
    return "hello";
});
