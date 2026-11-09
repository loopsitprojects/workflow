<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Deliverable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ClosedArchiveController extends Controller
{
    /**
     * Display a listing of closed projects and closed deliverables.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $userId = $user->id;
        $isGlobalViewer = $user->isAdmin() || in_array($user->role, ['Operations Manager']);

        $isClosedTabEnabled = \App\Services\FeatureManager::isClosedTabEnabled();
        if (!$isClosedTabEnabled && !$user->isAdmin()) {
            abort(403, 'The Closed Work Archive tab is currently disabled by an administrator.');
        }
        $isFeatureDisabled = !$isClosedTabEnabled;

        $tab = $request->query('tab', 'projects'); // 'projects' or 'deliverables'
        $selectedBrandId = $request->query('brand_id');
        $selectedWorkflow = $request->query('workflow_type');
        $search = trim($request->query('search', ''));
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        // Accessible Brands for filter dropdown
        $brandsQuery = Brand::query()->orderBy('name');
        if (!$isGlobalViewer) {
            $brandsQuery->where(function($q) use ($userId) {
                $q->where('created_by', $userId)
                  ->orWhereHas('members', fn($m) => $m->where('users.id', $userId));
            });
        }
        $brandsList = $brandsQuery->get();
        $accessibleBrandIds = $isGlobalViewer ? null : $brandsList->pluck('id')->toArray();

        // 1. Closed Projects Query
        $projectsQuery = Project::with([
            'brand',
            'lead',
            'brandManager',
            'deliverables' => function($q) {
                $q->whereNull('parent_deliverable_id');
            }
        ])
        ->where(function($q) {
            $q->whereIn('status', ['Completed', 'Closed', 'Done'])
              ->orWhere(function($sub) {
                  // Projects where at least 1 deliverable exists and ALL non-parent deliverables are closed/done
                  $sub->whereHas('deliverables', function($d) {
                      $d->whereNull('parent_deliverable_id');
                  })->whereDoesntHave('deliverables', function($d) {
                      $d->whereNull('parent_deliverable_id')
                        ->whereNotIn('approval_stage', ['Closed', 'Close'])
                        ->where('status', '!=', 'Done');
                  });
              });
        });

        if (!$isGlobalViewer) {
            $projectsQuery->where(function($q) use ($userId, $accessibleBrandIds) {
                if (!empty($accessibleBrandIds)) {
                    $q->whereIn('brand_id', $accessibleBrandIds);
                }
                $q->orWhere('lead_id', $userId)
                  ->orWhere('brand_manager_id', $userId)
                  ->orWhere('coordinator_id', $userId)
                  ->orWhere('writer_id', $userId)
                  ->orWhere('designer_id', $userId)
                  ->orWhere('approver_id', $userId)
                  ->orWhereHas('deliverables', function($d) use ($userId) {
                      $d->where('writer_id', $userId)
                        ->orWhere('designer_id', $userId)
                        ->orWhere('approver_id', $userId)
                        ->orWhere('coordinator_id', $userId)
                        ->orWhere('brand_manager_id', $userId);
                  });
            });
        }

        if ($selectedBrandId) {
            $projectsQuery->where('brand_id', $selectedBrandId);
        }
        if ($selectedWorkflow) {
            $projectsQuery->where('workflow_type', $selectedWorkflow);
        }
        if ($search !== '') {
            $projectsQuery->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('job_number', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($dateFrom) {
            $projectsQuery->whereDate('updated_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $projectsQuery->whereDate('updated_at', '<=', $dateTo);
        }

        $closedProjects = $projectsQuery->orderBy('updated_at', 'desc')->get();

        // 2. Closed Deliverables Query
        // Non-parent (leaf deliverables or subtasks) that have reached Closed or Done
        $deliverablesQuery = Deliverable::doesntHave('subtasks')
            ->with(['project.brand', 'parent', 'writer', 'designer', 'approver', 'brandManager', 'coordinator'])
            ->where(function($q) {
                $q->whereIn('approval_stage', ['Closed', 'Close', 'closed', 'close'])
                  ->orWhere('status', 'Done');
            });

        if (!$isGlobalViewer) {
            $deliverablesQuery->where(function($q) use ($userId, $accessibleBrandIds) {
                if (!empty($accessibleBrandIds)) {
                    $q->whereHas('project', fn($p) => $p->whereIn('brand_id', $accessibleBrandIds));
                }
                $q->orWhere('writer_id', $userId)
                  ->orWhere('designer_id', $userId)
                  ->orWhere('approver_id', $userId)
                  ->orWhere('coordinator_id', $userId)
                  ->orWhere('brand_manager_id', $userId)
                  ->orWhere('further_approver_id', $userId)
                  ->orWhereHas('project', function($p) use ($userId) {
                      $p->where('brand_manager_id', $userId)
                        ->orWhere('lead_id', $userId);
                  });
            });
        }

        if ($selectedBrandId) {
            $deliverablesQuery->whereHas('project', fn($p) => $p->where('brand_id', $selectedBrandId));
        }
        if ($selectedWorkflow) {
            $deliverablesQuery->whereHas('project', fn($p) => $p->where('workflow_type', $selectedWorkflow));
        }
        if ($search !== '') {
            $deliverablesQuery->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('post_type', 'like', "%{$search}%")
                  ->orWhere('copy', 'like', "%{$search}%")
                  ->orWhereHas('project', fn($p) => $p->where('name', 'like', "%{$search}%")->orWhere('job_number', 'like', "%{$search}%"));
            });
        }
        if ($dateFrom) {
            $deliverablesQuery->whereDate('updated_at', '>=', $dateFrom);
        }
        if ($dateTo) {
            $deliverablesQuery->whereDate('updated_at', '<=', $dateTo);
        }

        // Stats counts before pagination
        $totalClosedProjects = $closedProjects->count();
        $totalClosedDeliverables = (clone $deliverablesQuery)->count();
        $closedThisMonth = (clone $deliverablesQuery)
            ->whereMonth('deliverables.updated_at', now()->month)
            ->whereYear('deliverables.updated_at', now()->year)
            ->count();

        $closedDeliverables = $deliverablesQuery->orderBy('updated_at', 'desc')->paginate(25)->withQueryString();

        return view('closed.index', compact(
            'closedProjects',
            'closedDeliverables',
            'brandsList',
            'tab',
            'selectedBrandId',
            'selectedWorkflow',
            'search',
            'dateFrom',
            'dateTo',
            'totalClosedProjects',
            'totalClosedDeliverables',
            'closedThisMonth',
            'isFeatureDisabled'
        ));
    }

    /**
     * Toggle a project's status between Completed and In Progress.
     */
    public function toggleProjectClose(Request $request, Project $project)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && !in_array($user->role, ['Operations Manager', 'Brand Manager'])) {
            abort(403, 'Unauthorized.');
        }

        $isClosed = in_array($project->status, ['Completed', 'Closed', 'Done']);
        $newStatus = $isClosed ? 'In Progress' : 'Completed';
        $project->update(['status' => $newStatus]);

        $message = $isClosed
            ? "Project '{$project->name}' has been reopened and restored to In Progress."
            : "Project '{$project->name}' has been marked as Completed and moved to the Closed Archive.";

        return back()->with('success', $message);
    }
}
