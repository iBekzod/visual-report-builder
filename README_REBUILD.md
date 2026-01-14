# 📊 Visual Report Builder - Template-Based System

> **Professional, production-ready reporting system for Laravel**

[![Latest Version](https://img.shields.io/badge/version-2.0.0-blue.svg)](https://github.com/ibekzod/visual-report-builder)
[![Laravel](https://img.shields.io/badge/laravel-10.0%2B-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/php-8.1%2B-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

A complete Laravel composer package for building professional, template-based reports with role-based access control, multiple visualizations, and team collaboration.

## ⚡ What's New (v2.0.0 - Rebuilt)

### Architecture Changes
- ✅ **Template-Based**: Pre-configured report templates instead of flexible drag-and-drop
- ✅ **3-Column Dashboard**: Professional sidebar + main content + saved reports layout
- ✅ **Role-Based Access**: Control which roles can access specific templates
- ✅ **Dynamic Filters**: Filters defined per template with operators
- ✅ **Multiple Views**: Switch between table, line, bar, pie, area, scatter visualizations
- ✅ **State Preservation**: Save reports with filters, view type, and preferences
- ✅ **Professional UI**: Chart.js and ApexCharts integration for rich visualizations

### Perfect For
- 📊 Business Intelligence Dashboards
- 📈 Executive Reports
- 💰 Financial Analysis
- 📦 Inventory Tracking
- 🎯 Sales Analytics
- 🔍 Data Analysis

---

## 🎯 Key Features

| Feature | Description |
|---------|-------------|
| 🎨 **3-Column Dashboard** | Left sidebar (templates) + Center (report) + Right sidebar (saved) |
| 📊 **Multiple View Types** | Table, Line, Bar, Pie, Area, Scatter charts |
| 🔍 **Dynamic Filters** | Text, select, date, number filters with operators |
| 💾 **Save & Restore** | Save report state (filters, view, preferences) |
| 🔐 **Role-Based Access** | Control template visibility per role |
| 📤 **Export Formats** | CSV, Excel, PDF, JSON export |
| ⭐ **Favorites** | Mark frequently used reports |
| 📈 **Aggregations** | Sum, Count, Avg, Min, Max statistics |
| 🚀 **REST API** | Complete API for integration |
| 📱 **Responsive** | Works on desktop, tablet, mobile |

---

## 🚀 Installation

### Step 1: Install Package

```bash
composer require ibekzod/visual-report-builder
```

### Step 2: Publish Migrations

```bash
php artisan vendor:publish --tag=visual-report-builder-migrations
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Access Dashboard

```
http://yourapp.test/visual-reports
```

---

## 📖 Documentation

### Getting Started
- **[REBUILD_SETUP.md](REBUILD_SETUP.md)** - Quick start guide with examples
- **[REBUILD_ARCHITECTURE.md](REBUILD_ARCHITECTURE.md)** - Complete technical architecture

### API Reference
- **[REBUILD_API.md](REBUILD_API.md)** - Full API documentation with examples

---

## 💡 Quick Start

### 1. Create Your First Template

Create a command `app/Console/Commands/CreateSalesTemplate.php`:

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
            'user_id' => 1,
            'name' => 'Sales Dashboard',
            'description' => 'Monthly sales by region',
            'model' => 'App\\Models\\Order',
            'icon' => '💰',
            'category' => 'Sales',
            'is_active' => true,

            'dimensions' => [
                ['column' => 'region', 'label' => 'Region', 'type' => 'string'],
                ['column' => 'created_at', 'label' => 'Month', 'type' => 'date'],
            ],

            'metrics' => [
                ['column' => 'amount', 'label' => 'Total Sales', 'aggregate' => 'sum'],
                ['column' => 'id', 'label' => 'Order Count', 'aggregate' => 'count'],
            ],

            'default_view' => ['type' => 'table'],
        ]);

        // Add filters
        TemplateFilter::create([
            'report_template_id' => $template->id,
            'column' => 'status',
            'label' => 'Order Status',
            'type' => 'select',
            'operator' => '=',
            'options' => [
                ['value' => 'completed', 'label' => 'Completed'],
                ['value' => 'pending', 'label' => 'Pending'],
            ],
        ]);

        $this->info('Sales template created!');
    }
}
```

Run it:
```bash
php artisan create:sales-template
```

### 2. Visit Dashboard

Open browser to `/visual-reports` and:
1. ✅ See "Sales Dashboard" in left sidebar
2. ✅ Click to select it
3. ✅ Choose filters
4. ✅ Click Execute
5. ✅ View results as table or chart
6. ✅ Save report to right sidebar

---

## 🎨 Dashboard Layout

```
┌─────────────────────────────────────────────────────┐
│              Navigation Bar                          │
├──────────┬──────────────────────────┬──────────────┤
│          │                          │              │
│  LEFT    │     CENTER MAIN          │   RIGHT      │
│          │                          │              │
│Templates │ • Template Name          │ Saved        │
│          │ • Filters Section        │ Reports      │
│ 📊 Sales │ • View Type Toggle       │              │
│ 💰 Finance │ • Report Content       │ • Load      │
│ 📦 Inventory │   (Table/Chart)      │ • Delete    │
│          │ • Summary Stats          │ • Star      │
│          │                          │              │
│ Search   │                          │ Save Button  │
│ Filter   │                          │              │
└──────────┴──────────────────────────┴──────────────┘
```

---

## 🔄 How It Works

### 1. Template Definition
Admin creates templates with:
- **Dimensions**: Grouping fields (region, month, status)
- **Metrics**: Aggregates (sum, count, avg, min, max)
- **Filters**: Available filters with operators

### 2. Execution
User selects template and:
- Applies filters (e.g., region=North, month=Jan)
- Chooses view type (table, chart)
- Executes query

### 3. Results
System returns:
- Grouped data by dimensions
- Calculated metrics with aggregates
- Summary statistics
- Ready to render

### 4. Saving
User can save execution with:
- Report name and description
- Applied filters (so they can reload later)
- View type and preferences
- Cached data

### 5. Sharing
Share with team:
- Set view/edit permissions
- Track who has access
- Control export rights

---

## 📊 Example: Sales Dashboard

### Template Definition
```json
{
  "name": "Sales Dashboard",
  "model": "App\\Models\\Order",

  "dimensions": [
    {"column": "region", "label": "Region"},
    {"column": "created_at", "label": "Month"}
  ],

  "metrics": [
    {"column": "amount", "label": "Total Sales", "aggregate": "sum"},
    {"column": "id", "label": "Orders", "aggregate": "count"}
  ],

  "filters": [
    {"column": "status", "type": "select", "operator": "="},
    {"column": "region", "type": "select", "operator": "="}
  ]
}
```

### Execution
```json
POST /api/visual-reports/templates/1/execute
{
  "filters": {
    "status": "completed",
    "region": "North"
  },
  "view_type": "bar"
}
```

### Response
```json
{
  "data": {
    "rows": [
      {"region": "North", "month": "2024-01", "amount": 10000, "id": 125},
      {"region": "North", "month": "2024-02", "amount": 12000, "id": 145}
    ],
    "summary": {
      "amount": {"sum": 22000, "avg": 11000},
      "id": {"sum": 270, "count": 2}
    }
  }
}
```

---

## 🎯 Use Cases

### Sales Team
- Monthly sales by region and product
- Customer acquisition metrics
- Pipeline analysis
- Performance tracking

### Finance
- Budget vs actual spending
- Revenue by department
- Cash flow analysis
- Cost tracking

### Operations
- Inventory levels by warehouse
- Order fulfillment metrics
- Supplier performance
- Production metrics

### Executive
- KPI dashboards
- Performance summaries
- Trend analysis
- Year-over-year comparisons

---

## 🔐 Access Control

### Role-Based Template Access

```php
// Grant sales team access to Sales Dashboard
$template = ReportTemplate::find(1);

$template->roles()->attach('sales', [
    'can_view' => true,
    'can_export' => true,
    'can_save' => true,
    'can_edit_filters' => false,
]);

// Finance can only view, not export
$template->roles()->attach('finance', [
    'can_view' => true,
    'can_export' => false,
]);
```

---

## 🚀 API Usage

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
  "view_type": "line"
}
```

### Save Report

```bash
POST /api/visual-reports/templates/1/save
{
  "name": "Q1 2024 North Sales",
  "applied_filters": {"region": "North"},
  "view_type": "line",
  "data": [...]
}
```

### Export Report

```bash
POST /api/visual-reports/results/42/export/excel
Authorization: Bearer YOUR_TOKEN
```

---

## 📚 Detailed Guides

### Setting Up First Template
See [REBUILD_SETUP.md](REBUILD_SETUP.md) - Complete step-by-step guide

### Understanding Architecture
See [REBUILD_ARCHITECTURE.md](REBUILD_ARCHITECTURE.md) - Database schema, models, services

### API Reference
See [REBUILD_API.md](REBUILD_API.md) - All endpoints with examples

---

## 🔄 Upgrade from v1.0.0

The rebuilt v2.0.0 is **not backward compatible** with v1.0.0 due to architectural changes.

**Migration Path:**
1. Backup your database
2. Clear old report data
3. Run new migrations
4. Create templates using new system
5. Users start fresh with templates

---

## ⚙️ Configuration

### Supported Aggregates
- `sum`: Total of values
- `count`: Count rows
- `avg`: Average value
- `min`: Minimum value
- `max`: Maximum value
- `count_distinct`: Unique count

### Filter Types
- `text`: Text input
- `select`: Dropdown
- `date`: Date picker
- `daterange`: Date range
- `number`: Number input

### View Types
- `table`: HTML table
- `line`: Line chart
- `bar`: Bar chart
- `pie`: Pie chart
- `area`: Area chart
- `scatter`: Scatter plot

---

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

---

## 📄 License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## 🙋 Support

- 📖 Read the [documentation](REBUILD_ARCHITECTURE.md)
- 📝 Check [API reference](REBUILD_API.md)
- 🚀 Follow the [quick start guide](REBUILD_SETUP.md)

---

## 🎉 What You Get

- ✅ **28+ PHP classes** - Ready-to-use services and controllers
- ✅ **5 Database migrations** - Pre-built schema
- ✅ **3-column dashboard** - Professional UI
- ✅ **Chart.js integration** - Multiple visualization types
- ✅ **Complete API** - 11 endpoints
- ✅ **Role-based access** - Fine-grained permissions
- ✅ **Documentation** - 3 comprehensive guides
- ✅ **Production ready** - Tested and optimized

---

## 📊 Status

**v2.0.0 - Rebuilt** ✅ Complete & Production Ready
- New template-based architecture
- Professional 3-column dashboard
- Complete API with documentation
- Role-based access control
- Multiple visualization types
- Report state preservation

**Total Lines of Code**: 5,000+ PHP, 2,500+ lines documentation

---

**Build professional reports in Laravel in minutes!** 🚀
