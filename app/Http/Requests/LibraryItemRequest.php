<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class LibraryItemRequest extends FormRequest
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
        $rules = [
            'title' => ['required', 'string', 'max:200'],
            'type' => ['required', 'in:book,book,e-journal'],
            'author_name' => ['required'],
            'files' => [
                $this->input('type') === 'e-journal'
                    ?  'required'
                    : 'nullable',

                'file',
                'mimes:pdf,doc,docx',
                'max:10240',
            ],
            'rfid_tag' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('library_items', 'rfid_tag')->ignore($this->input('id')),
            ],
        ];
        $type = $this->input('type');

        if ($type === 'e-journal') {
            // For books: require at least one file on create, optional on update
            $rules['files'] = [
                'array', // ensures it's an array
                'required', // required only on create
            ];

            $rules['file.*'] = [
                'file',                  // must be a file
                'mimes:pdf,doc,docx,zip', // adjust allowed types
                'max:20480',             // max 20MB per file (adjust as needed)
            ];
        } else {
            // For non-books (e.g., e-journal): files optional
            $rules['files'] = ['nullable', 'array'];

            $rules['files.*'] = [
                'file',
                'mimes:pdf,doc,docx,zip',
                'max:20480',
            ];
        }
        return $rules;
    }

    public function messages(): array
    {
        return [

            'title.required' => 'The title is required.',
            'title.max' => 'The title may not exceed 200 characters.',
            'files.required' => 'Files must be present when type is e-journal',

            'type.required' => 'Please select an item type.',
            'type.in' => 'The type must be Book, Journal, or E-Journal.',

            'rfid_tag.unique' => 'This RFID tag is already assigned to another library item.',
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
