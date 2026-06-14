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
        if (!$user->isAdmin() && !in_array($user->role, ['Brand Manager', 'Coordinator', 'Approver'])) abort(403);

        $brandId = request('brand_id');
        $brands = \App\Models\Brand::all();
        
        if ($brandId) {
            $brand = \App\Models\Brand::with('members')->find($brandId);
            $users = $brand ? $brand->members : collect();
        } else {
            $users = \App\Models\User::all();
        }

        $writers = $users->where('role', 'Writer');
        $approvers = $users->where('role', 'Approver');
        $managers = $users->where('role', 'Brand Manager');
        $designers = $users->where('role', 'Designer');
        
        $groupedUsers = $users->groupBy('role');

        return view('projects.create', compact('brands', 'writers', 'approvers', 'managers', 'designers', 'users', 'groupedUsers'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !in_array($user->role, ['Brand Manager', 'Coordinator', 'Approver'])) abort(403);

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

        $project = Project::create($validated);

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
        $approvers = \App\Models\User::where('role', 'Approver')
            ->whereHas('brands', fn($b) => $b->where('brands.id', $brandId))
            ->get();
        $coordinators = \App\Models\User::where('role', 'Coordinator')
            ->whereHas('brands', fn($b) => $b->where('brands.id', $brandId))
            ->get();
        
        $stages = $project->workflow_type === 'retainer' ? \App\Models\Deliverable::STAGES : \App\Models\Deliverable::CAMPAIGN_STAGES;
        
        return view('projects.show', compact('project', 'brandManagers', 'designers', 'approvers', 'coordinators', 'stages'));
    }

    public function edit(Project $project)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !in_array($user->role, ['Brand Manager', 'Coordinator', 'Approver'])) abort(403);
        $brands = \App\Models\Brand::all();
        $brand = $project->brand()->with('members')->first();
        $users = $brand ? $brand->members : collect();

        $writers = $users->where('role', 'Writer');
        $approvers = $users->where('role', 'Approver');
        $managers = $users->where('role', 'Brand Manager');
        $designers = $users->where('role', 'Designer');
        
        $groupedUsers = $users->groupBy('role');
        
        return view('projects.edit', compact('project', 'brands', 'writers', 'approvers', 'managers', 'designers', 'users', 'groupedUsers'));
    }

    public function update(Request $request, Project $project)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !in_array($user->role, ['Brand Manager', 'Coordinator', 'Approver'])) abort(403);
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

