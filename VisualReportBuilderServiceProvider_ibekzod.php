<?php

namespace Ibekzod\VisualReportBuilder;

use Illuminate\Support\ServiceProvider;
use Ibekzod\VisualReportBuilder\Services\ReportBuilder;
use Ibekzod\VisualReportBuilder\Services\QueryBuilder;
use Ibekzod\VisualReportBuilder\Services\PivotEngine;
use Ibekzod\VisualReportBuilder\Services\ExporterFactory;
use Ibekzod\VisualReportBuilder\Services\DataSourceManager;

class VisualReportBuilderServiceProvider extends ServiceProvider
{
    /**
     * Register any package services
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/visual-report-builder.php',
            'visual-report-builder'
        );

        // Register core services
        $this->app->singleton(QueryBuilder::class, function ($app) {
            return new QueryBuilder();
        });

        $this->app->singleton(PivotEngine::class, function ($app) {
            return new PivotEngine();
        });

        $this->app->singleton(ExporterFactory::class, function ($app) {
            return new ExporterFactory();
        });

        $this->app->singleton(DataSourceManager::class, function ($app) {
            return new DataSourceManager();
        });

        $this->app->singleton(ReportBuilder::class, function ($app) {
            return new ReportBuilder(
                $app->make(QueryBuilder::class),
                $app->make(PivotEngine::class),
                $app->make(DataSourceManager::class),
                $app->make(ExporterFactory::class)
            );
        });

        // Bind to container for easy access
        $this->app->bind('visual-report-builder', function ($app) {
            return $app->make(ReportBuilder::class);
        });
    }

    /**
     * Bootstrap any package services
     */
    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'visual-report-builder');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // Load translations
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'visual-report-builder');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'visual-report-builder-migrations');

        // Publish config
        $this->publishes([
            __DIR__.'/../config/visual-report-builder.php' => config_path('visual-report-builder.php'),
        ], 'visual-report-builder-config');

        // Publish assets
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/visual-report-builder'),
        ], 'visual-report-builder-assets');

        // Publish views
        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/visual-report-builder'),
        ], 'visual-report-builder-views');

        // Publish seeders
        $this->publishes([
            __DIR__.'/../database/seeders' => database_path('seeders'),
        ], 'visual-report-builder-seeders');
    }
}
