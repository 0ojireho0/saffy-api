<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Vinkla\Hashids\Facades\Hashids;

class Gallery extends Model
{
    //

    protected $fillable = [
        'product_id',
        'title',
        'description',
        'material',
        'color',
        'shape',
        'size',
        'weight',
        'img_path',
        'category',
        'isFeatured'

    ];

    public function getIdAttribute($value){
        return Hashids::encode($value);
    }
}
