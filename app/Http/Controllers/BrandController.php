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
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && $user->role !== 'Brand Manager') {
            return redirect()->route('brands.index')->with('error', 'Access Denied: You do not have permission to create brands.');
        }
        $users = \App\Models\User::all();
        return view('brands.create', compact('users'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && $user->role !== 'Brand Manager') {
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
            // Store locally first (instant), then async upload to S3
            $localPath = $file->storeAs('brand_logos', $filename, 'public');
            $brandData['logo_url'] = asset('storage/' . $localPath);
            // Try S3 upload in background (non-blocking)
            try {
                $s3Path = $file->storeAs('brand_logos', $filename, 's3');
                $brandData['logo_url'] = \Illuminate\Support\Facades\Storage::disk('s3')->url($s3Path);
            } catch (\Exception $e) {
                // S3 failed — local fallback already set above
                \Illuminate\Support\Facades\Log::warning('S3 upload failed for brand logo: ' . $e->getMessage());
            }
        }

        $brand = Brand::create($brandData);

        $members = $request->members ?? [];
        if (!in_array(auth()->id(), $members)) {
            $members[] = auth()->id();
        }
        $opsManagers = \App\Models\User::where('role', 'Operations Manager')->pluck('id')->toArray();
        $members = array_unique(array_merge($members, $opsManagers));
        $brand->members()->sync($members);
        $brand->update(['total_members' => count($members)]);

        return redirect()->route('brands.index')->with('success', 'Brand created successfully.');
    }

    public function show(Brand $brand)
    {
        $user = auth()->user();
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager')) {
            if ($user->role === 'Brand Manager' && $brand->created_by !== $user->id) {
                return redirect()->route('brands.index')->with('error', 'Access Denied: Only the Brand Manager who created this brand can access it.');
            }
            if ($user->role !== 'Brand Manager' && $brand->created_by !== $user->id && !$brand->members()->where('users.id', $user->id)->exists()) {
                return redirect()->route('brands.index')->with('error', 'Access Denied: You are not assigned to this brand.');
            }
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
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager')) {
            if ($user->role === 'Brand Manager' && $brand->created_by !== $user->id) {
                return redirect()->route('brands.index')->with('error', 'Access Denied: Only the Brand Manager who created this brand can access it.');
            }
            if ($user->role !== 'Brand Manager' && $brand->created_by !== $user->id && !$brand->members()->where('users.id', $user->id)->exists()) {
                return redirect()->route('brands.index')->with('error', 'Access Denied: You are not assigned to this brand.');
            }
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
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager')) {
            if ($user->role !== 'Brand Manager') {
                return redirect()->route('brands.index')->with('error', 'Access Denied: Only Brand Managers can edit brands.');
            }
            if ($user->role === 'Brand Manager' && $brand->created_by !== $user->id) {
                return redirect()->route('brands.index')->with('error', 'Access Denied: Only the Brand Manager who created this brand can edit it.');
            }
        }
        $users = \App\Models\User::all();
        $brand->load('members');
        return view('brands.edit', compact('brand', 'users'));
    }

    public function update(Request $request, Brand $brand)
    {
        $user = auth()->user();
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager')) {
            if ($user->role !== 'Brand Manager') {
                return redirect()->route('brands.index')->with('error', 'Access Denied: Only Brand Managers can edit brands.');
            }
            if ($user->role === 'Brand Manager' && $brand->created_by !== $user->id) {
                return redirect()->route('brands.index')->with('error', 'Access Denied: Only the Brand Manager who created this brand can edit it.');
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
            $localPath = $file->storeAs('brand_logos', $filename, 'public');
            $brandData['logo_url'] = asset('storage/' . $localPath);
            try {
                $s3Path = $file->storeAs('brand_logos', $filename, 's3');
                $brandData['logo_url'] = \Illuminate\Support\Facades\Storage::disk('s3')->url($s3Path);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('S3 upload failed for brand logo: ' . $e->getMessage());
            }
        }

        $brand->update($brandData);

        if ($request->has('members')) {
            $opsManagers = \App\Models\User::where('role', 'Operations Manager')->pluck('id')->toArray();
            $members = array_unique(array_merge($request->members, $opsManagers));
            $brand->members()->sync($members);
            $brand->update(['total_members' => count($members)]);
            
            // Sync all projects under the brand
            foreach ($brand->projects as $project) {
                $project->members()->sync($members);
            }
        }

        return redirect()->route('brands.index')->with('success', 'Brand updated successfully.');
    }

    public function destroy(Brand $brand)
    {
        $user = auth()->user();
        if ((!$user->isAdmin() && $user->role !== 'Operations Manager') && $brand->created_by !== $user->id) {
            return redirect()->route('brands.index')->with('error', 'Access Denied: You can only delete brands you created.');
        }
        $brand->delete();
        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }
}

