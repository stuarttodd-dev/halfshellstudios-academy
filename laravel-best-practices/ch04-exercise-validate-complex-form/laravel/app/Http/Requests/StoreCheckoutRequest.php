<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => is_string($this->input('email')) ? strtolower(trim($this->input('email'))) : $this->input('email'),
            'name' => is_string($this->input('name')) ? trim($this->input('name')) : $this->input('name'),
            'company_name' => is_string($this->input('company_name')) ? trim($this->input('company_name')) : $this->input('company_name'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'account_type' => ['required', 'in:personal,business'],
            'company_name' => ['required_if:account_type,business', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:64'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],

            'purchase_order_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:4096'],
        ];
    }
}
