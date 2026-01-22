<?php

use Illuminate\Support\Facades\Route;
use Ibekzod\VisualReportBuilder\Http\Controllers\TemplateController;
use Ibekzod\VisualReportBuilder\Http\Controllers\BuilderController;
use Ibekzod\VisualReportBuilder\Http\Controllers\DashboardController;

Route::middleware(['api', 'auth:sanctum'])->prefix('api/visual-reports')->name('visual-reports.')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Template endpoints (Template-Based Execution)
    Route::get('templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('templates/{template}', [TemplateController::class, 'show'])->name('templates.show');

    // Execute template with filters
    Route::post('templates/{template}/execute', [TemplateController::class, 'execute'])->name('templates.execute');

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
