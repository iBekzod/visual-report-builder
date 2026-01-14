# 🏗️ Visual Report Builder - Rebuild Architecture

## Overview

This document describes the **rebuilt** Visual Report Builder package, which uses a **template-based architecture** instead of the original flexible drag-and-drop builder.

## ✅ Key Changes from Original Build

### Original Architecture (❌ Deprecated)
- **Flexible drag-and-drop** report builder
- Users designed reports by dragging dimensions/metrics
- Single `Report` model storing all configuration
- One-size-fits-all approach

### New Architecture (✅ Current)
- **Template-based** reporting system
- Pre-configured report templates for each business need
- 3-column dashboard layout (left sidebar, center main, right sidebar)
- Role-based template access control
- Professional business reporting workflow

---

## 📊 Database Schema

### 1. **report_templates** - Template Definitions

Stores the blueprint for each report type.

```sql
CREATE TABLE report_templates (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,  -- Creator
    name VARCHAR(255),  -- e.g., "Sales by Region"
    description TEXT,  -- Business purpose
    model VARCHAR(255),  -- Eloquent model class

    -- Template Configuration (JSON)
    dimensions JSON,  -- Available dimensions: [{column, label, type}]
    metrics JSON,     -- Available metrics: [{column, label, type, aggregate}]
    filters JSON,     -- Available filters: [{column, label, type, operator}]

    -- Display Options
    default_view JSON,  -- {type: 'table', options: {...}}
    chart_config JSON,  -- Chart.js/ApexCharts config
    icon VARCHAR(10),   -- Emoji for visual ID
    category VARCHAR(100),  -- Grouping: "Sales", "Finance", etc.
    sort_order INT,

    -- Control Flags
    is_active BOOLEAN,
    is_public BOOLEAN,

    timestamps
);
```

**Example:**
```json
{
  "name": "Sales Dashboard",
  "model": "App\\Models\\Order",
  "dimensions": [
    {"column": "region", "label": "Region", "type": "string"},
    {"column": "month", "label": "Month", "type": "date"}
  ],
  "metrics": [
    {"column": "amount", "label": "Total Sales", "aggregate": "sum"},
    {"column": "id", "label": "Order Count", "aggregate": "count"}
  ]
}
```

### 2. **template_filters** - Filter Definitions

Defines what filters are available for each template.

```sql
CREATE TABLE template_filters (
    id BIGINT PRIMARY KEY,
    report_template_id BIGINT,

    -- Filter Definition
    column VARCHAR(255),  -- Database column to filter
    label VARCHAR(255),   -- Display label
    type ENUM('text', 'select', 'date', 'daterange', 'number'),
    operator VARCHAR(50),  -- =, !=, >, <, in, like, between
    options JSON,         -- For select: [{value, label}]

    -- Validation
    is_required BOOLEAN DEFAULT false,
    is_active BOOLEAN DEFAULT true,
    default_value VARCHAR(255),
    sort_order INT,

    timestamps
);
```

### 3. **report_template_roles** - Access Control

Controls which roles can access which templates.

```sql
CREATE TABLE report_template_roles (
    id BIGINT PRIMARY KEY,
    report_template_id BIGINT,
    role_id BIGINT,

    -- Permissions
    can_view BOOLEAN DEFAULT true,
    can_export BOOLEAN DEFAULT true,
    can_save BOOLEAN DEFAULT true,
    can_edit_filters BOOLEAN DEFAULT true,

    timestamps
);
```

### 4. **report_results** - Saved Report Executions

Stores saved reports with all state (filters, view type, data).

