# 📊 Visual Report Builder

> **Build professional, multi-dimensional reports in Laravel without writing SQL or JavaScript**

A complete, production-ready Laravel composer package for creating pivot tables, visual reports, and analytics dashboards with:
- ✅ **Drag-and-drop template builder** - Create reports visually without coding
- ✅ **Template-based execution** - Execute pre-defined templates with dynamic filters
- ✅ **Multi-dimensional pivot tables** - Group by unlimited dimensions and metrics
- ✅ **Any data source** - Works with any Eloquent model automatically (no modifications needed)
- ✅ **6+ aggregate functions** - sum, avg, min, max, count, count_distinct
- ✅ **Multiple exports** - CSV, Excel, PDF, JSON
- ✅ **REST API** - Full API for programmatic access
- ✅ **Zero setup** - Works out of the box with auto-discovery

## 🚀 Quick Start

### Installation

```bash
# Install package
composer require ibekzod/visual-report-builder

# Run migrations
php artisan migrate

# Visit the dashboard
http://yourapp.test/visual-reports
```

That's it! No configuration needed. The package auto-discovers your Eloquent models.

### Your First Report (2 Minutes)

1. Go to `http://yourapp.test/visual-reports`
2. Click **"+ Create Template"** button (top-right)
3. Select a model (e.g., "Order")
4. **Drag** dimensions to row/column sections
5. **Drag** metrics to metrics section
6. Click **"Preview"** to see results
7. Fill in template name, category, description
8. Click **"Save Template"**
9. Your template now appears in dashboard for all users

That's it! Users can now execute your template, apply filters, and save reports.

## 📋 Architecture Overview

### Two Complementary Workflows

#### 1. **Template Creation** (Drag-and-Drop Builder)
```
Admin/Power User → /visual-reports/builder
    → Select Model (auto-discovered from app/Models)
    → Drag Dimensions (row, column)
    → Drag Metrics (sum, count, etc.)
    → Preview Results
    → Save as Template
    → Available to All Users
```

#### 2. **Template Execution** (Dashboard)
```
All Users → /visual-reports (Dashboard)
    → Select Template (from left sidebar)
    → Apply Filters (dynamic, based on template)
    → View Results (table, line, bar, pie, area charts)
    → Export (CSV, Excel, PDF, JSON)
    → Save Report (personal library)
```

### Core Components

**Services:**
- `DataSourceManager` - Auto-discovers models, columns, relationships (uses PHP Reflection API)
- `QueryBuilder` - Generates dynamic SQL with GROUP BY, aggregates, HAVING, JOINs
- `TemplateExecutor` - Executes templates with user-selected filter values
- `PivotEngine` - Creates pivot table structures from flat query results
- `AggregateCalculator` - Computes sum, avg, min, max, count, count_distinct
- `FilterManager` - Applies dynamic filter conditions
- 5 Exporters (CSV, Excel, PDF, JSON) - Multi-format export

**Models:**
- `ReportTemplate` - Template definition (model, dimensions, metrics, category)
- `TemplateFilter` - Filter configurations with operators
- `ReportResult` - Saved report executions with user filters

**Controllers:**
- `BuilderController` - Model discovery, relationships, save templates
- `TemplateController` - Template CRUD, execution, filtering
- `ReportController` - Saved report management
- `ExportController` - Multi-format exports

## 💻 Usage Examples

### Web UI - No Coding Required

**At `/visual-reports/builder`:**
- Select data source (model)
- Drag blue fields → Row Dimensions
- Drag gray fields → Column Dimensions
- Drag green fields → Metrics
- Click "Preview" to see JSON
- Click "Save Template" → Fill metadata → Done

**At `/visual-reports` (Dashboard):**
- Click template in left sidebar
- Adjust filters (right panel)
- Select view type (table, chart)
- Click "Execute"
- Export or save report

### Via PHP Code

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
            'label' => 'Total Sales'
        ],
        [
            'column' => 'id',
            'aggregate' => 'count',
            'label' => 'Order Count'
        ]
    ],
    'filters' => [
        'status' => ['completed', 'paid']
    ]
]);

return response()->json($result);
```

### Via REST API

```bash
# Get all templates
curl -X GET http://yourapp.test/api/visual-reports/templates \
  -H "Authorization: Bearer YOUR_TOKEN"

# Execute a template
curl -X POST http://yourapp.test/api/visual-reports/templates/1/execute \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{"filters": {"region": "North"}}'

