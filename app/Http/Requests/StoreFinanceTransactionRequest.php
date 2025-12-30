<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
class StoreFinanceTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'transaction_type' => ['required', Rule::in(['funding', 'expense'])],

            'source_type' => ['required', Rule::in([
                'annual_fee',
                'donation',
                'other_funding',
                'committee_expense',
                'welfare_expense',
                'other_expense',
            ])],

           'cnic' => [
                Rule::requiredIf(fn () => $this->source_type === 'annual_fee'),
                'nullable',
                'string',
                'exists:users,cnic'
            ],

            'committee_id' => [
                Rule::requiredIf(fn () => $this->source_type === 'committee_expense'),
                'nullable',
                'exists:committees,id'
            ],

            'welfare_claim_id' => [
                Rule::requiredIf(fn () => $this->source_type === 'welfare_expense'),
                'nullable',
                'exists:welfare_claims,id'
            ],

            'title' => ['required', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],

            'amount' => ['required', 'integer', 'min:1'],

        ];
    }

    public function messages(): array
    {
        return [
            'transaction_type.required' => 'Transaction type is required.',
            'transaction_type.in' => 'Transaction type must be either funding or expense.',

            'source_type.required' => 'Source type is required.',
            'source_type.in' => 'Invalid source type selected.',

            'cnic.required' => 'CNIC is required for annual fee transactions.',
            'cnic.exists' => 'No member found with this CNIC.',

            'committee_id.required' => 'Committee is required for committee expense transactions.',
            'committee_id.exists' => 'Selected committee does not exist.',

            'welfare_claim_id.required' => 'Welfare claim is required for welfare expense transactions.',
            'welfare_claim_id.exists' => 'Selected welfare claim does not exist.',

            'title.required' => 'Transaction title is required.',
            'title.max' => 'Transaction title cannot exceed 255 characters.',

            'amount.required' => 'Amount is required.',
            'amount.integer' => 'Amount must be a whole number in PKR.',
            'amount.min' => 'Amount must be greater than zero.',

        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => false,
            'errors' => $validator->errors(),
        ], 422));
    }
}
