<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('create-projects');
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'exists:departments,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'name')->where(function ($query) {
                    return $query->where('department_id', $this->input('department_id'));
                }),
            ],
            'code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('projects', 'code')->where(function ($query) {
                    return $query->where('department_id', $this->input('department_id'));
                }),
            ],
            'description' => ['nullable', 'string'],
            'admin_user_id' => ['nullable', 'exists:users,id'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
