<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchRefundRequest extends FormRequest
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
            'type' => ['required', 'string', 'in:full,partial'],
            'storage_id' => ['required', 'integer'],
            'products' => ['required_if:type,partial', 'array'],
            'products.*.id' => ['required_if:type,partial', 'integer', 'exists:products,id'],
            'products.*.quantity' => ['required_if:type,partial', 'integer', 'min:1'],
        ];
    }
}
