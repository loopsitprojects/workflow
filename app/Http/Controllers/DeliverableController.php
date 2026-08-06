<?php

namespace App\Http\Controllers;

use App\Models\Deliverable;
use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

use App\Models\User;
use App\Models\DeliverableReassignment;
use App\Notifications\DeliverableUpdated;
use Illuminate\Support\Str;
use App\Http\Requests\StoreDeliverableRequest;

class DeliverableController extends Controller
{


    public function show(Deliverable $deliverable)
    {
        $deliverable->load([
            'project.brand', 'parent', 'writer', 'approver', 'brandManager', 
            'coordinator', 'designer', 'revisionsHistory.user', 
            'approvalsHistory.user', 'reassignments.fromUser', 
            'reassignments.toUser', 'reassignments.reassignedBy'
        ]);

        $userRole = str_replace(' ', '', strtolower(auth()->user()->role));
        $isAdmin = $userRole === 'admin';
        
        $brand = $deliverable->project->brand()->with('members')->first();
        $users = $brand ? $brand->members : collect();
        
        $approvers = $users->whereIn('role', ['Approver', 'Approver Coordinator']);
        $brandManagers = $users->where('role', 'Brand Manager');
        $coordinators = $users->whereIn('role', ['Coordinator', 'Approver Coordinator']);
        $designers = User::whereIn('role', ['Designer', 'Admin'])->get();
        
        $stages = ['Writer', 'Writer Review', 'Approver', 'Approver Review', 'Further Approver', 'Brand Manager', 'Coordinator', 'Designer', 'AM/BD', 'Final Approval'];

        // Get subtasks if it's a parent task
        if ($deliverable->task_type === 'Retainer' && $deliverable->post_type === 'Parent') {
            $deliverable->load('subtasks');
        }

        $deliverable->append(['subtask_type', 'subtask_copy', 'subtask_type_colors', 'associates', 'revisions_history', 'approvals_history', 'reassignments_history']);

        return view('deliverables.show', compact('deliverable', 'userRole', 'isAdmin', 'approvers', 'brandManagers', 'coordinators', 'designers', 'stages'));
    }

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

        // Auto-assign the logged-in user as writer if they have the Writer role
        $creator = auth()->user();
        if ($creator->role === 'Writer' && empty($validated['writer_id'])) {
            $validated['writer_id'] = $creator->id;
            $validated['assignee_name'] = $creator->name;
        }

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


