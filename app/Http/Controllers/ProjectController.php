<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Brand;
use App\Models\User;
use App\Models\Deliverable;
use App\Models\SubtaskType;
use App\Notifications\BriefUploaded;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('brand')->get();
        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !in_array($user->role, ['Operations Manager', 'Brand Manager', 'Coordinator', 'Approver', 'Approver Coordinator'])) abort(403);

        $brandId = request('brand_id');
        $brands = \App\Models\Brand::all();
        
        if ($brandId) {
            $brand = \App\Models\Brand::with('members')->find($brandId);
            $users = $brand ? $brand->members : collect();
        } else {
            $users = \App\Models\User::all();
        }

        $writers = $users->where('role', 'Writer');
        $approvers = $users->whereIn('role', ['Approver', 'Approver Coordinator', 'Operations Manager']);
        $managers = $users->where('role', 'Brand Manager');
        $designers = $users->where('role', 'Designer');
        
        $groupedUsers = $users->groupBy('role');
        $subtaskTypes = \App\Models\SubtaskType::orderBy('workflow_type')->orderBy('name')->get();

        return view('projects.create', compact('brands', 'writers', 'approvers', 'managers', 'designers', 'users', 'groupedUsers', 'subtaskTypes'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !in_array($user->role, ['Operations Manager', 'Brand Manager', 'Coordinator', 'Approver', 'Approver Coordinator'])) abort(403);

        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'job_number' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lead_name' => 'nullable|string',
            'status' => 'required|string',
            'deadline' => 'nullable|date',
            'priority' => 'required|string',
            'type' => 'required|string',
            'workflow_type' => 'required|string|in:retainer,campaign,pitch',
            'writer_id' => 'nullable|exists:users,id',
            'approver_id' => 'nullable|exists:users,id',
            'brand_manager_id' => 'nullable|exists:users,id',
            'coordinator_id' => 'nullable|exists:users,id',
            'designer_id' => 'nullable|exists:users,id',
            'sub_type' => 'nullable|string',
            'lead_id' => 'nullable|exists:users,id',
            'brief_file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,txt,jpg,png|max:10240',
            'posts_count' => 'nullable|integer|min:0|max:200',
            'post_type_counts' => 'nullable|array',
            'post_type_counts.*' => 'nullable|integer|min:0|max:200',
            'batches' => 'nullable|array',
            'batches.*.name' => 'required|string',
            'batches.*.deadline' => 'nullable',
            'batches.*.post_types' => 'nullable|array',
            'batches.*.posts_count' => 'nullable',
        ]);

        $postTypeCounts = $request->input('post_type_counts', []);
        $postsCount = (int) ($validated['posts_count'] ?? 0);
        $batches = $request->input('batches', []);
        unset($validated['posts_count'], $validated['post_type_counts'], $validated['batches']);

        if ($request->hasFile('brief_file')) {
            $file = $request->file('brief_file');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('briefs', $filename, 's3');
            $validated['brief_file_path'] = \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
        }

        if (empty($validated['brand_manager_id'])) {
            $brand = \App\Models\Brand::find($validated['brand_id']);
            if ($brand && $brand->created_by) {
                $validated['brand_manager_id'] = $brand->created_by;
            }
        }

        $project = Project::create($validated);

        // Bulk-generate blank deliverable slots per post type (or by count if no types given)
        $firstStage = in_array($project->workflow_type, ['campaign', 'pitch'])
            ? \App\Models\Deliverable::CAMPAIGN_STAGES[0]
            : \App\Models\Deliverable::STAGES[0];

        $writerName = $project->writer?->name ?? 'Unassigned';
        $deliverables = [];

        $baseRow = [
            'project_id'       => $project->id,
            'status'           => 'To Do',
            'task_type'        => 'Deliverable',
            'approval_stage'   => $firstStage,
            'priority'         => $project->priority ?? 'Medium',
            'progress_percent' => 0,
            'revisions'        => 0,
            'deadline'         => $project->deadline,
            'writer_id'        => $project->writer_id,
            'approver_id'      => $project->approver_id,
            'brand_manager_id' => $project->brand_manager_id,
            'coordinator_id'   => $project->coordinator_id,
            'designer_id'      => $project->designer_id,
            'assignee_name'    => $writerName,
            'created_at'       => now(),
            'updated_at'       => now(),
        ];

        if (!empty($batches)) {
            $typeIds = [];
            foreach ($batches as $batch) {
                if (!empty($batch['post_types'])) {
                    foreach (array_keys($batch['post_types']) as $tId) {
                        $typeIds[] = $tId;
                    }
                }
            }
            $subtaskTypeModels = collect();
            if (!empty($typeIds)) {
                $subtaskTypeModels = \App\Models\SubtaskType::whereIn('id', array_unique($typeIds))->get()->keyBy('id');
            }

            foreach ($batches as $batch) {
                $batchName = $batch['name'] ?? 'Batch';
                $batchDeadline = !empty($batch['deadline']) ? $batch['deadline'] : $project->deadline;
                $postTypes = $batch['post_types'] ?? [];

                // Create the parent deliverable (the batch itself)
                $parent = \App\Models\Deliverable::create(array_merge($baseRow, [
                    'title'     => $batchName,
                    'post_type' => null,
                    'deadline'  => $batchDeadline,
                ]));

                $children = [];

                foreach ($postTypes as $typeId => $typeData) {
                    if (is_array($typeData)) {
                        $count = (int)($typeData['count'] ?? 0);
                        $typeDeadline = !empty($typeData['deadline']) ? $typeData['deadline'] : null;
                        $itemDates = $typeData['dates'] ?? [];
                    } else {
                        $count = (int)$typeData;
                        $typeDeadline = null;
                        $itemDates = [];
                    }

                    if ($count <= 0) continue;

                    $typeName = $subtaskTypeModels[$typeId]->name ?? 'Post';

                    for ($i = 1; $i <= $count; $i++) {
                        $deliverableDeadline = !empty($itemDates[$i])
                            ? $itemDates[$i]
                            : ($typeDeadline ?: $batchDeadline);

                        $children[] = array_merge($baseRow, [
                            'parent_deliverable_id' => $parent->id,
                            'title'                 => $typeName . ' ' . $i,
                            'post_type'             => $typeName,
                            'deadline'              => $deliverableDeadline,
                        ]);
                    }
                }

                $bPostsCount = is_array($batch['posts_count'] ?? null) 
                    ? (int)($batch['posts_count']['count'] ?? 0) 
                    : (int)($batch['posts_count'] ?? 0);
                $bPostsDeadline = is_array($batch['posts_count'] ?? null) && !empty($batch['posts_count']['deadline'])
                    ? $batch['posts_count']['deadline']
                    : $batchDeadline;

                if ($bPostsCount > 0) {
                    for ($i = 1; $i <= $bPostsCount; $i++) {
                        $children[] = array_merge($baseRow, [
                            'parent_deliverable_id' => $parent->id,
                            'title'                 => 'Post ' . $i,
                            'post_type'             => null,
                            'deadline'              => $bPostsDeadline,
                        ]);
                    }
                }

                if (!empty($children)) {
                    \App\Models\Deliverable::insert($children);
                }
            }
        } else {
            $hasTypeCounts = !empty(array_filter($postTypeCounts, fn($v) => (is_array($v) ? (int)($v['count'] ?? 0) : (int)$v) > 0));
            $postTypeDates = $request->input('post_type_dates', []);

            if ($hasTypeCounts) {
                $subtaskTypeModels = \App\Models\SubtaskType::all()->keyBy('id');

                foreach ($postTypeCounts as $typeId => $typeVal) {
                    if (is_array($typeVal)) {
                        $count = (int)($typeVal['count'] ?? 0);
                        $typeDeadline = !empty($typeVal['deadline']) ? $typeVal['deadline'] : null;
                    } else {
                        $count = (int)$typeVal;
                        $typeDeadline = $postTypeDates[$typeId] ?? null;
                    }

                    if ($count <= 0) continue;

                    $typeName = $subtaskTypeModels[$typeId]->name ?? 'Post';
                    $itemDeadline = $typeDeadline ?: $project->deadline;

                    $parent = \App\Models\Deliverable::create(array_merge($baseRow, [
                        'title'     => $typeName,
                        'post_type' => $typeName,
                        'deadline'  => $itemDeadline,
                    ]));

                    $children = [];
                    for ($i = 1; $i <= $count; $i++) {
                        $children[] = array_merge($baseRow, [
                            'parent_deliverable_id' => $parent->id,
                            'title'                 => $typeName . ' ' . $i,
                            'post_type'             => $typeName,
                            'deadline'              => $itemDeadline,
                        ]);
                    }
                    \App\Models\Deliverable::insert($children);
                }
            } elseif ($postsCount > 0) {
                for ($i = 1; $i <= $postsCount; $i++) {
                    $deliverables[] = array_merge($baseRow, [
                        'title'    => 'Post ' . $i,
                        'deadline' => $project->deadline,
                    ]);
                }
            }

            if (!empty($deliverables)) {
                \App\Models\Deliverable::insert($deliverables);
            }
        }

        // Automatically sync brand members to the project
        $brand = Brand::with('members')->find($validated['brand_id']);
        if ($brand) {
            $membersToSync = $brand->members;
            
            // If a Brand Manager created the project, only include them, exclude other Brand Managers
            if ($user->role === 'Brand Manager') {
                $membersToSync = $membersToSync->reject(function ($member) use ($user) {
                    return $member->role === 'Brand Manager' && $member->id !== $user->id;
                });
            }
            
            $project->members()->sync($membersToSync->pluck('id'));
        }

        // Notify all writers in the brand
        $actor = auth()->user();
        $notifiedIds = [];
        try {
            if ($brand) {
                foreach ($brand->members->where('role', 'Writer') as $writer) {
                    $writer->notify(new BriefUploaded($project, $actor));
                    $notifiedIds[] = $writer->id;
                }
            }
            // Also notify the specifically assigned writer if not already a brand member
            if ($project->writer_id && !in_array($project->writer_id, $notifiedIds)) {
                $project->writer->notify(new BriefUploaded($project, $actor));
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to send BriefUploaded notification: ' . $e->getMessage());
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project created successfully.');
    }

    public function show(Project $project)
    {
        $project->load([
            'members',
            'deliverables.revisionsHistory.user', 
            'deliverables.revisionsHistory.fixedByUser',
            'deliverables.approvalsHistory.user',
            'deliverables.writer',
            'deliverables.approver',
            'deliverables.brandManager',
            'deliverables.coordinator',
            'deliverables.designer',
            'deliverables.artworkReviews',
            'deliverables.subtasks.revisionsHistory.user', 
            'deliverables.subtasks.revisionsHistory.fixedByUser',
            'deliverables.subtasks.approvalsHistory.user',
            'deliverables.subtasks.writer',
            'deliverables.subtasks.approver',
            'deliverables.subtasks.brandManager',
            'deliverables.subtasks.coordinator',
            'deliverables.subtasks.designer',
            'deliverables.subtasks.artworkReviews',
        ]);
        $brandId = $project->brand_id;
        $brandManagers = \App\Models\User::where('role', 'Brand Manager')
            ->where(function ($q) use ($project, $brandId) {
                if ($project->brand_manager_id) {
                    $q->where('id', $project->brand_manager_id);
                } else {
                    $q->whereHas('brands', fn($b) => $b->where('brands.id', $brandId));
                }
            })
            ->get();
        $designers = \App\Models\User::where('role', 'Designer')
            ->whereHas('brands', fn($b) => $b->where('brands.id', $brandId))
            ->get();
        $approvers = \App\Models\User::whereIn('role', ['Approver', 'Approver Coordinator', 'Operations Manager'])
            ->whereHas('brands', fn($b) => $b->where('brands.id', $brandId))
            ->get();
        $coordinators = \App\Models\User::whereIn('role', ['Coordinator', 'Approver Coordinator'])
            ->whereHas('brands', fn($b) => $b->where('brands.id', $brandId))
            ->get();
        
        $stages = $project->workflow_type === 'retainer' ? \App\Models\Deliverable::STAGES : \App\Models\Deliverable::CAMPAIGN_STAGES;

        // Push completed deliverables to the bottom
        $project->setRelation('deliverables',
            $project->deliverables->sortBy(fn($d) => $d->status === 'Done' ? 1 : 0)->values()
        );

        return view('projects.show', compact('project', 'brandManagers', 'designers', 'approvers', 'coordinators', 'stages'));
    }

    public function edit(Project $project)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !in_array($user->role, ['Operations Manager', 'Brand Manager', 'Coordinator', 'Approver', 'Approver Coordinator'])) abort(403);
        $brands = \App\Models\Brand::all();
        $brand = $project->brand()->with('members')->first();
        $users = $brand ? $brand->members : collect();

        $writers = $users->where('role', 'Writer');
        $approvers = $users->whereIn('role', ['Approver', 'Approver Coordinator', 'Operations Manager']);
        $managers = $users->where('role', 'Brand Manager');
        $designers = $users->where('role', 'Designer');
        
        $groupedUsers = $users->groupBy('role');
        
        return view('projects.edit', compact('project', 'brands', 'writers', 'approvers', 'managers', 'designers', 'users', 'groupedUsers'));
    }

    public function update(Request $request, Project $project)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !in_array($user->role, ['Operations Manager', 'Brand Manager', 'Coordinator', 'Approver', 'Approver Coordinator'])) abort(403);
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'job_number' => 'nullable|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'lead_name' => 'nullable|string',
            'status' => 'required|string',
            'deadline' => 'nullable|date',
            'priority' => 'required|string',
            'type' => 'required|string',
            'workflow_type' => 'required|string|in:retainer,campaign,pitch',
            'writer_id' => 'nullable|exists:users,id',
            'approver_id' => 'nullable|exists:users,id',
            'brand_manager_id' => 'nullable|exists:users,id',
            'coordinator_id' => 'nullable|exists:users,id',
            'designer_id' => 'nullable|exists:users,id',
            'sub_type' => 'nullable|string',
            'lead_id' => 'nullable|exists:users,id',
            'brief_file' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,txt,jpg,jpeg,png|max:10240',
        ]);

        if ($request->hasFile('brief_file')) {
            $file = $request->file('brief_file');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('briefs', $filename, 's3');
            $validated['brief_file_path'] = \Illuminate\Support\Facades\Storage::disk('s3')->url($path);
        } elseif ($request->input('remove_brief_file') == '1') {
            $validated['brief_file_path'] = null;
        }

        if (empty($validated['brand_manager_id'])) {
            $brand = \App\Models\Brand::find($validated['brand_id']);
            if ($brand && $brand->created_by) {
                $validated['brand_manager_id'] = $brand->created_by;
            }
        }

        $project->update($validated);

        // Automatically sync brand members to the project
        $brand = Brand::with('members')->find($validated['brand_id']);
        if ($brand) {
            $membersToSync = $brand->members;
            
            // Maintain the existing Brand Managers on the project so we don't overwrite restrictions
            $existingBrandManagerIds = $project->members()->where('role', 'Brand Manager')->pluck('users.id')->toArray();
            
            $membersToSync = $membersToSync->reject(function ($member) use ($existingBrandManagerIds) {
                return $member->role === 'Brand Manager' && !in_array($member->id, $existingBrandManagerIds);
            });

            $project->members()->sync($membersToSync->pluck('id'));
        }

        if (isset($validated['brief_file_path'])) {
            $actor = auth()->user();
            $notifiedIds = [];
            try {
                if ($brand) {
                    foreach ($brand->members->where('role', 'Writer') as $writer) {
                        $writer->notify(new BriefUploaded($project, $actor, true));
                        $notifiedIds[] = $writer->id;
                    }
                }
                if ($project->writer_id && !in_array($project->writer_id, $notifiedIds)) {
                    $project->writer->notify(new BriefUploaded($project, $actor, true));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Failed to send BriefUploaded notification: ' . $e->getMessage());
            }
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !in_array($user->role, ['Operations Manager', 'Brand Manager'])) abort(403);
        $project->delete();
        return redirect()->route('brands.show', $project->brand->slug)->with('success', 'Project deleted successfully.');
    }

    /**
     * Return the latest updated_at timestamp across the project and all its deliverables.
     * Used by the client-side polling mechanism to detect when another user has made changes.
     */
    public function lastUpdated(Project $project)
    {
        $latestDeliverable = $project->deliverables()->max('updated_at');
        $timestamps = array_filter([$project->updated_at?->toIso8601String(), $latestDeliverable]);
        $latest = !empty($timestamps) ? max($timestamps) : $project->updated_at?->toIso8601String();

        return response()->json(['last_updated' => $latest]);
    }
}