# Export report as Excel
curl -X POST http://yourapp.test/api/visual-reports/results/1/export/excel \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o report.xlsx

# Get available models
curl -X GET http://yourapp.test/api/visual-reports/models \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get model dimensions
curl -X GET http://yourapp.test/api/visual-reports/models/App%5CModels%5COrder/dimensions \
  -H "Authorization: Bearer YOUR_TOKEN"

# Get model metrics
curl -X GET http://yourapp.test/api/visual-reports/models/App%5CModels%5COrder/metrics \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 🔌 Complete API Reference

| Method | Endpoint | Purpose |
|--------|----------|---------|
| GET | `/api/visual-reports/templates` | List all templates |
| GET | `/api/visual-reports/templates/{id}` | Get single template |
| POST | `/api/visual-reports/templates/{id}/execute` | Execute template |
| POST | `/api/visual-reports/results` | Save report |
| GET | `/api/visual-reports/results` | List saved reports |
| POST | `/api/visual-reports/results/{id}/export/{format}` | Export report |
| POST | `/api/visual-reports/builder/save-template` | Save new template |
| GET | `/api/visual-reports/models` | List models |
| GET | `/api/visual-reports/models/{model}/dimensions` | Get dimensions |
| GET | `/api/visual-reports/models/{model}/metrics` | Get metrics |
| GET | `/api/visual-reports/models/{model}/relationships` | Get relationships (for JOINs) |
| POST | `/api/visual-reports/preview` | Preview configuration |

## ⚙️ Configuration

The package works out-of-the-box, but you can customize via config:

```bash
php artisan vendor:publish --tag=visual-report-builder-config
```

Edit `config/visual-report-builder.php`:

```php
return [
    // Route prefix
    'prefix' => env('VISUAL_REPORT_PREFIX', 'visual-reports'),

    // Middleware for web routes
    'middleware' => ['web', 'auth'],

    // Middleware for API routes
    'api_middleware' => ['api', 'auth:sanctum'],

    // Enable/disable exporters
    'exporters' => [
        'csv' => true,
        'excel' => true,
        'pdf' => true,
        'json' => true,
    ],

    // Caching
    'cache' => [
        'enabled' => env('VISUAL_REPORT_CACHE_ENABLED', true),
        'ttl' => env('VISUAL_REPORT_CACHE_TTL', 3600), // 1 hour
    ],

    // Auto-discovery of models
    'models' => [
        'auto_discover' => env('VISUAL_REPORT_AUTO_DISCOVER', true),
        'namespace' => env('VISUAL_REPORT_MODEL_NAMESPACE', 'App\\Models'),
        'path' => env('VISUAL_REPORT_MODEL_PATH', app_path('Models')),
    ],

    // Permissions
    'permissions' => [
        'create_templates' => env('VISUAL_REPORT_CREATE_TEMPLATES', 'all'),
        // Options: 'all' (everyone), 'admin' (admins only), or specific role
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
# Enable/disable auto-discovery (default: true)
VISUAL_REPORT_AUTO_DISCOVER=true

# Model namespace (default: App\Models)
VISUAL_REPORT_MODEL_NAMESPACE=App\\Models

# Who can create templates (default: all)
# Options: 'all', 'admin', or role name like 'power_user'
VISUAL_REPORT_CREATE_TEMPLATES=all

# Cache results (default: true)
VISUAL_REPORT_CACHE_ENABLED=true

# Cache TTL in seconds (default: 3600)
VISUAL_REPORT_CACHE_TTL=3600
```

## 🎨 Features in Detail

### Auto-Discovery (Zero Configuration)

The package **automatically**:
- 📁 Scans `app/Models` for Eloquent models
- 🗂️ Reads database schema directly (no model modifications needed)
- 🔗 Detects relationships (BelongsTo, HasMany, HasOne, BelongsToMany, etc.)
- 🏷️ Extracts dimensions (string, date, boolean columns)
- 📊 Extracts metrics (integer, decimal, double columns)
- 🎯 Works with 5+ year old legacy projects without any code changes

### Drag-and-Drop Builder

- Two-panel layout: Configuration (left) + Available Fields (right)
- Color-coded fields:
  - 🔵 **Blue** = Row dimensions
  - ⚪ **Gray** = Column dimensions
  - 🟢 **Green** = Metrics
- Native HTML5 drag-and-drop (no external library)
- Visual feedback during drag operations
- Live JSON preview before saving
- Modal for template metadata (name, category, icon, description)
- Automatic redirect to dashboard after save

