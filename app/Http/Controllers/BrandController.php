<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $brands = Brand::withCount('projects')->with('members')->get();
        return view('brands.index', compact('brands'));
    }

    public function create()
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role !== 'Brand Manager') {
            return redirect()->route('brands.index')->with('error', 'Access Denied: You do not have permission to create brands.');
        }
        $users = \App\Models\User::all();
        return view('brands.create', compact('users'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $user->role !== 'Brand Manager') {
            return redirect()->route('brands.index')->with('error', 'Access Denied: You do not have permission to create brands.');
        }
        // Auto-generate slug from name if not provided
        if (!$request->filled('slug')) {
            $request->merge(['slug' => \Illuminate\Support\Str::slug($request->name)]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:brands,slug',
            'logo' => 'nullable|image|max:2048', // New image validation
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        $brandData = [
            'name'       => $validated['name'],
            'slug'       => $validated['slug'],
            'created_by' => auth()->id(),
        ];

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('brand_logos'), $filename);
            $brandData['logo_url'] = '/brand_logos/' . $filename;
        }

        $brand = Brand::create($brandData);

        $members = $request->members ?? [];
        if (!in_array(auth()->id(), $members)) {
            $members[] = auth()->id();
        }
        $brand->members()->sync($members);
        $brand->update(['total_members' => count($members)]);

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function show(Brand $brand)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $brand->created_by !== $user->id && !$brand->members()->where('users.id', $user->id)->exists()) {
            return redirect()->route('brands.index')->with('error', 'Access Denied: You are not assigned to this brand.');
        }

        $brand->load(['projects' => function($q) {
            $q->orderBy('type', 'desc'); // primary first
        }]);
        
        $pendingDeliverables = $brand->deliverables()->where('deliverables.status', '!=', 'Done')->with('project')->get();
        
        return view('brands.show', compact('brand', 'pendingDeliverables'));
    }

    public function retainerBoard(Brand $brand)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $brand->created_by !== $user->id && !$brand->members()->where('users.id', $user->id)->exists()) {
            return redirect()->route('brands.index')->with('error', 'Access Denied: You are not assigned to this brand.');
        }

        $deliverables = $brand->deliverables()
            ->whereHas('project', function($q) {
                $q->where('workflow_type', 'retainer');
            })
            ->with(['project', 'writer', 'approver'])
            ->orderBy('deadline', 'asc')
            ->get();

        return view('brands.retainer_board', compact('brand', 'deliverables'));
    }

    public function edit(Brand $brand)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            if ($user->role !== 'Brand Manager') {
                return redirect()->route('brands.index')->with('error', 'Access Denied: Only Brand Managers can edit brands.');
            }
            if ($brand->created_by !== $user->id && !$brand->members()->where('users.id', $user->id)->exists()) {
                return redirect()->route('brands.index')->with('error', 'Access Denied: You are not assigned to this brand.');
            }
        }
        $users = \App\Models\User::all();
        $brand->load('members');
        return view('brands.edit', compact('brand', 'users'));
    }

    public function update(Request $request, Brand $brand)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            if ($user->role !== 'Brand Manager') {
                return redirect()->route('brands.index')->with('error', 'Access Denied: Only Brand Managers can edit brands.');
            }
            if ($brand->created_by !== $user->id && !$brand->members()->where('users.id', $user->id)->exists()) {
                return redirect()->route('brands.index')->with('error', 'Access Denied: You are not assigned to this brand.');
            }
        }
        // Auto-generate slug from name if not provided or empty
        if (!$request->filled('slug')) {
            $request->merge(['slug' => \Illuminate\Support\Str::slug($request->name)]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:brands,slug,' . $brand->id,
            'logo' => 'nullable|image|max:2048',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);

        $brandData = \Illuminate\Support\Arr::except($validated, ['members', 'logo']);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = \Illuminate\Support\Str::uuid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('brand_logos'), $filename);
            $brandData['logo_url'] = '/brand_logos/' . $filename;
        }

        $brand->update($brandData);

        if ($request->has('members')) {
            $brand->members()->sync($request->members);
            $brand->update(['total_members' => count($request->members)]);
            
            // Sync all projects under the brand
            foreach ($brand->projects as $project) {
                $project->members()->sync($request->members);
            }
        }

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && $brand->created_by !== $user->id) {
            return redirect()->route('brands.index')->with('error', 'Access Denied: You can only delete brands you created.');
        }
        $brand->delete();
        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }
}

