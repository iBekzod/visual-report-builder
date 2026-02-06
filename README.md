# Visual Report Builder

A complete, production-ready Laravel package for creating pivot tables, visual reports, and analytics dashboards with drag-and-drop template creation and template-based execution.

## Features

- **Drag-and-Drop Template Builder** - Create reports visually without writing code
- **Template-Based Execution** - Execute pre-defined templates with dynamic filters
- **Multi-Dimensional Pivot Tables** - Group data by unlimited dimensions and metrics
- **Universal Data Source Support** - Works with any Eloquent model automatically
- **6 Aggregate Functions** - SUM, AVG, MIN, MAX, COUNT, COUNT_DISTINCT
- **Multiple Export Formats** - CSV, Excel, PDF, JSON
- **REST API** - Complete API for programmatic access
- **Zero Configuration** - Works out of the box with Laravel auto-discovery
- **Database Agnostic** - PostgreSQL, MySQL, MariaDB, SQLite, SQL Server
- **Modern UI** - Clean, minimalistic Laravel-style design

## Requirements

- PHP 8.1 or higher
- Laravel 10.x or 11.x
- Any supported database (PostgreSQL, MySQL, SQLite, SQL Server)

## Installation

### Step 1: Install via Composer

```bash
composer require ibekzod/visual-report-builder
```

### Step 2: Run Migrations

```bash
php artisan migrate
```

### Step 3: Access the Dashboard

Navigate to your application:

```
http://yourapp.test/visual-reports
```

The package auto-discovers your Eloquent models and is ready to use immediately.

## Quick Start Guide

### Creating Your First Report Template

1. Go to `http://yourapp.test/visual-reports`
2. Click the **"Create Template"** button in the header
3. Select a data source (model) from the dropdown
4. Drag dimension fields to the **Row Dimensions** or **Column Dimensions** zones
5. Drag metric fields to the **Metrics** zone
6. Click **"Preview"** to see the data
7. Click **"Save Template"** and fill in the template details
8. Your template now appears in the dashboard for all users

### Executing Reports

1. Go to `http://yourapp.test/visual-reports` (Dashboard)
2. Select a template from the left sidebar
3. Apply any filters in the filter panel
4. Click **"Run Report"** to execute
5. View results as table or chart
6. Export to CSV, Excel, PDF, or JSON
7. Save the report to your personal library

## Architecture Overview

### Two Complementary Workflows

#### Template Creation (Builder)
```
Admin/Power User → /visual-reports/builder
    → Select Model (auto-discovered from app/Models)
    → Drag Dimensions (row, column grouping)
    → Drag Metrics (aggregated values)
    → Preview Results
    → Save as Template
    → Available to All Users
```

#### Template Execution (Dashboard)
```
All Users → /visual-reports (Dashboard)
    → Select Template (from sidebar)
    → Apply Filters (dynamic, based on template)
    → View Results (table, line, bar, pie, area charts)
    → Export (CSV, Excel, PDF, JSON)
    → Save Report (personal library)
```

### Core Components

#### Services

| Service | Purpose |
|---------|---------|
| `DataSourceManager` | Auto-discovers models, columns, and relationships using PHP Reflection |
| `QueryBuilder` | Generates dynamic SQL with GROUP BY, aggregates, HAVING, and JOINs |
| `TemplateExecutor` | Executes templates with user-selected filter values |
| `PivotEngine` | Creates pivot table structures from flat query results |
| `AggregateCalculator` | Computes sum, avg, min, max, count, count_distinct |
| `FilterManager` | Applies dynamic filter conditions to queries |
| `ExporterFactory` | Creates appropriate exporter for each format |

#### Models

| Model | Purpose |
|-------|---------|
| `ReportTemplate` | Template definition (model, dimensions, metrics, filters, category) |
| `TemplateFilter` | Filter configurations with operators and default values |
| `ReportResult` | Saved report executions with user-applied filters |

#### Controllers

| Controller | Purpose |
|------------|---------|
| `BuilderController` | Model discovery, relationships, template creation |
| `TemplateController` | Template CRUD, execution, filtering, export |
| `DashboardController` | Dashboard statistics and overview |

## Usage

### Web Interface

The package provides two main views:

