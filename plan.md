# 📊 VISUAL REPORT BUILDER - Laravel Composer Package

**Like Kyubit.com - A Complete Laravel Package for Visual Report Building**

A single Composer package that provides:
- 🎨 Visual Report Builder UI (drag-n-drop, no code)
- 📊 Multi-dimensional Pivot Tables
- 🔄 Dynamic Filters, Dimensions, Metrics  
- 💾 Save/Load Report Templates
- 📥 Multiple Data Sources (Any Eloquent Model)
- 📤 Multiple Export Formats (CSV, Excel, PDF, JSON)
- 🔐 Role-Based Access Control
- 🚀 RESTful API + Embedded Web UI

**Installation:** `composer require yourname/visual-report-builder`

---

## 📋 COMPLETE ARCHITECTURE

### Package Structure

```
visual-report-builder/
├── src/
│   ├── VisualReportBuilderServiceProvider.php    # Package service provider
│   ├── config/
│   │   └── visual-report-builder.php             # Package config
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ReportController.php              # Report CRUD
│   │   │   ├── BuilderController.php             # Builder endpoints
│   │   │   ├── DataSourceController.php          # Data source endpoints
│   │   │   ├── ExportController.php              # Export endpoints
│   │   │   └── TemplateController.php            # Template endpoints
│   │   │
│   │   ├── Requests/
│   │   │   ├── StoreReportRequest.php
│   │   │   ├── UpdateReportRequest.php
│   │   │   └── ExecuteReportRequest.php
│   │   │
│   │   └── Resources/
│   │       ├── ReportResource.php
│   │       ├── DimensionResource.php
│   │       └── MetricResource.php
│   │
│   ├── Models/
│   │   ├── Report.php                           # Report model
│   │   ├── ReportTemplate.php                   # Template model
│   │   ├── SavedReport.php                      # Saved report results
│   │   ├── DataSource.php                       # Data source config
│   │   └── ReportShare.php                      # Sharing permissions
│   │
│   ├── Services/
│   │   ├── ReportBuilder.php                    # Core report builder
│   │   ├── QueryBuilder.php                     # SQL query generation
│   │   ├── PivotEngine.php                      # Pivot table generation
│   │   ├── DataSourceManager.php                # Data source management
│   │   ├── ExporterFactory.php                  # Export factory
│   │   ├── AggregateCalculator.php              # Aggregate functions
│   │   └── FilterManager.php                    # Filter logic
│   │
│   ├── Exporters/
│   │   ├── BaseExporter.php                     # Abstract exporter
│   │   ├── CSVExporter.php                      # CSV export
│   │   ├── ExcelExporter.php                    # Excel export
│   │   ├── PDFExporter.php                      # PDF export
│   │   └── JSONExporter.php                     # JSON export
│   │
│   ├── Traits/
│   │   ├── HasDimensions.php                    # Add dimensions support
│   │   ├── HasMetrics.php                       # Add metrics support
│   │   └── Reportable.php                       # Make models reportable
│   │
│   ├── Events/
│   │   ├── ReportCreated.php
│   │   ├── ReportExecuted.php
│   │   └── ReportExported.php
│   │
│   ├── Listeners/
│   │   ├── LogReportCreated.php
│   │   └── CacheReportResults.php
│   │
│   ├── Jobs/
│   │   ├── GenerateReport.php                   # Async report generation
│   │   ├── ExportReport.php                     # Async export
│   │   └── CacheReport.php                      # Cache warming
│   │
│   ├── Database/
│   │   ├── Migrations/
│   │   │   ├── create_reports_table.php
│   │   │   ├── create_report_templates_table.php
│   │   │   ├── create_saved_reports_table.php
│   │   │   ├── create_data_sources_table.php
│   │   │   └── create_report_shares_table.php
│   │   │
│   │   ├── Seeders/
│   │   │   └── ReportTemplateSeeder.php
│   │   │
│   │   └── Factories/
│   │       ├── ReportFactory.php
│   │       └── TemplateFactory.php
│   │
│   ├── Resources/
│   │   ├── views/
│   │   │   ├── layouts/
│   │   │   │   └── app.blade.php
│   │   │   ├── index.blade.php                  # Main UI page
│   │   │   ├── builder.blade.php                # Builder page
│   │   │   ├── reports/
│   │   │   │   ├── index.blade.php
│   │   │   │   ├── create.blade.php
│   │   │   │   ├── edit.blade.php
│   │   │   │   └── show.blade.php
│   │   │   └── components/
│   │   │       ├── builder-canvas.blade.php
│   │   │       ├── dimension-selector.blade.php
│   │   │       ├── metric-selector.blade.php
│   │   │       ├── filter-builder.blade.php
│   │   │       └── preview-panel.blade.php
│   │   │
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   ├── app.css
│   │   │   │   └── builder.css
│   │   │   ├── js/
│   │   │   │   ├── app.js
│   │   │   │   ├── builder.js
│   │   │   │   ├── canvas.js
│   │   │   │   └── preview.js
│   │   │   └── vendor/
│   │   │       ├── vue.global.js
│   │   │       ├── chart.js
│   │   │       └── apexcharts.js
│   │   │
│   │   └── lang/
│   │       ├── en/
│   │       │   ├── messages.php
│   │       │   └── validation.php
│   │       └── fr/
│   │           └── messages.php
│   │
│   ├── Routes/
│   │   ├── api.php                              # API routes
│   │   └── web.php                              # Web routes
│   │
│   ├── Policies/
│   │   ├── ReportPolicy.php
│   │   ├── TemplatePolicy.php
│   │   └── DataSourcePolicy.php
│   │
│   └── Observers/
│       └── ReportObserver.php
│
├── tests/
│   ├── Feature/
│   │   ├── ReportTest.php
│   │   ├── BuilderTest.php
│   │   ├── ExportTest.php
│   │   └── ExporterTest.php
│   │
│   ├── Unit/
│   │   ├── QueryBuilderTest.php
│   │   ├── PivotEngineTest.php
│   │   └── AggregateCalculatorTest.php
│   │
│   └── TestCase.php
│
├── composer.json                                 # Package configuration
├── package.json                                  # Frontend dependencies
├── webpack.mix.js                                # Laravel Mix
├── phpunit.xml                                   # PHPUnit config
├── README.md                                     # Package documentation
├── CHANGELOG.md                                  # Version history
├── LICENSE                                       # MIT License
└── plan.md                                       # THIS FILE
```

