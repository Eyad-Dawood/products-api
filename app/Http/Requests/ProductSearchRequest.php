<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductSearchRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'gte:min_price'],
            'category_id' => ['nullable', 'integer','exists:categories,id'],
            'is_available' => ['nullable', 'boolean'],
            'sort_by' => ['nullable', 'in:price,created_at'],
            'sort_dir' => ['nullable', 'in:asc,desc'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function cacheKey():string{
       $params = $this->only(['q', 'min_price', 'max_price', 'category_id', 'is_available', 'sort_by', 'sort_dir', 'per_page', 'page']);

       return 'product_search_' . md5(json_encode($params));
    }
}
