<?php

namespace Ibekzod\VisualReportBuilder\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Ibekzod\VisualReportBuilder\Models\Report;
use Ibekzod\VisualReportBuilder\Models\ReportTemplate;
use Ibekzod\VisualReportBuilder\Models\ReportResult;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get dashboard data with statistics and recent activity
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Get statistics
        $reportsCount = Report::where('user_id', $userId)->count();
        $sharedReportsCount = Report::whereHas('shares', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->count();
        $templatesCount = ReportTemplate::active()->count();
        $resultsCount = ReportResult::where('user_id', $userId)->count();

        // Get recent reports
        $recentReports = Report::where('user_id', $userId)
            ->with(['user'])
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'description', 'user_id', 'created_at']);

        // Get favorite reports
        $favoriteResults = ReportResult::where('user_id', $userId)
            ->where('is_favorite', true)
            ->with(['reportTemplate'])
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'report_template_id', 'view_type', 'created_at']);

        // Get popular templates
        $popularTemplates = ReportTemplate::active()
            ->public()
            ->withCount('results')
            ->orderByDesc('results_count')
            ->limit(5)
            ->get(['id', 'name', 'icon', 'category']);

        // Get shared with me
        $sharedWithMe = Report::whereHas('shares', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
            ->with(['user', 'shares' => function ($q) use ($userId) {
                $q->where('user_id', $userId);
            }])
            ->latest()
            ->limit(5)
            ->get(['id', 'name', 'user_id', 'created_at']);

        return response()->json([
            'statistics' => [
                'total_reports' => $reportsCount,
                'shared_reports' => $sharedReportsCount,
                'total_templates' => $templatesCount,
                'total_results' => $resultsCount,
            ],
            'recent_reports' => $recentReports,
            'favorite_results' => $favoriteResults,
            'popular_templates' => $popularTemplates,
            'shared_with_me' => $sharedWithMe,
        ]);
    }
}