---

## 🔧 CORE COMPONENTS

### 1. Service Provider (`VisualReportBuilderServiceProvider.php`)

```php
class VisualReportBuilderServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/visual-report-builder.php', 'visual-report-builder');
        $this->app->singleton('visual-report-builder', function () {
            return new ReportBuilder();
        });
    }

    public function boot()
    {
        // Register routes
        $this->loadRoutesFrom(__DIR__.'/../Routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../Routes/web.php');
        
        // Register migrations
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');
        
        // Register views
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'visual-report-builder');
        
        // Register translations
        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'visual-report-builder');
        
        // Publish migrations
        $this->publishes([
            __DIR__.'/../Database/Migrations' => database_path('migrations'),
            __DIR__.'/../config/visual-report-builder.php' => config_path('visual-report-builder.php'),
            __DIR__.'/../Resources/assets' => resource_path('visual-report-builder'),
        ]);
    }
}
```

### 2. Report Builder Service (`ReportBuilder.php`)

```php
class ReportBuilder
{
    protected QueryBuilder $queryBuilder;
    protected PivotEngine $pivotEngine;
    protected DataSourceManager $dataSourceManager;
    
    /**
     * Execute report with configuration
     */
    public function execute(array $config): array
    {
        // Validate configuration
        $this->validateConfig($config);
        
        // Get data source
        $model = $config['model'] ?? null;
        
        // Build query
        $query = $this->queryBuilder->build($config, $model);
        
        // Execute and get results
        $results = $query->get();
        
        // Build pivot if needed
        if ($config['row_dimensions'] || $config['column_dimensions']) {
            return $this->pivotEngine->build($results, $config);
        }
        
        return $results->toArray();
    }
    
    /**
     * Build configuration for UI
     */
    public function getMetadata(string $model): array
    {
        $modelInstance = new $model();
        
        return [
            'dimensions' => $this->getDimensions($modelInstance),
            'metrics' => $this->getMetrics($modelInstance),
        ];
    }
}
```

