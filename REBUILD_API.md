# 📡 API Reference - Template-Based System

Complete API documentation for the rebuilt Visual Report Builder.

---

## Base URL

```
http://yourapp.test/api/visual-reports
```

## Authentication

All endpoints require Laravel Sanctum authentication:

```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     -H "Accept: application/json" \
     http://yourapp.test/api/visual-reports/templates
```

---

## Endpoints

### 1️⃣ GET `/templates`

List all available templates (filtered by user's role).

**Response:**
```json
{
  "templates": [
    {
      "id": 1,
      "name": "Sales Dashboard",
      "description": "Monthly sales by region",
      "icon": "💰",
      "category": "Sales",
      "model": "App\\Models\\Order",
      "dimensions": [
        {
          "column": "region",
          "label": "Region",
          "type": "string"
        }
      ],
      "metrics": [
        {
          "column": "amount",
          "label": "Total Sales",
          "aggregate": "sum",
          "type": "decimal"
        }
      ],
      "filters": [
        {
          "id": 1,
          "column": "status",
          "label": "Order Status",
          "type": "select",
          "operator": "=",
          "options": [
            {"value": "completed", "label": "Completed"}
          ],
          "is_required": false,
          "default_value": null
        }
      ],
      "default_view": {"type": "table"},
      "recent_results": [
        {"id": 42, "name": "Q1 Sales", "view_type": "bar", "created_at": "..."}
      ]
    }
  ],
  "categories": ["Sales", "Finance", "Operations"]
}
```

---

### 2️⃣ GET `/templates/{id}`

Get single template with full metadata.

**Parameters:**
- `id` (integer, required): Template ID

**Response:**
```json
{
  "id": 1,
  "name": "Sales Dashboard",
  "description": "Monthly sales by region",
  "icon": "💰",
  "category": "Sales",
  "model": "App\\Models\\Order",
  "dimensions": [...],
  "metrics": [...],
  "filters": [...],
  "default_view": {"type": "table"},
  "chart_config": {...},
  "view_types": ["table", "line", "bar", "pie", "area", "scatter"]
}
```

---

### 3️⃣ POST `/templates/{id}/execute`

Execute template and get report data.

**Parameters:**
- `id` (integer, required): Template ID

**Request Body:**
```json
{
  "filters": {
    "region": "North",
    "status": "completed",
    "created_at": ["2024-01-01", "2024-03-31"]
  },
  "view_type": "table",
  "chart_config": {
    "indexAxis": "y"
  }
}
```

**Filter Types:**
- Simple value: `"region": "North"`
- Multiple values: `"status": ["completed", "pending"]`
- Date range: `"created_at": ["2024-01-01", "2024-03-31"]`
- Null to skip: `"status": null`

**View Types:**
- `table`: HTML table view
- `line`: Line chart
- `bar`: Bar chart
- `pie`: Pie chart
- `area`: Area chart
- `scatter`: Scatter plot

**Response:**
```json
{
  "success": true,
  "data": {
    "rows": [
      {
        "region": "North",
        "month": "2024-01",
        "total_sales": 10000,
        "order_count": 125
      },
      {
        "region": "North",
        "month": "2024-02",
        "total_sales": 12000,
        "order_count": 145
      }
    ],
    "dimensions": [
      {"column": "region", "label": "Region"},
      {"column": "month", "label": "Month"}
    ],
    "metrics": [
      {"column": "total_sales", "label": "Total Sales", "alias": "total_sales"},
      {"column": "order_count", "label": "Order Count", "alias": "order_count"}
    ],
    "summary": {
      "total_sales": {
        "sum": 22000,
        "avg": 11000,
        "min": 10000,
        "max": 12000,
        "count": 2
      },
      "order_count": {
        "sum": 270,
        "avg": 135,
        "count": 2
      }
    },
    "view_type": "table"
  },
  "metadata": {
    "execution_time_ms": 145,
    "record_count": 2,
    "dimensions": [...],
    "metrics": [...]
  }
}
```

**Error Response:**
```json
{
  "success": false,
  "message": "Model App\\Models\\Order does not exist"
}
```

---

### 4️⃣ POST `/templates/{id}/save`

Save a report execution with state.

**Parameters:**
- `id` (integer, required): Template ID

**Request Body:**
```json
{
  "name": "Q1 2024 North Sales",
  "description": "Sales data for Q1 2024 in North region",
  "applied_filters": {
    "region": "North",
    "status": "completed",
    "created_at": ["2024-01-01", "2024-03-31"]
  },
  "view_type": "line",
  "view_config": {
    "chartTitle": "Q1 Sales Trend",
    "showLegend": true
  },
  "data": [
    {"region": "North", "month": "2024-01", "total_sales": 10000},
    {"region": "North", "month": "2024-02", "total_sales": 12000}
  ]
}
```

**Response:**
```json
{
  "success": true,
  "message": "Report saved successfully",
  "result_id": 42
}
```

**Validation Errors:**
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": ["The name field is required."],
    "data": ["The data field is required."]
  }
}
```

---

### 5️⃣ GET `/templates/{id}/saved`

List user's saved reports for a template.

**Parameters:**
- `id` (integer, required): Template ID

**Response:**
```json
[
  {
    "id": 42,
    "name": "Q1 2024 North Sales",
    "description": "Sales data for Q1 2024",
    "view_type": "line",
    "is_favorite": true,
    "created_at": "2024-01-15T10:30:00Z",
    "executed_at": "2024-01-15T10:30:00Z"
  },
  {
    "id": 43,
    "name": "Q2 2024 All Regions",
    "description": null,
    "view_type": "table",
    "is_favorite": false,
    "created_at": "2024-04-01T14:20:00Z",
    "executed_at": "2024-04-01T14:20:00Z"
  }
]
```

---

### 6️⃣ GET `/results/{id}`

Load a saved report.

**Parameters:**
- `id` (integer, required): Report Result ID

**Response:**
```json
{
  "id": 42,
  "name": "Q1 2024 North Sales",
  "description": "Sales data for Q1 2024",
  "applied_filters": {
    "region": "North",
    "created_at": ["2024-01-01", "2024-03-31"]
  },
  "view_type": "line",
  "view_config": {
    "chartTitle": "Q1 Sales Trend"
  },
  "data": [
    {"region": "North", "month": "2024-01", "total_sales": 10000},
    {"region": "North", "month": "2024-02", "total_sales": 12000},
    {"region": "North", "month": "2024-03", "total_sales": 11000}
  ],
  "execution_time_ms": 145,
  "created_at": "2024-01-15T10:30:00Z"
}
```

**Authorization Error:**
```json
{
  "message": "Unauthorized"
}
```

---

### 7️⃣ DELETE `/results/{id}`

Delete a saved report.

**Parameters:**
- `id` (integer, required): Report Result ID

**Response:**
```json
{
  "success": true,
  "message": "Report deleted successfully"
}
```

---

### 8️⃣ POST `/results/{id}/favorite`

Toggle favorite status of a saved report.

**Parameters:**
- `id` (integer, required): Report Result ID

**Response:**
```json
{
  "is_favorite": true
}
```

---

### 9️⃣ POST `/results/{id}/export/{format}`

Export a saved report in specified format.

**Parameters:**
- `id` (integer, required): Report Result ID
- `format` (string, required): csv, excel, pdf, or json

**Request:**
```bash
POST /results/42/export/excel
```

**Response:**
- File download (xlsx, csv, pdf) or JSON data

**Supported Formats:**
- `csv`: Comma-separated values
- `excel`: Microsoft Excel (xlsx)
- `pdf`: PDF document
- `json`: JSON data

---

### 🔟 POST `/results/{id}/share`

Share a report with another user.

**Parameters:**
- `id` (integer, required): Report Result ID

**Request Body:**
```json
{
  "user_id": 2,
  "can_edit": true,
  "can_share": false
}
```

**Response:**
```json
{
  "success": true,
  "message": "Report shared successfully"
}
```

---

### 1️⃣1️⃣ POST `/results/{id}/unshare`

Stop sharing a report with a user.

**Parameters:**
- `id` (integer, required): Report Result ID

**Request Body:**
```json
{
  "user_id": 2
}
```

**Response:**
```json
{
  "success": true,
  "message": "Report unshared successfully"
}
```

---

## Common Patterns

### Pattern 1: List Templates and Show Categories

```bash
# Get all templates grouped by category
curl -H "Authorization: Bearer TOKEN" \
     http://yourapp.test/api/visual-reports/templates
