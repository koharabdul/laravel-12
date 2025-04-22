<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            // 'slug' => 'required|string|unique:product,slug,' . $this->user->id,
            'slug' => 'required|string',
            'product_category' => 'required|string',
            'description' => 'sometimes|string',
            'price' => 'required|numeric',
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ];
    }
}