### 3. Query Builder (`QueryBuilder.php`)

```php
class QueryBuilder
{
    /**
     * Build Eloquent query from configuration
     */
    public function build(array $config, string $model)
    {
        $query = $model::query();
        
        // Add dimensions to select
        $dimensions = $config['row_dimensions'] ?? [];
        $columnDims = $config['column_dimensions'] ?? [];
        $allDims = array_merge($dimensions, $columnDims);
        
        if ($allDims) {
            $query->selectRaw(implode(',', $allDims));
        }
        
        // Add metric aggregates
        foreach ($config['metrics'] ?? [] as $metric) {
            $column = $metric['column'];
            $aggregate = $metric['aggregate'];
            
            match($aggregate) {
                'sum' => $query->selectRaw("SUM({$column}) as {$column}_sum"),
                'avg' => $query->selectRaw("AVG({$column}) as {$column}_avg"),
                'min' => $query->selectRaw("MIN({$column}) as {$column}_min"),
                'max' => $query->selectRaw("MAX({$column}) as {$column}_max"),
                'count' => $query->selectRaw("COUNT({$column}) as {$column}_count"),
                default => null,
            };
        }
        
        // Add GROUP BY
        if ($allDims) {
            $query->groupBy($allDims);
        }
        
        // Apply filters
        $this->applyFilters($query, $config);
        
        return $query;
    }
}
```

### 4. Pivot Engine (`PivotEngine.php`)

```php
class PivotEngine
{
    /**
     * Build multi-dimensional pivot table
     */
    public function build(Collection $data, array $config): array
    {
        $rowDims = $config['row_dimensions'] ?? [];
        $colDims = $config['column_dimensions'] ?? [];
        $metrics = $config['metrics'] ?? [];
        
        // Build headers
        $rowHeaders = $this->buildRowHeaders($data, $rowDims);
        $colHeaders = $this->buildColumnHeaders($data, $colDims);
        
        // Build data matrix
        $matrix = $this->buildMatrix($data, $rowHeaders, $colHeaders, $metrics);
        
        // Calculate totals
        $rowTotals = $this->calculateRowTotals($matrix, $metrics);
        $colTotals = $this->calculateColumnTotals($matrix, $metrics);
        $grandTotal = $this->calculateGrandTotal($matrix, $metrics);
        
        return [
            'row_headers' => $rowHeaders,
            'column_headers' => $colHeaders,
            'data_matrix' => $matrix,
            'row_totals' => $rowTotals,
            'column_totals' => $colTotals,
            'grand_total' => $grandTotal,
        ];
    }
}
```

### 5. Exporters

```php
// BaseExporter.php
abstract class BaseExporter
{
    abstract public function export(array $data, array $config): string;
    
    protected function formatValue($value, string $column): string
    {
        // Format based on column type
    }
}

// CSVExporter.php
class CSVExporter extends BaseExporter
{
    public function export(array $data, array $config): string
    {
        // Generate CSV
    }
}

// ExcelExporter.php
class ExcelExporter extends BaseExporter
{
    public function export(array $data, array $config): string
    {
        // Generate Excel using PhpSpreadsheet
    }
}

// PDFExporter.php
class PDFExporter extends BaseExporter
{
    public function export(array $data, array $config): string
    {
        // Generate PDF using DomPDF
    }
}
```

