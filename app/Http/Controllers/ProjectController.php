<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Brand;
use App\Notifications\BriefUploaded;
use Illuminate\Http\Request;

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
        if (!$user->isAdmin() && !in_array($user->role, ['Brand Manager', 'Coordinator', 'Approver', 'Approver Coordinator'])) abort(403);

        $brandId = request('brand_id');
        $brands = \App\Models\Brand::all();
        
        if ($brandId) {
            $brand = \App\Models\Brand::with('members')->find($brandId);
            $users = $brand ? $brand->members : collect();
        } else {
            $users = \App\Models\User::all();
        }

        $writers = $users->where('role', 'Writer');
        $approvers = $users->whereIn('role', ['Approver', 'Approver Coordinator']);
        $managers = $users->where('role', 'Brand Manager');
        $designers = $users->where('role', 'Designer');
        
        $groupedUsers = $users->groupBy('role');
        $subtaskTypes = \App\Models\SubtaskType::orderBy('workflow_type')->orderBy('name')->get();

        return view('projects.create', compact('brands', 'writers', 'approvers', 'managers', 'designers', 'users', 'groupedUsers', 'subtaskTypes'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !in_array($user->role, ['Brand Manager', 'Coordinator', 'Approver', 'Approver Coordinator'])) abort(403);

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
            'brief_file' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,png|max:10240',
            'posts_count' => 'nullable|integer|min:0|max:200',
            'post_type_counts' => 'nullable|array',
            'post_type_counts.*' => 'nullable|integer|min:0|max:200',
        ]);

        $postTypeCounts = $request->input('post_type_counts', []);
        $postsCount = (int) ($validated['posts_count'] ?? 0);
        unset($validated['posts_count'], $validated['post_type_counts']);

        if ($request->hasFile('brief_file')) {
            $file = $request->file('brief_file');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('briefs'), $filename);
            $validated['brief_file_path'] = '/briefs/' . $filename;
        }

        $project = Project::create($validated);

        // Bulk-generate blank deliverable slots per post type (or by count if no types given)
        $firstStage = in_array($project->workflow_type, ['campaign', 'pitch'])
            ? \App\Models\Deliverable::CAMPAIGN_STAGES[0]
            : \App\Models\Deliverable::STAGES[0];

        $writerName = $project->writer?->name ?? 'Unassigned';
        $deliverables = [];

        $hasTypeCounts = !empty(array_filter($postTypeCounts, fn($v) => (int)$v > 0));

        if ($hasTypeCounts) {
            $activeCounts = array_filter($postTypeCounts, fn($v) => (int)$v > 0);
            $multipleTypes = count($activeCounts) > 1;
            $subtaskTypeModels = \App\Models\SubtaskType::whereIn('id', array_keys($activeCounts))->get()->keyBy('id');

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

            foreach ($activeCounts as $typeId => $count) {
                $count = (int) $count;
                $typeName = $subtaskTypeModels[$typeId]->name ?? 'Post';

                if ($multipleTypes) {
                    // Create a parent batch deliverable for this post type
                    $parent = \App\Models\Deliverable::create(array_merge($baseRow, [
                        'title'     => $typeName,
                        'post_type' => $typeName,
                    ]));

                    $children = [];
                    for ($i = 1; $i <= $count; $i++) {
                        $children[] = array_merge($baseRow, [
                            'parent_deliverable_id' => $parent->id,
                            'title'                 => $typeName . ' ' . $i,
                            'post_type'             => $typeName,
                        ]);
                    }
                    \App\Models\Deliverable::insert($children);
                } else {
                    // Single type — flat deliverables, no parent
                    for ($i = 1; $i <= $count; $i++) {
                        $deliverables[] = array_merge($baseRow, [
                            'title'     => $typeName . ' ' . $i,
                            'post_type' => $typeName,
                        ]);
                    }
                }
            }
        } elseif ($postsCount > 0) {
            for ($i = 1; $i <= $postsCount; $i++) {
                $deliverables[] = [
                    'project_id'       => $project->id,
                    'title'            => 'Post ' . $i,
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
            }
        }

        if (!empty($deliverables)) {
            \App\Models\Deliverable::insert($deliverables);
        }

        // Automatically sync brand members to the project
        $brand = Brand::with('members')->find($validated['brand_id']);
        if ($brand) {
            $project->members()->sync($brand->members->pluck('id'));
        }

        // Notify all writers in the brand
        $actor = auth()->user();
        $notifiedIds = [];
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
            'deliverables.subtasks.revisionsHistory.user', 
            'deliverables.subtasks.revisionsHistory.fixedByUser',
            'deliverables.subtasks.approvalsHistory.user',
            'deliverables.subtasks.writer',
            'deliverables.subtasks.approver',
            'deliverables.subtasks.brandManager',
            'deliverables.subtasks.coordinator',
            'deliverables.subtasks.designer',
        ]);
        $brandId = $project->brand_id;
        $brandManagers = \App\Models\User::where('role', 'Brand Manager')
            ->whereHas('brands', fn($b) => $b->where('brands.id', $brandId))
            ->get();
        $designers = \App\Models\User::where('role', 'Designer')
            ->whereHas('brands', fn($b) => $b->where('brands.id', $brandId))
            ->get();
        $approvers = \App\Models\User::whereIn('role', ['Approver', 'Approver Coordinator'])
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
        if (!$user->isAdmin() && !in_array($user->role, ['Brand Manager', 'Coordinator', 'Approver', 'Approver Coordinator'])) abort(403);
        $brands = \App\Models\Brand::all();
        $brand = $project->brand()->with('members')->first();
        $users = $brand ? $brand->members : collect();

        $writers = $users->where('role', 'Writer');
        $approvers = $users->whereIn('role', ['Approver', 'Approver Coordinator']);
        $managers = $users->where('role', 'Brand Manager');
        $designers = $users->where('role', 'Designer');
        
        $groupedUsers = $users->groupBy('role');
        
        return view('projects.edit', compact('project', 'brands', 'writers', 'approvers', 'managers', 'designers', 'users', 'groupedUsers'));
    }

    public function update(Request $request, Project $project)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !in_array($user->role, ['Brand Manager', 'Coordinator', 'Approver', 'Approver Coordinator'])) abort(403);
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
            'brief_file' => 'nullable|file|mimes:pdf,doc,docx,txt,jpg,png|max:10240',
        ]);

        if ($request->hasFile('brief_file')) {
            $file = $request->file('brief_file');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('briefs'), $filename);
            $validated['brief_file_path'] = '/briefs/' . $filename;
        }

        $project->update($validated);

        // Automatically sync brand members to the project
        $brand = Brand::with('members')->find($validated['brand_id']);
        if ($brand) {
            $project->members()->sync($brand->members->pluck('id'));
        }

        if (isset($validated['brief_file_path'])) {
            $actor = auth()->user();
            $notifiedIds = [];
            if ($brand) {
                foreach ($brand->members->where('role', 'Writer') as $writer) {
                    $writer->notify(new BriefUploaded($project, $actor, true));
                    $notifiedIds[] = $writer->id;
                }
            }
            if ($project->writer_id && !in_array($project->writer_id, $notifiedIds)) {
                $project->writer->notify(new BriefUploaded($project, $actor, true));
            }
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role !== 'Brand Manager') abort(403);
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

