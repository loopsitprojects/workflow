<?php

namespace App\Http\Controllers;

use App\Models\Deliverable;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        $deliverables = Deliverable::doesntHave('subtasks')
            ->with(['project.brand', 'parent', 'writer', 'approver', 'brandManager', 'coordinator', 'designer'])
            ->where(function($q) use ($userId) {
                // Done deliverables: user is associated in any role
                $q->where(function($sub) use ($userId) {
                    $sub->where('status', 'Done')
                        ->where(function($inner) use ($userId) {
                            $inner->where('writer_id', $userId)
                                  ->orWhere('approver_id', $userId)
                                  ->orWhere('brand_manager_id', $userId)
                                  ->orWhere('coordinator_id', $userId)
                                  ->orWhere('designer_id', $userId);
                        });
                })
                // Active/Pending deliverables: user is responsible for the current stage
                ->orWhere(function($sub) use ($userId) {
                    $sub->where('status', '!=', 'Done')
                        ->where(function($inner) use ($userId) {
                            $inner->where(function($w) use ($userId) {
                                $w->whereIn('approval_stage', ['Writer', 'Assignee'])
                                  ->orWhereNull('approval_stage');
                            })->where('writer_id', $userId)
                            ->orWhere(function($a) use ($userId) {
                                $a->where('approval_stage', 'Approver')
                                  ->where('approver_id', $userId);
                            })
                            ->orWhere(function($b) use ($userId) {
                                $b->whereIn('approval_stage', ['Brand Manager', 'AM/BD', 'Final Approval'])
                                  ->where('brand_manager_id', $userId);
                            })
                            ->orWhere(function($c) use ($userId) {
                                $c->where('approval_stage', 'Coordinator')
                                  ->where('coordinator_id', $userId);
                            })
                            ->orWhere(function($d) use ($userId) {
                                $d->where('approval_stage', 'Designer')
                                  ->where('designer_id', $userId);
                            })
                            ->orWhere(function($wr) use ($userId) {
                                $wr->where('approval_stage', 'Writer Review')
                                   ->where('writer_id', $userId);
                            })
                            ->orWhere(function($ar) use ($userId) {
                                $ar->where('approval_stage', 'Approver Review')
                                   ->where('approver_id', $userId);
                            })
                            ->orWhere(function($fa) use ($userId) {
                                $fa->where('approval_stage', 'Further Approver')
                                   ->where('approver_id', $userId);
                            });
                        });
                });
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