### Template-Based Execution

- Pre-built templates available to all users
- Dynamic filters based on template definition
- Multiple view types:
  - 📊 Table (spreadsheet format)
  - 📈 Line Chart (trends over time)
  - 📊 Bar Chart (comparisons)
  - 🥧 Pie Chart (composition)
  - 📈 Area Chart (stacked trends)
- Export in 4 formats (CSV, Excel, PDF, JSON)
- Save reports to personal library

### Relationship Joins

- **Auto-detect** relationships from model methods
- Join tables dynamically through relationships
- Example: Order model with customer belongsTo relationship
  - Automatically detects "customer" relationship
  - Can select "customer.region" dimension
  - Generates JOIN automatically

### Aggregate Functions

| Function | Description | Example |
|----------|-------------|---------|
| **sum** | Total of values | 100 + 200 + 300 = 600 |
| **avg** | Average value | (100 + 200 + 300) / 3 = 200 |
| **min** | Minimum value | min(100, 200, 300) = 100 |
| **max** | Maximum value | max(100, 200, 300) = 300 |
| **count** | Row count | 3 records = 3 |
| **count_distinct** | Unique values | count_distinct(1, 1, 2) = 2 |

### Role-Based Permissions

Control who can create templates:

```bash
# Everyone can create templates
VISUAL_REPORT_CREATE_TEMPLATES=all

# Only admins can create templates
VISUAL_REPORT_CREATE_TEMPLATES=admin

# Only users with 'power_user' role can create templates
VISUAL_REPORT_CREATE_TEMPLATES=power_user
```

## 🔐 Security

- ✅ SQL injection prevention (parameterized queries)
- ✅ CSRF protection
- ✅ User ownership verification on all operations
- ✅ Authorization policies for editing/sharing
- ✅ Input validation on all endpoints
- ✅ Granular permissions (create_templates, create_reports, share_reports, export_reports)

## 📚 How the Merge Works

This package combines two previously separate systems:

### Before: Two Separate Solutions
- **Template System** = Fixed templates, hard to create custom ones
- **Builder** = Create any report, but doesn't persist for team use

### Now: Integrated Solution
- **Builder** (new) → Creates templates that appear in **Dashboard**
- **Dashboard** (existing) → Executes templates and allows personal report saving
- **Result**: Users can create custom templates OR use pre-built ones

## 🛠️ Making Models Reportable (Optional)

### Basic Usage (Auto-Discovery)

Just use your models as-is. The package auto-discovers everything:

```php
// Your existing model - no changes needed!
class Order extends Model
{
    // ... your code
}

// In builder, Order automatically appears with all columns as dimensions/metrics
```

### Custom Dimensions & Metrics (Advanced)

If you want to customize what appears in the builder:

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

    // Define custom dimensions
    public static function dimensions(): array
    {
        return [
            ['column' => 'region', 'label' => 'Sales Region', 'type' => 'string'],
            ['column' => 'status', 'label' => 'Order Status', 'type' => 'string'],
            ['column' => 'created_at', 'label' => 'Order Date', 'type' => 'date'],
        ];
    }

    // Define custom metrics
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

## 🚀 Real-World Examples

### Sales Report by Region & Month

```
Data Source: Order model
Row Dimensions: region
Column Dimensions: month
Metrics:
  - amount (sum) → Total Sales
  - id (count) → Order Count
Result: Sales by region and month with totals
```

### Inventory Stock Levels

```
Data Source: InventoryItem model
Row Dimensions: warehouse, product_type
Column Dimensions: status
Metrics:
  - quantity (sum) → Total Items
  - quantity (min) → Min Stock
  - quantity (max) → Max Stock
Result: Stock levels by warehouse, product, and status
```

### Customer Analysis

```
Data Source: Order model (with customer relationship)
Row Dimensions: customer.region (joined via relationship!)
Column Dimensions: month
Metrics:
  - amount (sum) → Customer Spending
  - id (count) → Order Count
  - customer_id (count_distinct) → Unique Customers
Result: Customer analysis by region and time
```

## 🐛 Troubleshooting

### Models Not Appearing in Builder?

The package scans `app/Models` by default. If models are elsewhere:

```bash
php artisan vendor:publish --tag=visual-report-builder-config
```

Edit `config/visual-report-builder.php` and update the `models.namespace` and `models.path`.

### Dimensions/Metrics Not Loading?

