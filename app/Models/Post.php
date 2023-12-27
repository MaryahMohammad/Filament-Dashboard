<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'image',
        'slug',
        'content',
        'featured',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function author(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(){
        return $this->belongsToMany(Category::class);
    }
    public function comments(){
        return $this->hasMany(Comment::class);
    }

}
