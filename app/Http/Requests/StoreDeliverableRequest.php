<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreDeliverableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create-deliverable');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'priority' => 'nullable|string',
            'assignee_name' => 'nullable|string',
            'deadline' => 'nullable|date',
            'task_type' => 'required|string',
            'progress_percent' => 'required|integer',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'approver_id' => 'nullable|exists:users,id',
            'writer_id' => 'nullable|exists:users,id',
            'approval_stage' => 'nullable|string',
            'parent_deliverable_id' => 'nullable|exists:deliverables,id',
            
            // Subtasks validation
            'subtasks' => 'nullable|array',
            'subtasks.*.title' => 'nullable|string',
            'subtasks.*.post_type' => 'nullable|string',
            'subtasks.*.concept' => 'nullable|string',
            'subtasks.*.caption' => 'nullable|string',
            'subtasks.*.post_copy' => 'nullable|string',
            'subtasks.*.reference' => 'nullable|string',
            'subtasks.*.reference_file' => 'nullable|file|mimes:jpg,jpeg,png,webp|max:10240',
            'subtasks.*.deadline' => 'nullable|date',
            'subtasks.*.priority' => 'nullable|string',
            'subtasks.*.writer_id' => 'nullable|exists:users,id',
            'subtasks.*.notes' => 'nullable|string',
        ];
    }
}