### 6. Traits for Models

```php
// Make any Eloquent model reportable
trait Reportable
{
    public function getDimensions(): array
    {
        // Auto-discover dimensions from relationships
    }
    
    public function getMetrics(): array
    {
        // Auto-discover metrics from columns
    }
}
```

---

## 🚀 API ENDPOINTS

### Authentication
```
POST   /api/visual-reports/auth/login          Login
POST   /api/visual-reports/auth/logout         Logout
GET    /api/visual-reports/auth/user           Current user
```

### Reports
```
GET    /api/visual-reports/reports             List reports
POST   /api/visual-reports/reports             Create report
GET    /api/visual-reports/reports/{id}        Get report
PUT    /api/visual-reports/reports/{id}        Update report
DELETE /api/visual-reports/reports/{id}        Delete report
POST   /api/visual-reports/reports/{id}/execute Execute
```

### Builder
```
GET    /api/visual-reports/models              List available models
GET    /api/visual-reports/models/{model}      Get model metadata
GET    /api/visual-reports/{model}/dimensions  Available dimensions
GET    /api/visual-reports/{model}/metrics     Available metrics
```

### Templates
```
GET    /api/visual-reports/templates           List templates
POST   /api/visual-reports/templates           Create template
GET    /api/visual-reports/templates/{id}      Get template
```

### Exports
```
POST   /api/visual-reports/reports/{id}/export/csv      CSV
POST   /api/visual-reports/reports/{id}/export/excel    Excel
POST   /api/visual-reports/reports/{id}/export/pdf      PDF
POST   /api/visual-reports/reports/{id}/export/json     JSON
```

---

## 📊 DATABASE TABLES

```php
// create_reports_table.php
Schema::create('reports', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('model'); // Eloquent model class
    $table->json('configuration'); // Report config
    $table->json('view_options')->nullable();
    $table->foreignId('user_id')->constrained();
    $table->timestamps();
});

// create_report_templates_table.php
Schema::create('report_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('model');
    $table->json('default_config');
    $table->json('allowed_metrics')->nullable();
    $table->json('allowed_dimensions')->nullable();
    $table->timestamps();
});

// create_saved_reports_table.php
Schema::create('saved_reports', function (Blueprint $table) {
    $table->id();
    $table->foreignId('report_id')->constrained();
    $table->json('data'); // Cached results
    $table->timestamp('cached_at');
    $table->integer('cache_duration')->default(3600);
    $table->timestamps();
});
```

---

## 🎯 INSTALLATION & USAGE

### Installation

```bash
composer require yourname/visual-report-builder
```

### Configuration

```bash
# Publish config
php artisan vendor:publish --provider="YourNamespace\VisualReportBuilder\VisualReportBuilderServiceProvider"

# Run migrations
php artisan migrate
```

### Usage - Basic

```php
// In controller
use YourNamespace\VisualReportBuilder\Facades\VisualReportBuilder;

$config = [
    'model' => 'App\Models\Contract',
    'row_dimensions' => ['region', 'status'],
    'column_dimensions' => ['month'],
    'metrics' => [
        ['column' => 'amount', 'aggregate' => 'sum', 'label' => 'Total'],
        ['column' => 'id', 'aggregate' => 'count', 'label' => 'Count']
    ]
];

$result = VisualReportBuilder::execute($config);
return response()->json($result);
```

### Usage - With Trait

```php
// app/Models/Contract.php
use YourNamespace\VisualReportBuilder\Traits\Reportable;

class Contract extends Model
{
    use Reportable;
}

// In controller
$metadata = Contract::getReportMetadata();
```

### Usage - Web UI

```
Visit: /visual-reports
- Login with your Laravel user
- Select data source (Eloquent model)
- Drag dimensions and metrics
- Preview report
- Save or export
```

