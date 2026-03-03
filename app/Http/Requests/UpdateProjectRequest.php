<?php

namespace App\Http\Requests;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('edit-projects');
    }

    public function rules(): array
    {
        /** @var Project|null $project */
        $project = $this->route('project');
        $projectId = $project?->id;

        return [
            'department_id' => ['required', 'exists:departments,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'name')
                    ->where(function ($query) {
                        return $query->where('department_id', $this->input('department_id'));
                    })
                    ->ignore($projectId),
            ],
            'code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('projects', 'code')
                    ->where(function ($query) {
                        return $query->where('department_id', $this->input('department_id'));
                    })
                    ->ignore($projectId),
            ],
            'description' => ['nullable', 'string'],
            'admin_user_id' => ['nullable', 'exists:users,id'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
