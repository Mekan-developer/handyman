<?php

namespace App\Models;

use App\Enums\CategoryIconType;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    /** @var array<int, string> */
    protected $fillable = [
        'parent_id',
        'name',
        'icon_type',
        'icon',
        'is_active',
    ];

    /** @var array<int, string> */
    protected $appends = ['icon_url'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'parent_id' => 'integer',
            'icon_type' => CategoryIconType::class,
        ];
    }

    /**
     * Resolved public URL of the category icon — preset asset or uploaded file.
     * Returns null when no icon is set. Consumed by web (CSS mask) and the mobile API.
     */
    protected function iconUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => match (true) {
            $this->icon_type === CategoryIconType::Preset && $this->icon !== null => asset("icons/services/{$this->icon}.svg"),
            $this->icon_type === CategoryIconType::Custom && $this->icon !== null => Storage::disk('public')->url($this->icon),
            default => null,
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function content(): HasOne
    {
        return $this->hasOne(CategoryContent::class);
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }
}
