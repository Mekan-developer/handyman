<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreCategoryContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price' => ['nullable', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'max:10240'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $hasNewImage = $this->hasFile('image');
            /** @var Category $category */
            $category = $this->route('category');
            $hasExisting = $category->content?->images()->exists() ?? false;

            if (! $hasNewImage && ! $hasExisting) {
                $v->errors()->add('image', __('categories.content_images_required'));
            }
        });
    }
}
