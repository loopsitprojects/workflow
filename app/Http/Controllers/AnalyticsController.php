<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use App\Services\FeatureManager;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    /**
     * Display the analytics and reporting dashboard.
     */
    public function index(Request $request)
    {
        $isAnalyticsEnabled = FeatureManager::isAnalyticsEnabled();

        // If disabled and not admin, restrict access
        if (!$isAnalyticsEnabled && !auth()->user()->isAdmin()) {
            return redirect()->route('dashboard')->with('error', 'The Analytics & Reports dashboard is currently disabled by an administrator.');
        }

        $currentUser = auth()->user();
        $analytics = $this->analyticsService->getAnalyticsData($request->all(), $currentUser);

        return view('analytics.index', array_merge($analytics, [
            'isFeatureDisabled' => !$isAnalyticsEnabled,
        ]));
    }

    /**
     * Export analytics data to CSV.
     */
    public function exportCsv(Request $request)
    {
        if (!FeatureManager::isAnalyticsEnabled() && !auth()->user()->isAdmin()) {
            abort(403, 'Analytics export is currently disabled.');
        }

        $currentUser = auth()->user();
        return $this->analyticsService->exportDeliverablesCsv($request->all(), $currentUser);
    }

    /**
     * Display or return all details of a single deliverable for reporting.
     */
    public function showDeliverable(Request $request, \App\Models\Deliverable $deliverable)
    {
        $isAnalyticsEnabled = FeatureManager::isAnalyticsEnabled();

        // If disabled and not admin, restrict access
        if (!$isAnalyticsEnabled && !auth()->user()->isAdmin()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Analytics is currently disabled.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'The Analytics & Reports dashboard is currently disabled by an administrator.');
        }

        $currentUser = auth()->user();

        if (!$this->analyticsService->canUserViewDeliverable($currentUser, $deliverable)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => 'Unauthorized to view this deliverable.'], 403);
            }
            abort(403, 'Unauthorized access to deliverable details.');
        }

        $deliverableData = $this->analyticsService->formatDeliverableDetails($deliverable);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($deliverableData);
        }

        return view('analytics.deliverable_detail', compact('deliverableData', 'deliverable'));
    }
}
