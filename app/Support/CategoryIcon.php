<?php

namespace App\Support;

use App\Enums\CategoryIconType;
use App\Models\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoryIcon
{
    /** Public-disk folder holding uploaded custom icons. */
    private const CUSTOM_DIR = 'category-icons';

    /**
     * Flat list of allowed preset icon keys from config/service_icons.php.
     *
     * @return array<int, string>
     */
    public static function presetKeys(): array
    {
        return collect(config('service_icons', []))->flatten()->values()->all();
    }

    /**
     * Resolve the icon_type / icon columns from validated data and the uploaded
     * file. Stores a new custom file when provided, keeps an existing custom
     * icon when none is re-uploaded, and removes a replaced custom file.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function apply(array $data, ?UploadedFile $file, ?Category $existing = null): array
    {
        unset($data['icon_file']);

        $type = $data['icon_type'] ?? null;
        $previousCustom = $existing?->icon_type === CategoryIconType::Custom ? $existing->icon : null;

        if ($type === CategoryIconType::Custom->value) {
            if ($file instanceof UploadedFile) {
                $data['icon'] = $file->store(self::CUSTOM_DIR, 'public');
            } elseif ($previousCustom !== null) {
                $data['icon'] = $previousCustom;
            } else {
                [$data['icon_type'], $data['icon']] = [null, null];
            }
        } elseif ($type !== CategoryIconType::Preset->value) {
            [$data['icon_type'], $data['icon']] = [null, null];
        }

        if ($previousCustom !== null && ($data['icon'] ?? null) !== $previousCustom) {
            Storage::disk('public')->delete($previousCustom);
        }

        return $data;
    }

    /** Remove a category's custom icon file from disk, if any. */
    public static function purge(Category $category): void
    {
        if ($category->icon_type === CategoryIconType::Custom && $category->icon !== null) {
            Storage::disk('public')->delete($category->icon);
        }
    }
}
