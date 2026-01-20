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

        return [
            'title' => 'required|string|max:200',

            'type' => 'required|in:general,welfare,committee',

            'content' => 'required|string',

            'committee_id' => 'required_if:type,committee|exists:committees,id',

            'attachment' => 'nullable|file|mimes:pdf,doc,docx,zip,png,jpg|max:20480'
        ];
    }
}
