# 🚀 Quick Start Guide - Rebuilt Template-Based System

## Installation

```bash
# 1. Install package
composer require ibekzod/visual-report-builder

# 2. Publish migrations
php artisan vendor:publish --tag=visual-report-builder-migrations

# 3. Run migrations
php artisan migrate

# 4. Visit dashboard
http://yourapp.test/visual-reports
```

---

## Creating Your First Template

### Step 1: Create Template Model

Create a file `app/Console/Commands/CreateSalesTemplate.php`:

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ibekzod\VisualReportBuilder\Models\ReportTemplate;
use Ibekzod\VisualReportBuilder\Models\TemplateFilter;

class CreateSalesTemplate extends Command
{
    protected $signature = 'create:sales-template';

    public function handle()
    {
        $template = ReportTemplate::create([
            'user_id' => 1,  // Admin user
            'name' => 'Sales Dashboard',
            'description' => 'Monthly sales by region and product',
            'model' => 'App\\Models\\Order',
            'icon' => '💰',
            'category' => 'Sales',
            'is_active' => true,
            'is_public' => true,

            'dimensions' => [
                [
                    'column' => 'region',
                    'label' => 'Region',
                    'type' => 'string'
                ],
                [
                    'column' => 'created_at',
                    'label' => 'Month',
                    'type' => 'date'
                ]
            ],

            'metrics' => [
                [
                    'column' => 'amount',
                    'label' => 'Total Sales',
                    'aggregate' => 'sum',
                    'type' => 'decimal'
                ],
                [
                    'column' => 'id',
                    'label' => 'Order Count',
                    'aggregate' => 'count',
                    'type' => 'integer'
                ]
            ],

            'default_view' => [
                'type' => 'table',
                'options' => [
                    'sortBy' => 'total_sales',
                    'order' => 'desc'
                ]
            ],

            'chart_config' => [
                'responsive' => true,
                'maintainAspectRatio' => false
            ]
        ]);

        // Add filters
        TemplateFilter::create([
            'report_template_id' => $template->id,
            'column' => 'region',
            'label' => 'Filter by Region',
            'type' => 'select',
            'operator' => '=',
            'options' => [
                ['value' => 'North', 'label' => 'North Region'],
                ['value' => 'South', 'label' => 'South Region'],
                ['value' => 'East', 'label' => 'East Region'],
                ['value' => 'West', 'label' => 'West Region'],
            ],
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 1
        ]);

        TemplateFilter::create([
            'report_template_id' => $template->id,
            'column' => 'created_at',
            'label' => 'Date Range',
            'type' => 'daterange',
            'operator' => 'between',
            'is_required' => false,
            'is_active' => true,
            'sort_order' => 2
        ]);

        $this->info('Sales Dashboard template created!');
    }
}
```

Run it:
```bash
php artisan create:sales-template
```

### Step 2: Access Dashboard

Open your browser:
```
http://yourapp.test/visual-reports
```

You'll see:
- **Left Sidebar**: Sales Dashboard template
- **Center**: Click to select it and choose filters
- **Right Sidebar**: Empty (no saved reports yet)

### Step 3: Execute Report

1. Click "Sales Dashboard" in left sidebar
2. Select filters (Region, Date Range)
3. Click "▶️ Execute"
4. View results as Table/Chart

### Step 4: Save Report

1. Click "💾 Save" button
2. Enter name: "Q1 2024 - North Region"
3. Click "Save"
4. Saved report appears in right sidebar

---

## Dashboard Features

### 🔍 Filters

Automatically rendered based on template definition:

```json
{
  "column": "region",
  "label": "Select Region",
  "type": "select",
  "operator": "=",
  "options": [
    {"value": "North", "label": "North Region"},
    {"value": "South", "label": "South Region"}
  ]
}
```

**Filter Types:**
- `text`: Simple text input
- `select`: Dropdown menu
- `date`: Single date picker
- `daterange`: Start + end date
- `number`: Numeric input

**Operators:**
- `=`: Equals
- `!=`: Not equals
- `>`: Greater than
- `<`: Less than
- `in`: Multiple values
- `like`: Text contains
- `between`: Range (for dates/numbers)

### 📊 View Types

Switch between visualizations using dropdown:

1. **Table**: Default spreadsheet view
2. **Line Chart**: Trends (Chart.js)
3. **Bar Chart**: Comparisons (Chart.js)
4. **Pie Chart**: Composition (Chart.js)
5. **Area Chart**: Stacked areas (Chart.js)
6. **Scatter**: XY plot (Chart.js)

### 💾 Save Reports

Save execution state:
- **Name**: Report identifier
- **Description**: Optional notes
- **Filters**: Exact filters used (stored as JSON)
- **View Type**: Chart type (table/line/bar/pie)
- **Data**: Query results (cached)

Load from right sidebar by clicking saved report.

### 📥 Export

Export any saved report:
- **CSV**: Excel-compatible spreadsheet
- **Excel**: XLSX format
- **PDF**: Professional document
- **JSON**: Raw data format

### ⭐ Favorites

Star icon to mark frequently used reports.

---

## API Usage

### List Templates

```bash
GET /api/visual-reports/templates
```

Response:
```json
{
  "templates": [
    {
      "id": 1,
      "name": "Sales Dashboard",
      "icon": "💰",
      "category": "Sales",
      "dimensions": [...],
      "metrics": [...],
      "filters": [...]
    }
  ],
  "categories": ["Sales", "Finance", "Operations"]
}
```

### Execute Template

```bash
POST /api/visual-reports/templates/1/execute
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "filters": {
    "region": "North",
    "created_at": ["2024-01-01", "2024-03-31"]
  },
  "view_type": "bar"
}
```

Response:
```json
{
  "success": true,
  "data": {
    "rows": [
      {"region": "North", "month": "2024-01", "amount": 10000},
      {"region": "North", "month": "2024-02", "amount": 12000}
    ],
    "summary": {
      "amount": {"sum": 22000, "avg": 11000, "count": 2}
    }
  },
  "metadata": {
    "execution_time_ms": 145,
    "record_count": 2
  }
}
```

### Save Report

```bash
POST /api/visual-reports/templates/1/save
Content-Type: application/json
Authorization: Bearer YOUR_TOKEN

