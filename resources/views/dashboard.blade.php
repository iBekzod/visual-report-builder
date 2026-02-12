@extends('visual-report-builder::layouts.app')

@section('title', 'Dashboard - Visual Report Builder')

@section('styles')
<style>
    /* Power BI Style Data Grid */
    .data-grid-container {
        position: relative;
        overflow: hidden;
        background: white;
        border-radius: var(--radius);
    }

    .data-grid-wrapper {
        overflow: auto;
        max-height: 500px;
    }

    .data-grid {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.8125rem;
    }

    .data-grid th {
        position: sticky;
        top: 0;
        z-index: 10;
        padding: 0.625rem 0.875rem;
        text-align: left;
        font-weight: 600;
        color: var(--text);
        background: linear-gradient(to bottom, #f8fafc, #f1f5f9);
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
        transition: background 0.15s;
    }

    .data-grid th:hover {
        background: linear-gradient(to bottom, #f1f5f9, #e2e8f0);
    }

    .data-grid th.sorted-asc::after,
    .data-grid th.sorted-desc::after {
        content: '';
        display: inline-block;
        margin-left: 0.5rem;
        border: 4px solid transparent;
    }

    .data-grid th.sorted-asc::after {
        border-bottom-color: var(--primary);
        transform: translateY(-2px);
    }

    .data-grid th.sorted-desc::after {
        border-top-color: var(--primary);
        transform: translateY(2px);
    }

    .data-grid th .resize-handle {
        position: absolute;
        right: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        cursor: col-resize;
        background: transparent;
    }

    .data-grid th .resize-handle:hover,
    .data-grid th .resize-handle.resizing {
        background: var(--primary);
    }

    .data-grid td {
        padding: 0.5rem 0.875rem;
        border-bottom: 1px solid #f1f5f9;
        color: var(--text);
        max-width: 250px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .data-grid tbody tr:hover {
        background: #f8fafc;
    }

    .data-grid tbody tr:nth-child(even) {
        background: #fafbfc;
    }

    .data-grid tbody tr:nth-child(even):hover {
        background: #f1f5f9;
    }

    .data-grid .number-cell {
        text-align: right;
        font-family: 'SF Mono', 'Monaco', 'Menlo', monospace;
        font-size: 0.75rem;
    }

    .data-grid .positive {
        color: #059669;
    }

    .data-grid .negative {
        color: #dc2626;
    }

    /* Column Filter Row */
    .data-grid .filter-row th {
        padding: 0.375rem 0.5rem;
        background: #f8fafc;
    }

    .data-grid .filter-row input {
        width: 100%;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        border: 1px solid var(--border);
        border-radius: 3px;
        outline: none;
    }

    .data-grid .filter-row input:focus {
        border-color: var(--primary);
    }

    /* Grid Toolbar */
    .grid-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0.75rem 1rem;
        background: #f8fafc;
        border-bottom: 1px solid var(--border);
        gap: 1rem;
        flex-wrap: wrap;
    }

    .grid-toolbar-left {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .grid-toolbar-right {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .grid-search {
        position: relative;
    }

    .grid-search input {
        padding: 0.375rem 0.75rem 0.375rem 2rem;
        font-size: 0.8125rem;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        width: 220px;
        outline: none;
    }

    .grid-search input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(245, 48, 3, 0.1);
    }

    .grid-search svg {
        position: absolute;
        left: 0.625rem;
        top: 50%;
        transform: translateY(-50%);
        width: 14px;
        height: 14px;
        color: var(--text-muted);
    }

    /* Chart Container */
    .chart-container {
        background: white;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        overflow: hidden;
    }

    .chart-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .chart-title {
        font-size: 0.9375rem;
        font-weight: 600;
        color: var(--dark);
    }

    .chart-body {
        padding: 1rem;
        min-height: 350px;
    }

    /* Chart Type Selector */
    .chart-type-selector {
        display: flex;
        background: #f1f5f9;
        border-radius: var(--radius);
        padding: 0.25rem;
    }

    .chart-type-btn {
        padding: 0.375rem 0.75rem;
        border: none;
        background: transparent;
        color: var(--text-muted);
        font-size: 0.75rem;
        font-weight: 500;
        cursor: pointer;
        border-radius: calc(var(--radius) - 2px);
        transition: all 0.15s;
        display: flex;
        align-items: center;
        gap: 0.375rem;
    }

    .chart-type-btn:hover {
        color: var(--text);
    }

    .chart-type-btn.active {
        background: white;
        color: var(--primary);
        box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }

    .chart-type-btn svg {
        width: 14px;
        height: 14px;
    }

    /* Pivot Controls */
    .pivot-controls {
        display: flex;
        gap: 1rem;
        padding: 1rem;
        background: #fafbfc;
        border-bottom: 1px solid var(--border);
        flex-wrap: wrap;
    }

    .pivot-group {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .pivot-label {
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .pivot-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
        min-height: 32px;
        padding: 0.375rem;
        background: white;
        border: 1px dashed var(--border);
        border-radius: var(--radius);
        min-width: 180px;
    }

    .pivot-chips.drop-active {
        border-color: var(--primary);
        background: #fef2f2;
    }

    .pivot-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.25rem 0.5rem;
        background: var(--primary);
        color: white;
        font-size: 0.6875rem;
        font-weight: 500;
        border-radius: 3px;
        cursor: move;
    }

    .pivot-chip .remove {
        background: none;
        border: none;
        color: rgba(255,255,255,0.7);
        cursor: pointer;
        padding: 0;
        font-size: 0.875rem;
        line-height: 1;
    }

    .pivot-chip .remove:hover {
        color: white;
    }

    /* Summary Cards */
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 1rem;
        padding: 1rem;
        background: linear-gradient(to right, #f0fdf4, #ecfeff);
        border-bottom: 1px solid #bbf7d0;
    }

    .summary-card {
        text-align: center;
        padding: 0.75rem;
        background: white;
        border-radius: var(--radius);
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .summary-card-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: var(--dark);
        font-family: 'SF Mono', monospace;
    }

    .summary-card-label {
        font-size: 0.6875rem;
        color: var(--text-muted);
        margin-top: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    /* View Tabs */
    .view-tabs {
        display: flex;
        border-bottom: 1px solid var(--border);
        background: #fafbfc;
    }

    .view-tab {
        padding: 0.75rem 1.25rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--text-muted);
        background: transparent;
        border: none;
        cursor: pointer;
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.15s;
    }

    .view-tab:hover {
        color: var(--text);
        background: white;
    }

    .view-tab.active {
        color: var(--primary);
        background: white;
    }

    .view-tab.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 2px;
        background: var(--primary);
    }

    .view-tab svg {
        width: 16px;
        height: 16px;
    }
</style>
@endsection

@section('content')
    <!-- Page Header -->
    <div class="page-header flex justify-between items-center">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">View and analyze your reports</p>
        </div>
        <a href="{{ route('visual-reports.builder') }}" class="btn btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 5v14M5 12h14"/>
            </svg>
            Create Template
        </a>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-icon primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 17H7A5 5 0 017 7h2"/>
                    <path d="M15 7h2a5 5 0 010 10h-2"/>
                    <path d="M8 12h8"/>
                </svg>
            </div>
            <div class="stat-value" id="statTemplates">0</div>
            <div class="stat-label">Templates</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 3v18h18"/>
                    <path d="M18 17V9"/>
                    <path d="M13 17V5"/>
                    <path d="M8 17v-3"/>
                </svg>
            </div>
            <div class="stat-value" id="statReports">0</div>
            <div class="stat-label">Saved Reports</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon warning">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                </svg>
            </div>
            <div class="stat-value" id="statFavorites">0</div>
            <div class="stat-label">Favorites</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-icon info">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <div class="stat-value" id="statRecent">-</div>
            <div class="stat-label">Last Executed</div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div style="display: grid; grid-template-columns: 300px 1fr; gap: 1.5rem;">
        <!-- Left Sidebar: Templates -->
        <div class="card" style="height: fit-content; max-height: calc(100vh - 380px); display: flex; flex-direction: column;">
            <div class="card-header flex justify-between items-center">
                <h3 class="card-title">Templates</h3>
                <span class="badge badge-secondary" id="templateCount">0</span>
            </div>
            <div style="padding: 0.75rem; border-bottom: 1px solid var(--border);">
                <div style="position: relative;">
                    <svg style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 1rem; height: 1rem; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" id="templateSearch" placeholder="Search templates..."
                        class="form-input" style="padding-left: 2.25rem; font-size: 0.8125rem;"
                        oninput="filterTemplates(this.value)">
                </div>
            </div>
            <div id="templatesList" style="flex: 1; overflow-y: auto; padding: 0.5rem;">
                <div class="empty-state" style="padding: 2rem 1rem;">
                    <div class="empty-state-icon" style="width: 3rem; height: 3rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 17H7A5 5 0 017 7h2"/>
                            <path d="M15 7h2a5 5 0 010 10h-2"/>
                            <path d="M8 12h8"/>
                        </svg>
                    </div>
                    <p class="text-muted">Loading templates...</p>
                </div>
            </div>
        </div>

        <!-- Right Content: Report Display -->
        <div class="card" style="display: flex; flex-direction: column; min-height: calc(100vh - 380px);">
            <!-- Report Header -->
            <div class="card-header">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="card-title" id="reportTitle">Select a Template</h3>
                        <p class="text-muted mt-1" id="reportDescription" style="font-size: 0.8125rem;">Choose a template from the sidebar to get started</p>
                    </div>
                    <div class="flex gap-2" id="reportActions" style="display: none;">
                        <button onclick="executeReport()" class="btn btn-primary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="5 3 19 12 5 21 5 3"/>
                            </svg>
                            Execute
                        </button>
                        <button onclick="openExportModal()" class="btn btn-secondary">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                <polyline points="7 10 12 15 17 10"/>
                                <line x1="12" y1="15" x2="12" y2="3"/>
                            </svg>
                            Export
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div id="filtersSection" style="display: none; padding: 0.75rem 1rem; background: var(--light); border-bottom: 1px solid var(--border);">
                <div class="flex items-center gap-2 mb-2">
                    <svg style="width: 0.875rem; height: 0.875rem; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    <span class="font-medium" style="font-size: 0.8125rem;">Filters</span>
                </div>
                <div id="filterInputs" style="display: flex; flex-wrap: wrap; gap: 0.75rem;"></div>
            </div>

            <!-- View Tabs -->
            <div id="viewTabs" class="view-tabs" style="display: none;">
                <button class="view-tab active" data-view="table" onclick="switchView('table')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    Table
                </button>
                <button class="view-tab" data-view="bar" onclick="switchView('bar')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><rect x="7" y="10" width="3" height="8"/><rect x="14" y="6" width="3" height="12"/></svg>
                    Bar Chart
                </button>
                <button class="view-tab" data-view="line" onclick="switchView('line')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M3 15l5-5 4 4 9-9"/></svg>
                    Line Chart
                </button>
                <button class="view-tab" data-view="area" onclick="switchView('area')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M3 15l5-5 4 4 9-9v12H3z"/></svg>
                    Area Chart
                </button>
                <button class="view-tab" data-view="pie" onclick="switchView('pie')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                    Pie Chart
                </button>
                <button class="view-tab" data-view="donut" onclick="switchView('donut')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
                    Donut
                </button>
                <button class="view-tab" data-view="heatmap" onclick="switchView('heatmap')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                    Heatmap
                </button>
            </div>

            <!-- Summary Cards -->
            <div id="summaryCards" class="summary-cards" style="display: none;"></div>

            <!-- Report Content -->
            <div id="reportContent" style="flex: 1; overflow: auto;">
                <div class="empty-state">
                    <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M3 3v18h18"/>
                        <path d="M18 17V9"/>
                        <path d="M13 17V5"/>
                        <path d="M8 17v-3"/>
                    </svg>
                    <h3 class="empty-state-title">No Report Selected</h3>
                    <p class="empty-state-text">Select a template from the sidebar and click Execute to view the report</p>
                </div>
            </div>

            <!-- Pagination Bar -->
            <div id="paginationBar" style="display: none; padding: 0.75rem 1rem; border-top: 1px solid var(--border); background: var(--light);">
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <span class="text-muted" style="font-size: 0.8125rem;">
                            Showing <span id="showingFrom">0</span>-<span id="showingTo">0</span> of <span id="totalRecordsDisplay">0</span> records
                        </span>
                        <select id="pageSizeSelect" class="form-select" style="width: auto; padding: 0.375rem 0.5rem; font-size: 0.8125rem;" onchange="changePageSize()">
                            <option value="10">10 rows</option>
                            <option value="20" selected>20 rows</option>
                            <option value="50">50 rows</option>
                            <option value="100">100 rows</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="goToPage(1)" id="firstPageBtn" class="btn btn-secondary btn-sm" disabled style="padding: 0.375rem;">
                            <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
                        </button>
                        <button onclick="goToPage(currentPage - 1)" id="prevPageBtn" class="btn btn-secondary btn-sm" disabled style="padding: 0.375rem;">
                            <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <span style="font-size: 0.8125rem; padding: 0 0.5rem;">
                            Page <input type="number" id="currentPageInput" class="form-input" style="width: 50px; text-align: center; padding: 0.375rem; font-size: 0.8125rem;" value="1" min="1" onchange="goToPage(parseInt(this.value))"> of <span id="totalPagesDisplay">1</span>
                        </span>
                        <button onclick="goToPage(currentPage + 1)" id="nextPageBtn" class="btn btn-secondary btn-sm" disabled style="padding: 0.375rem;">
                            <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                        <button onclick="goToPage(totalPages)" id="lastPageBtn" class="btn btn-secondary btn-sm" disabled style="padding: 0.375rem;">
                            <svg style="width: 1rem; height: 1rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div id="exportModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Export Report</h3>
                <button onclick="closeExportModal()" class="modal-close">
                    <svg style="width: 1.25rem; height: 1.25rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Export Format</label>
                    <select id="exportFormat" class="form-select">
                        <option value="csv">CSV (Spreadsheet)</option>
                        <option value="excel">Excel (XLSX)</option>
                        <option value="pdf">PDF (Document)</option>
                        <option value="json">JSON (Data)</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeExportModal()" class="btn btn-secondary">Cancel</button>
                <button onclick="performExport()" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/>
                        <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Export
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    // State
    let currentTemplate = null;
    let currentData = null;
    let currentFilters = {};
    let currentView = 'table';
    let allTemplates = [];
    let chartInstance = null;

    // Pagination state
    let currentPage = 1;
    let pageSize = 20;
    let totalRecords = 0;
    let totalPages = 1;

    // Sort state
    let sortColumn = null;
    let sortDirection = 'asc';

    // ApexCharts color palette
    const chartColors = ['#f53003', '#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#84cc16'];

    document.addEventListener('DOMContentLoaded', async () => {
        await loadDashboardData();
    });

    async function loadDashboardData() {
        try {
            const response = await window.apiClient.get('/api/visual-reports/templates');
            allTemplates = response.templates || [];

            document.getElementById('statTemplates').textContent = allTemplates.length;
            document.getElementById('templateCount').textContent = allTemplates.length;

            renderTemplates(allTemplates);
        } catch (error) {
            console.error('Error loading data:', error);
        }
    }

    function renderTemplates(templates) {
        const container = document.getElementById('templatesList');

        if (templates.length === 0) {
            container.innerHTML = `
                <div class="empty-state" style="padding: 2rem 1rem;">
                    <svg class="empty-state-icon" style="width: 3rem; height: 3rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M9 17H7A5 5 0 017 7h2"/>
                        <path d="M15 7h2a5 5 0 010 10h-2"/>
                        <path d="M8 12h8"/>
                    </svg>
                    <h3 class="empty-state-title" style="font-size: 0.9375rem;">No Templates</h3>
                    <p class="empty-state-text" style="font-size: 0.8125rem;">Create your first template</p>
                    <a href="{{ route('visual-reports.builder') }}" class="btn btn-primary btn-sm">Create</a>
                </div>
            `;
            return;
        }

        // Group by category
        const grouped = {};
        templates.forEach(t => {
            const cat = t.category || 'Other';
            if (!grouped[cat]) grouped[cat] = [];
            grouped[cat].push(t);
        });

        let html = '';
        Object.entries(grouped).forEach(([category, temps]) => {
            html += `
                <div style="margin-bottom: 0.25rem;">
                    <button onclick="toggleCategory(this)" class="btn btn-ghost" style="width: 100%; justify-content: space-between; padding: 0.5rem 0.75rem; font-size: 0.8125rem;">
                        <span class="font-medium">${category}</span>
                        <span class="badge badge-secondary" style="font-size: 0.6875rem;">${temps.length}</span>
                    </button>
                    <div class="category-items" style="padding-left: 0.375rem;">
            `;

            temps.forEach(t => {
                html += `
                    <div onclick="selectTemplate(${t.id})" class="template-item" data-id="${t.id}"
                        style="padding: 0.625rem 0.75rem; border-radius: var(--radius); cursor: pointer; margin: 0.125rem 0; transition: all 0.15s ease; border-left: 3px solid transparent;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.125rem;">${t.icon || '📊'}</span>
                            <div style="flex: 1; min-width: 0;">
                                <div class="font-medium" style="color: var(--dark); font-size: 0.8125rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${t.name}</div>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += `</div></div>`;
        });

        container.innerHTML = html;

        document.querySelectorAll('.template-item').forEach(item => {
            item.addEventListener('mouseenter', () => {
                if (!item.classList.contains('active')) {
                    item.style.background = 'var(--light)';
                }
            });
            item.addEventListener('mouseleave', () => {
                if (!item.classList.contains('active')) {
                    item.style.background = 'transparent';
                    item.style.borderLeftColor = 'transparent';
                }
            });
        });
    }

    function filterTemplates(query) {
        const filtered = allTemplates.filter(t =>
            t.name.toLowerCase().includes(query.toLowerCase()) ||
            (t.description && t.description.toLowerCase().includes(query.toLowerCase()))
        );
        renderTemplates(filtered);
    }

    function toggleCategory(btn) {
        const items = btn.nextElementSibling;
        items.style.display = items.style.display === 'none' ? 'block' : 'none';
    }

    async function selectTemplate(id) {
        try {
            const response = await window.apiClient.get(`/api/visual-reports/templates/${id}`);
            currentTemplate = response;
            currentFilters = {};
            currentView = 'table';

            document.getElementById('reportTitle').textContent = response.name;
            document.getElementById('reportDescription').textContent = response.description || 'No description';
            document.getElementById('reportActions').style.display = 'flex';
            document.getElementById('viewTabs').style.display = 'none';

            // Highlight selected
            document.querySelectorAll('.template-item').forEach(item => {
                item.classList.remove('active');
                item.style.background = 'transparent';
                item.style.borderLeftColor = 'transparent';
            });
            const selectedItem = document.querySelector(`.template-item[data-id="${id}"]`);
            if (selectedItem) {
                selectedItem.classList.add('active');
                selectedItem.style.background = '#fef2f2';
                selectedItem.style.borderLeftColor = 'var(--primary)';
            }

            renderFilters(response.filters);

            document.getElementById('reportContent').innerHTML = `
                <div class="empty-state">
                    <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                    <h3 class="empty-state-title">Ready to Execute</h3>
                    <p class="empty-state-text">Click the Execute button to generate the report</p>
                </div>
            `;

            document.getElementById('summaryCards').style.display = 'none';
            document.getElementById('paginationBar').style.display = 'none';
        } catch (error) {
            console.error('Error selecting template:', error);
        }
    }

    function renderFilters(filters) {
        const container = document.getElementById('filterInputs');
        const section = document.getElementById('filtersSection');

        if (!filters || filters.length === 0) {
            section.style.display = 'none';
            return;
        }

        section.style.display = 'block';
        let html = '';

        filters.forEach(f => {
            html += `<div style="min-width: 160px;">
                <label style="font-size: 0.6875rem; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">${f.label}${f.is_required ? ' *' : ''}</label>`;

            if (f.type === 'select' && f.options) {
                html += `<select class="form-select" style="font-size: 0.8125rem; padding: 0.375rem 0.5rem;" data-column="${f.column}" onchange="updateFilter('${f.column}', this.value)">
                    <option value="">All</option>`;
                f.options.forEach(o => {
                    html += `<option value="${o.value}">${o.label}</option>`;
                });
                html += `</select>`;
            } else if (f.type === 'date') {
                html += `<input type="date" class="form-input" style="font-size: 0.8125rem; padding: 0.375rem 0.5rem;" data-column="${f.column}" onchange="updateFilter('${f.column}', this.value)">`;
            } else {
                html += `<input type="text" class="form-input" style="font-size: 0.8125rem; padding: 0.375rem 0.5rem;" data-column="${f.column}" placeholder="Enter..." onchange="updateFilter('${f.column}', this.value)">`;
            }

            html += `</div>`;
        });

        container.innerHTML = html;
    }

    function updateFilter(column, value) {
        currentFilters[column] = value || null;
    }

    async function executeReport(resetPage = true) {
        if (!currentTemplate) {
            alert('Please select a template first');
            return;
        }

        if (resetPage) {
            currentPage = 1;
        }

        const container = document.getElementById('reportContent');
        container.innerHTML = `
            <div class="empty-state">
                <div style="width: 2rem; height: 2rem; border: 3px solid var(--border); border-top-color: var(--primary); border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 1rem;"></div>
                <p class="text-muted">Loading report data...</p>
            </div>
            <style>@keyframes spin { to { transform: rotate(360deg); } }</style>
        `;

        try {
            const response = await window.apiClient.post(
                `/api/visual-reports/templates/${currentTemplate.id}/execute`,
                {
                    filters: currentFilters,
                    view_type: currentView,
                    page: currentPage,
                    per_page: pageSize
                }
            );

            if (response.success) {
                currentData = response;

                if (response.pagination) {
                    totalRecords = response.pagination.total || 0;
                    totalPages = response.pagination.total_pages || 1;
                    currentPage = response.pagination.current_page || 1;
                } else if (response.metadata) {
                    totalRecords = response.metadata.record_count || (response.data.rows ? response.data.rows.length : 0);
                    totalPages = Math.ceil(totalRecords / pageSize) || 1;
                }

                document.getElementById('viewTabs').style.display = 'flex';
                renderSummaryCards(response.data.summary, response.metadata);
                renderView();
                updatePaginationControls();
                document.getElementById('statRecent').textContent = 'Just now';
            } else {
                container.innerHTML = `
                    <div class="alert alert-danger" style="margin: 1rem;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4"/>
                            <circle cx="12" cy="16" r="1" fill="currentColor"/>
                        </svg>
                        <span>${response.message}</span>
                    </div>
                `;
                document.getElementById('paginationBar').style.display = 'none';
            }
        } catch (error) {
            container.innerHTML = `
                <div class="alert alert-danger" style="margin: 1rem;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v4"/>
                        <circle cx="12" cy="16" r="1" fill="currentColor"/>
                    </svg>
                    <span>Error executing report: ${error.message}</span>
                </div>
            `;
            document.getElementById('paginationBar').style.display = 'none';
        }
    }

    function renderSummaryCards(summary, metadata) {
        const container = document.getElementById('summaryCards');

        if (!summary && !metadata) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'grid';

        let html = `
            <div class="summary-card">
                <div class="summary-card-value">${(totalRecords || 0).toLocaleString()}</div>
                <div class="summary-card-label">Total Records</div>
            </div>
        `;

        if (metadata?.execution_time_ms) {
            html += `
                <div class="summary-card">
                    <div class="summary-card-value">${metadata.execution_time_ms}ms</div>
                    <div class="summary-card-label">Query Time</div>
                </div>
            `;
        }

        if (summary) {
            Object.entries(summary).forEach(([key, stats]) => {
                if (stats.sum !== undefined && stats.sum !== 0) {
                    const formattedValue = formatNumber(stats.sum);
                    html += `
                        <div class="summary-card">
                            <div class="summary-card-value">${formattedValue}</div>
                            <div class="summary-card-label">Sum of ${formatColumnName(key)}</div>
                        </div>
                    `;
                }
            });
        }

        container.innerHTML = html;
    }

    function switchView(view) {
        currentView = view;

        document.querySelectorAll('.view-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.view === view);
        });

        renderView();
        updatePaginationControls();
    }

    function renderView() {
        if (!currentData) return;

        if (currentView === 'table') {
            renderDataGrid(currentData.data.rows);
        } else {
            renderChart(currentView, currentData);
        }
    }

    function renderDataGrid(rows) {
        const container = document.getElementById('reportContent');

        if (!rows || rows.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M8 12h8"/>
                    </svg>
                    <h3 class="empty-state-title">No Data</h3>
                    <p class="empty-state-text">No records found for the selected criteria</p>
                </div>
            `;
            return;
        }

        const columns = Object.keys(rows[0]);

        let html = `
            <div class="data-grid-container">
                <div class="grid-toolbar">
                    <div class="grid-toolbar-left">
                        <div class="grid-search">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                            <input type="text" placeholder="Search in results..." oninput="filterGridData(this.value)">
                        </div>
                    </div>
                    <div class="grid-toolbar-right">
                        <span class="text-muted" style="font-size: 0.75rem;">${columns.length} columns</span>
                    </div>
                </div>
                <div class="data-grid-wrapper">
                    <table class="data-grid">
                        <thead>
                            <tr>
        `;

        columns.forEach((col, i) => {
            const label = formatColumnName(col);
            const sortClass = sortColumn === col ? `sorted-${sortDirection}` : '';
            html += `<th class="${sortClass}" onclick="sortByColumn('${col}')" style="position: relative;">
                ${label}
                <div class="resize-handle" onmousedown="startResize(event, ${i})"></div>
            </th>`;
        });

        html += `</tr></thead><tbody>`;

        rows.forEach(row => {
            html += '<tr>';
            columns.forEach(col => {
                const val = row[col];
                const isNumber = typeof val === 'number' || (!isNaN(parseFloat(val)) && isFinite(val));

                let cellClass = '';
                let displayVal = val;

                if (val === null || val === undefined) {
                    displayVal = '<span style="color: #94a3b8;">-</span>';
                } else if (isNumber) {
                    cellClass = 'number-cell';
                    const num = parseFloat(val);
                    if (num > 0) cellClass += ' positive';
                    if (num < 0) cellClass += ' negative';
                    displayVal = formatNumber(num);
                } else if (typeof val === 'boolean') {
                    displayVal = val
                        ? '<span class="badge badge-success">Yes</span>'
                        : '<span class="badge badge-secondary">No</span>';
                }

                html += `<td class="${cellClass}">${displayVal}</td>`;
            });
            html += '</tr>';
        });

        html += '</tbody></table></div></div>';
        container.innerHTML = html;
    }

    function renderChart(type, data) {
        const container = document.getElementById('reportContent');
        const rows = data.data.rows;

        if (!rows || rows.length === 0) return;

        container.innerHTML = '<div id="chartContainer" style="padding: 1rem; min-height: 400px;"></div>';

        const dimensions = data.data.dimensions || [];
        const metrics = data.data.metrics || [];

        const labelColumn = dimensions.length > 0 ? dimensions[0].column : Object.keys(rows[0])[0];
        const labels = rows.map(r => r[labelColumn] || 'Unknown');

        // Find metric columns
        const metricColumns = metrics.length > 0
            ? metrics.map(m => m.alias || `${m.column}_${m.aggregate}`)
            : Object.keys(rows[0]).filter(k => k !== labelColumn);

        // Destroy previous chart
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        let options = {
            chart: {
                type: getApexChartType(type),
                height: 380,
                toolbar: {
                    show: true,
                    tools: {
                        download: true,
                        selection: true,
                        zoom: true,
                        zoomin: true,
                        zoomout: true,
                        pan: true,
                        reset: true
                    }
                },
                animations: {
                    enabled: true,
                    speed: 500
                },
                fontFamily: 'Inter, sans-serif'
            },
            colors: chartColors,
            dataLabels: {
                enabled: type === 'pie' || type === 'donut'
            },
            stroke: {
                curve: 'smooth',
                width: type === 'area' || type === 'line' ? 2 : 0
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center'
            },
            tooltip: {
                shared: true,
                intersect: false
            }
        };

        if (type === 'pie' || type === 'donut') {
            // Pie/Donut chart
            const firstMetricCol = metricColumns[0];
            const values = rows.map(r => parseFloat(r[firstMetricCol]) || 0);

            options.series = values;
            options.labels = labels;

            if (type === 'donut') {
                options.plotOptions = {
                    pie: {
                        donut: {
                            size: '55%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function(w) {
                                        return formatNumber(w.globals.seriesTotals.reduce((a, b) => a + b, 0));
                                    }
                                }
                            }
                        }
                    }
                };
            }
        } else if (type === 'heatmap') {
            // Heatmap
            const series = [];
            metricColumns.forEach((col, i) => {
                series.push({
                    name: formatColumnName(col),
                    data: labels.map((label, j) => ({
                        x: label,
                        y: parseFloat(rows[j][col]) || 0
                    }))
                });
            });

            options.series = series;
            options.plotOptions = {
                heatmap: {
                    colorScale: {
                        ranges: [
                            { from: -1000000, to: 0, color: '#dc2626', name: 'Negative' },
                            { from: 0, to: 1000, color: '#f59e0b', name: 'Low' },
                            { from: 1000, to: 10000, color: '#10b981', name: 'Medium' },
                            { from: 10000, to: 1000000, color: '#059669', name: 'High' }
                        ]
                    }
                }
            };
        } else {
            // Bar, Line, Area charts
            const series = metricColumns.map((col, i) => ({
                name: formatColumnName(col),
                data: rows.map(r => parseFloat(r[col]) || 0)
            }));

            options.series = series;
            options.xaxis = {
                categories: labels,
                labels: {
                    rotate: -45,
                    style: {
                        fontSize: '11px'
                    }
                }
            };
            options.yaxis = {
                labels: {
                    formatter: function(val) {
                        return formatNumber(val);
                    }
                }
            };

            if (type === 'area') {
                options.fill = {
                    type: 'gradient',
                    gradient: {
                        shadeIntensity: 1,
                        opacityFrom: 0.7,
                        opacityTo: 0.3
                    }
                };
            }
        }

        chartInstance = new ApexCharts(document.getElementById('chartContainer'), options);
        chartInstance.render();
    }

    function getApexChartType(type) {
        const typeMap = {
            'bar': 'bar',
            'line': 'line',
            'area': 'area',
            'pie': 'pie',
            'donut': 'donut',
            'heatmap': 'heatmap'
        };
        return typeMap[type] || 'bar';
    }

    function updatePaginationControls() {
        if (currentView !== 'table' || totalRecords === 0) {
            document.getElementById('paginationBar').style.display = 'none';
            return;
        }

        document.getElementById('paginationBar').style.display = 'block';

        const from = (currentPage - 1) * pageSize + 1;
        const to = Math.min(currentPage * pageSize, totalRecords);

        document.getElementById('showingFrom').textContent = totalRecords > 0 ? from : 0;
        document.getElementById('showingTo').textContent = to;
        document.getElementById('totalRecordsDisplay').textContent = totalRecords;
        document.getElementById('currentPageInput').value = currentPage;
        document.getElementById('currentPageInput').max = totalPages;
        document.getElementById('totalPagesDisplay').textContent = totalPages;

        document.getElementById('firstPageBtn').disabled = currentPage <= 1;
        document.getElementById('prevPageBtn').disabled = currentPage <= 1;
        document.getElementById('nextPageBtn').disabled = currentPage >= totalPages;
        document.getElementById('lastPageBtn').disabled = currentPage >= totalPages;
    }

    function goToPage(page) {
        if (page < 1 || page > totalPages || page === currentPage) return;
        currentPage = page;
        executeReport(false);
    }

    function changePageSize() {
        pageSize = parseInt(document.getElementById('pageSizeSelect').value);
        currentPage = 1;
        executeReport(false);
    }

    function sortByColumn(column) {
        if (sortColumn === column) {
            sortDirection = sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            sortColumn = column;
            sortDirection = 'asc';
        }

        if (currentData && currentData.data.rows) {
            const rows = [...currentData.data.rows];
            rows.sort((a, b) => {
                let valA = a[column];
                let valB = b[column];

                if (typeof valA === 'number' && typeof valB === 'number') {
                    return sortDirection === 'asc' ? valA - valB : valB - valA;
                }

                valA = String(valA || '').toLowerCase();
                valB = String(valB || '').toLowerCase();

                if (sortDirection === 'asc') {
                    return valA.localeCompare(valB);
                } else {
                    return valB.localeCompare(valA);
                }
            });

            currentData.data.rows = rows;
            renderDataGrid(rows);
        }
    }

    function filterGridData(query) {
        if (!currentData || !currentData.data.rows) return;

        const originalRows = currentData.data.originalRows || currentData.data.rows;
        if (!currentData.data.originalRows) {
            currentData.data.originalRows = [...originalRows];
        }

        if (!query) {
            currentData.data.rows = [...currentData.data.originalRows];
        } else {
            const lowerQuery = query.toLowerCase();
            currentData.data.rows = currentData.data.originalRows.filter(row => {
                return Object.values(row).some(val =>
                    String(val || '').toLowerCase().includes(lowerQuery)
                );
            });
        }

        renderDataGrid(currentData.data.rows);
    }

    function startResize(e, colIndex) {
        e.preventDefault();
        e.stopPropagation();

        const th = e.target.parentElement;
        const startX = e.pageX;
        const startWidth = th.offsetWidth;

        e.target.classList.add('resizing');

        function onMouseMove(e) {
            const width = startWidth + (e.pageX - startX);
            th.style.width = Math.max(50, width) + 'px';
        }

        function onMouseUp() {
            document.removeEventListener('mousemove', onMouseMove);
            document.removeEventListener('mouseup', onMouseUp);
            document.querySelector('.resize-handle.resizing')?.classList.remove('resizing');
        }

        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('mouseup', onMouseUp);
    }

    // Helpers
    function formatColumnName(col) {
        return col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
    }

    function formatNumber(num) {
        if (num === null || num === undefined) return '-';
        if (Math.abs(num) >= 1000000) {
            return (num / 1000000).toFixed(1) + 'M';
        }
        if (Math.abs(num) >= 1000) {
            return (num / 1000).toFixed(1) + 'K';
        }
        return Number.isInteger(num) ? num.toLocaleString() : num.toFixed(2);
    }

    // Export Functions
    function openExportModal() {
        if (!currentData) {
            alert('Please execute a report first');
            return;
        }
        document.getElementById('exportModal').classList.add('active');
    }

    function closeExportModal() {
        document.getElementById('exportModal').classList.remove('active');
    }

    async function performExport() {
        const format = document.getElementById('exportFormat').value;

        if (!currentTemplate || !currentData) {
            alert('Please execute a report first');
            closeExportModal();
            return;
        }

        try {
            const exportBtn = document.querySelector('#exportModal .btn-primary');
            const originalText = exportBtn.innerHTML;
            exportBtn.innerHTML = '<svg class="animate-spin" style="width:1rem;height:1rem;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none" opacity="0.25"/><path fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Exporting...';
            exportBtn.disabled = true;

            const exportUrl = `/api/visual-reports/templates/${currentTemplate.id}/export/${format}`;

            const response = await fetch(exportUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': format === 'json' ? 'application/json' : '*/*',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    data: currentData.data.rows,
                    filters: currentFilters
                })
            });

            if (!response.ok) {
                throw new Error('Export failed');
            }

            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;

            const disposition = response.headers.get('Content-Disposition');
            let filename = `${currentTemplate.name.replace(/\s+/g, '_')}_${new Date().toISOString().slice(0,10)}`;

            if (disposition && disposition.includes('filename=')) {
                const matches = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (matches && matches[1]) {
                    filename = matches[1].replace(/['"]/g, '');
                }
            } else {
                const extensions = { csv: 'csv', json: 'json', excel: 'xlsx', pdf: 'html' };
                filename += '.' + (extensions[format] || 'csv');
            }

            a.download = filename;
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);

            exportBtn.innerHTML = originalText;
            exportBtn.disabled = false;
            closeExportModal();

        } catch (error) {
            console.error('Export error:', error);
            alert('Error exporting report: ' + error.message);

            const exportBtn = document.querySelector('#exportModal .btn-primary');
            exportBtn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Export';
            exportBtn.disabled = false;
        }
    }
</script>
@endsection