```

**Use in UI:**
```javascript
const response = await fetch('/api/visual-reports/templates', {
  headers: {'Authorization': 'Bearer ' + token}
});
const {templates, categories} = await response.json();
```

### Pattern 2: Execute and Save Workflow

```javascript
// 1. Load template
const template = await fetch(`/api/visual-reports/templates/${id}`);

// 2. Execute with filters
const result = await fetch(`/api/visual-reports/templates/${id}/execute`, {
  method: 'POST',
  body: JSON.stringify({
    filters: {region: 'North'},
    view_type: 'line'
  })
});

// 3. Save report
const saved = await fetch(`/api/visual-reports/templates/${id}/save`, {
  method: 'POST',
  body: JSON.stringify({
    name: 'Q1 Sales',
    applied_filters: {region: 'North'},
    view_type: 'line',
    data: result.data.rows
  })
});

// 4. Load later
const report = await fetch(`/api/visual-reports/results/${saved.result_id}`);
```

### Pattern 3: Export Report

```javascript
// Save first
const saved = await saveReport();

// Then export
const response = await fetch(
  `/api/visual-reports/results/${saved.result_id}/export/excel`,
  {method: 'POST'}
);

// Download file
const blob = await response.blob();
const url = window.URL.createObjectURL(blob);
const a = document.createElement('a');
a.href = url;
a.download = 'report.xlsx';
a.click();
```

---

## Filter Operators

### Text Filters

```json
{
  "column": "product_name",
  "operator": "like",
  "value": "electronics"
}
```

Result: WHERE product_name LIKE '%electronics%'

### Select Filters

```json
{
  "column": "status",
  "operator": "=",
  "value": "completed"
}
```

Result: WHERE status = 'completed'

### Multiple Values

```json
{
  "column": "status",
  "operator": "in",
  "value": ["completed", "shipped"]
}
```

Result: WHERE status IN ('completed', 'shipped')

### Date Range

```json
{
  "column": "created_at",
  "operator": "between",
  "value": ["2024-01-01", "2024-03-31"]
}
```

Result: WHERE created_at BETWEEN '2024-01-01' AND '2024-03-31'

### Numeric Comparison

```json
{
  "column": "amount",
  "operator": ">",
  "value": 1000
}
```

Result: WHERE amount > 1000

---

## Error Handling

### 400 Bad Request

```json
{
  "message": "Invalid filter operator",
  "errors": {
    "filters.region": ["Unknown operator 'xyz'"]
  }
}
```

### 401 Unauthorized

```json
{
  "message": "Unauthenticated"
}
```

### 403 Forbidden

```json
{
  "message": "You don't have permission to access this template"
}
```

### 404 Not Found

```json
{
  "message": "Template not found"
}
```

### 422 Validation Error

```json
{
  "message": "The given data was invalid",
  "errors": {
    "name": ["The name field is required"],
    "data": ["The data must be an array"]
  }
}
```

### 500 Server Error

```json
{
  "message": "Database connection error"
}
```

---

## Performance Tips

### 1. Use Specific Filters

```javascript
// ✅ Good - Filtered query
execute(templateId, {
  filters: {region: 'North', year: 2024}
})

