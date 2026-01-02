<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnouncementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $isUpdate = $this->route('id') !== null;

        return [
            'title' => $isUpdate
                ? 'sometimes|string|max:200'
                : 'required|string|max:200',

            'type' => $isUpdate
                ? 'sometimes|in:general,welfare,committee'
                : 'required|in:general,welfare,committee',

            'content' => $isUpdate
                ? 'sometimes|string'
                : 'required|string',

            'committee_id' => $isUpdate
                ? 'nullable|required_if:type,committee|exists:committees,id'
                : 'nullable|required_if:type,committee|exists:committees,id',
        ];
    }
}
