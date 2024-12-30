<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProviderPurchaseRequest extends FormRequest
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
            'storage_id' => ['required', 'integer'],
            'products' => ['required', 'array'],
            'products.*.name' => ['required', 'string', 'max:255'],
            'products.*.category' => ['required', 'string', 'max:255'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
            'products.*.purchase_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