```sql
CREATE TABLE report_results (
    id BIGINT PRIMARY KEY,
    report_template_id BIGINT,
    user_id BIGINT,

    -- Report Identity
    name VARCHAR(255),
    description TEXT,

    -- Saved State
    applied_filters JSON,  -- {column: value} - exact filters used
    view_type VARCHAR(50),  -- table, line, bar, pie, area, scatter
    view_config JSON,      -- Chart-specific options
    data JSON,             -- Cached result data

    -- Execution Metadata
    executed_at TIMESTAMP,
    execution_time_ms INT,
    record_count INT,

    -- Usage Tracking
    is_favorite BOOLEAN DEFAULT false,
    view_count INT DEFAULT 0,
    last_viewed_at TIMESTAMP,

    timestamps (soft delete)
);
```

---

## 🏗️ Core Components

### Models

#### **ReportTemplate** (`src/Models/ReportTemplate.php`)

```php
class ReportTemplate extends Model {
    protected $table = 'report_templates';

    // Relationships
    public function creator() -> BelongsTo;
    public function results() -> HasMany(ReportResult);
    public function templateFilters() -> HasMany(TemplateFilter);

    // Data Access
    public function getDimensions(): array;
    public function getMetrics(): array;
    public function getFilters(): array;
    public function getViewTypes(): array;  // ['table', 'line', 'bar', 'pie', 'area', 'scatter']

    // Scopes
    public function scopeActive($query);
    public function scopePublic($query);
    public function scopeByCategory($query, $category);
}
```

#### **TemplateFilter** (`src/Models/TemplateFilter.php`)

```php
class TemplateFilter extends Model {
    protected $table = 'template_filters';

    // Relationships
    public function template() -> BelongsTo(ReportTemplate);

    // Constants
    public static function getAvailableTypes(): array;  // text, select, date, daterange, number
    public static function getAvailableOperators(): array;  // =, !=, >, <, >=, <=, in, like, between
}
```

#### **ReportResult** (`src/Models/ReportResult.php`)

```php
class ReportResult extends Model {
    use SoftDeletes;
    protected $table = 'report_results';

    // Relationships
    public function template() -> BelongsTo(ReportTemplate);
    public function user() -> BelongsTo(User);

    // State Management
    public function markAsFavorite(): self;
    public function removeFromFavorite(): self;
    public function recordView(): self;

    // Scopes
    public function scopeFavorites($query);
    public function scopeByUser($query, $userId);
    public function scopeByTemplate($query, $templateId);
    public function scopeRecentlyViewed($query, $days = 7);
}
```

### Services

#### **TemplateExecutor** (`src/Services/TemplateExecutor.php`)

Executes templates with filter application and metric aggregation.

**Method:**
```php
public function execute(ReportTemplate $template, array $appliedFilters = []): array
```

**Process:**
1. Get model class from template
2. Build Eloquent query
3. Apply template filters with operators
4. Execute GROUP BY with dimensions
5. Calculate aggregates for metrics
6. Return structured result with metadata

**Returns:**
```json
{
  "data": [...],
  "dimensions": [...],
  "metrics": [...],
  "execution_time_ms": 125,
  "record_count": 1500
}
```

---

## 🎮 Controllers

### **TemplateController** (`src/Http/Controllers/TemplateController.php`)

**Endpoints:**

| Method | Route | Purpose |
|--------|-------|---------|
| GET | `/api/visual-reports/templates` | List all templates |
| GET | `/api/visual-reports/templates/{id}` | Get template metadata |
| POST | `/api/visual-reports/templates/{id}/execute` | Execute template with filters |
| POST | `/api/visual-reports/templates/{id}/save` | Save report result |
| GET | `/api/visual-reports/templates/{id}/saved` | List user's saved reports |
| GET | `/api/visual-reports/results/{id}` | Load saved report |
| DELETE | `/api/visual-reports/results/{id}` | Delete saved report |
| POST | `/api/visual-reports/results/{id}/favorite` | Toggle favorite |
| POST | `/api/visual-reports/results/{id}/export/{format}` | Export saved report |

**Key Methods:**

