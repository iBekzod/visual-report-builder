# 🚀 START HERE - Visual Report Builder

Welcome to the rebuilt **Visual Report Builder** - a professional, template-based reporting system for Laravel!

---

## ⚡ Quick Navigation

### 🟢 First Time? Start Here
1. **[README_REBUILD.md](README_REBUILD.md)** (5 min read)
   - Overview of what the package does
   - Key features
   - Quick installation
   - Use cases

### 🟢 Ready to Install & Try?
2. **[REBUILD_SETUP.md](REBUILD_SETUP.md)** (10 min read)
   - Step-by-step installation
   - Create your first template
   - Execute your first report
   - Save & export examples

### 🟡 Need Technical Details?
3. **[REBUILD_ARCHITECTURE.md](REBUILD_ARCHITECTURE.md)** (30 min read)
   - Complete database schema
   - Models, Services, Controllers
   - How everything works together
   - Advanced features

### 🟡 Building an Integration?
4. **[REBUILD_API.md](REBUILD_API.md)** (20 min read)
   - Full API reference
   - All 11 endpoints documented
   - Request/response examples
   - Code examples (JavaScript, Python, cURL)

### 🔵 Want an Overview of Changes?
5. **[REBUILD_SUMMARY.md](REBUILD_SUMMARY.md)** (15 min read)
   - What was changed from old to new
   - All files that were created/modified
   - Complete feature list

---

## 📚 Documentation Index

| Document | Purpose | For Whom | Time |
|----------|---------|----------|------|
| **README_REBUILD.md** | Features & overview | Everyone | 5 min |
| **REBUILD_SETUP.md** | Installation & first template | Getting started | 10 min |
| **REBUILD_ARCHITECTURE.md** | Technical details | Developers | 30 min |
| **REBUILD_API.md** | API endpoints | Integrators | 20 min |
| **REBUILD_SUMMARY.md** | Change summary | Upgraders | 15 min |

---

## 🎯 Choose Your Path

### Path 1: I Just Want to Use It
```
1. Read: README_REBUILD.md
2. Follow: REBUILD_SETUP.md
3. Visit: http://yourapp.test/visual-reports
```

### Path 2: I Need to Integrate It
```
1. Read: README_REBUILD.md
2. Study: REBUILD_API.md
3. Build: Use the examples
```

### Path 3: I Need to Understand Everything
```
1. Read: README_REBUILD.md
2. Follow: REBUILD_SETUP.md
3. Study: REBUILD_ARCHITECTURE.md
4. Reference: REBUILD_API.md
```

---

## ⚡ 2-Minute Quick Start

```bash
# Install
composer require ibekzod/visual-report-builder
php artisan vendor:publish --tag=visual-report-builder-migrations
php artisan migrate

# Create template
php artisan tinker
>>> use Ibekzod\VisualReportBuilder\Models\ReportTemplate;
>>> ReportTemplate::create(['name' => 'Sales', 'model' => 'App\Models\Order', ...])

# Visit dashboard
http://yourapp.test/visual-reports
```

---

## ✨ What You Get

✅ Professional 3-column dashboard
✅ Template-based reporting system
✅ 6 visualization types (table, line, bar, pie, area, scatter)
✅ Dynamic filtering with operators
✅ Save/load reports with state preservation
✅ Export (CSV, Excel, PDF, JSON)
✅ Role-based access control
✅ 11 REST API endpoints
✅ Complete documentation

---

## 📖 Next Step

→ Go to **[README_REBUILD.md](README_REBUILD.md)**