#### Dashboard (`/visual-reports`)
- Template selection sidebar organized by category
- Statistics cards showing templates, saved reports, favorites
- Report display area with table and chart views
- Filter panel for applying dynamic filters
- Export and save functionality

#### Builder (`/visual-reports/builder`)
- Two-panel layout: Configuration (left) + Available Fields (right)
- Color-coded draggable fields:
  - **Blue border** = Dimensions (text, date, boolean columns)
  - **Green border** = Metrics (numeric columns)
- Drop zones for Row Dimensions, Column Dimensions, and Metrics
- Live JSON preview before saving
- Modal for template metadata (name, category, description, icon)

### PHP Code Usage

```php
<?php

use Ibekzod\VisualReportBuilder\Facades\VisualReportBuilder;

// Execute a report configuration
$result = VisualReportBuilder::execute([
    'model' => 'App\Models\Order',
    'row_dimensions' => ['region', 'status'],
    'column_dimensions' => ['month'],
    'metrics' => [
        [
            'column' => 'amount',
            'aggregate' => 'sum',
            'alias' => 'total_sales'
        ],
        [
            'column' => 'id',
            'aggregate' => 'count',
            'alias' => 'order_count'
        ]
    ],
    'filters' => [
        'status' => ['completed', 'paid']
    ]
]);

return response()->json($result);
```

### REST API

#### Template Endpoints

```bash
# List all templates
GET /api/visual-reports/templates

# Get single template
GET /api/visual-reports/templates/{id}

# Execute template with filters
POST /api/visual-reports/templates/{id}/execute
Body: {"filters": {"region": "North", "status": "active"}}

# Save report result
POST /api/visual-reports/templates/{id}/save
Body: {"name": "Q1 Sales", "applied_filters": {...}, "data": [...]}

# Get saved reports for template
GET /api/visual-reports/templates/{id}/saved
```

#### Builder Endpoints

```bash
# List available models
GET /api/visual-reports/models

# Get model dimensions
GET /api/visual-reports/models/{model}/dimensions

# Get model metrics
GET /api/visual-reports/models/{model}/metrics

# Get model relationships
GET /api/visual-reports/models/{model}/relationships

# Preview report configuration
POST /api/visual-reports/preview
Body: {"model": "App\\Models\\Order", "row_dimensions": [...], "metrics": [...]}

# Save new template
POST /api/visual-reports/builder/save-template
Body: {"name": "Sales Report", "model": "App\\Models\\Order", ...}
```

#### Report Result Endpoints

```bash
# Load saved report
GET /api/visual-reports/results/{id}

# Delete saved report
DELETE /api/visual-reports/results/{id}

# Toggle favorite
POST /api/visual-reports/results/{id}/favorite

# Export report
POST /api/visual-reports/results/{id}/export/{format}
# format: csv, excel, pdf, json
```

## Configuration

### Publishing Configuration

```bash
php artisan vendor:publish --tag=visual-report-builder-config
```

### Configuration Options

Edit `config/visual-report-builder.php`:

```php
return [
    // Authentication
    'auth' => [
        'enabled' => env('VISUAL_REPORT_AUTH_ENABLED', false),
        'web_middleware' => env('VISUAL_REPORT_WEB_MIDDLEWARE', ''),
        'api_middleware' => env('VISUAL_REPORT_API_MIDDLEWARE', ''),
    ],

    // Model auto-discovery
    'models' => [
        'auto_discover' => env('VISUAL_REPORT_AUTO_DISCOVER', true),
        'namespace' => env('VISUAL_REPORT_MODEL_NAMESPACE', 'App\\Models'),
        'path' => env('VISUAL_REPORT_MODEL_PATH', null), // defaults to app_path('Models')
    ],

    // Permissions
    'permissions' => [
        'create_templates' => env('VISUAL_REPORT_CREATE_TEMPLATES', 'all'),
        // Options: 'all', 'admin', or specific role name
    ],

    // Export options
    'exporters' => [
        'csv' => true,
        'excel' => true,
        'pdf' => true,
        'json' => true,
    ],

    // Cache settings
    'cache' => [
        'enabled' => env('VISUAL_REPORT_CACHE_ENABLED', true),
        'ttl' => env('VISUAL_REPORT_CACHE_TTL', 3600),
    ],

    // Pivot table limits
    'pivot' => [
        'max_dimensions' => env('VISUAL_REPORT_MAX_DIMENSIONS', 5),
        'max_metrics' => env('VISUAL_REPORT_MAX_METRICS', 10),
        'include_totals' => env('VISUAL_REPORT_INCLUDE_TOTALS', true),
    ],
];
```

