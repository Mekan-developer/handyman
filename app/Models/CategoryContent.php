<?php

namespace App\Models;

use Database\Factories\CategoryContentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryContent extends Model
{
    /** @use HasFactory<CategoryContentFactory> */
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = [
        'category_id',
        'title',
        'description',
        'price',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CategoryContentImage::class)->orderBy('sort_order')->orderBy('id');
    }
}
