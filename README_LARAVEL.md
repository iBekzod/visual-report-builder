# 📊 Visual Report Builder - Laravel Composer Package

**Build Professional Reports Without Code - Like Kyubit.com**

A powerful Laravel Composer package for creating multi-dimensional pivot tables, visual reports, and data analysis dashboards without writing any code.

## ✨ Features

### Visual Report Builder UI
- 🎨 Drag-and-drop interface
- 📊 Real-time preview
- 💾 Save templates
- 🔄 Share with team
- 📤 Multiple export formats

### Multi-Dimensional Pivot Tables
```
Rows:    Region → Building → Client
Columns: Status → Month
Metrics: Sum, Average, Min, Max, Count, Raw Value
```

### 6 Aggregate Functions
- **Sum** (∑) - Total values
- **Average** (μ) - Mean
- **Minimum** (↓) - Lowest value
- **Maximum** (↑) - Highest value
- **Count** (#) - Number of records
- **Value** (=) - Raw value, no aggregation

### Data Sources
- Any Eloquent model
- Relationships support
- Custom data sources
- Multiple models in one report

### Export Formats
- CSV (Spreadsheet compatible)
- Excel (Formatted)
- PDF (Professional)
- JSON (API integration)

### Advanced Features
- Role-based access control
- Report templates
- Result caching
- Async export
- Event listeners
- Customizable UI

---

## 🚀 Installation

### Step 1: Install Package

```bash
composer require yourname/visual-report-builder
```

### Step 2: Publish Assets

```bash
php artisan vendor:publish --provider="YourNamespace\VisualReportBuilder\VisualReportBuilderServiceProvider"
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Done! 🎉

---

## 📖 Quick Start

### Basic Usage

```php
use VisualReportBuilder;

// Build report configuration
$config = [
    'model' => 'App\Models\Contract',
    'row_dimensions' => ['region', 'status'],
    'column_dimensions' => ['month'],
    'metrics' => [
        ['column' => 'amount', 'aggregate' => 'sum', 'label' => 'Total'],
        ['column' => 'id', 'aggregate' => 'count', 'label' => 'Orders']
    ]
];

// Execute report
$result = VisualReportBuilder::execute($config);

// Export to Excel
VisualReportBuilder::export($result, 'excel', 'report.xlsx');
```

### Make Model Reportable

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use YourNamespace\VisualReportBuilder\Traits\Reportable;

class Contract extends Model
{
    use Reportable;
}
```

### Web UI

Visit: `http://yourapp.test/visual-reports`

1. Login with your Laravel user
2. Select a data source (Eloquent model)
3. Drag dimensions and metrics to canvas
4. Preview report in real-time
5. Save template or export

---

## 🎯 API Endpoints

### Reports
```
GET    /api/visual-reports/reports              List reports
POST   /api/visual-reports/reports              Create report
GET    /api/visual-reports/reports/{id}         Get report
PUT    /api/visual-reports/reports/{id}         Update report
DELETE /api/visual-reports/reports/{id}         Delete report
POST   /api/visual-reports/reports/{id}/execute Execute report
```

### Builder
```
GET    /api/visual-reports/models               List available models
GET    /api/visual-reports/models/{model}       Get model metadata
GET    /api/visual-reports/{model}/dimensions   Available dimensions
GET    /api/visual-reports/{model}/metrics      Available metrics
```

### Templates
```
GET    /api/visual-reports/templates            List templates
POST   /api/visual-reports/templates            Create template
GET    /api/visual-reports/templates/{id}       Get template
```

### Exports
```
POST   /api/visual-reports/reports/{id}/export/csv      Export as CSV
POST   /api/visual-reports/reports/{id}/export/excel    Export as Excel
POST   /api/visual-reports/reports/{id}/export/pdf      Export as PDF
POST   /api/visual-reports/reports/{id}/export/json     Export as JSON
```

---

## 📊 Configuration

Publish config file:

```bash
php artisan vendor:publish --tag=visual-report-builder-config
```

Edit `config/visual-report-builder.php`:

```php
return [
    // Route prefix
    'prefix' => 'visual-reports',
    
    // Middleware
    'middleware' => ['web', 'auth'],
    
    // Enabled exporters
    'exporters' => [
        'csv' => true,
        'excel' => true,
        'pdf' => true,
        'json' => true,
    ],
    
    // Caching
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
    ],
    
    // Model discovery
    'models' => [
        'auto_discover' => true,
        'namespace' => 'App\\Models',
    ],
    
    // Authentication
    'auth' => [
        'guard' => 'web',
        'verify_ownership' => true,
    ],
];
```

---

## 🔧 Advanced Usage

### Custom Data Source

```php
use YourNamespace\VisualReportBuilder\Contracts\DataSourceContract;

class CustomDataSource implements DataSourceContract
{
    public function query()
    {
        // Return collection or array
    }
    
    public function getDimensions(): array
    {
        return ['region', 'status'];
    }
    
    public function getMetrics(): array
    {
        return ['amount', 'count'];
    }
}
```

### Custom Exporter

```php
use YourNamespace\VisualReportBuilder\Exporters\BaseExporter;

class CustomExporter extends BaseExporter
{
    public function export(array $data, array $config): string
    {
        // Your export logic
        return $content;
    }
}
```

### Async Export

```php
use YourNamespace\VisualReportBuilder\Jobs\ExportReport;

// Queue export job
dispatch(new ExportReport($report, 'excel'));

// Get export URL when ready
Route::get('/exports/{id}', function ($id) {
    return storage_path("exports/{$id}.xlsx");
});
```

### Events

```php
use YourNamespace\VisualReportBuilder\Events\ReportExecuted;
use YourNamespace\VisualReportBuilder\Events\ReportExported;

// Listen to events
Event::listen(ReportExecuted::class, function ($event) {
    // Log or process
});
```

---

## 📦 Database Tables

Package creates these tables:

- `reports` - Report definitions
- `report_templates` - Template configurations
- `saved_reports` - Saved report results
- `data_sources` - Data source configurations
- `report_shares` - Report sharing permissions

---

## 🧪 Testing

```bash
# Run tests
php artisan test packages/visual-report-builder

# With coverage
php artisan test packages/visual-report-builder --coverage

# Specific test
php artisan test packages/visual-report-builder --filter=ReportTest
```

---

## 🎨 Customization

### Publish Views

```bash
php artisan vendor:publish --tag=visual-report-builder-views
```

Edit views in `resources/views/vendor/visual-report-builder/`

### Publish Assets

```bash
php artisan vendor:publish --tag=visual-report-builder-assets
```

Modify CSS/JS in `public/vendor/visual-report-builder/`

---

## 📚 Real-World Examples

### Sales Report

```php
$config = [
    'model' => 'App\Models\Sale',
    'row_dimensions' => ['region', 'sales_agent'],
    'column_dimensions' => ['product_category'],
    'metrics' => [
        ['column' => 'amount', 'aggregate' => 'sum', 'label' => 'Total Sales'],
        ['column' => 'id', 'aggregate' => 'count', 'label' => 'Orders'],
        ['column' => 'commission', 'aggregate' => 'sum', 'label' => 'Commission']
    ],
    'filters' => [
        'status' => ['completed', 'paid']
    ]
];

$result = VisualReportBuilder::execute($config);
```

### Financial Analysis

```php
$config = [
    'model' => 'App\Models\Transaction',
    'row_dimensions' => ['department', 'cost_center'],
    'column_dimensions' => ['month'],
    'metrics' => [
        ['column' => 'budget', 'aggregate' => 'sum', 'label' => 'Budget'],
        ['column' => 'spent', 'aggregate' => 'sum', 'label' => 'Spent'],
        ['column' => 'spent', 'aggregate' => 'avg', 'label' => 'Daily Avg']
    ]
];
```

### Inventory Report

```php
$config = [
    'model' => 'App\Models\InventoryItem',
    'row_dimensions' => ['warehouse', 'product_type'],
    'column_dimensions' => ['status'],
    'metrics' => [
        ['column' => 'quantity', 'aggregate' => 'sum', 'label' => 'Total Items'],
        ['column' => 'quantity', 'aggregate' => 'min', 'label' => 'Minimum'],
        ['column' => 'quantity', 'aggregate' => 'max', 'label' => 'Maximum']
    ]
];
```

---

## 🔐 Security

- ✅ Automatic user ownership verification
- ✅ Role-based access control
- ✅ SQL injection prevention (parameterized queries)
- ✅ CSRF protection
- ✅ Rate limiting
- ✅ Audit logging

---

## 🐛 Troubleshooting

### Routes not working
```bash
# Clear cache
php artisan route:clear
php artisan cache:clear
```

### Assets not loading
```bash
php artisan vendor:publish --tag=visual-report-builder-assets --force
```

### Models not showing
```bash
# Check config
php artisan vendor:publish --tag=visual-report-builder-config

# Edit models namespace in config/visual-report-builder.php
```

---

## 📖 Full Documentation

- [API Documentation](docs/api.md)
- [Developer Guide](docs/developer.md)
- [User Guide](docs/user-guide.md)
- [Troubleshooting](docs/troubleshooting.md)

---

## 🤝 Contributing

Contributions welcome! Please:

1. Fork the repository
2. Create a feature branch
3. Add tests for your changes
4. Submit a pull request

---

## 📄 License

MIT License - see LICENSE file for details

---

## 🎉 Support

- 📧 Email: support@example.com
- 💬 GitHub Issues: [Report issue](https://github.com/yourname/visual-report-builder/issues)
- 💬 Discussions: [Ask question](https://github.com/yourname/visual-report-builder/discussions)

---

## 🙏 Acknowledgments

Inspired by Kyubit.com and built for the Laravel community.

---

**Made with ❤️ for Laravel developers**

**Start building powerful reports today!** 📊