### Environment Variables

```bash
# Authentication
VISUAL_REPORT_AUTH_ENABLED=false
VISUAL_REPORT_WEB_MIDDLEWARE=auth
VISUAL_REPORT_API_MIDDLEWARE=auth:sanctum

# Model discovery
VISUAL_REPORT_AUTO_DISCOVER=true
VISUAL_REPORT_MODEL_NAMESPACE=App\\Models

# Permissions
VISUAL_REPORT_CREATE_TEMPLATES=all

# Caching
VISUAL_REPORT_CACHE_ENABLED=true
VISUAL_REPORT_CACHE_TTL=3600
```

## Database Support

The package supports multiple database systems with automatic identifier quoting:

| Database | Quote Character | Status |
|----------|-----------------|--------|
| PostgreSQL | `"` (double quotes) | Fully supported |
| MySQL | `` ` `` (backticks) | Fully supported |
| MariaDB | `` ` `` (backticks) | Fully supported |
| SQLite | `"` (double quotes) | Fully supported |
| SQL Server | `"` (double quotes) | Fully supported |

The package automatically detects the database driver and uses the appropriate quoting.

## Aggregate Functions

| Function | Description | SQL Generated |
|----------|-------------|---------------|
| `sum` | Total of all values | `SUM(column)` |
| `avg` | Average value | `AVG(column)` |
| `min` | Minimum value | `MIN(column)` |
| `max` | Maximum value | `MAX(column)` |
| `count` | Count of rows | `COUNT(column)` |
| `count_distinct` | Count of unique values | `COUNT(DISTINCT column)` |
| `value` | Raw value (no aggregation) | `column` |

## Custom Model Configuration

### Default Behavior (Auto-Discovery)

The package automatically discovers all Eloquent models and their columns:

```php
// Your existing model - no changes needed
class Order extends Model
{
    protected $fillable = ['amount', 'status', 'region', 'customer_id'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

// The package automatically:
// - Discovers the Order model
// - Extracts all columns as dimensions or metrics
// - Detects the customer relationship
```

### Custom Dimensions and Metrics (Optional)

For fine-grained control over what appears in the builder:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ibekzod\VisualReportBuilder\Traits\Reportable;
use Ibekzod\VisualReportBuilder\Traits\HasDimensions;
use Ibekzod\VisualReportBuilder\Traits\HasMetrics;

class Order extends Model
{
    use Reportable, HasDimensions, HasMetrics;

    public static function dimensions(): array
    {
        return [
            ['column' => 'region', 'label' => 'Sales Region', 'type' => 'string'],
            ['column' => 'status', 'label' => 'Order Status', 'type' => 'string'],
            ['column' => 'created_at', 'label' => 'Order Date', 'type' => 'date'],
        ];
    }

