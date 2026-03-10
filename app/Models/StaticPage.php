<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
        'images',
    ];

    protected $casts = [
        'images' => 'array',
    ];
}
