<?php

use Illuminate\Support\Facades\Route;
use Ibekzod\VisualReportBuilder\Http\Controllers\TemplateController;
use Ibekzod\VisualReportBuilder\Http\Controllers\BuilderController;
use Ibekzod\VisualReportBuilder\Http\Controllers\DashboardController;

// Build middleware stack based on config
$apiMiddleware = ['api'];
$apiMiddlewareConfig = config('visual-report-builder.auth.api_middleware', 'auth:sanctum');

// Parse middleware - can be string (single or comma-separated) or array
if (is_string($apiMiddlewareConfig)) {
    if (!empty(trim($apiMiddlewareConfig))) {
        // Handle comma-separated middleware
        $parts = array_map('trim', explode(',', $apiMiddlewareConfig));
        $apiMiddleware = array_merge($apiMiddleware, $parts);
    }
} elseif (is_array($apiMiddlewareConfig)) {
    $apiMiddleware = array_merge($apiMiddleware, array_filter($apiMiddlewareConfig));
}

Route::middleware($apiMiddleware)->prefix('api/visual-reports')->name('visual-reports.api.')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Template endpoints (Template-Based Execution)
    Route::get('templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('templates/{template}', [TemplateController::class, 'show'])->name('templates.show');

    // Execute template with filters
    Route::post('templates/{template}/execute', [TemplateController::class, 'execute'])->name('templates.execute');

    // Export template data directly (without saving)
    Route::post('templates/{template}/export/{format}', [TemplateController::class, 'exportDirect'])->name('templates.export');

    // Saved report results
    Route::post('templates/{template}/save', [TemplateController::class, 'saveResult'])->name('results.store');
    Route::get('templates/{template}/saved', [TemplateController::class, 'savedReports'])->name('results.saved');
    Route::get('results/{result}', [TemplateController::class, 'loadResult'])->name('results.show');
    Route::delete('results/{result}', [TemplateController::class, 'deleteResult'])->name('results.destroy');

    // Favorite/unfavorite saved reports
    Route::post('results/{result}/favorite', [TemplateController::class, 'toggleFavorite'])->name('results.favorite');

    // Export saved report
    Route::post('results/{result}/export/{format}', [TemplateController::class, 'export'])->name('results.export');

    // Share report result
    Route::post('results/{result}/share', [TemplateController::class, 'share'])->name('results.share');
    Route::post('results/{result}/unshare', [TemplateController::class, 'unshare'])->name('results.unshare');

    // Builder endpoints (Drag-and-Drop Template Creation)
    Route::get('models', [BuilderController::class, 'models'])->name('builder.models');
    Route::get('models/{model}/metadata', [BuilderController::class, 'modelMetadata'])->name('builder.metadata');
    Route::get('models/{model}/dimensions', [BuilderController::class, 'dimensions'])->name('builder.dimensions');
    Route::get('models/{model}/metrics', [BuilderController::class, 'metrics'])->name('builder.metrics');
    Route::get('models/{model}/relationships', [BuilderController::class, 'relationships'])->name('builder.relationships');
    Route::post('preview', [BuilderController::class, 'preview'])->name('builder.preview');
    Route::post('builder/save-template', [BuilderController::class, 'saveTemplate'])->name('builder.save-template');
});
