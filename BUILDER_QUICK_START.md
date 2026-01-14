# 🚀 Drag-and-Drop Builder - Quick Start Guide

Your Visual Report Builder now includes a **visual template creator** that requires NO coding!

## What is the Builder?

The Builder is a drag-and-drop interface where you can:
- ✅ Select any database table/model
- ✅ Choose dimensions (grouping fields)
- ✅ Choose metrics (calculations like sum, count, etc.)
- ✅ Join related tables
- ✅ Preview results instantly
- ✅ Save as a template for team use

---

## 60-Second Quick Start

### Step 1: Access the Builder
```
1. Go to your app
2. Click "Builder" in the top navbar
   OR
   In Dashboard → Click "+ Create Template" button
```

### Step 2: Create a Template
```
1. Select a model (e.g., "Order")
2. Drag fields:
   - Dimensions (blue) → "Row Dimensions" or "Column Dimensions"
   - Metrics (green) → "Metrics"
3. Click "Preview" to see results
4. Click "Save Template"
5. Fill in: Name, Category, Description (optional)
6. Click "Save"
```

### Step 3: Use Your Template
```
1. Go to Dashboard
2. Find your template in the left sidebar
3. Click it
4. Click "Execute"
5. View results (try different chart types!)
6. Save as report or export
```

---

## Step-by-Step Tutorial

### Example: Create a "Sales by Region" Report

#### Part 1: Open Builder

**Navigate to:** `http://yourapp.test/visual-reports/builder`

You'll see:
- **LEFT PANEL:** Configuration zones
- **RIGHT PANEL:** Available fields to drag

#### Part 2: Select Your Data Source

1. Click the "📊 Data Source" dropdown
2. Choose "Order" (or your model)
3. Wait for fields to load

**You'll see:**
- **Dimensions List** (blue items): region, month, category, status, etc.
- **Metrics List** (green items): amount, quantity, count, etc.

#### Part 3: Drag Your Dimensions

1. **For Rows:** Drag "region" to "📌 Row Dimensions" zone
   - See "region" appear as a blue tag
   - Click the "×" to remove it

2. **For Columns:** Drag "month" to "📊 Column Dimensions" zone
   - See "month" appear as a gray tag

*Note: You can add multiple dimensions!*

#### Part 4: Drag Your Metrics

1. Drag "amount" to "📈 Metrics" zone
   - See "amount (sum)" appear as a green tag
2. Drag "id" to metrics
   - See "id (count)" appear

*These will be calculated for each region + month combination*

#### Part 5: Preview Results

1. Click "👁️ Preview" button
2. See JSON preview of your report data

**Example output:**
```json
{
  "region": "North",
  "month": "2024-01",
  "amount_sum": 50000,
  "id_count": 125
}
```

#### Part 6: Save Your Template

1. Click "💾 Save Template"
2. Fill the modal:
   - **Name:** "Sales by Region" (required)
   - **Category:** "Sales" (required)
   - **Description:** "Shows sales total and order count by region and month"
   - **Icon:** 📊 (optional)
3. Click "💾 Save Template"
4. **Automatic redirect to Dashboard**

---

## Your Template is Live!

Once saved, your template appears in the **Dashboard left sidebar**:

```
📊 Report Templates
  └─ Sales
     └─ Sales by Region
```

### Other Users Can Now:
1. Click "Sales by Region" in dashboard
2. Apply filters (select region, date range, etc.)
3. View results
4. Switch between chart types
5. Export as CSV/Excel/PDF/JSON
6. Save their own version with custom filters

---

## Advanced Features

### 🔗 Join Related Tables

If you have relationships between models:

1. Select "Order" model
2. Look for "🔗 Join Related Table" dropdown
3. Select "customer" (if you have a BelongsTo relationship)
4. Now you can use customer fields (e.g., customer.name, customer.email)

**Example:** Sales by Customer Region
```
- Drag "customer.region" to Row Dimensions
- Drag "amount" to Metrics
- Get sales grouped by customer region!
```

### 📊 Multiple Metrics

Add multiple metrics to compare:
```
- Drag "amount" (sum) → Shows total sales
- Drag "quantity" (sum) → Shows total items
- Drag "id" (count) → Shows order count
```

Result: Report with 3 calculated columns!

### 📈 View Types

After executing your template in Dashboard, try:
- **Table** - Spreadsheet view
- **Line Chart** - Trends over time
- **Bar Chart** - Comparisons
- **Pie Chart** - Composition
- **Area Chart** - Stacked trends

### 🔍 Dynamic Filters

