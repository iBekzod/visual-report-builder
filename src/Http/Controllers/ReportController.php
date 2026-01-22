<?php

namespace Ibekzod\VisualReportBuilder\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Ibekzod\VisualReportBuilder\Models\Report;
use Ibekzod\VisualReportBuilder\Http\Resources\ReportResource;

class ReportController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get list of reports
     */
    public function index(Request $request)
    {
        $reports = Report::query()
            ->where(function ($q) use ($request) {
                $q->where('user_id', auth()->id())
                    ->orWhereHas('shares', function ($sq) {
                        $sq->where('user_id', auth()->id());
                    });
            })
            ->with(['user', 'shares'])
            ->paginate(15);

        return ReportResource::collection($reports);
    }

    /**
     * Create a new report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'model' => 'required|string',
            'configuration' => 'required|array',
        ]);

        $report = Report::create(array_merge(
            $validated,
            ['user_id' => auth()->id()]
        ));

        return new ReportResource($report);
    }

    /**
     * Get single report
     */
    public function show(Report $report)
    {
        $this->authorize('view', $report);
        return new ReportResource($report->load(['user', 'shares']));
    }

    /**
     * Update report
     */
    public function update(Request $request, Report $report)
    {
        $this->authorize('update', $report);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'configuration' => 'sometimes|array',
            'view_options' => 'sometimes|nullable|array',
        ]);

        $report->update($validated);

        return new ReportResource($report);
    }

    /**
     * Delete report
     */
    public function destroy(Report $report)
    {
        $this->authorize('delete', $report);
        $report->delete();

        return response()->noContent();
    }

    /**
     * Execute report
     */
    public function execute(Request $request, Report $report)
    {
        $this->authorize('view', $report);

        try {
            $result = $report->execute();

            return response()->json([
                'success' => true,
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to execute report: ' . $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Share report with user
     */
    public function share(Request $request, Report $report)
    {
        $this->authorize('share', $report);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'can_edit' => 'boolean',
            'can_share' => 'boolean',
        ]);

        $report->shareWith(
            $validated['user_id'],
            $validated['can_edit'] ?? false,
            $validated['can_share'] ?? false
        );

        return response()->json(['message' => 'Report shared successfully']);
    }

    /**
     * Stop sharing report
     */
    public function unshare(Request $request, Report $report)
    {
        $this->authorize('share', $report);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $report->stopSharingWith($validated['user_id']);

        return response()->json(['message' => 'Report sharing stopped']);
    }
}
