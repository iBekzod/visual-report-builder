# 📮 Postman Collection Guide

Complete guide to using the Visual Report Builder API with Postman

## 🚀 Quick Start

### 1. Import Collection

1. Open Postman
2. Click **Import** button (top-left)
3. Select **Upload Files**
4. Choose `Visual_Report_Builder.postman_collection.json`
5. Click **Import**

### 2. Set Environment Variables

Before making requests, set these variables in Postman:

| Variable | Value | Example |
|----------|-------|---------|
| `base_url` | Your Laravel app URL | `http://yourapp.test` |
| `api_token` | Bearer token from API | Get from `/api/token` or Laravel Sanctum |
| `report_id` | ID of a report | `1` |
| `user_id` | ID of a user to share with | `2` |
| `model_name` | Full model class name | `App\Models\Order` |

**To set variables:**

1. Click **Environment** (top-right)
2. Click **Create** or select existing
3. Add the variables above
4. Click **Save**

### 3. Get API Token

If using Laravel Sanctum or API tokens:

```bash
# Generate token via command
php artisan tinker
>>> $user = User::find(1);
>>> $token = $user->createToken('api-token')->plainTextToken;
>>> echo $token;
```

Or login via API:

```bash
POST http://yourapp.test/api/login
{
  "email": "user@example.com",
  "password": "password"
}
```

---

## 📊 Complete API Workflow

### Step 1: List Available Models

Use this to see what data sources are available.

**Request:** GET `/api/visual-reports/models`

**Response Example:**
```json
[
  {
    "class": "App\\Models\\Order",
    "name": "Order",
    "label": "Order"
  },
  {
    "class": "App\\Models\\Customer",
    "name": "Customer",
    "label": "Customer"
  }
]
```

### Step 2: Get Model Metadata

Learn what dimensions and metrics are available for a model.

**Request:** GET `/api/visual-reports/models/App\Models\Order/metadata`

**Response Example:**
```json
{
  "dimensions": [
    {
      "column": "region",
      "label": "Region",
      "type": "string"
    },
    {
      "column": "status",
      "label": "Status",
      "type": "string"
    }
  ],
  "metrics": [
    {
      "column": "amount",
      "label": "Amount",
      "type": "decimal",
      "default_aggregate": "sum"
    },
    {
      "column": "quantity",
      "label": "Quantity",
      "type": "integer",
      "default_aggregate": "sum"
    }
  ]
}
```

### Step 3: Preview Configuration

Test your report configuration before saving.

**Request:** POST `/api/visual-reports/preview`

**Body:**
```json
{
  "model": "App\\Models\\Order",
  "row_dimensions": ["region"],
  "column_dimensions": ["month"],
  "metrics": [
    {
      "column": "amount",
      "aggregate": "sum",
      "label": "Total Sales"
    },
    {
      "column": "id",
      "aggregate": "count",
      "label": "Orders"
    }
  ]
}
```

**Response:** Full report data with pivot table

### Step 4: Create Report

Save a report configuration.

**Request:** POST `/api/visual-reports/reports`

**Body:**
```json
{
  "name": "Monthly Sales by Region",
  "description": "Shows sales totals by region per month",
  "model": "App\\Models\\Order",
  "configuration": {
    "row_dimensions": ["region"],
    "column_dimensions": ["month"],
    "metrics": [
      {
        "column": "amount",
        "aggregate": "sum",
        "label": "Total Sales",
        "alias": "total_sales"
      }
    ]
  }
}
```

**Response:**
```json
{
  "id": 1,
  "name": "Monthly Sales by Region",
  "model": "App\\Models\\Order",
  "configuration": {...},
  "user_id": 1,
  "created_at": "2024-01-14T10:30:00Z",
  "updated_at": "2024-01-14T10:30:00Z"
}
```

### Step 5: Execute Report

Get the actual report data.

**Request:** POST `/api/visual-reports/reports/1/execute`

