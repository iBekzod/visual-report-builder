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
        // Middleware is now configured at route level via config('visual-report-builder.auth.web_middleware')
    }

    /**
     * Get dashboard data with statistics and recent activity
     * Works both with and without authentication
     */
    public function index(Request $request)
    {
        $authEnabled = config('visual-report-builder.auth.enabled', false);
        $userId = auth()->id();

        // If auth is enabled but user is not authenticated, return error
        if ($authEnabled && !$userId) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 401);
        }

        // Build statistics based on auth status
        if ($userId) {
            // Authenticated user - show personalized data
            $reportsCount = Report::where('user_id', $userId)->count();
            $sharedReportsCount = Report::whereHas('shares', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->count();
            $resultsCount = ReportResult::where('user_id', $userId)->count();

            // Get recent reports for user
            $recentReports = Report::where('user_id', $userId)
                ->with(['user'])
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'description', 'user_id', 'created_at']);

            // Get favorite reports for user
            $favoriteResults = ReportResult::where('user_id', $userId)
                ->where('is_favorite', true)
                ->with(['reportTemplate'])
                ->latest()
                ->limit(5)
                ->get(['id', 'name', 'report_template_id', 'view_type', 'created_at']);

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
        } else {
            // Guest user - show only public/general data
            $reportsCount = 0;
            $sharedReportsCount = 0;
            $resultsCount = 0;
            $recentReports = [];
            $favoriteResults = [];
            $sharedWithMe = [];
        }

        // Public templates are available to everyone
        $templatesCount = ReportTemplate::active()->count();

        // Get popular templates (public)
        $popularTemplates = ReportTemplate::active()
            ->public()
            ->withCount('results')
            ->orderByDesc('results_count')
            ->limit(5)
            ->get(['id', 'name', 'icon', 'category']);

        return response()->json([
            'authenticated' => (bool) $userId,
            'auth_enabled' => $authEnabled,
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
