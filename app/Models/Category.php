<?php

namespace App\Models;

use App\Helpers\ModelHelper;
use App\Helpers\ModelHelperConfig;
use App\Traits\HasFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory, HasFilter, ModelHelper;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'slug',
        'status',
        'type',
        'is_featured',
        'category_id',
        'lang',
        'icon',
        'preview',
    ];

    public function modelHelperConfig(): ModelHelperConfig
    {
        return ModelHelperConfig::create()
            ->files(['icon', 'preview'])
            ->generateSlug('title');
    }

    //
    public function categories()
    {
        return $this->hasMany(Category::class, 'category_id', 'id');
    }

    //parent category
    public function parent()
    {
        return $this->hasOne(Category::class, 'id', 'category_id');
    }

    //nasted
    public function childrenCategories()
    {
        return $this->hasMany(Category::class, 'category_id', 'id')->with('categories');
    }

    public function postcategory()
    {
        return $this->belongsTo(PostCategory::class);
    }

    public function postCategories()
    {
        return $this->hasMany(PostCategory::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function posts()
    {
        return $this->belongsToMany('App\Models\Post', 'postCategories', 'post_id', 'category_id');
    }

    public function faqs()
    {
        return $this->belongsToMany('App\Models\Post', 'post_categories', 'category_id', 'post_id')
            ->where('type', 'faq');
    }


    public function companies()
    {
        return $this->hasMany(User::class, 'category_id', 'id');
    }

    public function scopeActive($query): Builder
    {
        return $query->where('status', 1);
    }
}