    public static function metrics(): array
    {
        return [
            [
                'column' => 'amount',
                'label' => 'Order Amount',
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

## Relationship Joins

The package automatically detects Eloquent relationships and allows joining related tables:

```php
// Order model
class Order extends Model
{
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

// In the builder:
// 1. Select Order as data source
// 2. Choose "customer" from the relationship dropdown
// 3. Customer fields appear with prefix: "customer.region", "customer.name"
// 4. Drag "customer.region" to dimensions
// 5. The query automatically JOINs the customers table
```

Supported relationship types:
- `belongsTo`
- `hasOne`
- `hasMany`
- `belongsToMany`

## Real-World Examples

### Sales Report by Region and Month

```
Data Source: Order
Row Dimensions: region
Column Dimensions: created_at (grouped by month)
Metrics:
  - amount (sum) → Total Sales
  - id (count) → Order Count
```

### Inventory Analysis

```
Data Source: InventoryItem
Row Dimensions: warehouse, product_category
Column Dimensions: status
Metrics:
  - quantity (sum) → Total Items
  - quantity (min) → Minimum Stock
  - quantity (max) → Maximum Stock
```

### Customer Spending Analysis

```
Data Source: Order (with customer relationship)
Row Dimensions: customer.region
Column Dimensions: status
Metrics:
  - amount (sum) → Total Spending
  - customer_id (count_distinct) → Unique Customers
```

### User Activity Report

```
Data Source: User
Row Dimensions: is_active
Metrics:
  - id (count) → User Count
```

## File Structure

```
src/
├── Facades/
│   └── VisualReportBuilder.php       # Facade for quick access
├── Http/
│   └── Controllers/
│       ├── BuilderController.php     # Model discovery, template creation
│       ├── TemplateController.php    # Template CRUD, execution
│       └── DashboardController.php   # Dashboard statistics
├── Models/
│   ├── ReportTemplate.php            # Template definition
│   ├── TemplateFilter.php            # Filter configurations
│   └── ReportResult.php              # Saved report results
├── Services/
│   ├── DataSourceManager.php         # Model auto-discovery
│   ├── QueryBuilder.php              # Dynamic SQL generation
│   ├── TemplateExecutor.php          # Template execution
│   ├── PivotEngine.php               # Pivot table creation
│   ├── AggregateCalculator.php       # Aggregate calculations
│   ├── FilterManager.php             # Filter application
│   └── ExporterFactory.php           # Export factory
├── Traits/
│   ├── Reportable.php                # Model mixin
│   ├── HasDimensions.php             # Custom dimensions
│   └── HasMetrics.php                # Custom metrics
└── VisualReportBuilderServiceProvider.php

resources/views/
├── layouts/
│   └── app.blade.php                 # Main layout with modern design
├── dashboard.blade.php               # Template execution dashboard
├── builder.blade.php                 # Drag-and-drop builder
└── index.blade.php                   # Landing page

routes/
├── web.php                           # Web routes
└── api.php                           # API routes

database/migrations/
├── create_report_templates_table.php
├── create_template_filters_table.php
├── create_report_results_table.php
└── make_created_by_nullable.php
```

## Security

- SQL injection prevention via parameterized queries
- CSRF protection on all forms
- User ownership verification on saved reports
- Optional authentication middleware
- Input validation on all endpoints
- Role-based template creation permissions

## Troubleshooting

### Models Not Appearing

1. Verify models are in `app/Models` directory
2. Check model namespace matches configuration
3. Clear cache: `php artisan cache:clear`

### Dimensions/Metrics Not Loading

1. Verify the model's table exists in database
2. Test model in tinker: `App\Models\Order::first()`
3. Check browser console for JavaScript errors

### Routes Returning 404

```bash
php artisan route:clear
php artisan cache:clear
php artisan config:clear
```

### Export Not Working

Install optional dependencies:

```bash
# For Excel export
composer require maatwebsite/excel

# For PDF export
composer require barryvdh/laravel-dompdf
```

### SQL Errors

- Verify column names match database exactly
- Check that model's `$table` property is correct
- Test queries in `php artisan tinker`

## Optional Dependencies

| Package | Purpose | Installation |
|---------|---------|--------------|
| `maatwebsite/excel` | Excel export | `composer require maatwebsite/excel` |
| `barryvdh/laravel-dompdf` | PDF export | `composer require barryvdh/laravel-dompdf` |

## Design Patterns

The package implements several design patterns:

- **Service Layer** - Business logic in dedicated service classes
- **Repository Pattern** - Models act as repositories for data access
- **Factory Pattern** - `ExporterFactory` creates appropriate exporters
- **Strategy Pattern** - Different export strategies for each format
- **Facade Pattern** - `VisualReportBuilder` facade for convenient access
- **Trait Pattern** - `Reportable` trait adds functionality to models

## API Response Format

All API endpoints return JSON with consistent format:

### Success Response
```json
{
    "success": true,
    "data": [...],
    "metadata": {
        "record_count": 100,
        "execution_time_ms": 45
    }
}
```

### Error Response
```json
{
    "success": false,
    "message": "Error description"
}
```

## Contributing

Contributions are welcome. When adding features:

1. Follow existing architecture patterns
2. Keep services single-responsibility
3. Add tests for new functionality
4. Update this README for user-facing features
5. Submit a pull request

## License

MIT License - see LICENSE file for details.

---

**Built for developers who want powerful reports without complexity.**
