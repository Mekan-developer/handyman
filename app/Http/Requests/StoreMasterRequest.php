<?php

namespace App\Http\Requests;

use App\PaymentModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMasterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20', 'unique:masters,phone'],
            'payment_model' => ['required', Rule::enum(PaymentModel::class)],
            'payment_value' => ['required', 'numeric', 'min:0'],
            'access_expires_at' => ['nullable', 'date', 'after:now'],
            'is_active' => ['required', 'boolean'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'exists:categories,id'],
        ];
    }
}
