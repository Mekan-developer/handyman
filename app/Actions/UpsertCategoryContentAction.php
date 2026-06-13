<?php

namespace App\Actions;

use App\Models\Category;
use App\Models\CategoryContent;
use App\Repositories\CategoryContentRepository;
use App\Support\PhotoConverter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpsertCategoryContentAction
{
    public function __construct(private readonly CategoryContentRepository $repository) {}

    /**
     * @param  array{title: string, description: ?string, price: ?string}  $data
     */
    public function handle(Category $category, array $data, ?UploadedFile $image): CategoryContent
    {
        $content = $this->repository->upsert($category, [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'price' => $data['price'] ?? null,
        ]);

        if ($image !== null) {
            $this->repository->deleteImagesExcept($content, []);

            $path = $image->store('category-content', 'public');
            $absolutePath = Storage::disk('public')->path($path);

            try {
                $webpAbsolute = PhotoConverter::convertContent($absolutePath);
                Storage::disk('public')->delete($path);
                $webpRelative = ltrim(
                    str_replace(Storage::disk('public')->path(''), '', $webpAbsolute),
                    DIRECTORY_SEPARATOR
                );
                $this->repository->addImage($content, $webpRelative);
            } catch (\Throwable) {
                $this->repository->addImage($content, $path);
            }
        }

        return $content;
    }
}
