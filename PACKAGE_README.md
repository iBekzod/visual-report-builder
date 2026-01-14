# Visual Report Builder - Laravel Composer Package

A powerful Laravel Composer package for creating multi-dimensional pivot tables, visual reports, and data analysis without writing any code. Similar to Kyubit.com's visual report builder.

## 🎯 Overview

This complete composer package enables you to build professional, interactive reports and dashboards in Laravel without writing a single line of SQL or JavaScript. It's fully dynamic, works with any Laravel user's models, and includes everything you need to get started.

## ✨ Key Features

- 🎨 **Visual Report Builder UI** - Drag-and-drop interface with real-time preview
- 📊 **Multi-Dimensional Pivot Tables** - Create reports with multiple row/column dimensions
- 🔄 **6+ Aggregate Functions** - sum, avg, min, max, count, count_distinct, value
- 💾 **Save & Share** - Save reports, create templates, share with team
- 📥 **Multiple Data Sources** - Works with any Eloquent model
- 📤 **Export Formats** - CSV, Excel, PDF, JSON exports
- 🔐 **Access Control** - Built-in permissions and sharing
- 🚀 **Full REST API** - Complete API for programmatic use
- ⚡ **Performance** - Caching, optimization, efficient queries

## 📦 What's Included

### Core Files (Fully Implemented)

✅ **Services** - Complete report generation engine
- ReportBuilder.php - Main orchestrator
- QueryBuilder.php - SQL query generation
- PivotEngine.php - Multi-dimensional pivot tables
- DataSourceManager.php - Model discovery and metadata
- ExporterFactory.php - Export factory pattern
- FilterManager.php - Filter application logic
- AggregateCalculator.php - Aggregate calculations

✅ **Models** - Database models with relationships
- Report - Report definitions with sharing
- ReportTemplate - Reusable templates
- SavedReport - Cached results
- DataSource - Data source management
- ReportShare - Permission management

✅ **Controllers** - HTTP endpoints
- ReportController - CRUD operations
- BuilderController - Builder UI endpoints
- ExportController - Export endpoints

✅ **Exporters** - Multiple format support
- CSVExporter - CSV export
- ExcelExporter - Excel export via PhpSpreadsheet
- PDFExporter - PDF export via DomPDF
- JSONExporter - JSON export

✅ **Traits** - Model enhancements
- Reportable - Make models reportable
- HasDimensions - Define dimensions
- HasMetrics - Define metrics

✅ **Database** - All migrations
- Reports table
- Templates table
- Saved reports table
- Data sources table
- Report shares table

✅ **Routes** - Web and API routes
- API endpoints (RESTful)
- Web routes (UI)

✅ **Views** - Blade templates
- Dashboard (reports list)
- Builder (interactive UI)
- Layout (base template)

✅ **Configuration** - Package config
- Fully customizable settings
- Environment variables support

✅ **Helpers** - Helper functions
- visual_report_builder()
- execute_report()
- export_report()
- get_report_metadata()
- get_available_models()

## 🚀 Installation

### Step 1: Require the Package

```bash
composer require ibekzod/visual-report-builder
```

### Step 2: Publish Assets

```bash
php artisan vendor:publish --provider="Ibekzod\VisualReportBuilder\VisualReportBuilderServiceProvider"
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Access the UI

```
http://yourapp.test/visual-reports
```

## 💡 Usage Examples

### Example 1: Sales Report

```php
<?php

use Ibekzod\VisualReportBuilder\Facades\VisualReportBuilder;

// Create a multi-dimensional sales report
$config = [
    'model' => 'App\Models\Sale',
    'row_dimensions' => ['region', 'sales_agent'],
    'column_dimensions' => ['product_category', 'month'],
    'metrics' => [
        [
            'column' => 'amount',
            'aggregate' => 'sum',
            'label' => 'Total Sales',
            'alias' => 'total_sales'
        ],
        [
            'column' => 'commission',
            'aggregate' => 'sum',
            'label' => 'Commission',
            'alias' => 'total_commission'
        ],
        [
            'column' => 'id',
            'aggregate' => 'count',
            'label' => 'Orders',
            'alias' => 'order_count'
        ]
    ],
    'filters' => [
        'status' => ['completed', 'paid']
    ]
];

$result = VisualReportBuilder::execute($config);
return response()->json($result);
```

### Example 2: Using Reportable Trait

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ibekzod\VisualReportBuilder\Traits\Reportable;

class Order extends Model
{
    use Reportable;

    protected $fillable = ['order_number', 'amount', 'status', 'region'];
}

// In your controller:
$result = Order::executeReport([
    'row_dimensions' => ['region', 'status'],
    'column_dimensions' => [],
    'metrics' => [
        ['column' => 'amount', 'aggregate' => 'sum', 'label' => 'Total']
    ]
]);
```

### Example 3: With Custom Dimensions

