<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    use HasFactory;

    public function Category()
    {
        return $this->belongsToMany(Category::class);
    }

    public function post()
    {
        return $this->belongsToMany(Post::class);
    }
    
    public function faqs()
    {
        return $this->belongsToMany(Post::class, 'post_category', 'category_id', 'post_id');
    }
}