When viewing your template in Dashboard:
1. Filters appear based on your template's columns
2. Change filter values
3. Click "Execute"
4. Results update instantly!

---

## Common Templates to Create

### Sales Dashboard
```
Dimensions: region, month, product_category
Metrics: amount (sum), id (count)
Result: See sales totals and order counts by region, month, product
```

### Inventory Report
```
Dimensions: warehouse, product_type, status
Metrics: quantity (sum), quantity (min), quantity (max)
Result: Monitor stock levels by warehouse and product
```

### Customer Analysis
```
Dimensions: customer.region, customer.status, month
Metrics: amount (sum), id (count), customer_id (count_distinct)
Result: Analyze customers by region and activity
```

### Financial Summary
```
Dimensions: department, cost_center, month
Metrics: budget (sum), spent (sum), spent (avg)
Result: Budget vs actual spending analysis
```

---

## Troubleshooting

### Problem: Models don't appear in dropdown

**Solution:**
- Make sure you have Eloquent models in `app/Models`
- Models must extend `Illuminate\Database\Eloquent\Model`
- Restart your app

### Problem: Dimensions/Metrics don't load

**Solution:**
- Select a different model and select it again
- Check browser console for errors
- Verify the model's table exists in database

### Problem: Drag-and-drop not working

**Solution:**
- Use a modern browser (Chrome, Firefox, Safari, Edge)
- Ensure JavaScript is enabled
- Try refreshing the page

### Problem: Can't save template

**Solution:**
- Make sure you filled in the required fields:
  - Name: Required
  - Category: Required
  - At least 1 metric: Required
- Check browser console for error messages

---

## Permission Levels

Your admin can configure who can create templates:

```
VISUAL_REPORT_CREATE_TEMPLATES=all       # Everyone can create
VISUAL_REPORT_CREATE_TEMPLATES=admin     # Only admins can create
```

If you see **"Only admins can create templates"** error:
- Ask your admin for permission
- Or request they create the template for you

---

## Tips & Tricks

### 💡 Tip 1: Templates are Reusable
Once you create a template, everyone on your team can:
- Execute it with their own filters
- See the same structure, different data
- Save their own variations

### 💡 Tip 2: Save Multiple Variations
You can create similar templates with different combinations:
- "Sales by Region" (region + month)
- "Sales by Product" (product + month)
- "Sales by Customer" (customer + region)

### 💡 Tip 3: Preview Before Saving
Always click "Preview" to verify results look correct before saving!

### 💡 Tip 4: Use Descriptive Names
```
❌ Bad:  "Report1", "Template1"
✅ Good: "Sales by Region - 2024", "Inventory by Warehouse"
```

### 💡 Tip 5: Categories Help Organization
Use categories to group related templates:
- Sales
- Finance
- Operations
- Inventory
- HR

---

## What Happens After You Save?

```
1. ✅ Template saved to database
2. ✅ Available to ALL users immediately
3. ✅ Appears in Dashboard sidebar
4. ✅ Other users can execute it
5. ✅ Each user's executions are separate
6. ✅ Your saved reports stay private
```

---

## Quick Comparison: Builder vs Dashboard

| Feature | Builder | Dashboard |
|---------|---------|-----------|
| **Purpose** | Create templates | Execute templates |
| **Who uses** | Admins, power users | Everyone |
| **Requires code?** | No, drag-and-drop | No, click and filter |
| **Result** | Saved template | Saved report (from template) |
| **Visibility** | Team-wide | User's own reports |
| **Can delete?** | Admin only | User can delete their own |

---

## Next Steps

1. **Try it out:** Go to `/visual-reports/builder`
2. **Create a template:** Follow the tutorial above
3. **View in dashboard:** Go to `/visual-reports`
4. **Execute it:** Click your template and run it
5. **Share:** Tell your team it's ready to use!

---

## Need Help?

### Questions?
- Check "MERGE_SUMMARY.md" for architecture details
- Read "REBUILD_ARCHITECTURE.md" for technical info
- Check "README_REBUILD.md" for full features

### Bugs or Issues?
- Check the browser console for errors
- Try refreshing the page
- Clear browser cache (Ctrl+Shift+Del)
- Contact your admin

---

## Feature Requests

Have an idea for the builder? Things you'd like to add?
- Calculated/computed columns
- More complex filters (AND/OR)
- Custom chart colors
- Scheduled reports
- Email distribution

Let your admin know!

---

**Happy reporting!** 📊✨

Go to `/visual-reports/builder` and create your first template now!