**Response Example:**
```json
{
  "row_headers": [
    ["North"],
    ["South"],
    ["East"]
  ],
  "column_headers": [
    ["January"],
    ["February"],
    ["March"]
  ],
  "data_matrix": [
    [
      {"total_sales": 10000},
      {"total_sales": 12000},
      {"total_sales": 11000}
    ],
    [
      {"total_sales": 8000},
      {"total_sales": 9000},
      {"total_sales": 9500}
    ]
  ],
  "row_totals": [
    {"total_sales": 33000},
    {"total_sales": 26500}
  ],
  "column_totals": [
    {"total_sales": 18000},
    {"total_sales": 21000},
    {"total_sales": 20500}
  ],
  "grand_total": {"total_sales": 59500}
}
```

### Step 6: Export Report

Export to your desired format.

**CSV Export:**
```
Request: POST /api/visual-reports/reports/1/export/csv
Response: CSV file download
```

**Excel Export:**
```
Request: POST /api/visual-reports/reports/1/export/excel
Response: XLSX file download
```

**PDF Export:**
```
Request: POST /api/visual-reports/reports/1/export/pdf
Response: PDF file download
```

**JSON Export:**
```
Request: POST /api/visual-reports/reports/1/export/json
Response: JSON data
```

### Step 7: Share Report

Share with team members.

**Request:** POST `/api/visual-reports/reports/1/share`

**Body:**
```json
{
  "user_id": 2,
  "can_edit": true,
  "can_share": false
}
```

---

## 🎯 Common Use Cases

### Use Case 1: Sales Dashboard

Create a report showing sales by region and product.

```json
{
  "name": "Sales Dashboard",
  "model": "App\\Models\\Sale",
  "configuration": {
    "row_dimensions": ["region", "product_category"],
    "column_dimensions": ["month"],
    "metrics": [
      {
        "column": "amount",
        "aggregate": "sum",
        "label": "Total Sales"
      },
      {
        "column": "commission",
        "aggregate": "sum",
        "label": "Commission"
      },
      {
        "column": "id",
        "aggregate": "count",
        "label": "Transactions"
      }
    ],
    "filters": {
      "status": ["completed", "paid"]
    }
  }
}
```

### Use Case 2: Financial Analysis

Analyze budget vs actual spending.

```json
{
  "name": "Budget vs Actual",
  "model": "App\\Models\\Transaction",
  "configuration": {
    "row_dimensions": ["department", "cost_center"],
    "column_dimensions": ["month", "year"],
    "metrics": [
      {
        "column": "budget",
        "aggregate": "sum",
        "label": "Budget"
      },
      {
        "column": "spent",
        "aggregate": "sum",
        "label": "Actual Spent"
      },
      {
        "column": "spent",
        "aggregate": "avg",
        "label": "Daily Average"
      }
    ]
  }
}
```

### Use Case 3: Inventory Report

Monitor inventory levels.

```json
{
  "name": "Inventory Status",
  "model": "App\\Models\\InventoryItem",
  "configuration": {
    "row_dimensions": ["warehouse", "product_type"],
    "column_dimensions": ["status"],
    "metrics": [
      {
        "column": "quantity",
        "aggregate": "sum",
        "label": "Total Items"
      },
      {
        "column": "quantity",
        "aggregate": "min",
        "label": "Min Stock"
      },
      {
        "column": "quantity",
        "aggregate": "max",
        "label": "Max Stock"
      }
    ]
  }
}
```

---

## 💡 Tips & Tricks

### Tip 1: Use Environments for Different Apps

Create separate environments for:
- Local development
- Staging
- Production

**To switch:** Use the environment dropdown (top-right)

### Tip 2: Save Responses as Examples

1. Make a request
2. Click **Save as Example**
3. Later you can quickly review what the response looks like

### Tip 3: Chain Requests with Tests

Use the **Tests** tab to automatically set variables:

```javascript
// Save report ID from create response
let response = pm.response.json();
pm.environment.set("report_id", response.id);
```

### Tip 4: Monitor API Performance

Check response times in the **Console** (bottom-left).

### Tip 5: Use Pre-request Scripts

Run code before requests (e.g., generate tokens):

```javascript
// Pre-request Script
console.log("Running against: " + pm.environment.get("base_url"));
```

---

## 🔒 Authentication

### Option 1: Bearer Token

Set `Authorization` header:
```
Authorization: Bearer YOUR_TOKEN_HERE
```