    public function showBatch(Deliverable $deliverable)
    {
        if ($deliverable->parent_deliverable_id) abort(404);

        $deliverable->load([
            'project.brand',
            'writer', 'approver', 'brandManager', 'coordinator', 'designer',
            'subtasks' => function ($query) {
                $query->orderByRaw("CASE
                    WHEN priority = 'High Priority' THEN 1
                    WHEN priority = 'Medium' THEN 2
                    WHEN priority = 'Low Priority' THEN 3
                    ELSE 4 END")
                ->orderBy('deadline', 'asc');
            },
            'subtasks.writer', 'subtasks.approver', 'subtasks.brandManager',
            'subtasks.coordinator', 'subtasks.designer',
            'subtasks.revisionsHistory.user',
            'subtasks.approvalsHistory.user',
        ]);

        return view('deliverables.batch', compact('deliverable'));
    }

    public function addToBatch(Request $request, Deliverable $deliverable)
    {
        $user = auth()->user();
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && !in_array($user->role, ['Brand Manager', 'Writer'])) abort(403);
        if ($deliverable->parent_deliverable_id) abort(403); // must be a parent

        $project = $deliverable->project;
        $firstStage = in_array($project->workflow_type, ['campaign', 'pitch'])
            ? Deliverable::CAMPAIGN_STAGES[0]
            : Deliverable::STAGES[0];

        $postType = $request->input('post_type');
        if (empty($postType)) {
            $postType = $deliverable->post_type ?? $deliverable->title;
        }

        $title = $request->input('title');
        if (empty($title)) {
            $siblingCount = $deliverable->subtasks()->where('post_type', $postType)->count();
            $title = $postType . ' ' . ($siblingCount + 1);
        }

        Deliverable::create([
            'project_id'            => $deliverable->project_id,
            'parent_deliverable_id' => $deliverable->id,
            'title'                 => $title,
            'post_type'             => $postType,
            'status'                => 'To Do',
            'task_type'             => 'Deliverable',
            'approval_stage'        => $firstStage,
            'priority'              => $deliverable->priority ?? 'Medium',
            'progress_percent'      => 0,
            'revisions'             => 0,
            'deadline'              => $deliverable->deadline,
            'writer_id'             => $deliverable->writer_id,
            'approver_id'           => $deliverable->approver_id,
            'brand_manager_id'      => $deliverable->brand_manager_id,
            'coordinator_id'        => $deliverable->coordinator_id,
            'designer_id'           => $deliverable->designer_id,
            'assignee_name'         => $deliverable->writer?->name ?? 'Unassigned',
        ]);

        return redirect()->route('projects.show', $deliverable->project_id)->with('success', 'Post added to batch.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Deliverable $deliverable)
    {
        $user = auth()->user();
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && $user->role !== 'Brand Manager' && $user->role !== 'Writer') abort(403);
        $projects = Project::all();
        $users = \App\Models\User::where('role', 'Writer')->get();
        $approvers = \App\Models\User::whereIn('role', ['Approver', 'Approver Coordinator', 'Admin'])->get();
        $subtaskTypes = \App\Models\SubtaskType::all();
        return view('deliverables.edit', compact('deliverable', 'projects', 'users', 'approvers', 'subtaskTypes'));
    }

    public function update(Request $request, Deliverable $deliverable)
    {
        $user = auth()->user();
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && $user->role !== 'Brand Manager' && $user->role !== 'Writer') abort(403);
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
            'designer_deadline' => 'nullable|date',
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

    public function updatePriority(Request $request, Deliverable $deliverable)
    {
        $user = auth()->user();
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && !in_array($user->role, ['Brand Manager', 'Writer', 'Approver', 'Approver Coordinator', 'Coordinator'])) abort(403);

        $validated = $request->validate([
            'priority' => 'required|string|in:High Priority,Medium,Low Priority'
        ]);

        $deliverable->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Priority updated successfully.']);
        }
        return back()->with('success', 'Priority updated.');
    }

    public function updateClientStatus(Request $request, Deliverable $deliverable)
    {
        $user = auth()->user();
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && $user->role !== 'Brand Manager') abort(403);

        $validated = $request->validate([
            'client_status' => 'nullable|string|in:Not Sent,Sent,Sent to Client,Waiting for Feedback,Client Approved,Client Revisions'
        ]);

        $deliverable->update(['client_status' => $validated['client_status'] === 'Not Sent' ? null : $validated['client_status']]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Client status updated successfully.']);
        }

        return redirect()->back()->with('success', 'Priority updated.');
    }

    public function reassignDesigner(Request $request, Deliverable $deliverable)
    {
        $user = auth()->user();
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && !in_array($user->role, ['Brand Manager', 'Coordinator', 'Approver Coordinator'])) {
            abort(403);
        }

        $validated = $request->validate([
            'designer_id' => 'required|exists:users,id',
            'reason' => 'nullable|string|max:500',
        ]);

        $oldDesignerId = $deliverable->designer_id;
        $newDesignerId = $validated['designer_id'];

        if ($oldDesignerId == $newDesignerId) {
            return response()->json(['success' => false, 'message' => 'Same designer selected.'], 422);
        }

        // Log the reassignment
        DeliverableReassignment::create([
            'deliverable_id' => $deliverable->id,
            'role' => 'designer',
            'from_user_id' => $oldDesignerId,
            'to_user_id' => $newDesignerId,
            'reassigned_by_user_id' => $user->id,
            'reason' => $validated['reason'] ?? null,
        ]);

        // Update the deliverable
        $deliverable->update(['designer_id' => $newDesignerId]);

        // Notify the new designer
        $newDesigner = User::find($newDesignerId);
        if ($newDesigner) {
            $newDesigner->notify(new DeliverableUpdated(
                $deliverable,
                "reassigned the deliverable to you (Designer)",
                'reassignment',
                $user
            ));
        }

        $fromName = $oldDesignerId ? User::find($oldDesignerId)?->name : 'Unassigned';
        $toName = $newDesigner?->name ?? 'Unknown';

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Designer reassigned from {$fromName} to {$toName}.",
                'new_designer_name' => $toName,
            ]);
        }

        return redirect()->back()->with('success', 'Designer reassigned successfully.');
    }

    /**
     * Generate a presigned URL for direct S3 upload.
     */
    public function generatePresignedUrl(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
            'folder' => 'required|string|in:artwork,references,briefs,brand_logos,revision_images'
        ]);

        $originalName = pathinfo($request->filename, PATHINFO_FILENAME);
        $safeName = \Illuminate\Support\Str::slug($originalName);
        $extension = pathinfo($request->filename, PATHINFO_EXTENSION);
        $filename = date('Y-m-d') . '_' . $safeName . '.' . $extension;
        
        $path = $request->folder . '/' . $filename;
        
        $client = \Illuminate\Support\Facades\Storage::disk('s3')->getClient();
        
        $command = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key'    => $path,
            'ContentType' => $request->content_type ?? 'application/octet-stream',
        ]);
        
        $presignedRequest = $client->createPresignedRequest($command, '+60 minutes');
        
        return response()->json([
            'url' => (string) $presignedRequest->getUri(),
            'path' => $path,
            'full_url' => \Illuminate\Support\Facades\Storage::disk('s3')->url($path)
        ]);
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
            
            if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && !($designerEditPermission && $deliverable->approval_stage === 'Designer')) {
                abort(403, 'Unauthorized action.');
            }
            
            if ($deliverable->final_designs) {
                $path = $deliverable->final_designs;
                if (preg_match('#/(artwork|references|briefs|brand_logos|revision_images)/([^/?]+)(?:\?.*)?$#', $path, $m)) {
                    \Illuminate\Support\Facades\Storage::disk('s3')->delete($m[1] . '/' . $m[2]);
                } else if (str_starts_with($path, '/artwork/')) {
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
            
            if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && !($designerEditPermission && $deliverable->approval_stage === 'Designer')) {
                abort(403, 'Unauthorized action.');
            }
            
            $deliverable->final_designs_link = null;
            $deliverable->save();
            
            return $request->wantsJson()
                ? response()->json(['success' => true, 'message' => 'Artwork link removed.'])
                : redirect()->back()->with('success', 'Artwork link removed successfully.');
        }

        if ($request->input('action') === 'save_only') {
            $user = auth()->user();
            $userRole = strtolower(str_replace(' ', '', $user->role));

            $isWriterStage = in_array($deliverable->approval_stage, ['Writer', 'Assignee', 'Writer Review']);
            $hasWriterRole = in_array($userRole, ['writer', 'assignee']);
            $isAssignedWriter = ($deliverable->writer_id && $user->id == $deliverable->writer_id);
            $isUnassignedWriter = (!$deliverable->writer_id && $hasWriterRole);
            
            $canEditContent = ($user->isAdmin() || $user->role === 'Operations Manager') || ($isWriterStage && ($isAssignedWriter || $isUnassignedWriter));

            if ($canEditContent) {
                if ($request->has('title')) $deliverable->title = $request->title;
                if ($request->has('concept')) $deliverable->concept = $request->concept;
                if ($request->has('notes')) $deliverable->notes = $request->notes;
                if ($request->has('caption')) $deliverable->caption = $request->caption;
                if ($request->has('post_copy')) $deliverable->post_copy = $request->post_copy;
                if ($request->has('reference')) $deliverable->reference = $request->reference;
                if ($request->has('final_designs_link')) $deliverable->final_designs_link = $request->final_designs_link;
            }
            if ($request->has('work_hours')) {
                $newWorkHours = $request->work_hours ?: null;
                $oldWorkHours = $deliverable->work_hours;
                
                if ($newWorkHours != $oldWorkHours) {
                    $isDesigner = $userRole === 'designer';
                    $isAssignedDesigner = ($deliverable->designer_id && $user->id == $deliverable->designer_id);
                    $isUnassignedDesigner = (!$deliverable->designer_id && $isDesigner);
                    if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && !($isDesigner && ($isAssignedDesigner || $isUnassignedDesigner))) {
                        abort(403, 'Unauthorized action: only the designer can edit work hours.');
                    }
                    $deliverable->work_hours = $newWorkHours;
                }
            }
            if ($request->has('designer_deadline')) $deliverable->designer_deadline = $request->designer_deadline ?: null;
            if ($request->has('deadline')) {
                $newDeadline = $request->deadline ?: null;
                $oldDeadline = $deliverable->deadline ? \Carbon\Carbon::parse($deliverable->deadline)->format('Y-m-d') : null;
                
                if ($newDeadline !== $oldDeadline) {
                    $isBrandManagerOrAdmin = ($user->isAdmin() || $user->role === 'Operations Manager') || $userRole === 'brandmanager';
                    if (!$isBrandManagerOrAdmin) {
                        abort(403, 'Unauthorized action: only Brand Managers or Admins can edit the deadline.');
                    }
                    $deliverable->deadline = $newDeadline;
                }
            }
            
            if ($request->boolean('delete_reference_file')) {
                if ($deliverable->reference_file) {
                    $path = $deliverable->reference_file;
                    if (preg_match('#/(artwork|references|briefs|brand_logos|revision_images)/([^/?]+)(?:\?.*)?$#', $path, $m)) {
                        \Illuminate\Support\Facades\Storage::disk('s3')->delete($m[1] . '/' . $m[2]);
                    } else if (str_starts_with($path, '/references/')) {
                        $fullPath = public_path(ltrim($path, '/'));
                        if (file_exists($fullPath)) @unlink($fullPath);
                    }
                }
                $deliverable->reference_file = null;
            } elseif ($request->has('reference_file') && is_string($request->reference_file)) {
                $deliverable->reference_file = \Illuminate\Support\Facades\Storage::disk('s3')->url(ltrim($request->reference_file, '/'));
            } elseif ($request->hasFile('reference_file')) {
                $deliverable->reference_file = $this->moveUploadedFile($request->file('reference_file'), 'references');
            }
            
            if ($request->has('final_designs_file') && is_string($request->final_designs_file)) {
                $deliverable->final_designs = \Illuminate\Support\Facades\Storage::disk('s3')->url(ltrim($request->final_designs_file, '/'));
            } elseif ($request->hasFile('final_designs_file')) {
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
        // Support both JSON (application/json) and FormData (multipart/form-data) submissions
        $rawBatchData = $request->input('batch_data', null);
        $batchData = is_string($rawBatchData) ? (json_decode($rawBatchData, true) ?? []) : ($rawBatchData ?: []);
        
        // Ensure we have current subtasks
        $deliverable->load('subtasks');
        $subtasks = $deliverable->subtasks;

        // Enforce: all subtasks must be at the same stage as the parent before batch submit is allowed
        $parentStage = $deliverable->approval_stage;
        $stages = $deliverable->getStages();
        $currIdx = array_search($parentStage, $stages);

        $parentStageNorm = $parentStage ?: $stages[0];
        foreach ($subtasks as $subtask) {
            $subStage = $subtask->approval_stage ?: $stages[0];
            if ($subStage !== $parentStageNorm) {
                return response()->json([
                    'success' => false,
                    'message' => 'All subtasks must be at the same stage before a batch action can be performed.'
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

                // Handle per-task reference image uploads (FormData submissions)
                if ($request->hasFile("reference_files.{$task->id}")) {
                    $mergedData['reference_file'] = $this->moveUploadedFile(
                        $request->file("reference_files.{$task->id}"),
                        'references'
                    );
                }

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
        if (!$file->isValid()) {
            throw new \Exception("File upload failed: " . $file->getErrorMessage());
        }
        
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeName = \Illuminate\Support\Str::slug($originalName);
        $filename = date('Y-m-d') . '_' . $safeName . '.' . $file->getClientOriginalExtension();
        
        try {
            $path = \Illuminate\Support\Facades\Storage::disk('s3')->putFileAs($folder, $file, $filename);
        } catch (\Throwable $e) {
            throw new \Exception("S3 Upload Exception: " . $e->getMessage());
        }

        if ($path === false) {
            throw new \Exception("Failed to upload file to S3. Please verify your AWS credentials and bucket permissions.");
        }
        return \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
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

        $oldStage = $deliverable->approval_stage ?? $stages[0];

        // Route Approver → Further Approver stage when a further approver is selected.
        // This creates a real intermediate stage in the workflow timeline.
        $routingToFurtherApprover = ($oldStage === 'Approver' && !empty($data['further_approver_id']));
        if ($routingToFurtherApprover) {
            $nextStage = 'Further Approver';
        } elseif ($nextStage === 'Further Approver') {
            // Skip 'Further Approver' when no further approver is being assigned
            $nextStage = 'Brand Manager';
        }

        // Brand Manager further approver: re-assign and stay at same stage (no new stage)
        $hasFurtherApprover = !empty($data['further_approver_id']) && in_array($oldStage, ['Brand Manager', 'AM/BD', 'Final Approval']);

        $requiredField = $deliverable->getRequiredFieldForStage($nextStage);
        if ($requiredField && !$hasFurtherApprover) {
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

        // Enforce: only the assigned person for the current stage (or admin) may submit
        $user = auth()->user();
        if ($user && (!$user->isAdmin() && $user->role !== 'Operations Manager')) {
            $stageFieldMap = [
                'Writer'           => 'writer_id',
                'Assignee'         => 'writer_id',
                'Writer Review'    => 'writer_id',
                'Approver'         => 'approver_id',
                'Approver Review'  => 'approver_id',
                'Further Approver' => 'further_approver_id',
                'Brand Manager'    => 'brand_manager_id',
                'AM/BD'            => 'brand_manager_id',
                'Final Approval'   => 'brand_manager_id',
                'Coordinator'      => 'coordinator_id',
                'Designer'         => 'designer_id',
                'Scheduled'        => 'writer_id',
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

        $hoursSpent = isset($data['hours_spent']) && is_numeric($data['hours_spent']) && $data['hours_spent'] > 0
            ? (float) $data['hours_spent'] : null;

        if ($dryRun) return ['success' => true];

        // Brand Manager "Further Approval": re-assign brand manager and stay at the same stage
        if (in_array($oldStage, ['Brand Manager', 'AM/BD', 'Final Approval']) && !empty($data['further_approver_id'])) {
            $furtherApproverId = (int) $data['further_approver_id'];
            $deliverable->brand_manager_id = $furtherApproverId;
            if ($hoursSpent) {
                $deliverable->work_hours = ($deliverable->work_hours ?? 0) + $hoursSpent;
            }
            $deliverable->save();

            $bmApprovalData = ['user_id' => auth()->id(), 'stage' => $oldStage, 'notes' => ($data['submit_notes'] ?? null)];
            if ($hoursSpent) $bmApprovalData['hours_spent'] = $hoursSpent;
            $deliverable->approvalsHistory()->create($bmApprovalData);

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

        // Record who performed the current stage (if the FK isn't already set)
        $currentStageField = $deliverable->getRequiredFieldForStage($oldStage);
        if ($currentStageField && !$deliverable->{$currentStageField}) {
            $deliverable->{$currentStageField} = auth()->id();
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
        // When routing to Further Approver stage, save the further approver ID
        if ($routingToFurtherApprover) {
            $deliverable->further_approver_id = (int) $data['further_approver_id'];
        }
        if (isset($data['brand_manager_id'])) $deliverable->brand_manager_id = $data['brand_manager_id'];
        if (isset($data['coordinator_id'])) $deliverable->coordinator_id = $data['coordinator_id'];
        if (isset($data['designer_id'])) $deliverable->designer_id = $data['designer_id'];

        // Coordinator sets an internal deadline for the designer when handing off (optional)
        if ($oldStage === 'Coordinator') {
            $deliverable->designer_deadline = !empty($data['designer_deadline']) ? $data['designer_deadline'] : null;
        }

        // Designer Delivery
        if ($oldStage === 'Designer') {
            if (isset($data['final_designs'])) $deliverable->final_designs = $data['final_designs'];
            if (isset($data['final_designs_link'])) $deliverable->final_designs_link = $data['final_designs_link'];
            
            // Handle file upload if present in the data array
            if (isset($data['final_designs_file'])) {
                if (is_string($data['final_designs_file'])) {
                    $deliverable->final_designs = \Illuminate\Support\Facades\Storage::disk('s3')->url(ltrim($data['final_designs_file'], '/'));
                } elseif ($data['final_designs_file'] instanceof \Illuminate\Http\UploadedFile) {
                    $deliverable->final_designs = $this->moveUploadedFile($data['final_designs_file'], 'artwork');
                }
            }
        }

        // Reset client_status when advancing past the first Brand Manager stage
        if (in_array($oldStage, ['Brand Manager', 'AM/BD']) && !in_array($nextStage, ['Brand Manager', 'AM/BD', 'Further Approver'])) {
            $deliverable->client_status = null;
        }

        $deliverable->approval_stage = $nextStage;
        $deliverable->progress_percent = $deliverable->getStageProgress();
        $deliverable->revision_instructions = null;
        $deliverable->status = ($nextStage === 'Closed' || $nextStage === 'closed') ? 'Done' : 'To Do';
        $deliverable->is_ready = false;
        if ($hoursSpent) {
            $deliverable->work_hours = ($deliverable->work_hours ?? 0) + $hoursSpent;
        }
        $deliverable->save();

        // History
        $approvalData = ['user_id' => auth()->id(), 'stage' => $oldStage, 'notes' => $data['submit_notes'] ?? null];
        if ($hoursSpent) $approvalData['hours_spent'] = $hoursSpent;
        $deliverable->approvalsHistory()->create($approvalData);
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
                'revision_target'       => 'nullable|in:writer,designer',
                'revision_image'        => 'nullable', // allow string or file
            ]);

            $oldStage = $deliverable->approval_stage;
            \Illuminate\Support\Facades\Log::info("Deliverable {$deliverable->id} requesting revision from stage: '{$oldStage}'");

            if (in_array($oldStage, ['Final Approval', 'Writer Review', 'Approver Review'])) {
                $target = $validated['revision_target'] ?? 'designer';
                if ($target === 'writer' || !in_array('Designer', $stages)) {
                    $deliverable->approval_stage = $firstStage;
                } else {
                    $deliverable->approval_stage = 'Designer';
                }
            } else {
                $deliverable->approval_stage = $firstStage;
            }

            // Reset approver so the submitter can pick a fresh one on resubmission
            if ($deliverable->approval_stage === $firstStage) {
                $deliverable->approver_id = null;
            }

            // Revert status to "To Do" if moved back for revisions
            $deliverable->status = 'To Do';

            $deliverable->progress_percent = $deliverable->getStageProgress();
            $deliverable->revisions += 1;
            $deliverable->revision_instructions = $validated['revision_instructions'];
            $deliverable->save();

            // Handle optional image upload
            $imagePath = null;
            if ($request->has('revision_image') && is_string($request->revision_image)) {
                $imagePath = \Illuminate\Support\Facades\Storage::disk('s3')->url(ltrim($request->revision_image, '/'));
            } elseif ($request->hasFile('revision_image')) {
                $file = $request->file('revision_image');
                $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs('revision_images', $filename, 's3');
                $imagePath = \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
            }

            // Record in history
            $deliverable->revisionsHistory()->create([
                'user_id' => auth()->id(),
                'instructions' => $validated['revision_instructions'],
                'image_path' => $imagePath,
                'stage_at_revision' => $oldStage,
            ]);

            // Notify the person responsible for the target stage
            $notifyTarget = $deliverable->approval_stage === 'Designer'
                ? ($deliverable->designer ?? $deliverable->project?->designer)
                : ($deliverable->writer ?? $deliverable->project?->writer);
            if ($notifyTarget) {
                $notifyTarget->notify(new \App\Notifications\DeliverableUpdated(
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
            'revision_target'       => 'nullable|in:writer,designer',
            'revision_image'        => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm|max:512000',
        ]);

        // Handle optional image upload once for the whole batch
        $imagePath = null;
        if ($request->hasFile('revision_image')) {
            $file = $request->file('revision_image');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('revision_images', $filename, 's3');
            $imagePath = \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
        }

        // Enforce: all subtasks must be at the same stage before batch revision
        $deliverable->load('subtasks');
        $batchStages = $deliverable->getStages();
        $batchParentStage = $deliverable->approval_stage ?: $batchStages[0];
        foreach ($deliverable->subtasks as $subtask) {
            $subStage = $subtask->approval_stage ?: $batchStages[0];
            if ($subStage !== $batchParentStage) {
                return response()->json([
                    'success' => false,
                    'message' => 'All subtasks must be at the same stage before a batch revision can be requested.'
                ], 422);
            }
        }

        $revisionTarget = $validated['revision_target'] ?? 'designer';
        $allTasks = collect([$deliverable])->merge($deliverable->subtasks);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            foreach ($allTasks as $task) {
                $stages = $task->getStages();
                $firstStage = $stages[0];
                $oldStage = $task->approval_stage;

                if (in_array($oldStage, ['Final Approval', 'Writer Review', 'Approver Review'])) {
                    if ($revisionTarget === 'writer' || !in_array('Designer', $stages)) {
                        $task->approval_stage = $firstStage;
                    } else {
                        $task->approval_stage = 'Designer';
                    }
                } else {
                    $task->approval_stage = $firstStage;
                }

                // Reset approver so the submitter can pick a fresh one on resubmission
                if ($task->approval_stage === $firstStage) {
                    $task->approver_id = null;
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
                    'image_path' => $imagePath,
                    'stage_at_revision' => $oldStage,
                ]);

                // Notify the person responsible for the target stage
                $notifyTarget = $task->approval_stage === 'Designer'
                    ? ($task->designer ?? $task->project?->designer)
                    : ($task->writer ?? $task->project?->writer);
                if ($notifyTarget) {
                    $notifyTarget->notify(new \App\Notifications\DeliverableUpdated(
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
    public function destroy(Request $request, Deliverable $deliverable)
    {
        $user = auth()->user();
        $isCreatorWriter = $user->role === 'Writer' && $deliverable->writer_id === $user->id;

        if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && $user->role !== 'Brand Manager' && !$isCreatorWriter) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => 'Only Admins, Brand Managers, and the assigned Writer can delete deliverables.'], 403);
            }
            abort(403);
        }
        $deliverable->delete();
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }
        return redirect()->back()->with('success', 'Deliverable deleted successfully.');
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
     * Batch Export Deliverables to PPTX
     */
    public function exportBatchPpt(Deliverable $deliverable)
    {
        $deliverable->load(['subtasks.project.brand', 'subtasks.writer', 'subtasks.approver', 'subtasks.brandManager', 'subtasks.coordinator', 'subtasks.designer']);

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
     * Batch Export Deliverables to PDF
     */
    public function exportBatchPdf(Deliverable $deliverable)
    {
        $deliverable->load(['subtasks.project.brand', 'subtasks.writer', 'subtasks.approver', 'subtasks.brandManager', 'subtasks.coordinator', 'subtasks.designer']);

        $deliverables = $deliverable->subtasks->isNotEmpty()
            ? $deliverable->subtasks
            : collect([$deliverable]);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('deliverables.batch_pdf', [
            'deliverables' => $deliverables,
            'parent' => $deliverable
        ]);

        $fileName = str_replace(' ', '_', $deliverable->title) . '_batch.pdf';
        return $pdf->download($fileName);
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

        if (str_starts_with($url, 'http')) {
            $content = @file_get_contents($url);
            if ($content) {
                $ext = preg_match('/\.([a-z0-9]+)(?:[\?#]|$)/i', $url, $m) ? $m[1] : 'png';
                $tmpFile = tempnam(sys_get_temp_dir(), 'pptimg_') . '.' . $ext;
                file_put_contents($tmpFile, $content);
                return $tmpFile;
            }
            return null;
        }

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
        
        // For client presentation, prioritize final artwork. Fallback to reference if no artwork exists.
        $primaryImgPath = $artPath ?: $refPath;
        $hasImages = (bool) $primaryImgPath;

        // Slide canvas (px, 96dpi, default 4:3 = 960×720)
        $SW = 960; $SH = 720;
        $headerH = 72; $footerH = 22;
        $contentY = $headerH + 16;
        $contentH = $SH - $headerH - $footerH - 24;

        // Column widths - Text on left, Image on right
        $textX = 24;
        $textW = $hasImages ? 450 : ($SW - 48);

        $imgX  = $hasImages ? ($textX + $textW + 32) : 0;
        $imgW  = $hasImages ? ($SW - $imgX - 24) : 0;

        // ── 1. Slide canvas background ───────────────────────────
        $slideBg = $slide->createRichTextShape()
            ->setHeight($SH)->setWidth($SW)->setOffsetX(0)->setOffsetY(0);
        $slideBg->getFill()->setFillType($Fill::FILL_SOLID)->setStartColor($color('FFF8FAFC'));
        $slideBg->getBorder()->setLineStyle($Border::LINE_NONE);
        $slideBg->createTextRun('')->getFont()->setSize(1)->setColor($color('FFF8FAFC'));

        // ── 2. White header background ───────────────────────────
        $hdrBg = $slide->createRichTextShape()
            ->setHeight($headerH)->setWidth($SW)->setOffsetX(0)->setOffsetY(0);
        $hdrBg->getFill()->setFillType($Fill::FILL_SOLID)
              ->setStartColor($color('FFFFFFFF'));
        $hdrBg->getBorder()->setLineStyle($Border::LINE_NONE);
        $hdrBg->createTextRun('')->getFont()->setSize(1)->setColor($color('FFFFFFFF'));

        // Bottom border line for header
        $hdrLine = $slide->createRichTextShape()
            ->setHeight(1)->setWidth($SW)->setOffsetX(0)->setOffsetY($headerH - 1);
        $hdrLine->getFill()->setFillType($Fill::FILL_SOLID)
                ->setStartColor($color('FFE2E8F0'));
        $hdrLine->getBorder()->setLineStyle($Border::LINE_NONE);
        $hdrLine->createTextRun('')->getFont()->setSize(1)->setColor($color('FFE2E8F0'));

        // ── 3. Loops Logo (Top-Right of Header) ──────────────────
        $logoPath = public_path('LoopsBlack.png');
        if (file_exists($logoPath)) {
            $logoH = 44;
            $logoW = 120; // max width
            [$origLogoW, $origLogoH] = @getimagesize($logoPath) ?: [1, 1];
            if ($origLogoW > 0 && $origLogoH > 0) {
                $logoRatio = $origLogoW / $origLogoH;
                $logoFitH = min($logoH, (int)($logoW / $logoRatio));
                $logoFitW = min($logoW, (int)($logoFitH * $logoRatio));
            } else {
                $logoFitW = $logoW;
                $logoFitH = $logoH;
            }
            
            $logoX = $SW - $logoFitW - 24;
            $logoY = (int) (($headerH - $logoFitH) / 2);
            
            $logoDrawing = new \PhpOffice\PhpPresentation\Shape\Drawing\File();
            $logoDrawing->setName('Loops Logo')
                        ->setPath($logoPath)
                        ->setWidth($logoFitW)
                        ->setHeight($logoFitH)
                        ->setOffsetX($logoX)
                        ->setOffsetY($logoY);
            $slide->addShape($logoDrawing);
        }

        // ── 4. Header text (dark slate colors) ───────────────────
        $hdr = $slide->createRichTextShape()
            ->setHeight($headerH - 8)->setWidth($SW - 200)->setOffsetX(24)->setOffsetY(20);
        $hdr->getBorder()->setLineStyle($Border::LINE_NONE);

        $run = $hdr->createTextRun($task->title);
        $run->getFont()->setName('Poppins')->setBold(true)->setSize(24)->setColor($color('FF0F172A'));

        // ── 5. Column divider ────────────────────────────────────
        if ($hasImages) {
            $div = $slide->createRichTextShape()
                ->setHeight($contentH)->setWidth(1)->setOffsetX($textX + $textW + 16)->setOffsetY($contentY);
            $div->getFill()->setFillType($Fill::FILL_SOLID)
                ->setStartColor($color('FFE2E8F0'));
            $div->getBorder()->setLineStyle($Border::LINE_NONE);
            $div->createTextRun('')->getFont()->setSize(1)->setColor($color('FFE2E8F0'));
        }

        // ── 6. Image Section (Right Column) ─────────────────────────────
        if ($hasImages) {
            $imgLabelText = $artPath ? 'FINAL ARTWORK' : 'REFERENCE IMAGE';
            
            $imgLabelY = $contentY;
            $lbl = $slide->createRichTextShape()
                ->setHeight(18)->setWidth($imgW)->setOffsetX($imgX)->setOffsetY($imgLabelY);
            $lbl->getBorder()->setLineStyle($Border::LINE_NONE);
            $lr = $lbl->createTextRun($imgLabelText);
            $lr->getFont()->setName('Poppins')->setBold(true)->setSize(11)->setColor($color('FF94A3B8'));
            
            $imgOffsetYOffset = 22;
            // Scale image to fit slot while preserving aspect ratio, centered in its box
            $maxH = $contentH - $imgOffsetYOffset;
            $maxW = $imgW;
            [$origW, $origH] = @getimagesize($primaryImgPath) ?: [1, 1];
            if ($origW > 0 && $origH > 0) {
                $ratio   = $origW / $origH;
                if (($maxW / $ratio) <= $maxH) {
                    $fitW = $maxW;
                    $fitH = (int)($maxW / $ratio);
                } else {
                    $fitH = $maxH;
                    $fitW = (int)($fitH * $ratio);
                }
            } else {
                $fitW = $maxW;
                $fitH = $maxH;
            }

            // Center image in the right column, below the label
            $imgOffsetX = $imgX + (int)(($maxW - $fitW) / 2);
            $imgOffsetY = $contentY + $imgOffsetYOffset + (int)(($maxH - $fitH) / 2);

            $drawing = new \PhpOffice\PhpPresentation\Shape\Drawing\File();
            $drawing->setName('Artwork')->setPath($primaryImgPath)
                    ->setWidth($fitW)->setHeight($fitH)
                    ->setOffsetX($imgOffsetX)->setOffsetY($imgOffsetY);
            $slide->addShape($drawing);
        }

        // ── 7. Text sections (Left column) with clean cards ──────
        $offsetY = $contentY;
        $maxBottom = $SH - $footerH - 12;

        $addSection = function(string $label, ?string $content) use (
            $slide, $textX, $textW, &$offsetY, $maxBottom, $color, $Fill, $Border
        ) {
            if (!$content || trim($content) === '') return;
            if ($offsetY >= $maxBottom) return;

            // Section label
            $lbl = $slide->createRichTextShape()
                ->setHeight(18)->setWidth($textW)->setOffsetX($textX)->setOffsetY($offsetY);
            $lbl->getBorder()->setLineStyle($Border::LINE_NONE);
            $lr = $lbl->createTextRun($label);
            $lr->getFont()->setName('Poppins')->setBold(true)->setSize(11)->setColor($color('FF94A3B8'));
            $offsetY += 22;

            // Content card block
            // Clean up HTML before displaying
            $cleanContent = $content;
            
            // Convert breaks and block ends to newlines
            $cleanContent = str_ireplace(['</p>', '</div>', '<br>', '<br/>', '<br />'], "\n", $cleanContent);
            
            // Handle ordered lists by numbering them sequentially within each <ol>
            $cleanContent = preg_replace_callback('/<ol[^>]*>(.*?)<\/ol>/is', function($matches) {
                $count = 1;
                return preg_replace_callback('/<li[^>]*data-list="ordered"[^>]*>/i', function($m2) use (&$count) {
                    return ($count++) . ". ";
                }, $matches[1]);
            }, $cleanContent);

            // Quill rich text editor uses data-list="bullet" and data-list="ordered" (if no <ol> wrapper)
            $cleanContent = preg_replace('/<li[^>]*data-list="bullet"[^>]*>/i', "• ", $cleanContent);
            $cleanContent = preg_replace('/<li[^>]*data-list="ordered"[^>]*>/i', "1. ", $cleanContent); // Fallback if no <ol>
            $cleanContent = preg_replace('/<li[^>]*>/i', "• ", $cleanContent); // fallback
            $cleanContent = str_ireplace('</li>', "\n", $cleanContent);
            
            $cleanContent = strip_tags($cleanContent);
            $cleanContent = html_entity_decode($cleanContent, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // Remove multiple consecutive newlines and trim
            $cleanContent = preg_replace("/\n\s*\n+/", "\n", $cleanContent);
            $cleanContent = trim($cleanContent);

            // Do not truncate text for client presentations
            $excerpt = $cleanContent;
            
            // Calculate lines based on explicit newlines plus word wrap estimate
            $explicitLines = substr_count($excerpt, "\n") + 1;
            $wordWrapLines = (int) ceil(mb_strlen(str_replace("\n", "", $excerpt)) / 70);
            $lines = max($explicitLines, $wordWrapLines);

            // Allow the block to grow to fit the text, up to the remaining slide height
            $blockH   = (int)($lines * 16) + 24;
            $blockH   = min($blockH, $maxBottom - $offsetY);
            if ($blockH < 16) return;

            $blk = $slide->createRichTextShape()
                ->setHeight($blockH)->setWidth($textW)->setOffsetX($textX)->setOffsetY($offsetY);
            
            // Styled card container
            $blk->getFill()->setFillType($Fill::FILL_NONE);
            $blk->getBorder()->setLineStyle($Border::LINE_NONE);

            $parts = explode("\n", $excerpt);
            foreach ($parts as $idx => $part) {
                if ($idx > 0) {
                    $blk->createBreak();
                }
                if ($part !== '') {
                    $run = $blk->createTextRun($part);
                    // Increased font size for better client presentation readability
                    $run->getFont()->setName('Poppins')->setSize(11)->setColor($color('FF334155'));
                }
            }
            
            $blk->getActiveParagraph()->getAlignment()->setMarginLeft(0)->setMarginTop(0)->setMarginRight(0);

            $offsetY += $blockH + 16;
        };

        // Client presentation layout fields
        $addSection('CONCEPT',      $task->concept);
        $addSection('CAPTION',      $task->caption);
        $addSection('COPY',         $task->post_copy ?: ($task->subtask_copy ?? null));
        $addSection('REFERENCE LINK', $task->reference);

        if ($task->reference_file) {
            $url = str_starts_with($task->reference_file, 'http') ? $task->reference_file : asset(ltrim($task->reference_file, '/'));
            $addSection('REFERENCE FILE', $url);
        }

        if ($task->final_designs) {
            $url = str_starts_with($task->final_designs, 'http') ? $task->final_designs : asset(ltrim($task->final_designs, '/'));
            $addSection('FINAL ARTWORK FILE', $url);
        }

        if ($task->final_designs_link) {
            $addSection('FINAL DESIGNS LINK', $task->final_designs_link);
        }

        // ── 8. Footer bar ────────────────────────────────────────
        $ftrBg = $slide->createRichTextShape()
            ->setHeight($footerH)->setWidth($SW)->setOffsetX(0)->setOffsetY($SH - $footerH);
        $ftrBg->getFill()->setFillType($Fill::FILL_SOLID)->setStartColor($color('FFFFFFFF'));
        $ftrBg->getBorder()->setLineStyle($Border::LINE_NONE);
        $ftrBg->createTextRun('')->getFont()->setSize(1)->setColor($color('FFFFFFFF'));
    }
}