{
  "name": "Q1 2024 North Sales",
  "description": "Sales data for Q1 2024 in North region",
  "applied_filters": {
    "region": "North",
    "created_at": ["2024-01-01", "2024-03-31"]
  },
  "view_type": "line",
  "view_config": {},
  "data": [...]
}
```

Response:
```json
{
  "success": true,
  "result_id": 42
}
```

### Load Saved Report

```bash
GET /api/visual-reports/results/42
Authorization: Bearer YOUR_TOKEN
```

Response:
```json
{
  "id": 42,
  "name": "Q1 2024 North Sales",
  "applied_filters": {...},
  "view_type": "line",
  "data": [...],
  "created_at": "2024-01-15T10:30:00Z"
}
```

---

## Complete Example: Finance Template

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ibekzod\VisualReportBuilder\Models\ReportTemplate;
use Ibekzod\VisualReportBuilder\Models\TemplateFilter;

class CreateFinanceTemplate extends Command
{
    protected $signature = 'create:finance-template';

    public function handle()
    {
        $template = ReportTemplate::create([
            'user_id' => 1,
            'name' => 'Budget vs Actual',
            'description' => 'Compare budgeted vs actual spending by department',
            'model' => 'App\\Models\\Transaction',
            'icon' => '💰',
            'category' => 'Finance',
            'is_active' => true,

            'dimensions' => [
                [
                    'column' => 'department',
                    'label' => 'Department',
                    'type' => 'string'
                ],
                [
                    'column' => 'created_at',
                    'label' => 'Month',
                    'type' => 'date'
                ]
            ],

            'metrics' => [
                [
                    'column' => 'budget_amount',
                    'label' => 'Budgeted',
                    'aggregate' => 'sum'
                ],
                [
                    'column' => 'actual_amount',
                    'label' => 'Actual Spend',
                    'aggregate' => 'sum'
                ],
                [
                    'column' => 'actual_amount',
                    'label' => 'Average Daily Spend',
                    'aggregate' => 'avg'
                ]
            ],

            'default_view' => ['type' => 'bar'],

            'chart_config' => []
        ]);

        // Department filter
        TemplateFilter::create([
            'report_template_id' => $template->id,
            'column' => 'department',
            'label' => 'Select Department',
            'type' => 'select',
            'operator' => '=',
            'options' => [
                ['value' => 'Engineering', 'label' => 'Engineering'],
                ['value' => 'Sales', 'label' => 'Sales'],
                ['value' => 'Marketing', 'label' => 'Marketing'],
                ['value' => 'Operations', 'label' => 'Operations'],
            ],
            'sort_order' => 1
        ]);

        // Date range filter
        TemplateFilter::create([
            'report_template_id' => $template->id,
            'column' => 'created_at',
            'label' => 'Period',
            'type' => 'daterange',
            'operator' => 'between',
            'sort_order' => 2
        ]);

        $this->info('Finance template created!');
    }
}
```

---

## Troubleshooting

### Templates Not Showing?

1. Check migration ran: `php artisan migrate:status`
2. Verify user is authenticated
3. Check templates exist: `ReportTemplate::count()`
4. Check `is_active` flag is true

### Filters Not Rendering?

1. Verify filters in database: `TemplateFilter::count()`
2. Check `is_active` flag is true
3. Verify JSON structure is valid

### Charts Not Displaying?

1. Check if Chart.js loaded: Open browser console, type `Chart`
2. Verify data has rows: Check execute response
3. Check view type is supported: table, line, bar, pie, area

### Export Not Working?

1. Verify saved report has data
2. Check export format is valid (csv, excel, pdf, json)
3. Check file permissions on storage directory

---

## Next Steps

1. ✅ Create your first template
2. ✅ Execute it with filters
3. ✅ Save a report
4. ✅ Load it from sidebar
5. ✅ Try different visualizations
6. ✅ Export to Excel

Read **REBUILD_ARCHITECTURE.md** for technical details.