```php
<?php

use Ibekzod\VisualReportBuilder\Traits\Reportable;
use Ibekzod\VisualReportBuilder\Traits\HasDimensions;
use Ibekzod\VisualReportBuilder\Traits\HasMetrics;

class Transaction extends Model
{
    use Reportable, HasDimensions, HasMetrics;

    protected static function dimensions(): array
    {
        return [
            ['column' => 'department', 'label' => 'Department', 'type' => 'string'],
            ['column' => 'cost_center', 'label' => 'Cost Center', 'type' => 'string'],
            ['column' => 'date', 'label' => 'Date', 'type' => 'date'],
        ];
    }

    protected static function metrics(): array
    {
        return [
            [
                'column' => 'amount',
                'label' => 'Amount',
                'type' => 'decimal',
                'default_aggregate' => 'sum'
            ],
            [
                'column' => 'quantity',
                'label' => 'Quantity',
                'type' => 'integer',
                'default_aggregate' => 'sum'
            ]
        ];
    }
}
```

### Example 4: Exporting Reports

```php
<?php

// Execute report
$result = VisualReportBuilder::execute($config);

// Export as different formats
$csv = VisualReportBuilder::export($result, 'csv');
$excel = VisualReportBuilder::export($result, 'excel');
$pdf = VisualReportBuilder::export($result, 'pdf', ['title' => 'Sales Report']);
$json = VisualReportBuilder::export($result, 'json');

// Download file
$builder = app('visual-report-builder');
return $builder->exportAsFile($result, 'csv', 'report.csv');
```

## 📊 Database Schema

### visual_reports
```
id, name, description, model, configuration (JSON), view_options (JSON),
user_id (FK), template_id (FK), created_at, updated_at, deleted_at
```

### visual_report_templates
```
id, name, description, model, default_config (JSON), allowed_metrics (JSON),
allowed_dimensions (JSON), category, icon, is_public, created_at, updated_at
```

### visual_saved_reports
```
id, report_id (FK), data (JSON), cached_at, cache_duration, created_at, updated_at
```

### visual_data_sources
```
id, name, description, type, model_class, configuration (JSON),
user_id (FK), is_public, created_at, updated_at
```

### visual_report_shares
```
id, report_id (FK), user_id (FK), can_edit, can_share, created_at, updated_at
```

## 🔌 API Endpoints

### Reports (CRUD)
```
GET    /api/visual-reports/reports              List all reports
POST   /api/visual-reports/reports              Create report
GET    /api/visual-reports/reports/{id}         Get single report
PUT    /api/visual-reports/reports/{id}         Update report
DELETE /api/visual-reports/reports/{id}         Delete report
```

### Report Execution & Sharing
```
POST   /api/visual-reports/reports/{id}/execute Execute report
POST   /api/visual-reports/reports/{id}/share   Share with user
DELETE /api/visual-reports/reports/{id}/unshare Stop sharing
```

### Builder Resources
```
GET    /api/visual-reports/models               List available models
GET    /api/visual-reports/models/{model}       Get model metadata
GET    /api/visual-reports/models/{model}/dimensions  List dimensions
GET    /api/visual-reports/models/{model}/metrics     List metrics
POST   /api/visual-reports/preview              Preview configuration
```

### Export
```
POST   /api/visual-reports/reports/{id}/export/csv      CSV export
POST   /api/visual-reports/reports/{id}/export/excel    Excel export
POST   /api/visual-reports/reports/{id}/export/pdf      PDF export
POST   /api/visual-reports/reports/{id}/export/json     JSON export
```

## ⚙️ Configuration

```php
// config/visual-report-builder.php

return [
    'prefix' => 'visual-reports',  // Route prefix
    'middleware' => ['web', 'auth'],  // Web middleware
    'api_middleware' => ['api', 'auth:sanctum'],  // API middleware

    'exporters' => [
        'csv' => true,
        'excel' => true,
        'pdf' => true,
        'json' => true,
    ],

    'cache' => [
        'enabled' => true,
        'ttl' => 3600,  // 1 hour
    ],

    'models' => [
        'auto_discover' => true,  // Auto-discover models
        'namespace' => 'App\\Models',
        'path' => app_path('Models'),
    ],

    'pivot' => [
        'max_dimensions' => 5,
        'max_metrics' => 10,
        'include_totals' => true,
    ],
];
```

## 🔐 Security & Permissions

The package includes:

- ✅ User ownership verification
- ✅ Granular sharing permissions (can_edit, can_share)
- ✅ Automatic SQL parameterization
- ✅ CSRF protection
- ✅ Authentication required on all endpoints
- ✅ Authorization policies for all resources

## 🧪 Testing

```bash
# Run tests
php artisan test packages/visual-report-builder

# With coverage
php artisan test packages/visual-report-builder --coverage
```

## 📚 Helper Functions

```php
// Get builder instance
visual_report_builder();

// Execute report
execute_report($config);

// Export data
export_report($data, 'excel', $options);

// Get metadata
get_report_metadata('App\Models\Order');

// Get available models
get_available_models();
```

## 🐛 Troubleshooting

### Models not showing?
```bash
php artisan vendor:publish --tag=visual-report-builder-config
# Edit models namespace in config
```

### Routes not working?
```bash
php artisan route:clear
php artisan cache:clear
```

### Assets not loading?
```bash
php artisan vendor:publish --tag=visual-report-builder-assets --force
```

## 📄 License

MIT License - feel free to use in commercial projects

## 🙏 Credits

Inspired by Kyubit.com and built for the Laravel community.

---

**Built for developers who want powerful reports without the complexity.** ✨