// ❌ Slow - All data
execute(templateId, {})
```

### 2. Reuse Saved Reports

```javascript
// ✅ Good - Load cached
loadResult(reportId)

// ❌ Slow - Re-execute
execute(templateId, {...})
```

### 3. Cache Metadata

```javascript
// ✅ Good - Cache template metadata
const metadata = await fetch(`/templates/${id}`);
// Use cached metadata for filter rendering

// ❌ Slow - Fetch every time
getTemplate()
```

---

## Code Examples

### JavaScript/Fetch

```javascript
const templateId = 1;
const token = 'your-api-token';

// Execute template
const response = await fetch(
  `/api/visual-reports/templates/${templateId}/execute`,
  {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      filters: {
        region: 'North',
        created_at: ['2024-01-01', '2024-03-31']
      },
      view_type: 'bar'
    })
  }
);

const data = await response.json();
console.log(data.data.rows);
```

### Python

```python
import requests
import json

token = 'your-api-token'
headers = {
    'Authorization': f'Bearer {token}',
    'Content-Type': 'application/json'
}

# Execute template
response = requests.post(
    'http://yourapp.test/api/visual-reports/templates/1/execute',
    headers=headers,
    json={
        'filters': {
            'region': 'North',
            'created_at': ['2024-01-01', '2024-03-31']
        },
        'view_type': 'bar'
    }
)

data = response.json()
print(json.dumps(data, indent=2))
```

### cURL

```bash
curl -X POST http://yourapp.test/api/visual-reports/templates/1/execute \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "filters": {
      "region": "North",
      "created_at": ["2024-01-01", "2024-03-31"]
    },
    "view_type": "bar"
  }'
```

---

## Rate Limiting

No rate limiting currently implemented. Implement as needed based on your requirements.

---

## Webhooks

Not currently implemented. Custom implementations can listen to model events:

```php
// In your service provider
ReportResult::created(function ($result) {
    // Webhook logic
});
```

---

## Changelog

### v2.0.0 (Rebuilt)
- ✅ New template-based architecture
- ✅ Role-based access control
- ✅ Dynamic filter system
- ✅ Multiple view types
- ✅ Report state preservation
- ✅ Comprehensive API

### v1.0.0 (Original - Deprecated)
- Flexible drag-and-drop builder
- No longer supported
