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
        $rules = [
            'project_id' => 'required|exists:projects,id',
            'title' => 'nullable|string|max:255',
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
            'subtasks.*.reference_file' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov,avi,webm,pdf,doc,docx,ppt,pptx,txt,zip|max:512000',
            'subtasks.*.deadline' => 'nullable|date',
            'subtasks.*.priority' => 'nullable|string',
            'subtasks.*.writer_id' => 'nullable|exists:users,id',
            'subtasks.*.notes' => 'nullable|string',
            'subtasks.*.brief' => 'nullable|string',
        ];

        // For retainer projects, ensure title is provided if not adding to an existing parent deliverable
        $project = \App\Models\Project::find($this->input('project_id'));
        if ($project && $project->workflow_type === 'retainer' && !$this->input('parent_deliverable_id')) {
            $rules['title'] = 'required|string|max:255';
        }

        return $rules;
    }
}
