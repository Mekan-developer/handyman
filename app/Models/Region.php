<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Region extends Model
{
    /** @var array<int, string> */
    protected $fillable = [
        'name',
        'oblast_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function oblast(): BelongsTo
    {
        return $this->belongsTo(Oblast::class);
    }
}
