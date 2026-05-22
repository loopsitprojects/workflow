<?php

namespace App\Http\Controllers;

use App\Models\Deliverable;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        
        $deliverables = Deliverable::with(['project.brand', 'writer', 'approver', 'brandManager', 'coordinator', 'designer'])
            ->where(function($q) use ($userId) {
                $q->where('writer_id', $userId)
                  ->orWhere('approver_id', $userId)
                  ->orWhere('brand_manager_id', $userId)
                  ->orWhere('coordinator_id', $userId)
                  ->orWhere('designer_id', $userId);
            })
            ->orderByRaw("CASE 
                WHEN priority = 'High Priority' THEN 1 
                WHEN priority = 'Medium' THEN 2 
                WHEN priority = 'Standard' THEN 3 
                WHEN priority = 'Low' THEN 4 
                ELSE 5 END")
            ->orderBy('deadline', 'asc')
            ->get();

        $brands = \App\Models\Brand::select('id', 'name', 'slug', 'logo_url')->get();
        $brandCount = $brands->count();
        return view('dashboard', compact('deliverables', 'brands', 'brandCount'));
    }

}
