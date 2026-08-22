<?php

namespace Modules\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Core\Models\Document;
use Modules\Core\Policies\DocumentPolicy;

class StoreDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return app(DocumentPolicy::class)->createWithScope(
            $this->user(),
            (string) $this->input('visibility'),
            $this->filled('division_id') ? (int) $this->input('division_id') : null,
            $this->filled('department_id') ? (int) $this->input('department_id') : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['nullable', 'string', 'max:50'],
            'file' => [
                'required', 'file', 'max:20480',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip,txt,csv',
            ],
            'visibility' => ['required', Rule::in([
                Document::VISIBILITY_PALJAYA, Document::VISIBILITY_DIVISION, Document::VISIBILITY_DEPARTMENT,
            ])],
            'division_id' => [
                'nullable', 'integer', Rule::exists('core_divisions', 'id'),
                Rule::requiredIf($this->input('visibility') === Document::VISIBILITY_DIVISION),
            ],
            'department_id' => [
                'nullable', 'integer', Rule::exists('core_departments', 'id'),
                Rule::requiredIf($this->input('visibility') === Document::VISIBILITY_DEPARTMENT),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.max' => 'Ukuran file maksimal 20 MB.',
            'file.mimes' => 'Jenis file tidak didukung.',
            'division_id.required' => 'Pilih division tujuan.',
            'department_id.required' => 'Pilih department tujuan.',
        ];
    }
}
