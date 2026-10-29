<?php

namespace App\Services;

use App\Models\Deliverable;
use App\Models\Brand;
use App\Models\Project;
use App\Models\User;
use App\Models\DeliverableApproval;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsService
{
    /**
     * Gather simplified analytics metrics: Active, Completed, Closed filtered by date.
     */
    public function getAnalyticsData(array $filters = [], ?User $currentUser = null): array
    {
        $currentUser = $currentUser ?: auth()->user();
        $isManager = $currentUser ? $currentUser->isManager() : true;

        $period = $filters['period'] ?? 'all';
        $brandId = !empty($filters['brand_id']) ? (int) $filters['brand_id'] : null;
        $projectId = !empty($filters['project_id']) ? (int) $filters['project_id'] : null;
        $workflowType = !empty($filters['workflow_type']) ? $filters['workflow_type'] : null;
        $stateFilter = !empty($filters['state']) ? strtolower($filters['state']) : (!empty($filters['status']) ? strtolower($filters['status']) : null);

        // If user is NOT a manager, strictly enforce individual mode for their own user ID
        if (!$isManager && $currentUser) {
            $userId = $currentUser->id;
            $targetUserId = $currentUser->id;
        } else {
            $userId = !empty($filters['user_id']) ? (int) $filters['user_id'] : null;
            $targetUserId = $userId ?: ($currentUser ? $currentUser->id : null);
        }

        // Base Query: Leaf deliverables (actual work deliverables)
        $query = Deliverable::doesntHave('subtasks')
            ->with([
                'project.brand',
                'writer',
                'approver',
                'brandManager',
                'coordinator',
                'designer',
                'creator',
                'revisionsHistory.user',
                'approvalsHistory.user',
                'artworkReviews',
            ]);

        // Apply Date / Period Filter
        $startDate = null;
        $endDate = null;

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $startDate = Carbon::parse($filters['start_date'])->startOfDay();
            $endDate = Carbon::parse($filters['end_date'])->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($period === 'today') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($period === 'yesterday') {
            $startDate = now()->subDay()->startOfDay();
            $endDate = now()->subDay()->endOfDay();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        } elseif ($period === '7d') {
            $startDate = now()->subDays(7)->startOfDay();
            $query->where('created_at', '>=', $startDate);
        } elseif ($period === '30d') {
            $startDate = now()->subDays(30)->startOfDay();
            $query->where('created_at', '>=', $startDate);
        } elseif ($period === 'month') {
            $startDate = now()->startOfMonth();
            $query->where('created_at', '>=', $startDate);
        } elseif ($period === 'last_month') {
            $startDate = now()->subMonth()->startOfMonth();
            $endDate = now()->subMonth()->endOfMonth();
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Apply Brand Filter
        if ($brandId) {
            $query->whereHas('project', function ($q) use ($brandId) {
                $q->where('brand_id', $brandId);
            });
        }

        // Apply Project Filter
        if ($projectId) {
            $query->where('project_id', $projectId);
        }

        // Apply Workflow Type Filter
        if ($workflowType) {
            $query->whereHas('project', function ($q) use ($workflowType) {
                $q->where('workflow_type', $workflowType);
            });
        }

        // Apply User Filter (for individual non-managers or when filtered by user)
        if ($userId) {
            $query->where(function ($q) use ($userId) {
                $q->where('writer_id', $userId)
                  ->orWhere('designer_id', $userId)
                  ->orWhere('approver_id', $userId)
                  ->orWhere('brand_manager_id', $userId)
                  ->orWhere('coordinator_id', $userId)
                  ->orWhere('created_by', $userId);
            });
        }

        $allPeriodDeliverables = $query->orderByDesc('created_at')->get();

        // Categorize into Active and Completed (Close means completed)
        $completedDeliverables = $allPeriodDeliverables->filter(function ($d) {
            return $d->status === 'Done' || in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
        });

        $activeDeliverables = $allPeriodDeliverables->filter(function ($d) {
            $isClosed = in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
            return $d->status !== 'Done' && !$isClosed;
        });

        $now = now();
        $overdueDeliverables = $allPeriodDeliverables->filter(function ($d) use ($now) {
            $isDone = ($d->status === 'Done' || in_array(strtolower((string) $d->approval_stage), ['closed', 'close']));
            return !$isDone && !empty($d->deadline) && Carbon::parse($d->deadline)->isPast();
        });

        $totalCount = $allPeriodDeliverables->count();
        $activeCount = $activeDeliverables->count();
        $completedCount = $completedDeliverables->count();
        $closedCount = $completedCount; // Close means completed
        $overdueCount = $overdueDeliverables->count();
        $completionRate = $totalCount > 0 ? round(($completedCount / $totalCount) * 100, 1) : 0;

        // Apply state quick-filter to the deliverables list if selected
        if ($stateFilter === 'active') {
            $filteredDeliverables = $activeDeliverables;
        } elseif ($stateFilter === 'completed' || $stateFilter === 'done' || $stateFilter === 'closed') {
            $filteredDeliverables = $completedDeliverables;
        } elseif ($stateFilter === 'overdue') {
            $filteredDeliverables = $overdueDeliverables;
        } else {
            $filteredDeliverables = $allPeriodDeliverables;
        }

        // Brand-Wise Report
        $brandPerformance = $this->buildBrandPerformance($allPeriodDeliverables);

        // Project-Wise Report
        $projectPerformance = $this->buildProjectPerformance($allPeriodDeliverables, $brandId);

        // Individual Report
        $targetUser = $targetUserId ? User::find($targetUserId) : $currentUser;
        $individualReport = $targetUser ? $this->buildIndividualReport($targetUser, $allPeriodDeliverables, $stateFilter) : null;

        // Visual Charts & Graphs Data
        $chartsData = $this->buildChartsData($allPeriodDeliverables, $period, $startDate, $endDate);

        // Dropdown filter options
        $brandsList = Brand::orderBy('name')->get(['id', 'name', 'slug', 'logo_url']);
        $projectsList = Project::orderBy('name')->get(['id', 'name', 'brand_id', 'workflow_type']);
        $usersList = User::orderBy('name')->get(['id', 'name', 'role']);

        return [
            'isManager' => $isManager,
            'currentUser' => $currentUser,
            'targetUser' => $targetUser,
            'filters' => [
                'period' => $period,
                'state' => $stateFilter,
                'brand_id' => $brandId,
                'project_id' => $projectId,
                'workflow_type' => $workflowType,
                'user_id' => $userId,
                'start_date' => $filters['start_date'] ?? null,
                'end_date' => $filters['end_date'] ?? null,
            ],
            'kpis' => [
                'total_deliverables' => $totalCount,
                'active_deliverables' => $activeCount,
                'completed_deliverables' => $completedCount,
                'closed_deliverables' => $closedCount,
                'overdue_count' => $overdueCount,
                'completion_rate' => $completionRate,
            ],
            'charts' => $chartsData,
            'deliverables' => $filteredDeliverables,
            'all_deliverables_count' => $totalCount,
            'brand_performance' => $brandPerformance,
            'project_performance' => $projectPerformance,
            'individual_report' => $individualReport,
            'brands_list' => $brandsList,
            'projects_list' => $projectsList,
            'users_list' => $usersList,
        ];
    }

    /**
     * Build structured data for visual diagrams, graphs, and pie charts.
     */
    public function buildChartsData(Collection $allDeliverables, string $period, ?Carbon $startDate, ?Carbon $endDate): array
    {
        // 1. Status Pie Chart (Close means completed)
        $completedCount = $allDeliverables->filter(function ($d) {
            return $d->status === 'Done' || in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
        })->count();

        $activeCount = $allDeliverables->filter(function ($d) {
            $isClosed = in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
            return $d->status !== 'Done' && !$isClosed;
        })->count();

        $total = $allDeliverables->count();

        $statusPie = [
            'labels' => ['Active', 'Completed'],
            'data' => [$activeCount, $completedCount],
            'colors' => ['#3b82f6', '#10b981'],
            'percentages' => [
                $total > 0 ? round(($activeCount / $total) * 100) : 0,
                $total > 0 ? round(($completedCount / $total) * 100) : 0,
            ],
            'total' => $total,
        ];

        // 2. Timeline Activity Graph (Opened vs Closed over dates)
        $effectiveStart = $startDate ? $startDate->copy()->startOfDay() : ($allDeliverables->min('created_at') ? Carbon::parse($allDeliverables->min('created_at'))->startOfDay() : now()->subDays(14)->startOfDay());
        $effectiveEnd = $endDate ? $endDate->copy()->endOfDay() : now()->endOfDay();

        $diffDays = max(1, (int) $effectiveStart->diffInDays($effectiveEnd));

        $timelineLabels = [];
        $openedData = [];
        $closedData = [];

        if ($diffDays <= 14) {
            $cursor = $effectiveStart->copy();
            while ($cursor->lte($effectiveEnd)) {
                $dayStr = $cursor->format('M d');
                $dateKey = $cursor->format('Y-m-d');
                $timelineLabels[] = $dayStr;

                $openedData[] = $allDeliverables->filter(function ($d) use ($dateKey) {
                    return $d->created_at && Carbon::parse($d->created_at)->format('Y-m-d') === $dateKey;
                })->count();

                $closedData[] = $allDeliverables->filter(function ($d) use ($dateKey) {
                    $closedAt = $this->resolveClosedDate($d);
                    return $closedAt && $closedAt->format('Y-m-d') === $dateKey;
                })->count();

                $cursor->addDay();
            }
        } else {
            $stepDays = max(2, (int) ceil($diffDays / 8));
            $cursor = $effectiveStart->copy();
            while ($cursor->lte($effectiveEnd)) {
                $nextCursor = $cursor->copy()->addDays($stepDays)->endOfDay();
                if ($nextCursor->gt($effectiveEnd)) {
                    $nextCursor = $effectiveEnd->copy();
                }

                $rangeStr = $cursor->format('M d');
                $timelineLabels[] = $rangeStr;

                $openedData[] = $allDeliverables->filter(function ($d) use ($cursor, $nextCursor) {
                    if (!$d->created_at) return false;
                    $c = Carbon::parse($d->created_at);
                    return $c->gte($cursor) && $c->lte($nextCursor);
                })->count();

                $closedData[] = $allDeliverables->filter(function ($d) use ($cursor, $nextCursor) {
                    $closedAt = $this->resolveClosedDate($d);
                    return $closedAt && $closedAt->gte($cursor) && $closedAt->lte($nextCursor);
                })->count();

                $cursor = $nextCursor->copy()->addSecond();
            }
        }

        $timelineGraph = [
            'labels' => $timelineLabels,
            'opened' => $openedData,
            'closed' => $closedData,
        ];

        // 3. Brand Workload Bar Chart (Top 6 Brands)
        $brandGroups = $allDeliverables->groupBy(fn($d) => $d->project?->brand?->name ?? 'Unassigned');
        $brandLabels = [];
        $brandActive = [];
        $brandCompleted = [];

        foreach ($brandGroups->sortByDesc->count()->take(6) as $brandName => $bDeliverables) {
            $brandLabels[] = \Illuminate\Support\Str::limit($brandName, 14);
            $bActive = $bDeliverables->filter(function ($d) {
                $isClosed = in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
                return $d->status !== 'Done' && !$isClosed;
            })->count();
            $bDone = $bDeliverables->count() - $bActive;

            $brandActive[] = $bActive;
            $brandCompleted[] = $bDone;
        }

        $brandBar = [
            'labels' => $brandLabels,
            'active' => $brandActive,
            'completed' => $brandCompleted,
        ];

        // 4. Deliverable Format / Types Pie Chart
        $typeGroups = $allDeliverables->groupBy(function ($d) {
            return $d->subtask_type ?: ($d->task_type ?: 'Deliverable');
        })->sortByDesc->count();

        $typeLabels = [];
        $typeData = [];
        $palette = ['#3b82f6', '#8b5cf6', '#ec4899', '#f59e0b', '#10b981', '#06b6d4', '#64748b'];
        $typeColors = [];

        $i = 0;
        foreach ($typeGroups->take(6) as $typeName => $items) {
            $typeLabels[] = $typeName;
            $typeData[] = $items->count();
            $typeColors[] = $palette[$i % count($palette)];
            $i++;
        }

        $typesPie = [
            'labels' => $typeLabels,
            'data' => $typeData,
            'colors' => $typeColors,
        ];

        return [
            'status_pie' => $statusPie,
            'timeline_graph' => $timelineGraph,
            'brand_bar' => $brandBar,
            'types_pie' => $typesPie,
        ];
    }

    /**
     * Build Brand-Wise Report: Brand, Active, Completed, Closed, Total, Rate.
     */
    public function buildBrandPerformance(Collection $deliverables): array
    {
        $brands = Brand::withCount('projects')->orderBy('name')->get();
        $deliverablesByBrand = $deliverables->groupBy(fn($d) => $d->project?->brand_id);

        $brandStats = [];

        foreach ($brands as $brand) {
            $bDeliverables = $deliverablesByBrand->get($brand->id, collect());
            $tot = $bDeliverables->count();

            $closed = $bDeliverables->filter(function ($d) {
                return in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
            })->count();

            $completed = $bDeliverables->filter(function ($d) {
                return $d->status === 'Done' || in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
            })->count();

            $active = $bDeliverables->filter(function ($d) {
                $isClosed = in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
                return $d->status !== 'Done' && !$isClosed;
            })->count();

            $overdue = $bDeliverables->filter(function ($d) {
                $isDone = ($d->status === 'Done' || in_array(strtolower((string) $d->approval_stage), ['closed', 'close']));
                return !$isDone && !empty($d->deadline) && Carbon::parse($d->deadline)->isPast();
            })->count();

            $rate = $tot > 0 ? round(($completed / $tot) * 100, 1) : 0;

            $brandStats[] = [
                'brand' => $brand,
                'projects_count' => $brand->projects_count,
                'total_deliverables' => $tot,
                'active_deliverables' => $active,
                'completed_deliverables' => $completed,
                'closed_deliverables' => $closed,
                'overdue_deliverables' => $overdue,
                'completion_rate' => $rate,
            ];
        }

        usort($brandStats, fn($a, $b) => $b['total_deliverables'] <=> $a['total_deliverables']);

        return $brandStats;
    }

    /**
     * Build Project-Wise Report: Project, Brand, Active, Completed, Closed, Total.
     */
    public function buildProjectPerformance(Collection $deliverables, ?int $brandId = null): array
    {
        $projectQuery = Project::with(['brand', 'lead']);
        if ($brandId) {
            $projectQuery->where('brand_id', $brandId);
        }
        $allProjects = $projectQuery->orderBy('name')->get();

        $deliverablesByProject = $deliverables->groupBy('project_id');
        $projectStats = [];

        foreach ($allProjects as $project) {
            $pDeliverables = $deliverablesByProject->get($project->id, collect());
            $tot = $pDeliverables->count();

            $closed = $pDeliverables->filter(function ($d) {
                return in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
            })->count();

            $completed = $pDeliverables->filter(function ($d) {
                return $d->status === 'Done' || in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
            })->count();

            $active = $pDeliverables->filter(function ($d) {
                $isClosed = in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
                return $d->status !== 'Done' && !$isClosed;
            })->count();

            $overdue = $pDeliverables->filter(function ($d) {
                $isDone = ($d->status === 'Done' || in_array(strtolower((string) $d->approval_stage), ['closed', 'close']));
                return !$isDone && !empty($d->deadline) && Carbon::parse($d->deadline)->isPast();
            })->count();

            $rate = $tot > 0 ? round(($completed / $tot) * 100, 1) : ($project->progress ?: 0);

            $projectStats[] = [
                'project' => $project,
                'brand' => $project->brand,
                'total_deliverables' => $tot,
                'active_deliverables' => $active,
                'completed_deliverables' => $completed,
                'closed_deliverables' => $closed,
                'overdue_deliverables' => $overdue,
                'completion_rate' => $rate,
                'status' => $project->status ?: 'Active',
                'workflow_type' => $project->workflow_type ?: 'retainer',
                'deadline' => $project->deadline,
                'lead' => $project->lead,
            ];
        }

        usort($projectStats, function ($a, $b) {
            return ($b['active_deliverables'] <=> $a['active_deliverables'])
                ?: ($b['total_deliverables'] <=> $a['total_deliverables']);
        });

        return $projectStats;
    }

    /**
     * Build Individual Report: Active, Completed, Closed, Projects breakdown.
     */
    public function buildIndividualReport(User $user, Collection $allDeliverables, ?string $stateFilter = null): array
    {
        $userId = $user->id;
        $userName = strtolower(trim($user->name));

        $userDeliverables = $allDeliverables->filter(function ($d) use ($userId, $userName) {
            return $d->writer_id === $userId
                || $d->designer_id === $userId
                || $d->approver_id === $userId
                || $d->coordinator_id === $userId
                || $d->brand_manager_id === $userId
                || $d->created_by === $userId
                || (!empty($d->assignee_name) && strtolower(trim($d->assignee_name)) === $userName)
                || $d->approvalsHistory->contains('user_id', $userId);
        });

        $tot = $userDeliverables->count();

        $closed = $userDeliverables->filter(function ($d) {
            return in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
        });

        $completed = $userDeliverables->filter(function ($d) {
            return $d->status === 'Done' || in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
        });

        $active = $userDeliverables->filter(function ($d) {
            $isClosed = in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
            return $d->status !== 'Done' && !$isClosed;
        });

        $now = now();
        $overdue = $userDeliverables->filter(function ($d) use ($now) {
            $isDone = ($d->status === 'Done' || in_array(strtolower((string) $d->approval_stage), ['closed', 'close']));
            return !$isDone && !empty($d->deadline) && Carbon::parse($d->deadline)->isPast();
        });

        $activeCount = $active->count();
        $completedCount = $completed->count();
        $closedCount = $closed->count();
        $overdueCount = $overdue->count();
        $rate = $tot > 0 ? round(($completedCount / $tot) * 100, 1) : 0;

        // Apply state filter if provided
        if ($stateFilter === 'active') {
            $displayDeliverables = $active;
        } elseif ($stateFilter === 'completed' || $stateFilter === 'done') {
            $displayDeliverables = $completed;
        } elseif ($stateFilter === 'closed') {
            $displayDeliverables = $closed;
        } elseif ($stateFilter === 'overdue') {
            $displayDeliverables = $overdue;
        } else {
            $displayDeliverables = $userDeliverables;
        }

        // Projects breakdown with Active, Completed, Closed counts
        $projectsBreakdown = $userDeliverables->groupBy(fn($d) => $d->project_id)->map(function ($group) {
            $first = $group->first();
            $p = $first->project;

            $closedCnt = $group->filter(fn($d) => in_array(strtolower((string)$d->approval_stage), ['closed', 'close']))->count();
            $completedCnt = $group->filter(fn($d) => $d->status === 'Done' || in_array(strtolower((string)$d->approval_stage), ['closed', 'close']))->count();
            $activeCnt = $group->filter(function ($d) {
                return $d->status !== 'Done' && !in_array(strtolower((string)$d->approval_stage), ['closed', 'close']);
            })->count();

            return [
                'project' => $p,
                'brand_name' => $p?->brand?->name ?? 'N/A',
                'total_tasks' => $group->count(),
                'active_tasks' => $activeCnt,
                'completed_tasks' => $completedCnt,
                'closed_tasks' => $closedCnt,
            ];
        })->values();

        return [
            'user' => $user,
            'kpis' => [
                'total_tasks' => $tot,
                'active_tasks' => $activeCount,
                'completed_tasks' => $completedCount,
                'closed_tasks' => $closedCount,
                'overdue_tasks' => $overdueCount,
                'completion_rate' => $rate,
            ],
            'projects_breakdown' => $projectsBreakdown,
            'deliverables' => $displayDeliverables,
        ];
    }

    /**
     * Stream CSV export of deliverables matching current analytics filters.
     */
    public function exportDeliverablesCsv(array $filters = [], ?User $currentUser = null): StreamedResponse
    {
        $data = $this->getAnalyticsData($filters, $currentUser);
        $deliverables = $data['deliverables'];

        $filename = 'loops_analytics_report_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($deliverables) {
            $handle = fopen('php://output', 'w');
            
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($handle, [
                'Deliverable ID',
                'Title',
                'Brand',
                'Project',
                'Workflow Type',
                'State',
                'Approval Stage',
                'Status',
                'Priority',
                'Assignee',
                'Open Date',
                'Due Date',
                'Closed Date',
            ]);

            foreach ($deliverables as $d) {
                $isClosed = in_array(strtolower((string) $d->approval_stage), ['closed', 'close']);
                $isCompleted = ($d->status === 'Done' || $isClosed);
                $state = $isClosed ? 'Closed' : ($isCompleted ? 'Completed' : 'Active');
                $closedDate = $this->resolveClosedDate($d);

                fputcsv($handle, [
                    $d->id,
                    $d->title,
                    $d->project?->brand?->name ?? 'N/A',
                    $d->project?->name ?? 'N/A',
                    ucfirst($d->project?->workflow_type ?? 'N/A'),
                    $state,
                    $d->approval_stage ?? 'N/A',
                    $d->status,
                    $d->priority ?? 'Medium',
                    $d->writer?->name ?? $d->designer?->name ?? $d->assignee_name ?? 'Unassigned',
                    $d->created_at ? Carbon::parse($d->created_at)->format('Y-m-d') : 'None',
                    $d->deadline ? Carbon::parse($d->deadline)->format('Y-m-d') : 'None',
                    $closedDate ? $closedDate->format('Y-m-d') : '—',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Determine if a user has access to view a deliverable's details.
     */
    public function canUserViewDeliverable(User $user, Deliverable $deliverable): bool
    {
        if ($user->isAdmin() || $user->isManager()) {
            return true;
        }

        if (
            $deliverable->writer_id === $user->id ||
            $deliverable->designer_id === $user->id ||
            $deliverable->approver_id === $user->id ||
            $deliverable->further_approver_id === $user->id ||
            $deliverable->brand_manager_id === $user->id ||
            $deliverable->coordinator_id === $user->id ||
            $deliverable->assignee_name === $user->name
        ) {
            return true;
        }

        if ($deliverable->project && $deliverable->project->members()->where('users.id', $user->id)->exists()) {
            return true;
        }

        if ($deliverable->project?->brand && $deliverable->project->brand->members()->where('users.id', $user->id)->exists()) {
            return true;
        }

        return false;
    }

    /**
     * Resolve the date a deliverable was closed or completed.
     */
    public function resolveClosedDate(Deliverable $deliverable): ?Carbon
    {
        $isClosed = in_array(strtolower((string) $deliverable->approval_stage), ['closed', 'close']);
        $isCompleted = ($deliverable->status === 'Done' || $isClosed);

        if (!$isCompleted && !$isClosed) {
            return null;
        }

        if ($deliverable->relationLoaded('approvalsHistory') && $deliverable->approvalsHistory->isNotEmpty()) {
            $closingApproval = $deliverable->approvalsHistory
                ->first(function ($appr) {
                    return in_array(strtolower((string) $appr->stage), ['closed', 'close', 'approved', 'final approval', 'scheduled']);
                });

            if ($closingApproval && $closingApproval->created_at) {
                return Carbon::parse($closingApproval->created_at);
            }

            $latest = $deliverable->approvalsHistory->sortByDesc('created_at')->first();
            if ($latest && $latest->created_at) {
                return Carbon::parse($latest->created_at);
            }
        }

        return $deliverable->updated_at ? Carbon::parse($deliverable->updated_at) : null;
    }

    /**
     * Format all details of a deliverable for read-only reporting.
     */
    public function formatDeliverableDetails(Deliverable $deliverable): array
    {
        $deliverable->load([
            'project.brand',
            'writer',
            'designer',
            'approver',
            'furtherApprover',
            'brandManager',
            'coordinator',
            'parent',
            'subtasks',
            'revisionsHistory.user',
            'revisionsHistory.fixedByUser',
            'approvalsHistory.user',
            'reassignments.fromUser',
            'reassignments.toUser',
            'reassignments.reassignedBy',
        ]);

        $isClosed = in_array(strtolower((string) $deliverable->approval_stage), ['closed', 'close']);
        $isCompleted = ($deliverable->status === 'Done' || $isClosed);
        $stateLabel = $isClosed ? 'Closed' : ($isCompleted ? 'Completed' : 'Active');

        $refFiles = $deliverable->getReferenceFilesArray();
        $refUrls = $deliverable->getReferenceUrlsArray();
        $artFiles = $deliverable->getFinalDesignsArray();
        $artUrls = $deliverable->getFinalDesignsUrlsArray();

        $formatUrl = function ($path) {
            if (empty($path)) return null;
            if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                return $path;
            }
            try {
                return \Illuminate\Support\Facades\Storage::url($path);
            } catch (\Throwable $e) {
                return '/storage/' . ltrim($path, '/');
            }
        };

        return [
            'id' => $deliverable->id,
            'title' => $deliverable->title,
            'description' => $deliverable->description,
            'status' => $deliverable->status,
            'approval_stage' => $deliverable->approval_stage,
            'state_label' => $stateLabel,
            'priority' => $deliverable->priority ?? 'Medium',
            'progress_percent' => (int) ($deliverable->progress_percent ?? 0),
            'task_type' => $deliverable->subtask_type ?? $deliverable->post_type ?? $deliverable->task_type ?? 'Deliverable',
            'flow_type' => $deliverable->flow_type ?? 'standard',
            'is_direct_design' => (bool) $deliverable->isDirectDesign(),
            'is_other_deliverable' => (bool) $deliverable->isOtherDeliverable(),
            'brand' => [
                'id' => $deliverable->project?->brand?->id,
                'name' => $deliverable->project?->brand?->name ?? 'N/A',
                'slug' => $deliverable->project?->brand?->slug,
            ],
            'project' => [
                'id' => $deliverable->project?->id,
                'name' => $deliverable->project?->name ?? 'N/A',
                'workflow_type' => $deliverable->project?->workflow_type ?? 'campaign',
            ],
            'assignee_name' => $deliverable->assignee_name,
            'team' => [
                'writer' => $deliverable->writer ? ['id' => $deliverable->writer->id, 'name' => $deliverable->writer->name, 'role' => $deliverable->writer->role, 'email' => $deliverable->writer->email] : null,
                'designer' => $deliverable->designer ? ['id' => $deliverable->designer->id, 'name' => $deliverable->designer->name, 'role' => $deliverable->designer->role, 'email' => $deliverable->designer->email] : null,
                'approver' => $deliverable->approver ? ['id' => $deliverable->approver->id, 'name' => $deliverable->approver->name, 'role' => $deliverable->approver->role, 'email' => $deliverable->approver->email] : null,
                'further_approver' => $deliverable->furtherApprover ? ['id' => $deliverable->furtherApprover->id, 'name' => $deliverable->furtherApprover->name, 'role' => $deliverable->furtherApprover->role, 'email' => $deliverable->furtherApprover->email] : null,
                'brand_manager' => $deliverable->brandManager ? ['id' => $deliverable->brandManager->id, 'name' => $deliverable->brandManager->name, 'role' => $deliverable->brandManager->role, 'email' => $deliverable->brandManager->email] : null,
                'coordinator' => $deliverable->coordinator ? ['id' => $deliverable->coordinator->id, 'name' => $deliverable->coordinator->name, 'role' => $deliverable->coordinator->role, 'email' => $deliverable->coordinator->email] : null,
            ],
            'schedule' => [
                'open_date' => $deliverable->created_at ? $deliverable->created_at->format('M d, Y') : null,
                'open_date_full' => $deliverable->created_at ? $deliverable->created_at->format('M d, Y · h:i A') : null,
                'due_date' => $deliverable->deadline ? Carbon::parse($deliverable->deadline)->format('M d, Y') : null,
                'due_date_iso' => $deliverable->deadline,
                'closed_date' => $this->resolveClosedDate($deliverable)?->format('M d, Y'),
                'closed_date_full' => $this->resolveClosedDate($deliverable)?->format('M d, Y · h:i A'),
                'is_overdue' => !$isCompleted && !empty($deliverable->deadline) && Carbon::parse($deliverable->deadline)->isPast(),
                'deadline' => $deliverable->deadline ? Carbon::parse($deliverable->deadline)->format('M d, Y') : null,
                'deadline_iso' => $deliverable->deadline,
                'designer_deadline' => $deliverable->designer_deadline ? Carbon::parse($deliverable->designer_deadline)->format('M d, Y') : null,
                'revision_deadline' => $deliverable->revision_deadline ? Carbon::parse($deliverable->revision_deadline)->format('M d, Y') : null,
                'start_date' => $deliverable->start_date ? Carbon::parse($deliverable->start_date)->format('M d, Y') : null,
                'end_date' => $deliverable->end_date ? Carbon::parse($deliverable->end_date)->format('M d, Y') : null,
                'created_at' => $deliverable->created_at ? $deliverable->created_at->format('M d, Y · h:i A') : null,
                'updated_at' => $deliverable->updated_at ? $deliverable->updated_at->format('M d, Y · h:i A') : null,
            ],
            'content' => [
                'concept' => $deliverable->concept ? html_entity_decode(strip_tags($deliverable->concept), ENT_QUOTES | ENT_HTML5, 'UTF-8') : null,
                'concept_raw' => $deliverable->concept,
                'caption' => $deliverable->caption ? html_entity_decode(strip_tags($deliverable->caption), ENT_QUOTES | ENT_HTML5, 'UTF-8') : null,
                'caption_raw' => $deliverable->caption,
                'post_copy' => $deliverable->post_copy ? html_entity_decode(strip_tags($deliverable->post_copy), ENT_QUOTES | ENT_HTML5, 'UTF-8') : null,
                'post_copy_raw' => $deliverable->post_copy,
                'notes' => $deliverable->notes,
            ],
            'assets' => [
                'reference_files' => array_values(array_filter(array_map($formatUrl, $refFiles))),
                'reference_urls' => array_values(array_filter($refUrls)),
                'reference_uploaded_by' => $deliverable->reference_uploaded_by,
                'artwork_files' => array_values(array_filter(array_map($formatUrl, $artFiles))),
                'artwork_urls' => array_values(array_filter($artUrls)),
                'work_hours' => $deliverable->work_hours ? (float) $deliverable->work_hours : null,
            ],
            'revisions' => [
                'count' => (int) ($deliverable->revisions ?? 0),
                'instructions' => $deliverable->revision_instructions,
                'history' => $deliverable->revisionsHistory->map(function ($rev) use ($formatUrl) {
                    return [
                        'id' => $rev->id,
                        'user_name' => $rev->user?->name ?? 'User',
                        'instructions' => $rev->instructions,
                        'image_url' => $rev->image_path ? $formatUrl($rev->image_path) : null,
                        'fixed_by' => $rev->fixedByUser?->name,
                        'created_at' => $rev->created_at ? $rev->created_at->format('M d, Y · h:i A') : null,
                    ];
                })->values()->all(),
            ],
            'approvals' => $deliverable->approvalsHistory->map(function ($appr) {
                return [
                    'id' => $appr->id,
                    'stage' => $appr->stage,
                    'user_name' => $appr->user?->name ?? 'System',
                    'user_role' => $appr->user?->role,
                    'hours_spent' => $appr->hours_spent ? (float) $appr->hours_spent : null,
                    'notes' => $appr->notes,
                    'created_at' => $appr->created_at ? $appr->created_at->format('M d, Y · h:i A') : null,
                ];
            })->values()->all(),
            'subtasks' => $deliverable->subtasks->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'title' => $sub->title,
                    'status' => $sub->status,
                    'approval_stage' => $sub->approval_stage,
                    'post_type' => $sub->post_type,
                    'priority' => $sub->priority,
                    'deadline' => $sub->deadline ? Carbon::parse($sub->deadline)->format('M d, Y') : null,
                ];
            })->values()->all(),
            'parent' => $deliverable->parent ? [
                'id' => $deliverable->parent->id,
                'title' => $deliverable->parent->title,
                'post_type' => $deliverable->parent->post_type,
            ] : null,
        ];
    }
}
