<?php

namespace App\Http\Controllers;

use App\Models\Deliverable;
use App\Models\Brand;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $userId = $user->id;

        $query = Deliverable::doesntHave('subtasks')
            ->with(['project.brand', 'parent', 'writer', 'approver', 'brandManager', 'coordinator', 'designer']);

        if (!$user->isAdmin()) {
            $query->where(function($q) use ($userId, $user) {
                // Done deliverables: user is associated in any role
                $q->where(function($sub) use ($userId) {
                    $sub->where('status', 'Done')
                        ->where(function($inner) use ($userId) {
                            $inner->where('writer_id', $userId)
                                  ->orWhere('approver_id', $userId)
                                  ->orWhere('brand_manager_id', $userId)
                                  ->orWhere('coordinator_id', $userId)
                                  ->orWhere('designer_id', $userId)
                                  ->orWhere('further_approver_id', $userId)
                                  ->orWhereHas('project', function($p) use ($userId) {
                                      $p->where('brand_manager_id', $userId);
                                  });
                        });
                })
                // Active/Pending deliverables: user is responsible for the current stage
                ->orWhere(function($sub) use ($userId, $user) {
                    $sub->where('status', '!=', 'Done')
                        ->where(function($inner) use ($userId, $user) {
                            $inner->where(function($w) use ($userId) {
                                $w->whereIn('approval_stage', ['Writer', 'Assignee', 'Assign'])
                                  ->orWhereNull('approval_stage');
                            })->where('writer_id', $userId)
                            ->orWhere(function($a) use ($userId) {
                                $a->where('approval_stage', 'Approver')
                                  ->where('approver_id', $userId);
                            })
                            ->orWhere(function($b) use ($userId, $user) {
                                $b->whereIn('approval_stage', ['Brand Manager', 'AM/BD', 'Final Approval', 'Approve', 'Manager Review'])
                                  ->where(function($bm) use ($userId, $user) {
                                      $bm->where('brand_manager_id', $userId)
                                         ->orWhereHas('project', function($p) use ($userId) {
                                             $p->where('brand_manager_id', $userId);
                                         });

                                      if (in_array($user->role, ['Brand Manager', 'Operations Manager'])) {
                                          $bm->orWhere(function($ft) use ($userId) {
                                              $ft->where('flow_type', 'direct_design')
                                                 ->whereHas('project.brand', function($br) use ($userId) {
                                                     $br->where('created_by', $userId)
                                                        ->orWhereHas('members', function($m) use ($userId) {
                                                            $m->where('users.id', $userId);
                                                        });
                                                 });
                                          })
                                          ->orWhere(function($unassigned) use ($userId) {
                                              $unassigned->whereNull('brand_manager_id')
                                                 ->whereHas('project.brand', function($br) use ($userId) {
                                                     $br->where('created_by', $userId)
                                                        ->orWhereHas('members', function($m) use ($userId) {
                                                            $m->where('users.id', $userId);
                                                        });
                                                 });
                                          });
                                      }
                                  });
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
                                   ->where('further_approver_id', $userId);
                            })
                            ->orWhere(function($sch) use ($userId) {
                                $sch->where('approval_stage', 'Scheduled')
                                   ->where('writer_id', $userId);
                            });
                        });
                });
            });
        }

        if ($user->isAdmin()) {
            $deliverables = collect();
        } else {
            $deliverables = $query->orderByRaw("CASE
                    WHEN priority = 'High Priority' THEN 1
                    WHEN priority = 'Medium' THEN 2
                    WHEN priority = 'Low Priority' THEN 3
                    ELSE 4 END")
                ->orderBy('deadline', 'asc')
                ->get();
        }

        $brands = \App\Models\Brand::select('id', 'name', 'slug', 'logo_url', 'created_by')->with('members')->get();
        $brandCount = $brands->count();
        return view('dashboard', compact('deliverables', 'brands', 'brandCount'));
    }

}
