<?php

namespace App\Http\Requests;

use App\Models\Booking;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(Request $request): bool
    {
        $isAuthenticated = (bool) $request->user();

        return $isAuthenticated;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(Request $request): array
    {
        $bookingId = $this->route('id');
        $user = $request->user();
        $isAdmin = $user->role === 'admin';
        return [

            'cnic_number' => [
                'nullable',
                'integer',
                'exists:users,cnic',
                Rule::requiredIf($isAdmin),   // ← just pass true/false
            ],
            'title' => 'required',
            'auditorium_id' => [
                'required',
                'integer',
                'exists:auditoriums,id',
            ],
            'booking_date' => [
                'required',
                'date',
                function ($attribute, $value, $fail) use ($bookingId) {
                    $exists = Booking::where('booking_date', $value)
                        ->when($bookingId, fn($q) => $q->where('id', '!=', $bookingId))
                        ->exists();

                    if ($exists) {
                        $fail('This date is already taken. Please choose another day.');
                    }
                },
            ],
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
