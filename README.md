# 📊 Visual Report Builder

> **Build professional, multi-dimensional reports in Laravel without writing SQL or JavaScript**

[![Latest Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/ibekzod/visual-report-builder)
[![Laravel](https://img.shields.io/badge/laravel-10.0%2B-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/php-8.1%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

A complete, production-ready Laravel composer package for creating multi-dimensional pivot tables, visual reports, and analytics dashboards—just like Kyubit.com, but for your Laravel app!

## ✨ Features

- 🎨 **Visual Report Builder UI** - Drag-and-drop interface with real-time preview
- 📊 **Multi-Dimensional Pivot Tables** - Group by unlimited dimensions
- 📈 **6+ Aggregate Functions** - sum, avg, min, max, count, count_distinct, value
- 💾 **Save & Share** - Save reports and templates, share with team members
- 📁 **Any Data Source** - Works with any Eloquent model automatically
- 📤 **Multiple Exports** - CSV, Excel, PDF, JSON formats
- 🔐 **Access Control** - Built-in user ownership and granular permissions
- 🚀 **REST API** - Full API for programmatic report generation
- ⚡ **Caching** - Configurable result caching for performance
- 🎯 **Zero Setup** - Works out of the box, no configuration needed

## 🚀 Quick Start

### Installation

```bash
# 1. Require the package
composer require ibekzod/visual-report-builder

# 2. Run migrations
php artisan migrate

# 3. Done! Visit the UI
http://yourapp.test/visual-reports
```

That's it! No configuration needed. The package auto-discovers your Eloquent models.

### First Report (2 Minutes)

1. Navigate to `http://yourapp.test/visual-reports`
2. Click **"Create New Report"**
3. Select your data source (Eloquent model)
4. Drag dimensions to row/column sections
5. Add metrics (amount, count, etc.)
6. Click **"Preview"** to see results
7. Click **"Save"** to save your report

## 💻 Usage Examples

### Via Web Interface

No coding required! Just drag-and-drop in the UI at `/visual-reports`

### Via Code (PHP)

```php
<?php

use Ibekzod\VisualReportBuilder\Facades\VisualReportBuilder;

// Simple sales report
$result = VisualReportBuilder::execute([
    'model' => 'App\Models\Order',
    'row_dimensions' => ['region', 'status'],
    'column_dimensions' => ['month'],
    'metrics' => [
        [
            'column' => 'amount',
            'aggregate' => 'sum',
            'label' => 'Total Sales',
            'alias' => 'total_sales'
        ],
        [
            'column' => 'id',
            'aggregate' => 'count',
            'label' => 'Orders',
            'alias' => 'order_count'
        ]
    ]
]);

return response()->json($result);
```

### Via API (REST)

```bash
# Execute a saved report
curl -X POST http://yourapp.test/api/visual-reports/reports/1/execute \
  -H "Authorization: Bearer YOUR_TOKEN"

# Export report as CSV
curl -X POST http://yourapp.test/api/visual-reports/reports/1/export/csv \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o report.csv

# Export as Excel
curl -X POST http://yourapp.test/api/visual-reports/reports/1/export/excel \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o report.xlsx

# Export as PDF
curl -X POST http://yourapp.test/api/visual-reports/reports/1/export/pdf \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o report.pdf
```

### Make Your Model Reportable

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ibekzod\VisualReportBuilder\Traits\Reportable;

class Order extends Model
{
    use Reportable;
}

// In your controller:
$result = Order::executeReport([
    'row_dimensions' => ['region'],
    'metrics' => [
        ['column' => 'amount', 'aggregate' => 'sum', 'label' => 'Total']
    ]
]);

$metadata = Order::getReportMetadata();
```

### With Custom Dimensions & Metrics

```php
<?php

use Ibekzod\VisualReportBuilder\Traits\Reportable;
use Ibekzod\VisualReportBuilder\Traits\HasDimensions;
use Ibekzod\VisualReportBuilder\Traits\HasMetrics;

class Order extends Model
{
    use Reportable, HasDimensions, HasMetrics;

    protected static function dimensions(): array
    {
        return [
            ['column' => 'region', 'label' => 'Sales Region', 'type' => 'string'],
            ['column' => 'status', 'label' => 'Order Status', 'type' => 'string'],
            ['column' => 'created_at', 'label' => 'Order Date', 'type' => 'date'],
        ];
    }

    protected static function metrics(): array
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

## 🎯 Real-World Examples

### Sales Dashboard

```php
$config = [
    'model' => 'App\Models\Sale',
    'row_dimensions' => ['region', 'sales_person'],
    'column_dimensions' => ['product_category'],
    'metrics' => [
        ['column' => 'amount', 'aggregate' => 'sum', 'label' => 'Sales'],
        ['column' => 'commission', 'aggregate' => 'sum', 'label' => 'Commission'],
        ['column' => 'id', 'aggregate' => 'count', 'label' => 'Transactions']
    ],
    'filters' => [
        'status' => ['completed', 'paid']
    ]
];

$result = execute_report($config);
return response()->json($result);
```

### Financial Analysis

```php
$config = [
    'model' => 'App\Models\Transaction',
    'row_dimensions' => ['department', 'cost_center'],
    'column_dimensions' => ['month', 'year'],
    'metrics' => [
        ['column' => 'budget', 'aggregate' => 'sum', 'label' => 'Budget'],
        ['column' => 'spent', 'aggregate' => 'sum', 'label' => 'Spent'],
        ['column' => 'spent', 'aggregate' => 'avg', 'label' => 'Daily Avg']
    ]
];

$result = execute_report($config);
```

### Inventory Report

```php
$config = [
    'model' => 'App\Models\InventoryItem',
    'row_dimensions' => ['warehouse', 'product_type'],
    'column_dimensions' => ['status'],
    'metrics' => [
        ['column' => 'quantity', 'aggregate' => 'sum', 'label' => 'Total Items'],
        ['column' => 'quantity', 'aggregate' => 'min', 'label' => 'Min Stock'],
        ['column' => 'quantity', 'aggregate' => 'max', 'label' => 'Max Stock']
    ]
];

$result = execute_report($config);
```

## 🔌 API Documentation

### Complete API Reference

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/visual-reports/reports` | List all reports |
| `POST` | `/api/visual-reports/reports` | Create new report |
| `GET` | `/api/visual-reports/reports/{id}` | Get single report |
| `PUT` | `/api/visual-reports/reports/{id}` | Update report |
| `DELETE` | `/api/visual-reports/reports/{id}` | Delete report |
| `POST` | `/api/visual-reports/reports/{id}/execute` | Execute report |
| `POST` | `/api/visual-reports/reports/{id}/share` | Share with user |
| `DELETE` | `/api/visual-reports/reports/{id}/unshare` | Stop sharing |
| `GET` | `/api/visual-reports/models` | List available models |
| `GET` | `/api/visual-reports/models/{model}` | Get model metadata |
| `GET` | `/api/visual-reports/models/{model}/dimensions` | Get dimensions |
| `GET` | `/api/visual-reports/models/{model}/metrics` | Get metrics |
| `POST` | `/api/visual-reports/preview` | Preview configuration |
| `POST` | `/api/visual-reports/reports/{id}/export/csv` | Export as CSV |
| `POST` | `/api/visual-reports/reports/{id}/export/excel` | Export as Excel |
| `POST` | `/api/visual-reports/reports/{id}/export/pdf` | Export as PDF |
| `POST` | `/api/visual-reports/reports/{id}/export/json` | Export as JSON |

### Example: Create and Execute Report

```bash
# Create a report
curl -X POST http://yourapp.test/api/visual-reports/reports \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -d '{
    "name": "Monthly Sales",
    "model": "App\\Models\\Order",
    "configuration": {
      "row_dimensions": ["region"],
      "column_dimensions": ["month"],
      "metrics": [
        {
          "column": "amount",
          "aggregate": "sum",
          "label": "Total Sales"
        }
      ]
    }
  }'

# Execute the report
curl -X POST http://yourapp.test/api/visual-reports/reports/1/execute \
  -H "Authorization: Bearer YOUR_TOKEN"

# Export as Excel
curl -X POST http://yourapp.test/api/visual-reports/reports/1/export/excel \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o sales_report.xlsx
```

## 🎨 Aggregate Functions

Choose from 6+ aggregate functions:

| Function | Description | Example |
|----------|-------------|---------|
| **sum** | Total of all values | 100 + 200 + 300 = 600 |
| **avg** | Average/Mean value | (100 + 200 + 300) / 3 = 200 |
| **min** | Minimum value | min(100, 200, 300) = 100 |
| **max** | Maximum value | max(100, 200, 300) = 300 |
| **count** | Number of records | 3 records = 3 |
| **count_distinct** | Unique values | count_distinct(1, 1, 2) = 2 |
| **value** | Raw value | First value |

## 📊 Report Configuration

Complete configuration format:

```php
[
    // Required: The Eloquent model to query
    'model' => 'App\Models\Order',

    // Optional: Dimensions for row grouping
    'row_dimensions' => ['region', 'status'],

    // Optional: Dimensions for column grouping
    'column_dimensions' => ['month', 'year'],

    // Optional: Metrics to calculate
    'metrics' => [
        [
            'column' => 'amount',           // Column to aggregate
            'aggregate' => 'sum',           // Aggregate function
            'label' => 'Total Sales',       // Display label
            'alias' => 'total_sales'        // Alias in results
        ]
    ],

    // Optional: Filter data
    'filters' => [
        'status' => ['completed', 'paid'],
        'region' => ['North', 'South']
    ],

    // Optional: Sort results
    'order_by' => [
        ['column' => 'region', 'direction' => 'asc'],
        ['column' => 'amount', 'direction' => 'desc']
    ],

    // Optional: Limit results
    'limit' => 100,
    'offset' => 0,

    // Optional: Include row/column totals (default: true)
    'include_totals' => true
]
```

## 🔐 Security & Permissions

### User Ownership

Every report is owned by a user. Users can only see/edit reports they own or that are shared with them.

### Sharing Reports

```php
// Share report with specific user
$report->shareWith($userId, $canEdit = false, $canShare = false);

// Grant edit permission
$report->shareWith($userId, $canEdit = true);

// Grant share permission
$report->shareWith($userId, $canEdit = true, $canShare = true);

// Stop sharing
$report->stopSharingWith($userId);
```

### Built-in Security

✅ SQL injection prevention (parameterized queries)
✅ CSRF protection
✅ User ownership verification
✅ Authorization policies
✅ Input validation
✅ Granular permissions (can_edit, can_share)

## ⚙️ Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=visual-report-builder-config
```

Edit `config/visual-report-builder.php`:

```php
return [
    // Route prefix
    'prefix' => 'visual-reports',

    // Web middleware
    'middleware' => ['web', 'auth'],

    // API middleware
    'api_middleware' => ['api', 'auth:sanctum'],

    // Enable/disable exporters
    'exporters' => [
        'csv' => true,
        'excel' => true,
        'pdf' => true,
        'json' => true,
    ],

    // Caching configuration
    'cache' => [
        'enabled' => true,
        'ttl' => 3600,  // 1 hour
    ],

    // Auto-discover models
    'models' => [
        'auto_discover' => true,
        'namespace' => 'App\\Models',
    ],

    // Pivot table configuration
    'pivot' => [
        'max_dimensions' => 5,
        'max_metrics' => 10,
        'include_totals' => true,
    ],
];
```

## 📚 Helper Functions

Quick helper functions for common tasks:

```php
// Get the report builder instance
visual_report_builder();

// Execute a report quickly
execute_report($config);

// Export data
export_report($data, 'excel', $options);

// Get model metadata (dimensions and metrics)
get_report_metadata('App\Models\Order');

// Get all available models
get_available_models();
```

## 🛠️ Making Models Reportable

### Option 1: Use the Trait

```php
use Ibekzod\VisualReportBuilder\Traits\Reportable;

class Order extends Model
{
    use Reportable;
}
```

This auto-discovers dimensions and metrics from your model's columns.

### Option 2: Define Custom Dimensions & Metrics

```php
use Ibekzod\VisualReportBuilder\Traits\Reportable;
use Ibekzod\VisualReportBuilder\Traits\HasDimensions;
use Ibekzod\VisualReportBuilder\Traits\HasMetrics;

class Order extends Model
{
    use Reportable, HasDimensions, HasMetrics;

    protected static function dimensions(): array
    {
        return [
            ['column' => 'region', 'label' => 'Region', 'type' => 'string'],
            ['column' => 'status', 'label' => 'Status', 'type' => 'string'],
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
        ];
    }
}
```

## 🌐 Web Interface

### Dashboard (`/visual-reports`)

View and manage all your reports:
- **Create Report** - Start building a new report
- **Edit** - Modify existing reports
- **Delete** - Remove reports
- **Share** - Share with team members

### Builder (`/visual-reports/builder`)

Visually create reports with no code:
- Select data source (model)
- Choose row dimensions
- Choose column dimensions
- Add metrics to calculate
- Preview in real-time
- Save or export

## 📤 Exporting Reports

### Export to CSV

```php
$result = execute_report($config);
$csv = VisualReportBuilder::export($result, 'csv');
```

### Export to Excel

```php
$builder = app('visual-report-builder');
return $builder->exportAsFile($result, 'excel', 'report.xlsx');
```

### Export to PDF

```php
$builder = app('visual-report-builder');
return $builder->exportAsFile($result, 'pdf', 'report.pdf', [
    'title' => 'Sales Report',
    'orientation' => 'landscape'
]);
```

### Export to JSON

```php
$json = VisualReportBuilder::export($result, 'json', ['pretty' => true]);
```

## 🚀 Performance Tips

1. **Use Caching** - Enable caching for frequently accessed reports
2. **Limit Dimensions** - Too many dimensions slow down pivot calculation
3. **Apply Filters** - Filter data at query time, not after fetching
4. **Use Appropriate Aggregates** - Choose the right aggregate function
5. **Index Your Tables** - Ensure proper database indices on dimension/metric columns

## 🐛 Troubleshooting

### Models Not Showing in Builder?

The package auto-discovers models in `app/Models`. If your models are elsewhere:

```bash
php artisan vendor:publish --tag=visual-report-builder-config
```

Edit config to set your models namespace.

### Routes Returning 404?

Clear the route cache:

```bash
php artisan route:clear
php artisan cache:clear
```

### Export Not Working?

Install optional dependencies:

```bash
# For Excel export
composer require maatwebsite/excel

# For PDF export
composer require barryvdh/laravel-dompdf
```

### SQL Errors?

- Check column names match exactly
- Verify model table name is correct
- Test queries in `php artisan tinker`

## 📖 Full Documentation

For complete documentation, see:

- [SETUP_GUIDE.md](SETUP_GUIDE.md) - Installation & quick start
- [PACKAGE_README.md](PACKAGE_README.md) - Complete reference
- [BUILD_SUMMARY.md](BUILD_SUMMARY.md) - Architecture & technical details

## 🤝 Contributing

Contributions welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Add tests for your changes
4. Submit a pull request

## 📄 License

MIT License - see [LICENSE](LICENSE) file for details

---

**Built for developers who want powerful reports without the complexity.** ✨

[View on GitHub](https://github.com/ibekzod/visual-report-builder) • [Report Issue](https://github.com/ibekzod/visual-report-builder/issues) • [Discussions](https://github.com/ibekzod/visual-report-builder/discussions)