All requests in the collection use `{{api_token}}` variable.

### Option 2: API Key

If using API keys instead of Bearer:

1. Edit environment variables
2. Change auth method in collection settings
3. Add API key header

---

## 🐛 Troubleshooting

### Error: 401 Unauthorized

**Solution:**
- Check `api_token` is set correctly
- Verify token hasn't expired
- Check user still exists

### Error: 404 Not Found

**Solution:**
- Verify `report_id` is correct
- Check `base_url` is correct (no trailing slash)
- Ensure the resource exists

### Error: 422 Unprocessable Entity

**Solution:**
- Check JSON formatting in body
- Verify required fields are present
- Check model name is correct

### Error: 500 Internal Server Error

**Solution:**
- Check server logs: `tail -f storage/logs/laravel.log`
- Verify database is connected
- Check model columns are correct

---

## 📈 Example: Complete Workflow

Here's a complete example from start to finish:

### 1. List Models
```bash
GET /api/visual-reports/models
```

Look for `App\Models\Order` in response.

### 2. Get Order Metadata
```bash
GET /api/visual-reports/models/App\Models\Order/metadata
```

Note available dimensions and metrics.

### 3. Preview Report
```bash
POST /api/visual-reports/preview
Content-Type: application/json

{
  "model": "App\\Models\\Order",
  "row_dimensions": ["region"],
  "column_dimensions": ["month"],
  "metrics": [
    {"column": "amount", "aggregate": "sum", "label": "Total"}
  ]
}
```

Check if results look good.

### 4. Create Report
```bash
POST /api/visual-reports/reports
Content-Type: application/json

{
  "name": "Sales by Region",
  "model": "App\\Models\\Order",
  "configuration": {
    "row_dimensions": ["region"],
    "column_dimensions": ["month"],
    "metrics": [
      {"column": "amount", "aggregate": "sum", "label": "Total"}
    ]
  }
}
```

Save response `id` as `{{report_id}}`.

### 5. Execute Report
```bash
POST /api/visual-reports/reports/{{report_id}}/execute
```

Get the actual data!

### 6. Export to Excel
```bash
POST /api/visual-reports/reports/{{report_id}}/export/excel
```

Download the file.

### 7. Share with Colleague
```bash
POST /api/visual-reports/reports/{{report_id}}/share
Content-Type: application/json

{
  "user_id": 2,
  "can_edit": true,
  "can_share": false
}
```

Done! 🎉

---

## 📚 API Response Codes

| Code | Meaning | Solution |
|------|---------|----------|
| 200 | Success | Request worked |
| 201 | Created | New resource created |
| 204 | No Content | Deleted successfully |
| 400 | Bad Request | Check JSON formatting |
| 401 | Unauthorized | Check API token |
| 403 | Forbidden | You don't have permission |
| 404 | Not Found | Resource doesn't exist |
| 422 | Validation Error | Check required fields |
| 500 | Server Error | Check server logs |

---

## 🔧 Advanced: Custom Headers

Some endpoints may need additional headers:

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer YOUR_TOKEN
X-Requested-With: XMLHttpRequest
```

All are automatically set in the collection.

---

## 📱 Mobile Testing

Postman also works on mobile! Install the Postman app:
- iOS App Store
- Google Play Store

Sync your collections across devices.

---

## 🔗 Integration Examples

### JavaScript (Fetch API)

```javascript
const response = await fetch('http://yourapp.test/api/visual-reports/reports', {
  method: 'GET',
  headers: {
    'Authorization': 'Bearer ' + token,
    'Accept': 'application/json'
  }
});
const reports = await response.json();
```

### Python

```python
import requests

headers = {'Authorization': f'Bearer {token}'}
response = requests.get(
    'http://yourapp.test/api/visual-reports/reports',
    headers=headers
)
reports = response.json()
```

### cURL

```bash
curl -X GET http://yourapp.test/api/visual-reports/reports \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

---

## 📞 Support

- Check error responses carefully
- Enable **Console** to see detailed logs
- Review server logs: `php artisan logs`
- Check API documentation in README.md

---

**Happy Testing!** 🚀

Use this collection to explore the Visual Report Builder API and integrate it into your applications.
