<?php

namespace App\Http\Controllers;

use App\Models\SubtaskType;
use Illuminate\Http\Request;

class SubtaskTypeController extends Controller
{
    public function index()
    {
        $types = SubtaskType::orderBy('workflow_type')->orderBy('name')->get();
        return view('admin.subtask-types.index', compact('types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'workflow_type' => 'required|in:retainer,campaign',
        ]);

        SubtaskType::create($request->only('name', 'workflow_type'));

        return back()->with('success', 'Subtask type added successfully.');
    }

    public function destroy(SubtaskType $subtaskType)
    {
        $subtaskType->delete();
        return back()->with('success', 'Subtask type deleted successfully.');
    }
}
