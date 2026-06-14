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
                $taskData['reference_file'] = $this->moveUploadedFile($request->file("subtasks.0.reference_file"), 'references');
            }

            $singleTask = Deliverable::create($taskData);
            return redirect()->route('projects.show', $singleTask->project_id)->with('success', 'Deliverable created.');
        }

        // Standard logic for 0 or 2+ subtasks, or adding to existing parent
        $parentData = \Illuminate\Support\Arr::except($validated, ['subtasks']);
        $parentData['priority'] = $parentData['priority'] ?? 'Medium';
        
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
                    $refFile = $this->moveUploadedFile($request->file("subtasks.{$index}.reference_file"), 'references');
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
                    'priority' => $sub['priority'] ?? ($parentTask->priority ?? 'Medium'),
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
        if (!$user->isAdmin() && $user->role !== 'Brand Manager' && $user->role !== 'Writer') abort(403);
        $projects = Project::all();
        $users = \App\Models\User::where('role', 'Writer')->get();
        $approvers = \App\Models\User::whereIn('role', ['Approver', 'Admin'])->get();
        $subtaskTypes = \App\Models\SubtaskType::all();
        return view('deliverables.edit', compact('deliverable', 'projects', 'users', 'approvers', 'subtaskTypes'));
    }

    public function update(Request $request, Deliverable $deliverable)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role !== 'Brand Manager' && $user->role !== 'Writer') abort(403);
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
            'writer_id' => 'nullable|exists:users,id',
            'approval_stage' => 'nullable|string',
            'final_designs' => 'nullable|string',
            'revisions' => 'nullable|integer',
            'is_ready' => 'nullable|boolean',
        ]);

        if ($request->has('writer_id')) {
            $writerId = $request->input('writer_id');
            $validated['writer_id'] = $writerId;
            if ($writerId) {
                $u = \App\Models\User::find($writerId);
                $validated['assignee_name'] = $u ? $u->name : 'Unassigned';
            } else {
                $validated['assignee_name'] = 'Unassigned';
            }
        }

        if ($request->boolean('delete_reference_file')) {
            $validated['reference_file'] = null;
        } elseif ($request->hasFile('reference_file')) {
            $validated['reference_file'] = $this->moveUploadedFile($request->file('reference_file'), 'references');
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
        if ($request->has('delete_final_designs')) {
            $user = auth()->user();
            $userRole = strtolower(str_replace(' ', '', $user->role));
            $isAssignedDesigner = $user->id == $deliverable->designer_id;
            $designerEditPermission = $isAssignedDesigner || ($userRole === 'designer' && !$deliverable->designer_id);
            
            if (!$user->isAdmin() && !($designerEditPermission && $deliverable->approval_stage === 'Designer')) {
                abort(403, 'Unauthorized action.');
            }
            
            if ($deliverable->final_designs) {
                $path = $deliverable->final_designs;
                if (str_starts_with($path, '/artwork/')) {
                    $fullPath = public_path(ltrim($path, '/'));
                    if (file_exists($fullPath)) @unlink($fullPath);
                }
                $deliverable->final_designs = null;
                $deliverable->save();
            }
            
            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => 'Artwork file removed.'])
                : redirect()->back()->with('success', 'Artwork file removed successfully.');
        }

        if ($request->has('delete_final_designs_link')) {
            $user = auth()->user();
            $userRole = strtolower(str_replace(' ', '', $user->role));
            $isAssignedDesigner = $user->id == $deliverable->designer_id;
            $designerEditPermission = $isAssignedDesigner || ($userRole === 'designer' && !$deliverable->designer_id);
            
            if (!$user->isAdmin() && !($designerEditPermission && $deliverable->approval_stage === 'Designer')) {
                abort(403, 'Unauthorized action.');
            }
            
            $deliverable->final_designs_link = null;
            $deliverable->save();
            
            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => 'Artwork link removed.'])
                : redirect()->back()->with('success', 'Artwork link removed successfully.');
        }

        if ($request->input('action') === 'save_only') {
            if ($request->has('concept')) $deliverable->concept = $request->concept;
            if ($request->has('notes')) $deliverable->notes = $request->notes;
            if ($request->has('caption')) $deliverable->caption = $request->caption;
            if ($request->has('post_copy')) $deliverable->post_copy = $request->post_copy;
            if ($request->has('reference')) $deliverable->reference = $request->reference;
            if ($request->has('final_designs_link')) $deliverable->final_designs_link = $request->final_designs_link;
            if ($request->has('work_hours')) $deliverable->work_hours = $request->work_hours ?: null;
            
            if ($request->hasFile('reference_file')) {
                $deliverable->reference_file = $this->moveUploadedFile($request->file('reference_file'), 'references');
            }

            if ($request->hasFile('final_designs_file')) {
                $deliverable->final_designs = $this->moveUploadedFile($request->file('final_designs_file'), 'artwork');
            }
            
            $deliverable->save();
            
            return $request->wantsJson() 
                ? response()->json(['success' => true, 'message' => 'Deliverable content saved successfully.'])
                : redirect()->back()->with('success', 'Deliverable content saved successfully.');
        }

        $result = $this->internallyAdvanceStage($deliverable, array_merge($request->all(), $request->allFiles()));

        if (!$result['success']) {
            return $request->wantsJson() 
                ? response()->json(['success' => false, 'message' => $result['message']], $result['code'] ?? 422)
                : redirect()->back()->with('error', $result['message']);
        }

        // If this is a subtask, automatically sync parent stage if all subtasks have advanced
        if ($deliverable->parent_deliverable_id) {
            $parent = $deliverable->parent;
            if ($parent) {
                $siblingSubtasks = $parent->subtasks()->get();
                $stages = $deliverable->getStages();
                $minStageIdx = null;

                foreach ($siblingSubtasks as $sub) {
                    $idx = array_search($sub->approval_stage, $stages);
                    if ($idx === false) continue;
                    if ($minStageIdx === null || $idx < $minStageIdx) {
                        $minStageIdx = $idx;
                    }
                }

                if ($minStageIdx !== null) {
                    $parentIdx = array_search($parent->approval_stage, $stages);
                    if ($parentIdx !== false && $minStageIdx > $parentIdx) {
                        $parent->approval_stage = $stages[$minStageIdx];
                        $parent->progress_percent = $parent->getStageProgress();
                        $parent->status = ($parent->approval_stage === 'Closed') ? 'Done' : 'To Do';
                        $parent->save();
                    }
                }
            }
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

        // Enforce: if one of the subtasks has been individually submitted (its stage is ahead of the parent's stage), block batch submit!
        $parentStage = $deliverable->approval_stage;
        $stages = $deliverable->getStages();
        $currIdx = array_search($parentStage, $stages);

        foreach ($subtasks as $subtask) {
            $subIdx = array_search($subtask->approval_stage, $stages);
            if ($subIdx !== false && $currIdx !== false && $subIdx > $currIdx) {
                return response()->json([
                    'success' => false,
                    'message' => 'This batch cannot be submitted because one or more subtasks have already been submitted individually.'
                ], 422);
            }
        }

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

            $parentStage = $deliverable->approval_stage;

            foreach ($allTasks as $task) {
                // Skip tasks that are not at the same stage as the parent (they have either moved ahead or are behind)
                if ($task->id !== $deliverable->id && $task->approval_stage !== $parentStage) {
                    continue;
                }

                // Ensure task belongs to the same project context if needed
                $taskSpecificData = $batchData[$task->id] ?? [];
                $mergedData = array_merge($request->all(), $taskSpecificData);
                
                $result = $this->internallyAdvanceStage($task, $mergedData);
                if (!$result['success']) {
                    \DB::rollBack();
                    return response()->json(['success' => false, 'message' => "Task #{$task->id} failed: " . $result['message']], 422);
                }
            }

            // Sync parent stage if a subtask was individually submitted
            if ($deliverable->parent_deliverable_id) {
                $parent = $deliverable->fresh()->parent;
                if ($parent) {
                    $siblingSubtasks = $parent->subtasks()->get();
                    $stageList = $deliverable->getStages();
                    $minStageIdx = null;

                    foreach ($siblingSubtasks as $sub) {
                        $idx = array_search($sub->approval_stage, $stageList);
                        if ($idx === false) continue;
                        if ($minStageIdx === null || $idx < $minStageIdx) {
                            $minStageIdx = $idx;
                        }
                    }

                    if ($minStageIdx !== null) {
                        $parentIdx = array_search($parent->approval_stage, $stageList);
                        if ($parentIdx !== false && $minStageIdx > $parentIdx) {
                            $parent->approval_stage = $stageList[$minStageIdx];
                            $parent->progress_percent = $parent->getStageProgress();
                            $parent->status = ($parent->approval_stage === 'Closed') ? 'Done' : 'To Do';
                            $parent->save();
                        }
                    }
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

    private function moveUploadedFile($file, string $folder): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($folder), $filename);
        return '/' . $folder . '/' . $filename;
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

        $oldStage = $deliverable->approval_stage ?? $stages[0];

        // Enforce: only the assigned person for the current stage (or admin) may submit
        $user = auth()->user();
        if ($user && !$user->isAdmin()) {
            $stageFieldMap = [
                'Writer'          => 'writer_id',
                'Assignee'        => 'writer_id',
                'Writer Review'   => 'writer_id',
                'Approver'        => 'approver_id',
                'Approver Review' => 'approver_id',
                'Brand Manager'   => 'brand_manager_id',
                'AM/BD'           => 'brand_manager_id',
                'Final Approval'  => 'brand_manager_id',
                'Coordinator'     => 'coordinator_id',
                'Designer'        => 'designer_id',
            ];
            $field     = $stageFieldMap[$oldStage] ?? null;
            $assignedId = $field ? $deliverable->{$field} : null;
            if ($assignedId && $user->id != $assignedId) {
                $stageLabel = $oldStage === 'AM/BD' ? 'AM/BD' : strtolower($oldStage);
                return [
                    'success' => false,
                    'message' => "Only the assigned {$stageLabel} can submit this deliverable.",
                    'code'    => 403,
                ];
            }
        }

        if ($oldStage === 'Designer') {
            $hasUpload = isset($data['final_designs_file']) && $data['final_designs_file'] instanceof \Illuminate\Http\UploadedFile;
            $hasDesigns = $deliverable->final_designs
                || $deliverable->final_designs_link
                || ($data['final_designs'] ?? null)
                || ($data['final_designs_link'] ?? null)
                || $hasUpload;

            if (!$hasDesigns) {
                return [
                    'success' => false,
                    'message' => 'Please upload the final artwork or provide an artwork link before submitting.',
                    'code' => 422
                ];
            }
        }

        if ($dryRun) return ['success' => true];

        // "Further Approval": re-assign to another reviewer and stay at the same stage
        if (in_array($oldStage, ['Approver', 'Brand Manager']) && !empty($data['further_approver_id'])) {
            $furtherApproverId = (int) $data['further_approver_id'];
            if ($oldStage === 'Approver') {
                $deliverable->approver_id = $furtherApproverId;
            } else {
                $deliverable->brand_manager_id = $furtherApproverId;
            }
            $deliverable->save();

            $deliverable->approvalsHistory()->create([
                'user_id' => auth()->id(),
                'stage'   => $oldStage,
                'notes'   => ($data['submit_notes'] ?? null),
            ]);

            $furtherApprover = \App\Models\User::find($furtherApproverId);
            if ($furtherApprover) {
                $furtherApprover->notify(new DeliverableUpdated(
                    $deliverable,
                    'sent **' . $deliverable->title . '** for your approval',
                    'stage_update',
                    auth()->user()
                ));
            }

            return ['success' => true, 'message' => 'Deliverable sent to ' . ($furtherApprover->name ?? 'further approver') . ' for additional approval.'];
        }

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
                $deliverable->final_designs = $this->moveUploadedFile($data['final_designs_file'], 'artwork');
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
        $deliverable->approvalsHistory()->create([
            'user_id' => auth()->id(),
            'stage'   => $oldStage,
            'notes'   => $data['submit_notes'] ?? null,
        ]);
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
            
            // Final Approval, Writer Review, and Approver Review all send back to Designer
            if (in_array($oldStage, ['Final Approval', 'Writer Review', 'Approver Review'])) {
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
                if (in_array($oldStage, ['Final Approval', 'Writer Review', 'Approver Review'])) {
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
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role !== 'Brand Manager' && $user->role !== 'Writer') {
            abort(403);
        }
        $deliverable->delete();
        return redirect()->back()->with('success', 'Deliverable deleted successfully.');
    }

    /**
     * Export Deliverable to PDF
     */
    public function exportPdf(Deliverable $deliverable)
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('deliverables.pdf', compact('deliverable'));
        return $pdf->download(str_replace(' ', '_', $deliverable->title) . '.pdf');
    }

    /**
     * Export Deliverable to DOCX
     */
    public function exportDocx(Deliverable $deliverable)
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $section->addText($deliverable->subtask_type ?? 'Standard', ['bold' => true, 'color' => '475569', 'size' => 10]);
        $section->addText($deliverable->title, ['name' => 'Helvetica', 'size' => 16, 'bold' => true, 'color' => '0055D4']);
        $section->addText('Stage: ' . $deliverable->approval_stage, ['bold' => true, 'color' => '4338ca', 'size' => 10]);
        $section->addTextBreak(1);

        if ($deliverable->revision_instructions) {
            $section->addText('REVISION REQUESTED', ['bold' => true, 'color' => 'ef4444']);
            $section->addText($deliverable->revision_instructions, ['color' => 'ef4444']);
            $section->addTextBreak(1);
        }

        if ($deliverable->notes) {
            $section->addText('MANAGER NOTES', ['bold' => true]);
            $section->addText($deliverable->notes);
            $section->addTextBreak(1);
        }

        if ($deliverable->concept) {
            $section->addText('CONCEPT', ['bold' => true]);
            $section->addText($deliverable->concept);
            $section->addTextBreak(1);
        }

        if ($deliverable->caption) {
            $section->addText('CAPTION', ['bold' => true]);
            $section->addText($deliverable->caption);
            $section->addTextBreak(1);
        }

        if ($deliverable->post_copy) {
            $section->addText('POST COPY', ['bold' => true]);
            $section->addText($deliverable->post_copy);
            $section->addTextBreak(1);
        }

        $section->addText('REFERENCE', ['bold' => true]);
        if ($deliverable->reference) {
            $section->addLink($deliverable->reference, $deliverable->reference);
        } elseif ($deliverable->reference_file) {
            $section->addLink($deliverable->reference_file, 'Attached File');
        } else {
            $section->addText('None', ['color' => '94a3b8']);
        }
        $section->addTextBreak(1);

        $section->addText('ARTWORK', ['bold' => true]);
        if ($deliverable->final_designs) {
            $section->addLink($deliverable->final_designs, 'Attached Artwork');
        } elseif ($deliverable->final_designs_link) {
            $section->addLink($deliverable->final_designs_link, $deliverable->final_designs_link);
        } else {
            $section->addText('Pending', ['color' => '94a3b8']);
        }
        $section->addTextBreak(1);

        $section->addText('TEAM', ['bold' => true]);
        $team = [
            'Writer' => $deliverable->writer->name ?? 'Unassigned',
            'Designer' => $deliverable->designer->name ?? 'Unassigned',
            'Approver' => $deliverable->approver->name ?? 'Unassigned',
            'Brand Manager' => $deliverable->brandManager->name ?? 'Unassigned',
        ];
        foreach ($team as $role => $name) {
            $section->addText($role . ': ' . $name);
        }

        $fileName = str_replace(' ', '_', $deliverable->title) . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'docx');
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Export Deliverable to PPTX
     */
    public function exportPpt(Deliverable $deliverable)
    {
        $prs = new \PhpOffice\PhpPresentation\PhpPresentation();
        $this->buildPptSlide($prs->getActiveSlide(), $deliverable);

        $fileName = str_replace(' ', '_', $deliverable->title) . '.pptx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'pptx');
        \PhpOffice\PhpPresentation\IOFactory::createWriter($prs, 'PowerPoint2007')->save($tmpFile);

        return response()->download($tmpFile, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Batch Export Deliverables to PDF
     */
    public function exportBatchPdf(Deliverable $deliverable)
    {
        $deliverables = $deliverable->subtasks->isNotEmpty() ? $deliverable->subtasks : collect([$deliverable]);
        $parent = $deliverable;
        
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('deliverables.batch_pdf', compact('deliverables', 'parent'));
        $fileName = str_replace(' ', '_', $deliverable->title) . '_batch.pdf';
        
        return $pdf->download($fileName);
    }

    /**
     * Batch Export Deliverables to PPTX
     */
    public function exportBatchPpt(Deliverable $deliverable)
    {
        $deliverables = $deliverable->subtasks->isNotEmpty()
            ? $deliverable->subtasks
            : collect([$deliverable]);

        $prs = new \PhpOffice\PhpPresentation\PhpPresentation();
        $prs->removeSlideByIndex(0);

        foreach ($deliverables as $task) {
            $this->buildPptSlide($prs->createSlide(), $task);
        }

        $fileName = str_replace(' ', '_', $deliverable->title) . '_batch.pptx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'pptx');
        \PhpOffice\PhpPresentation\IOFactory::createWriter($prs, 'PowerPoint2007')->save($tmpFile);

        return response()->download($tmpFile, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Resolve a stored image URL to an absolute local filesystem path.
     * Images are stored as asset('storage/...') URLs; this extracts the
     * relative part after /storage/ and maps it to storage/app/public/.
     */
    private function pptLocalImagePath(?string $url): ?string
    {
        if (!$url) return null;
        if (!preg_match('/\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i', $url)) return null;

        // New storage: /references/..., /artwork/..., /brand_logos/..., /briefs/...
        if (str_starts_with($url, '/') && !str_starts_with($url, '//')) {
            $abs = public_path(ltrim($url, '/'));
            if (file_exists($abs)) return $abs;
        }
        // Legacy storage: /storage/...
        if (preg_match('#/storage/(.+?)(\?.*)?$#i', $url, $m)) {
            $abs = storage_path('app/public/' . $m[1]);
            if (file_exists($abs)) return $abs;
        }
        // Absolute filesystem path fallback
        if (file_exists($url)) return $url;
        return null;
    }

    /**
     * Build a single professional deliverable slide.
     * Layout: blue header bar → left text column + right image column.
     */
    private function buildPptSlide(\PhpOffice\PhpPresentation\Slide $slide, $task): void
    {
        $color = fn(string $hex) => new \PhpOffice\PhpPresentation\Style\Color($hex);
        $Fill  = \PhpOffice\PhpPresentation\Style\Fill::class;
        $Border = \PhpOffice\PhpPresentation\Style\Border::class;

        $refPath   = $this->pptLocalImagePath($task->reference_file);
        $artPath   = $this->pptLocalImagePath($task->final_designs);
        $hasImages = $refPath || $artPath;

        // Slide canvas (px, 96dpi, default 4:3 = 960×720)
        $SW = 960; $SH = 720;
        $headerH = 72; $footerH = 18;
        $contentY = $headerH + 6;
        $contentH = $SH - $headerH - $footerH - 8;

        // Column widths
        $textX = 22;
        $textW = $hasImages ? 488 : ($SW - 44);
        $imgX  = 532;
        $imgW  = $SW - $imgX - 18;

        // ── Blue header background ──────────────────────────────
        $hdrBg = $slide->createRichTextShape()
            ->setHeight($headerH)->setWidth($SW)->setOffsetX(0)->setOffsetY(0);
        $hdrBg->getFill()->setFillType($Fill::FILL_SOLID)
              ->setStartColor($color('FF0055D4'));
        $hdrBg->getBorder()->setLineStyle($Border::LINE_NONE);

        // ── Header text ─────────────────────────────────────────
        $hdr = $slide->createRichTextShape()
            ->setHeight($headerH - 4)->setWidth($SW - 40)->setOffsetX(20)->setOffsetY(4);
        $hdr->getBorder()->setLineStyle($Border::LINE_NONE);

        $run = $hdr->createTextRun('[' . ($task->subtask_type ?? 'Standard') . ']  ');
        $run->getFont()->setSize(8)->setColor($color('FFBFDBFE'));

        $hdr->createBreak();
        $run = $hdr->createTextRun($task->title);
        $run->getFont()->setBold(true)->setSize(17)->setColor($color('FFFFFFFF'));

        $hdr->createBreak();
        $run = $hdr->createTextRun('● ' . ($task->approval_stage ?? ''));
        $run->getFont()->setSize(8)->setColor($color('FF93C5FD'));

        // ── Column divider ───────────────────────────────────────
        if ($hasImages) {
            $div = $slide->createRichTextShape()
                ->setHeight($contentH)->setWidth(1)->setOffsetX($imgX - 10)->setOffsetY($contentY);
            $div->getFill()->setFillType($Fill::FILL_SOLID)
                ->setStartColor($color('FFE2E8F0'));
            $div->getBorder()->setLineStyle($Border::LINE_NONE);
        }

        // ── Text sections (left column) ─────────────────────────
        $offsetY = $contentY;
        $maxBottom = $SH - $footerH - 6;

        $addSection = function(string $label, ?string $content) use (
            $slide, $textX, $textW, &$offsetY, $maxBottom, $color, $Fill, $Border
        ) {
            if (!$content || trim($content) === '') return;
            if ($offsetY >= $maxBottom) return;

            $isRevision = $label === 'REVISION REQUESTED';

            // Section label
            $lbl = $slide->createRichTextShape()
                ->setHeight(14)->setWidth($textW)->setOffsetX($textX)->setOffsetY($offsetY);
            $lbl->getBorder()->setLineStyle($Border::LINE_NONE);
            $lr = $lbl->createTextRun($label);
            $lr->getFont()->setBold(true)->setSize(7)
               ->setColor($color($isRevision ? 'FFEF4444' : 'FF94A3B8'));
            $offsetY += 14;

            // Content block
            $excerpt  = mb_strlen($content) > 350 ? mb_substr($content, 0, 347) . '…' : $content;
            $lines    = max(1, (int) ceil(mb_strlen($excerpt) / 65));
            $blockH   = min((int)($lines * 13) + 10, 110);
            $blockH   = min($blockH, $maxBottom - $offsetY);
            if ($blockH < 12) return;

            $blk = $slide->createRichTextShape()
                ->setHeight($blockH)->setWidth($textW)->setOffsetX($textX)->setOffsetY($offsetY);
            $blk->getFill()->setFillType($Fill::FILL_NONE);
            $blk->getBorder()->setLineStyle($Border::LINE_NONE);

            $run = $blk->createTextRun($excerpt);
            $run->getFont()->setSize(9)->setColor($color($isRevision ? 'FF991B1B' : 'FF334155'));
            $blk->getActiveParagraph()->getAlignment()->setMarginLeft(6)->setMarginTop(4);

            $offsetY += $blockH + 8;
        };

        if ($task->revision_instructions) {
            $addSection('REVISION REQUESTED', $task->revision_instructions);
        }
        $addSection('CONCEPT', $task->concept);
        $addSection('CAPTION', $task->caption);
        $addSection('COPY',    $task->post_copy ?: ($task->subtask_copy ?? null));
        $addSection('NOTES',   $task->notes);

        // ── Images (right column) ────────────────────────────────
        if ($hasImages) {
            $imgPairs  = array_filter([['REFERENCE', $refPath], ['ARTWORK', $artPath]],
                                      fn($p) => (bool) $p[1]);
            $totalImgs = count($imgPairs);
            $imgY      = $contentY;
            $slotH     = (int) ($contentH / $totalImgs);

            foreach ($imgPairs as [$label, $path]) {
                // Label
                $lbl = $slide->createRichTextShape()
                    ->setHeight(14)->setWidth($imgW)->setOffsetX($imgX)->setOffsetY($imgY);
                $lbl->getBorder()->setLineStyle($Border::LINE_NONE);
                $lr = $lbl->createTextRun($label);
                $lr->getFont()->setBold(true)->setSize(7)->setColor($color('FF94A3B8'));
                $imgY += 16;

                // Scale image to fit slot while preserving aspect ratio
                $maxH = $slotH - 22;
                $maxW = $imgW;
                [$origW, $origH] = @getimagesize($path) ?: [1, 1];
                if ($origW > 0 && $origH > 0) {
                    $ratio   = $origW / $origH;
                    $fitH    = min($maxH, (int)($maxW / $ratio));
                    $fitW    = min($maxW, (int)($fitH * $ratio));
                } else {
                    $fitW = $maxW;
                    $fitH = $maxH;
                }

                $drawing = new \PhpOffice\PhpPresentation\Shape\Drawing\File();
                $drawing->setName($label)->setPath($path)
                        ->setWidth($fitW)->setHeight($fitH)
                        ->setOffsetX($imgX)->setOffsetY($imgY);
                $slide->addShape($drawing);

                $imgY += $slotH;
            }
        }

        // ── Footer bar ───────────────────────────────────────────
        $ftrBg = $slide->createRichTextShape()
            ->setHeight($footerH)->setWidth($SW)->setOffsetX(0)->setOffsetY($SH - $footerH);
        $ftrBg->getFill()->setFillType($Fill::FILL_SOLID)->setStartColor($color('FFF8FAFC'));
        $ftrBg->getBorder()->setLineStyle($Border::LINE_NONE);

        $brand   = $task->project->brand->name ?? '';
        $project = $task->project->name ?? '';
        $ftr = $slide->createRichTextShape()
            ->setHeight($footerH)->setWidth($SW - 40)->setOffsetX(20)->setOffsetY($SH - $footerH);
        $ftr->getBorder()->setLineStyle($Border::LINE_NONE);
        $fr = $ftr->createTextRun(implode('  ·  ', array_filter(['Loops Work', $brand, $project])));
        $fr->getFont()->setSize(7)->setColor($color('FF94A3B8'));
    }
}

