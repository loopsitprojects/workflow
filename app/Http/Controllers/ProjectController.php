<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Brand;
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
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        if ($request->hasFile('brief_file')) {
            $path = $request->file('brief_file')->store('briefs', 'public');
            $validated['brief_file_path'] = $path;
        }

        $project = Project::create($validated);

        if ($request->has('members')) {
            $project->members()->sync($request->members);
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
        $brandManagers = \App\Models\User::whereIn('role', ['Brand Manager', 'Admin', 'Approver'])->get();
        $designers = \App\Models\User::whereIn('role', ['Designer', 'Admin'])->get();
        $approvers = \App\Models\User::whereIn('role', ['Approver', 'Admin'])->get();
        $coordinators = \App\Models\User::whereIn('role', ['Coordinator', 'Traffic Coordinator', 'Admin'])->get();
        
        $stages = $project->workflow_type === 'retainer' ? \App\Models\Deliverable::STAGES : \App\Models\Deliverable::CAMPAIGN_STAGES;
        
        return view('projects.show', compact('project', 'brandManagers', 'designers', 'approvers', 'coordinators', 'stages'));
    }

    public function edit(Project $project)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role !== 'Brand Manager') abort(403);
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
        if (!$user->isAdmin() && $user->role !== 'Brand Manager') abort(403);
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
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        if ($request->hasFile('brief_file')) {
            $path = $request->file('brief_file')->store('briefs', 'public');
            $validated['brief_file_path'] = $path;
        }

        $project->update($validated);

        if ($request->has('members')) {
            $project->members()->sync($request->members);
        }

        return redirect()->route('projects.show', $project)->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        if (!auth()->user()->isAdmin()) abort(403);
        $project->delete();
        return redirect()->route('brands.show', $project->brand->slug)->with('success', 'Project deleted successfully.');
    }
}