1. Verify the model's table exists in the database
2. Test in `php artisan tinker`:
   ```php
   >>> new App\Models\Order
   >>> App\Models\Order::first()
   ```
3. Check browser console for JavaScript errors

### Routes Returning 404?

```bash
php artisan route:clear
php artisan cache:clear
```

### Excel/PDF Export Not Working?

Install the optional dependencies:

```bash
# For Excel export
composer require maatwebsite/excel

# For PDF export
composer require barryvdh/laravel-dompdf
```

### SQL Errors?

- Verify column names match database exactly
- Test queries in `php artisan tinker`
- Check that model's `$table` property is correct

## 📖 Files Overview

### Key Source Files

```
src/
├── Services/
│   ├── DataSourceManager.php      # Auto-discovery, relationships
│   ├── QueryBuilder.php            # Dynamic SQL generation
│   ├── TemplateExecutor.php        # Template execution
│   ├── PivotEngine.php             # Pivot table creation
│   ├── FilterManager.php           # Dynamic filtering
│   ├── AggregateCalculator.php    # Calculations
│   └── ExporterFactory.php         # Export handling
├── Http/Controllers/
│   ├── BuilderController.php       # Builder endpoints (models, save template)
│   ├── TemplateController.php      # Template CRUD & execution
│   ├── ReportController.php        # Report management
│   └── ExportController.php        # Export endpoints
├── Models/
│   ├── ReportTemplate.php          # Template definition
│   ├── TemplateFilter.php          # Filter configurations
│   └── ReportResult.php            # Saved reports
└── Traits/
    ├── Reportable.php              # Model mixin
    ├── HasDimensions.php           # Custom dimensions
    └── HasMetrics.php              # Custom metrics
```

### Routes

```
routes/
├── web.php                         # GET /visual-reports, /visual-reports/builder
└── api.php                         # API endpoints (15+ routes)
```

### Views

```
resources/views/
├── builder.blade.php               # Drag-and-drop builder UI
├── dashboard.blade.php             # Template execution dashboard
├── layouts/app.blade.php           # Main layout
└── index.blade.php                 # Landing page
```

### Database

```
database/migrations/
├── create_report_templates_table.php          # Template definitions
├── create_template_filters_table.php          # Filter specs
├── create_report_results_table.php            # Saved reports
└── [other migrations...]
```

## 🎓 For AI Assistants

This package implements:

1. **Dynamic Model Discovery**
   - Scans file system for Eloquent models
   - Uses PHP Reflection API to detect relationships
   - Reads schema via Laravel Schema Builder
   - No manual registration needed

2. **Pivot Table Engine**
   - Groups results by multiple dimensions
   - Calculates aggregates for each group
   - Includes totals and subtotals
   - Returns flat array suitable for UI

3. **Template System**
   - Stores configuration in `report_templates` table
   - Filters defined in `template_filters` table
   - Execution separate from template definition
   - Allows templates to be reused across users

4. **REST API Design**
   - Resource-based endpoints
   - Authentication via Sanctum
   - Query parameter support for filters
   - JSON responses with consistent format

5. **Multi-Format Export**
   - Factory pattern for exporters
   - Strategy pattern for different formats
   - Stream responses for large files
   - Configurable options per format

### Key Patterns

- **Service Layer** - Business logic in services, controllers stay thin
- **Repository Pattern** - Models act as repositories for data
- **Factory Pattern** - ExporterFactory creates appropriate exporter
- **Strategy Pattern** - Different export strategies (CSV, Excel, PDF, JSON)
- **Trait Pattern** - Reportable trait adds methods to any model
- **Facade Pattern** - VisualReportBuilder facade for quick access

### Important Files for Future Development

- `src/Services/DataSourceManager.php` - Add new discovery logic here
- `src/Services/QueryBuilder.php` - Extend query capabilities here
- `src/Http/Controllers/BuilderController.php` - New builder endpoints here
- `resources/views/builder.blade.php` - UI enhancements here
- `config/visual-report-builder.php` - New configuration options here

## 🤝 Contributing

Contributions welcome! When adding features:

1. Follow the existing architecture patterns
2. Keep services single-responsibility
3. Add tests for new functionality
4. Update this README if adding user-facing features
5. Submit a pull request

## 📄 License

MIT License - see LICENSE file for details

---

**Built for developers who want powerful reports without the complexity.** ✨

Need help? Check the code comments or examine the existing implementations in `src/Services/`.