---

## 🔐 CONFIGURATION

```php
// config/visual-report-builder.php
return [
    'prefix' => 'visual-reports',
    
    'middleware' => ['web', 'auth'],
    
    'exporters' => [
        'csv' => true,
        'excel' => true,
        'pdf' => true,
        'json' => true,
    ],
    
    'cache' => [
        'enabled' => true,
        'ttl' => 3600, // 1 hour
    ],
    
    'models' => [
        // Auto-discover models or specify whitelist
        'auto_discover' => true,
    ],
    
    'auth' => [
        'guard' => 'web',
        'verify_ownership' => true,
    ],
];
```

---

## 🧪 TESTING

```bash
# Run tests
php artisan test packages/visual-report-builder

# Run with coverage
php artisan test packages/visual-report-builder --coverage
```

---

## 📦 DEPENDENCIES

```json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0",
        "laravel/tinker": "^2.8",
        "maatwebsite/excel": "^3.1",
        "barryvdh/laravel-dompdf": "^2.0",
        "spatie/laravel-permission": "^6.0"
    },
    "require-dev": {
        "phpunit/phpunit": "^10.0",
        "laravel/pint": "^1.0"
    }
}
```

---

## 🎨 FEATURES

### Multi-Dimensional Pivots
```
Rows:    Region → Building → Client
Columns: Status → Month
Metrics: Sum, Avg, Min, Max, Count, Value
```

### 6 Aggregate Functions
- Sum (∑) - Total
- Average (μ) - Mean
- Minimum (↓) - Lowest
- Maximum (↑) - Highest
- Count (#) - Records
- Value (=) - Raw

### Multiple Data Sources
- Any Eloquent model
- Multiple models in same report
- Relationships support
- Custom data sources

### Exports
- CSV (Spreadsheet)
- Excel (Formatted)
- PDF (Professional)
- JSON (API)

### UI Features
- Drag-n-drop builder
- Real-time preview
- Save templates
- Share reports
- Multiple visualizations

---

## 🔄 WORKFLOW

1. **Install Package**
   ```bash
   composer require yourname/visual-report-builder
   php artisan migrate
   ```

2. **Make Models Reportable**
   ```php
   use Reportable;
   ```

3. **Visit Web UI**
   ```
   http://localhost:8000/visual-reports
   ```

4. **Build Report**
   - Select model
   - Add dimensions
   - Add metrics
   - Preview
   - Save/Export

5. **Use API**
   ```php
   POST /api/visual-reports/reports
   ```

---

## 🚀 DEVELOPMENT WITH CLAUDE CODE

**For Claude Code Development:**

```markdown
Task: Implement VisualReportBuilderServiceProvider
Requirements:
1. Register service container bindings
2. Load routes from api.php and web.php
3. Load migrations from database folder
4. Register views with namespace 'visual-report-builder'
5. Publish config file when vendor:publish
6. Load and publish assets

Reference: Laravel ServiceProvider documentation
Include: dependency injection, facades, route registration
```

---

## ✅ CHECKLIST

### MVP (Minimum Viable Product)
- [ ] Service provider works
- [ ] Routes registered
- [ ] Migrations run
- [ ] API endpoints respond
- [ ] Web UI loads
- [ ] Report execution works
- [ ] CSV export works

### Full Release
- [ ] All exporters work
- [ ] All features implemented
- [ ] Complete tests
- [ ] Full documentation
- [ ] Published on Packagist

---

## 📚 DOCUMENTATION

- README.md - Installation & quick start
- DOCUMENTATION.md - Full guide
- API.md - API reference
- EXAMPLES.md - Usage examples
- TESTING.md - Testing guide

---

## 🎉 READY TO BUILD?

This is a complete, production-ready Laravel Composer package for visual report building like Kyubit.com!

**Installation:**
```bash
composer require yourname/visual-report-builder
```

**Next:** Implement using Claude Code following this plan!
