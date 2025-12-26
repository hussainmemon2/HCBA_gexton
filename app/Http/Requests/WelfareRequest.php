<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WelfareRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->isMethod('put') || $this->isMethod('patch') || $this->filled('id');

        $rules = [
            'id' => [$isUpdate ? 'required' : 'prohibited', 'integer', 'exists:welfare_claims,id'],
            'claimer_id' => ['nullable'],

            'cnic_number' => ['required', 'string', 'exists:users,cnic'],

            'type' => ['required', 'in:medical,death,others'],

            'amount' => ['nullable', 'numeric', 'min:0'],
            // 'description' => ['required'],
            // 'remark' => ['required_if:status,received,rejected,approved,funding,ready,collected', 'string'],
            // 'remark' => ['required_if:status,rejected,approved,funding,ready,collected', 'string'],

            'received_date' => ['nullable', 'date'],
            'reason'=>['required'],

            'status' => ['nullable', 'in:received'],

            'files' => ['nullable', 'array'],
            'files.*' => ['file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:10240'], // 10MB max per file
        ];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'id.required' => 'The welfare claim ID is required for updating.',
            'id.exists' => 'The selected welfare claim does not exist.',
            'id.prohibited' => 'ID should not be provided when creating a new claim.',

            'cnic_number.required' => 'CNIC number is required.',
            'cnic_number.exists' => 'The provided CNIC does not exist.',

            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'The selected user does not exist.',

            'type.required' => 'Please select a claim type.',
            'type.in' => 'The type must be medical, death, or others.',

            'amount.numeric' => 'Amount must be a number.',
            'amount.min' => 'Amount cannot be negative.',

            'files.array' => 'Files must be an array.',
            'files.*.file' => 'Each file must be a valid file.',
            'files.*.mimes' => 'Files must be PDF, DOC, DOCX, JPG, JPEG, or PNG.',
            'files.*.max' => 'Each file may not exceed 10MB.',
        ];
    }

    protected function prepareForValidation()
    {
        // Set claimer_id to authenticated user if not provided
        if (! $this->has('claimer_id')) {
            $this->merge(['claimer_id' => auth()->id()]);
        }
    }
}
