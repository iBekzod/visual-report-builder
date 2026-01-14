<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->prefix('visual-reports')->name('visual-reports.')->group(function () {
    // Main dashboard - Template-based reporting with 3-column layout
    Route::get('/', function () {
        return view('visual-report-builder::dashboard');
    })->name('dashboard');

    // Builder - Drag-and-drop template creation
    Route::get('/builder', function () {
        // Check permission from config
        $permission = config('visual-report-builder.permissions.create_templates', 'all');

        if ($permission !== 'all') {
            $user = auth()->user();

            // Check if user has required role
            if (method_exists($user, 'hasRole')) {
                if ($permission === 'admin' && !$user->hasRole('admin')) {
                    abort(403, 'Only admins can create templates.');
                } elseif ($permission !== 'admin' && !$user->hasRole($permission)) {
                    abort(403, "Only users with the '{$permission}' role can create templates.");
                }
            } else {
                // Fallback: check if user is admin via common attributes
                if ($permission === 'admin' && !($user->is_admin ?? false)) {
                    abort(403, 'Only admins can create templates.');
                }
            }
        }

        return view('visual-report-builder::builder');
    })->name('builder');
});
