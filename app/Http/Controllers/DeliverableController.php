<?php

namespace App\Http\Controllers;

use App\Models\Deliverable;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

use App\Models\User;
use App\Notifications\DeliverableUpdated;
use Illuminate\Support\Str;
use App\Http\Requests\StoreDeliverableRequest;

class DeliverableController extends Controller
{


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('create-deliverable');
        
        $projects = Project::with('brand')->get();
        $users = \App\Models\User::where('role', 'Writer')->get();
        $selectedProjectId = $request->query('project_id');
        $parentId = $request->query('parent_id');
        $progressPercent = $request->query('progress_percent', 0);
        
        $parentTask = $parentId ? Deliverable::find($parentId) : null;
        $project = $selectedProjectId ? Project::find($selectedProjectId) : null;
        $workflowType = $project ? $project->workflow_type : 'retainer';

        $subtaskTypes = \App\Models\SubtaskType::all();
        
        return view('deliverables.create', compact('projects', 'users', 'selectedProjectId', 'progressPercent', 'parentId', 'parentTask', 'workflowType', 'subtaskTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeliverableRequest $request)
    {
        \Illuminate\Support\Facades\Gate::authorize('create-deliverable');

        $validated = $request->validated();

        $subtasks = !empty($validated['subtasks']) ? $validated['subtasks'] : [];
        $parentId = $validated['parent_deliverable_id'] ?? null;

        // If creating a NEW deliverable and exactly 1 subtask is defined, consolidate into a standalone deliverable
        if (!$parentId && count($subtasks) === 1) {
            $sub = $subtasks[0];
            $taskData = \Illuminate\Support\Arr::except($validated, ['subtasks', 'parent_deliverable_id']);
            
            // Map subtask fields to the main task
            // If the user specified a custom subtask title, use it. Otherwise, keep the main title.
            if (!empty($sub['title'])) {
                $taskData['title'] = $sub['title'];
            }
            $taskData['post_type'] = $sub['post_type'] ?? null;
            $taskData['concept']   = $sub['concept'] ?? null;
            $taskData['notes']     = $sub['notes'] ?? null;
            $taskData['caption']   = $sub['caption'] ?? null;
            $taskData['post_copy'] = $sub['post_copy'] ?? null;
            $taskData['reference'] = $sub['reference'] ?? null;
            $taskData['deadline']  = $sub['deadline'] ?? ($taskData['deadline'] ?? null);
            $taskData['priority']  = $sub['priority'] ?? ($taskData['priority'] ?? 'Medium');
            $project = Project::find($taskData['project_id']);
            $stages = ($project && in_array($project->workflow_type, ['campaign', 'pitch'])) ? Deliverable::CAMPAIGN_STAGES : Deliverable::STAGES;
            $taskData['approval_stage'] = $stages[0]; 
            
            if (!empty($sub['writer_id'])) {
                $taskData['writer_id'] = $sub['writer_id'];
                $u = \App\Models\User::find($sub['writer_id']);
                if ($u) $taskData['assignee_name'] = $u->name;
            }

            if ($request->hasFile("subtasks.0.reference_file")) {
                $path = $request->file("subtasks.0.reference_file")->store('references', 'public');
                $taskData['reference_file'] = asset('storage/' . $path);
            }

            $singleTask = Deliverable::create($taskData);
            return redirect()->route('projects.show', $singleTask->project_id)->with('success', 'Deliverable created.');
        }

        // Standard logic for 0 or 2+ subtasks, or adding to existing parent
        $parentData = \Illuminate\Support\Arr::except($validated, ['subtasks']);
        
        if ($parentId) {
            $parentTask = Deliverable::findOrFail($parentId);
        } else {
            $parentTask = Deliverable::create($parentData);
        }

        if (!empty($subtasks)) {
            $existingCount = $parentTask->subtasks()->count();
            foreach ($subtasks as $index => $sub) {
                $writerId = $sub['writer_id'] ?? $parentTask->writer_id;
                $writerName = 'Unassigned';
                
                if ($writerId) {
                    $u = \App\Models\User::find($writerId);
                    if ($u) $writerName = $u->name;
                }

                $subTitle = !empty($sub['title']) 
                            ? $sub['title'] 
                            : $parentTask->title . ' - Subtask ' . ($existingCount + $index + 1);

                $refFile = null;
                if ($request->hasFile("subtasks.{$index}.reference_file")) {
                    $path = $request->file("subtasks.{$index}.reference_file")->store('references', 'public');
                    $refFile = asset('storage/' . $path);
                }

                Deliverable::create([
                    'parent_deliverable_id' => $parentTask->id,
                    'project_id' => $parentTask->project_id,
                    'title' => $subTitle,
                    'status' => 'To Do',
                    'task_type' => 'Deliverable',
                    'progress_percent' => 0,
                    'post_type' => $sub['post_type'] ?? null,
                    'concept' => $sub['concept'] ?? null,
                    'notes' => $sub['notes'] ?? null,
                    'caption' => $sub['caption'] ?? null,
                    'post_copy' => $sub['post_copy'] ?? null,
                    'reference' => $sub['reference'] ?? null,
                    'reference_file' => $refFile,
                    'deadline' => $sub['deadline'] ?? $parentTask->deadline,
                    'priority' => $sub['priority'] ?? $parentTask->priority,
                    'approval_stage' => ($parentTask->project && in_array($parentTask->project->workflow_type, ['campaign', 'pitch'])) ? Deliverable::CAMPAIGN_STAGES[0] : Deliverable::STAGES[0],
                    'writer_id' => $writerId,
                    'assignee_name' => $writerName,
                    'revisions' => 0,
                ]);
            }
        }

        return redirect()->route('projects.show', $parentTask->project_id)->with('success', 'Deliverables created.');
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Deliverable $deliverable)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role !== 'Brand Manager') abort(403);
        $projects = Project::all();
        $users = \App\Models\User::where('role', 'Writer')->get();
        $subtaskTypes = \App\Models\SubtaskType::all();
        return view('deliverables.edit', compact('deliverable', 'projects', 'users', 'subtaskTypes'));
    }

