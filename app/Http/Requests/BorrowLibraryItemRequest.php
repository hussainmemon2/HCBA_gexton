<?php

namespace App\Http\Requests;

use App\Models\Borrowing;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

use Illuminate\Contracts\Validation\Validator;

class BorrowLibraryItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'cnic_number' => [
                'nullable',
                'required_if:status,borrowed',
                'exists:users,cnic',
            ],

            'date' => [
                'nullable',
                'date',
                'required_if:status,borrowed',
                'after_or_equal:today',
            ],


            'status' => [
                'required',
                'in:borrowed,returned,reserved',
            ],

            'library_item_id' => [
                'required',
                'integer',
                'exists:library_items,id',
            ],
        ];

        // === ONLY APPLY AVAILABILITY CHECKS WHEN STATUS IS 'borrowed' ===
        if ($this->input('status') === 'borrowed') {
            $rules['library_item_id'][] = function ($attribute, $value, $fail) {
                $latestBorrowing = Borrowing::where('library_item_id', $value)
                    ->where('user_id', $this->input('user_id'))
                    ->latest('id')
                    ->first();

                if ($latestBorrowing && $latestBorrowing->status === 'borrowed') {
                    $fail('You have already borrowed this item and have not returned it.');
                }
                if ($latestBorrowing && $latestBorrowing->status === 'reserved') {
                    $fail('This item is already reserved you cannot borrow it.');
                }
            };
        }

        // if()
        // Optional: When returning, ensure the latest record is actually 'borrowed'
        if ($this->input('status') === 'returned') {
            $rules['library_item_id'][] = function ($attribute, $value, $fail) {
                $latestBorrowing = Borrowing::where('library_item_id', $value)
                    ->latest('id')
                    ->first();

                if (! $latestBorrowing || $latestBorrowing->status !== 'borrowed') {
                    $fail('This item is not currently borrowed, so it cannot be returned.');
                }
            };
        }
        if ($this->input('status') === 'reserved') {
            $rules['library_item_id'][] = function ($attribute, $value, $fail) {
                $latestBorrowing = Borrowing::where('library_item_id', $value)
                    ->latest('id')
                    ->first();

                if ($latestBorrowing && $latestBorrowing->status == 'borrowed') {
                    $fail('This item is currently borrowed,so it cannot be reserved.');
                }
            };
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'library_item_id.required' => 'Please select a library item.',
            'library_item_id.exists' => 'The selected item does not exist.',

            'user_id.required' => 'User ID is required.',
            'user_id.exists' => 'The selected user does not exist.',

            'cnic_number.required' => 'CNIC number is required.',
            'cnic_number.exists' => 'The provided CNIC does not match the user\'s registered CNIC.',

            'date.required' => 'Expected return date is required.',
            'date.after_or_equal' => 'Return date cannot be in the past.',

            'status.in' => 'Status must be either borrowed or returned.',
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