```php
public function execute(Request $request, ReportTemplate $template)
{
    // Request:
    // {
    //   "filters": {"region": "North", "month": "2024-01"},
    //   "view_type": "table|line|bar|pie|area|scatter"
    // }

    // Response:
    // {
    //   "success": true,
    //   "data": {rows, dimensions, metrics, summary},
    //   "metadata": {execution_time_ms, record_count}
    // }
}

public function saveResult(Request $request, ReportTemplate $template)
{
    // Saves report execution with state (filters, view type, data)
    // Returns: {success: true, result_id: 123}
}
```

---

## 🎨 Frontend Architecture

### **Dashboard View** (`resources/views/dashboard.blade.php`)

3-column layout with integrated Chart.js and ApexCharts:

```
┌──────────────────────────────────────────────────────────────┐
│                       NAVBAR                                  │
├──────────────────┬──────────────────────────┬──────────────────┤
│                  │                          │                  │
│  LEFT SIDEBAR    │     CENTER MAIN          │  RIGHT SIDEBAR   │
│                  │                          │                  │
│ Templates        │ • Template Info          │ Saved Reports    │
│ • Search         │ • View Type Selector     │ • Load Report    │
│ • Categories     │ • Filters Section        │ • Delete         │
│ • Favorites      │ • Report Content         │ • Favorites      │
│                  │   (Table/Chart)          │                  │
│                  │ • Summary Stats          │ • Save Button    │
│                  │                          │                  │
└──────────────────┴──────────────────────────┴──────────────────┘
```

### **JavaScript Functions**

| Function | Purpose |
|----------|---------|
| `loadTemplates()` | Fetch all templates |
| `selectTemplate(id)` | Load template metadata |
| `renderFilters(filters)` | Create dynamic filter inputs |
| `executeReport()` | Execute template with current filters |
| `updateView()` | Re-render in selected view type |
| `renderTable(rows)` | Display as HTML table |
| `renderChart(type, data)` | Render Chart.js visualization |
| `loadSavedReports(id)` | Get user's saved reports |
| `saveCurrent()` | Open save dialog |
| `loadResult(id)` | Load previously saved report |
| `performExport()` | Export in selected format |

### **View Type Support**

All view types use the same data but render differently:

- **Table**: Standard HTML table with sortable columns
- **Line Chart**: Trends over time (Chart.js)
- **Bar Chart**: Comparisons (Chart.js)
- **Pie Chart**: Composition (Chart.js)
- **Area Chart**: Filled line chart (Chart.js)
- **Scatter**: XY scatter plot (Chart.js)

---

## 🔄 Request/Response Flow

### Example: Execute Sales Dashboard Template

**1. User selects template from sidebar**
```javascript
await apiClient.get('/api/visual-reports/templates/1')
```

**Response:**
```json
{
  "id": 1,
  "name": "Sales Dashboard",
  "dimensions": [
    {"column": "region", "label": "Region"},
    {"column": "month", "label": "Month"}
  ],
  "metrics": [
    {"column": "amount", "label": "Total Sales", "aggregate": "sum"}
  ],
  "filters": [
    {"column": "status", "label": "Order Status", "type": "select", "options": [...]}
  ]
}
```

**2. UI renders filters based on definitions**

**3. User applies filters and clicks Execute**
```javascript
await apiClient.post('/api/visual-reports/templates/1/execute', {
  "filters": {
    "status": "completed",
    "region": "North"
  },
  "view_type": "table"
})
```

**Response:**
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

**4. User changes to line chart**
```javascript
updateView()  // Re-renders same data as chart
```

**5. User saves report**
```javascript
await apiClient.post('/api/visual-reports/templates/1/save', {
  "name": "Q1 2024 Sales",
  "description": "Sales by region, Jan-Mar",
  "applied_filters": {"status": "completed", "region": "North"},
  "view_type": "line",
  "view_config": {...},
  "data": [...]
})
```

**Response:**
```json
{
  "success": true,
  "result_id": 42
}
```

**6. Saved report appears in right sidebar**

---

