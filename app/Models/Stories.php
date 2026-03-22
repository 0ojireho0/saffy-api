<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stories extends Model
{
    //

    protected $fillable = [
        'type',
        'title',
        'author',
        'publish_date',
        'reading_time',
        'publication_image_path',
        'content'
    ];
}
