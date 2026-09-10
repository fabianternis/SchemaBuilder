<?php

namespace App\Http\Requests;

use App\Models\SchemaColumn;
use App\Support\ColumnTypeRules;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateColumnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Authorization is handled via abort_if() in the controller
    }

    public function rules(): array
    {
        $type = strtolower($this->input('type', ''));

        return [
            'name'                => ['required', 'string', 'max:255'],
            'type'                => ['required', 'string', Rule::in(SchemaColumn::VALID_TYPES)],
            'length'              => [
                'nullable', 'integer', 'min:1',
                Rule::prohibitedIf(!ColumnTypeRules::allows($type, 'length')),
            ],
            'auto_increment'      => [
                'boolean',
                Rule::prohibitedIf(!ColumnTypeRules::allows($type, 'auto_increment')),
            ],
            'is_nullable'         => ['boolean'],
            'is_primary'          => ['boolean'],
            'is_unique'           => ['boolean'],
            'default'             => ['nullable', 'string', 'max:255'],
            'on_cascade'          => ['nullable', 'string', 'max:50'],
            'referenced_table_id' => ['nullable', 'string'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function ($v) {
            if ($this->boolean('is_primary') && $this->boolean('is_nullable')) {
                $v->errors()->add('is_nullable', 'A primary key column cannot be nullable.');
            }
            if ($this->boolean('auto_increment') && $this->boolean('is_nullable')) {
                $v->errors()->add('is_nullable', 'AUTO_INCREMENT columns cannot be nullable.');
            }
            if ($this->boolean('is_primary') && $this->boolean('is_unique')) {
                $v->errors()->add('is_unique', 'Primary keys are unique by definition; is_unique is redundant.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'type.in'     => 'The selected column type is not supported.',
            'length.prohibited' => 'Length / precision is not supported for this column type.',
            'auto_increment.prohibited' => 'Auto-increment is not supported for this column type.',
        ];
    }
}