    public function update(Request $request, Deliverable $deliverable)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role !== 'Brand Manager') abort(403);
        if ($request->has('toggle_status')) {
            // Manual toggle disabled as per new workflow-locked requirement
            return response()->json(['success' => false, 'message' => 'Manual completion disabled. Use the workflow stages instead.']);
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'priority' => 'required|string',
            'assignee_name' => 'nullable|string',
            'deadline' => 'nullable|date',
            'task_type' => 'required|string',
            'progress_percent' => 'required|integer',
            'post_type' => 'nullable|string',
            'concept' => 'nullable|string',
            'caption' => 'nullable|string',
            'post_copy' => 'nullable|string',
            'reference' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'approver_id' => 'nullable|exists:users,id',
            'approval_stage' => 'nullable|string',
            'final_designs' => 'nullable|string',
            'revisions' => 'nullable|integer',
            'is_ready' => 'nullable|boolean',
        ]);

        if ($request->hasFile('reference_file')) {
            $path = $request->file('reference_file')->store('references', 'public');
            $validated['reference_file'] = asset('storage/' . $path);
        }

        $oldStage = $deliverable->approval_stage;
        $deliverable->update($validated);

        if (isset($validated['approval_stage']) && $validated['approval_stage'] !== $oldStage) {
            $deliverable->notifyStageChange($oldStage, $validated['approval_stage'], auth()->user());
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Deliverable updated successfully.']);
        }

        return redirect()->back()->with('success', 'Deliverable updated successfully.');
    }

    /**
     * Advance the deliverable to the next workflow stage.
     */
    public function submitStage(Request $request, Deliverable $deliverable)
    {
        if ($request->input('action') === 'save_only') {
            if ($request->has('concept')) $deliverable->concept = $request->concept;
            if ($request->has('notes')) $deliverable->notes = $request->notes;
            if ($request->has('caption')) $deliverable->caption = $request->caption;
            if ($request->has('post_copy')) $deliverable->post_copy = $request->post_copy;
            if ($request->has('reference')) $deliverable->reference = $request->reference;
            if ($request->has('final_designs_link')) $deliverable->final_designs_link = $request->final_designs_link;
            
            if ($request->hasFile('reference_file')) {
                $path = $request->file('reference_file')->store('references', 'public');
                $deliverable->reference_file = asset('storage/' . $path);
            }

            if ($request->hasFile('final_designs_file')) {
                $path = $request->file('final_designs_file')->store('deliveries', 'public');
                $deliverable->final_designs = asset('storage/' . $path);
            }
            
            $deliverable->save();
            
            return $request->wantsJson() 
                ? response()->json(['success' => true, 'message' => 'Deliverable content saved successfully.'])
                : redirect()->back()->with('success', 'Deliverable content saved successfully.');
        }

        $result = $this->internallyAdvanceStage($deliverable, $request->all());

        if (!$result['success']) {
            return $request->wantsJson() 
                ? response()->json(['success' => false, 'message' => $result['message']], $result['code'] ?? 422)
                : redirect()->back()->with('error', $result['message']);
        }

        return $request->wantsJson() 
            ? response()->json(['success' => true, 'message' => $result['message']])
            : redirect()->back()->with('success', $result['message']);
    }

    /**
     * Advance the entire batch (parent + all subtasks) to the next workflow stage.
     */
    public function batchSubmit(Request $request, Deliverable $deliverable)
    {
        $batchData = $request->input('batch_data', []); // Array keyed by task ID
        
        // Ensure we have current subtasks
        $deliverable->load('subtasks');
        $subtasks = $deliverable->subtasks;
        $allTasks = collect([$deliverable])->concat($subtasks->all());
        
        $nextStage = $deliverable->getNextStage();
        
        if (!$nextStage) {
            return response()->json(['success' => false, 'message' => 'Batch is already at the final stage.'], 400);
        }

        // Shared Role Validation (check if parent's logic allows the transition)
        $dummy = clone $deliverable;
        $valResult = $this->internallyAdvanceStage($dummy, $request->all(), true); // Dry run
        if (!$valResult['success']) {
            return response()->json(['success' => false, 'message' => $valResult['message']], $valResult['code'] ?? 422);
        }

        try {
            \DB::beginTransaction();

            foreach ($allTasks as $task) {
                // Ensure task belongs to the same project context if needed
                $taskSpecificData = $batchData[$task->id] ?? [];
                $mergedData = array_merge($request->all(), $taskSpecificData);
                
                $result = $this->internallyAdvanceStage($task, $mergedData);
                if (!$result['success']) {
                    \DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Task #{$task->id} failed: " . $result['message']], 422);
                }
            }

            \DB::commit();
            return response()->json(['success' => true, 'message' => "Batch successfully submitted to {$nextStage} stage."]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error("Batch Submit Error: " . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'An internal error occurred during batch processing: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Centralized logic for advancing a deliverable stage.
     */
    private function internallyAdvanceStage(Deliverable $deliverable, array $data, $dryRun = false)
    {
        $stages = $deliverable->getStages();
        $nextStage = $deliverable->getNextStage();
        
        if (!$nextStage) {
            return ['success' => false, 'message' => 'Deliverable is already at the final stage.', 'code' => 400];
        }

        $requiredField = $deliverable->getRequiredFieldForStage($nextStage);
        if ($requiredField) {
            $assignedId = $data[$requiredField] ?? $deliverable->{$requiredField};
            if (!$assignedId && $deliverable->project) {
                $assignedId = $deliverable->project->{$requiredField};
            }
            
            if (!$assignedId) {
                $roleName = ucwords(str_replace(['_id', '_'], ['', ' '], $requiredField));
                return [
                    'success' => false, 
                    'message' => "Cannot move to **{$nextStage}**: Please assign a **{$roleName}** to this specific task first.", 
                    'code' => 422
                ];
            }
        }

        if ($dryRun) return ['success' => true];

        $oldStage = $deliverable->approval_stage ?? $stages[0];

        // Content updates
        if (isset($data['concept'])) $deliverable->concept = $data['concept'];
        if (isset($data['notes'])) $deliverable->notes = $data['notes'];
        if (isset($data['caption'])) $deliverable->caption = $data['caption'];
        if (isset($data['post_copy'])) $deliverable->post_copy = $data['post_copy'];
        if (isset($data['reference'])) $deliverable->reference = $data['reference'];
        if (isset($data['reference_file'])) $deliverable->reference_file = $data['reference_file'];

        // Stakeholder updates
        if (isset($data['approver_id'])) $deliverable->approver_id = $data['approver_id'];
        if (isset($data['brand_manager_id'])) $deliverable->brand_manager_id = $data['brand_manager_id'];
        if (isset($data['coordinator_id'])) $deliverable->coordinator_id = $data['coordinator_id'];
        if (isset($data['designer_id'])) $deliverable->designer_id = $data['designer_id'];

        // Designer Delivery
        if ($oldStage === 'Designer') {
            if (isset($data['final_designs'])) $deliverable->final_designs = $data['final_designs'];
            if (isset($data['final_designs_link'])) $deliverable->final_designs_link = $data['final_designs_link'];
            
            // Handle file upload if present in the data array
            if (isset($data['final_designs_file']) && $data['final_designs_file'] instanceof \Illuminate\Http\UploadedFile) {
                $path = $data['final_designs_file']->store('deliveries', 'public');
                $deliverable->final_designs = asset('storage/' . $path);
            }
        }

        // Advance stage skipping logic
        if ($oldStage === 'Writer' || $oldStage === 'Assignee') {
            $lastRevision = $deliverable->revisionsHistory()->latest()->first();
            if (is_object($lastRevision) && $lastRevision->stage_at_revision === 'Brand Manager') {
                if (in_array('Brand Manager', $stages)) {
                    $nextStage = 'Brand Manager';
                }
            }
        }

        $deliverable->approval_stage = $nextStage;
        $deliverable->progress_percent = $deliverable->getStageProgress();
        $deliverable->revision_instructions = null;
        $deliverable->status = ($nextStage === 'Closed' || $nextStage === 'closed') ? 'Done' : 'To Do';
        $deliverable->is_ready = false; // Reset for next stage logic
        $deliverable->save();

        // History
        $deliverable->approvalsHistory()->create(['user_id' => auth()->id(), 'stage' => $oldStage]);
        $deliverable->revisionsHistory()->whereNull('fixed_by_user_id')->latest()->first()?->update(['fixed_by_user_id' => auth()->id(), 'fixed_at' => now()]);

        // Notify
        $deliverable->notifyStageChange($oldStage, $nextStage, auth()->user());

        return ['success' => true, 'message' => "Deliverable submitted to {$nextStage} stage."];
    }

    /**
     * Move the deliverable back to the previous stage for revisions.
     */
    public function requestRevisions(Request $request, Deliverable $deliverable)
    {
        $stages = $deliverable->getStages();
        $firstStage = $stages[0]; 

        if ($deliverable->approval_stage !== $firstStage) {
            $validated = $request->validate([
                'revision_instructions' => 'required|string|max:1000',
            ]);

            $oldStage = $deliverable->approval_stage;
            \Illuminate\Support\Facades\Log::info("Deliverable {$deliverable->id} requesting revision from stage: '{$oldStage}'");
            
            // If we are in Final Approval, we send it back specifically to the Designer (if exists) or Assignee
            if ($oldStage === 'Final Approval') {
                if (in_array('Designer', $stages)) {
                    $deliverable->approval_stage = 'Designer';
                } else {
                    $deliverable->approval_stage = $firstStage;
                }
            } else {
                $deliverable->approval_stage = $firstStage;
            }
            
            // Revert status to "To Do" if moved back for revisions
            $deliverable->status = 'To Do';
            
            $deliverable->progress_percent = $deliverable->getStageProgress();
            $deliverable->revisions += 1;
            $deliverable->revision_instructions = $validated['revision_instructions'];
            $deliverable->save();

            // Record in history
            $deliverable->revisionsHistory()->create([
                'user_id' => auth()->id(),
                'instructions' => $validated['revision_instructions'],
                'stage_at_revision' => $oldStage,
            ]);

            // Notify writer
            if ($deliverable->writer) {
                $deliverable->writer->notify(new \App\Notifications\DeliverableUpdated(
                    $deliverable, 
                    "requested revisions at stage **{$oldStage}**", 
                    'revision_request', 
                    auth()->user()
                ));
            }

            return redirect()->back()->with('success', 'Revision requested successfully.');
        }
        return redirect()->back()->with('error', 'Cannot request revisions for this stage.');
    }

    /**
     * Batch request revisions for a deliverable and all its subtasks.
     */
    public function batchRevisions(Request $request, Deliverable $deliverable)
    {
        $validated = $request->validate([
            'revision_instructions' => 'required|string|max:2000',
        ]);

        $allTasks = collect([$deliverable])->merge($deliverable->subtasks);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($allTasks as $task) {
                $stages = $task->getStages();
                $firstStage = $stages[0];
                $oldStage = $task->approval_stage;

                // Revert logic similar to single requestRevisions
                if ($oldStage === 'Final Approval') {
                    $task->approval_stage = in_array('Designer', $stages) ? 'Designer' : $firstStage;
                } else {
                    $task->approval_stage = $firstStage;
                }

                $task->status = 'To Do';
                $task->progress_percent = $task->getStageProgress();
                $task->revisions += 1;
                $task->revision_instructions = $validated['revision_instructions'];
                $task->is_ready = false; 
                $task->save();

                // History
                $task->revisionsHistory()->create([
                    'user_id' => auth()->id(),
                    'instructions' => $validated['revision_instructions'],
                    'stage_at_revision' => $oldStage,
                ]);

                // Notify
                if ($task->writer) {
                    $task->writer->notify(new \App\Notifications\DeliverableUpdated(
                        $task, 
                        "requested revisions for batch **{$deliverable->title}**", 
                        'revision_request', 
                        auth()->user()
                    ));
                }
            }

            \Illuminate\Support\Facades\DB::commit();
            return response()->json(['success' => true, 'message' => 'Batch revisions requested successfully.']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error requesting batch revisions: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Deliverable $deliverable)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $deliverable->delete();
        return redirect()->back()->with('success', 'Deliverable deleted successfully.');
    }
}