## 🔐 Role-Based Access Control

### Setup Example

```php
// Templates access control
$template = ReportTemplate::find(1);

// Grant sales team access to Sales Dashboard template
$template->roles()->attach(Role::findByName('sales'), [
    'can_view' => true,
    'can_export' => true,
    'can_save' => true,
    'can_edit_filters' => false,  // Sales can't modify template filters
]);

// Finance can only view, not export
$template->roles()->attach(Role::findByName('finance'), [
    'can_view' => true,
    'can_export' => false,
    'can_save' => false,
    'can_edit_filters' => false,
]);
```

---

## 📦 Migration Path

### Installation

```bash
composer require ibekzod/visual-report-builder

# Publish migrations
php artisan vendor:publish --tag=visual-report-builder-migrations

# Run migrations
php artisan migrate
```

### Creating a Template

```php
use Ibekzod\VisualReportBuilder\Models\ReportTemplate;
use Ibekzod\VisualReportBuilder\Models\TemplateFilter;

// Create template
$template = ReportTemplate::create([
    'user_id' => auth()->id(),
    'name' => 'Sales by Region',
    'description' => 'Monthly sales totals by region',
    'model' => 'App\\Models\\Order',
    'icon' => '📊',
    'category' => 'Sales',
    'default_view' => ['type' => 'table'],
    'dimensions' => [
        ['column' => 'region', 'label' => 'Region', 'type' => 'string'],
        ['column' => 'month', 'label' => 'Month', 'type' => 'date'],
    ],
    'metrics' => [
        ['column' => 'amount', 'label' => 'Total Sales', 'aggregate' => 'sum'],
        ['column' => 'id', 'label' => 'Order Count', 'aggregate' => 'count'],
    ],
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
    'is_required' => false,
]);

// Grant access
$template->roles()->attach('sales', ['can_view' => true, 'can_export' => true]);
```

---

## 🚀 Advanced Features

### Custom Filter Operators

```php
// Filter with between operator
'filters' => {
    'date_range' => ['2024-01-01', '2024-03-31']
}

// Filter with in operator
'filters' => {
    'status' => ['completed', 'shipped']
}

// Filter with like operator
'filters' => {
    'product_name' => '%electronics%'
}
```

### Chart Configuration

```javascript
// Custom chart options passed to Chart.js
{
  "view_type": "bar",
  "chart_config": {
    "indexAxis": "y",  // Horizontal bar
    "scales": {
      "x": {"beginAtZero": true}
    }
  }
}
```

### Summary Statistics

Automatically calculated from metrics:
- **Sum**: Total of all values
- **Average**: Mean value
- **Min**: Minimum value
- **Max**: Maximum value
- **Count**: Number of records

---

## 🔧 Configuration

### Supported Aggregate Functions

- `sum`: Add values
- `count`: Count rows
- `avg`: Calculate average
- `min`: Find minimum
- `max`: Find maximum
- `count_distinct`: Count unique values

### Available Filter Types

| Type | Input | Example |
|------|-------|---------|
| `text` | Text input | "Electronics" |
| `select` | Dropdown | "completed" |
| `multiselect` | Multi-check | ["completed", "shipped"] |
| `date` | Date picker | "2024-01-15" |
| `daterange` | Date range | ["2024-01-01", "2024-03-31"] |
| `number` | Number input | 1000 |

---

## 📝 Summary

The rebuilt Visual Report Builder is a **production-ready, template-based reporting system** that:

✅ Pre-defines report templates for business teams
✅ Controls access via role-based permissions
✅ Supports multiple visualization types
✅ Stores report state for reuse and sharing
✅ Provides professional 3-column dashboard UI
✅ Uses Chart.js for rich visualizations
✅ Allows complex filtering with operators
✅ Calculates summary statistics automatically
✅ Integrates seamlessly with Laravel

**Perfect for:** Organizations needing standardized, professional reports with team collaboration.
