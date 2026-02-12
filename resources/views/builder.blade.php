@extends('visual-report-builder::layouts.app')

@section('title', 'Report Builder')

@section('styles')
<style>
    .builder-layout {
        display: grid;
        grid-template-columns: 340px 1fr;
        gap: 1.5rem;
        min-height: calc(100vh - 160px);
    }

    /* Left Panel */
    .config-panel {
        background: white;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .panel-header {
        padding: 1rem 1.25rem;
        background: linear-gradient(135deg, var(--primary) 0%, #dc2626 100%);
        color: white;
    }

    .panel-header h2 {
        font-size: 1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .panel-body {
        flex: 1;
        overflow-y: auto;
        padding: 1.25rem;
    }

    /* Step sections */
    .config-step {
        margin-bottom: 1.5rem;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid var(--border);
    }

    .config-step:last-child {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }

    .step-header {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin-bottom: 0.75rem;
    }

    .step-number {
        width: 24px;
        height: 24px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        font-weight: 600;
        flex-shrink: 0;
    }

    .step-number.completed {
        background: var(--success);
    }

    .step-title {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text);
    }

    .step-hint {
        font-size: 0.75rem;
        color: var(--secondary);
        margin-left: 2.25rem;
        margin-bottom: 0.75rem;
    }

    /* Checkbox list for relationships */
    .checkbox-list {
        background: var(--light);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        max-height: 160px;
        overflow-y: auto;
    }

    .checkbox-item {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.625rem 0.875rem;
        border-bottom: 1px solid var(--border);
        cursor: pointer;
        transition: background 0.15s;
    }

    .checkbox-item:last-child {
        border-bottom: none;
    }

    .checkbox-item:hover {
        background: white;
    }

    .checkbox-item input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: var(--primary);
        cursor: pointer;
    }

    .checkbox-item label {
        flex: 1;
        cursor: pointer;
        font-size: 0.8125rem;
    }

    .checkbox-item .item-badge {
        font-size: 0.625rem;
        padding: 0.125rem 0.375rem;
        border-radius: 3px;
        background: #e0e7ff;
        color: #4f46e5;
    }

    /* Field chips */
    .field-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        min-height: 44px;
        padding: 0.5rem;
        background: var(--light);
        border: 2px dashed var(--border);
        border-radius: var(--radius);
    }

    .field-chips.active {
        border-color: var(--primary);
        background: #fef2f2;
    }

    .field-chips.has-items {
        border-style: solid;
    }

    .field-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.625rem;
        background: white;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        font-size: 0.8125rem;
        box-shadow: 0 1px 2px rgba(0,0,0,0.05);
    }

    .field-chip.dimension {
        border-left: 3px solid #3b82f6;
    }

    .field-chip.metric {
        border-left: 3px solid var(--success);
    }

    .field-chip .chip-label {
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .field-chip .chip-table {
        font-size: 0.625rem;
        color: #6366f1;
        background: #eef2ff;
        padding: 0.0625rem 0.25rem;
        border-radius: 2px;
    }

    .field-chip select {
        font-size: 0.6875rem;
        padding: 0.125rem 0.25rem;
        border: 1px solid var(--border);
        border-radius: 3px;
        background: var(--light);
    }

    .field-chip .remove-chip {
        width: 16px;
        height: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        background: transparent;
        color: var(--secondary);
        cursor: pointer;
        border-radius: 3px;
        padding: 0;
    }

    .field-chip .remove-chip:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    .chips-placeholder {
        color: var(--secondary);
        font-size: 0.75rem;
        padding: 0.375rem;
    }

    /* Filter row */
    .filter-row {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        background: white;
        border: 1px solid var(--border);
        border-left: 3px solid #f59e0b;
        border-radius: var(--radius);
        margin-bottom: 0.5rem;
    }

    .filter-row .filter-column {
        font-size: 0.8125rem;
        font-weight: 500;
        min-width: 80px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .filter-row select,
    .filter-row input {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border: 1px solid var(--border);
        border-radius: 3px;
    }

    .filter-row select {
        width: 70px;
    }

    .filter-row input {
        flex: 1;
        min-width: 60px;
    }

    /* Available fields panel */
    .fields-panel {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .field-group {
        background: var(--light);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        padding: 0.5rem;
    }

    .field-group-title {
        font-size: 0.6875rem;
        font-weight: 600;
        color: var(--secondary);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.375rem;
        padding: 0 0.25rem;
    }

    .field-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.375rem;
    }

    .field-button {
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
        padding: 0.375rem 0.625rem;
        background: white;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        font-size: 0.75rem;
        cursor: pointer;
        transition: all 0.15s;
    }

    .field-button:hover {
        border-color: var(--primary);
        background: #fef2f2;
    }

    .field-button.dimension {
        border-left: 2px solid #3b82f6;
    }

    .field-button.metric {
        border-left: 2px solid var(--success);
    }

    .field-button .field-table-badge {
        font-size: 0.5625rem;
        color: #6366f1;
        background: #eef2ff;
        padding: 0.0625rem 0.25rem;
        border-radius: 2px;
    }

    /* Actions panel */
    .panel-actions {
        padding: 1rem 1.25rem;
        background: var(--light);
        border-top: 1px solid var(--border);
        display: flex;
        gap: 0.75rem;
    }

    .panel-actions .btn {
        flex: 1;
    }

    /* Right Panel - Preview */
    .preview-panel {
        background: white;
        border-radius: var(--radius-lg);
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .preview-header {
        padding: 1rem 1.25rem;
        background: var(--light);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .preview-header h3 {
        font-size: 0.9375rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .preview-body {
        flex: 1;
        overflow: auto;
        padding: 0;
    }

    /* Data table */
    .data-table-wrapper {
        overflow-x: auto;
    }

    .data-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8125rem;
    }

    .data-table th {
        padding: 0.75rem 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--text);
        background: #f8fafc;
        border-bottom: 2px solid var(--border);
        white-space: nowrap;
        position: sticky;
        top: 0;
        z-index: 1;
    }

    .data-table td {
        padding: 0.625rem 1rem;
        border-bottom: 1px solid var(--border);
        color: var(--text);
    }

    .data-table tbody tr:hover {
        background: #f8fafc;
    }

    .data-table .number {
        text-align: right;
        font-family: 'SF Mono', monospace;
    }

    /* Pagination */
    .pagination-bar {
        padding: 0.75rem 1.25rem;
        background: var(--light);
        border-top: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .pagination-info {
        font-size: 0.8125rem;
        color: var(--secondary);
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pagination-controls .btn {
        padding: 0.375rem 0.75rem;
    }

    .page-size-select {
        font-size: 0.8125rem;
        padding: 0.375rem 0.5rem;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: white;
    }

    .page-input {
        width: 50px;
        text-align: center;
        font-size: 0.8125rem;
        padding: 0.375rem;
        border: 1px solid var(--border);
        border-radius: var(--radius);
    }

    /* Empty/loading states */
    .preview-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 3rem;
        text-align: center;
        color: var(--secondary);
    }

    .preview-placeholder svg {
        width: 48px;
        height: 48px;
        margin-bottom: 1rem;
        opacity: 0.4;
    }

    .preview-placeholder h4 {
        font-size: 1rem;
        font-weight: 600;
        color: var(--text);
        margin-bottom: 0.5rem;
    }

    .preview-placeholder p {
        font-size: 0.875rem;
        max-width: 300px;
    }

    /* Loading spinner */
    .loading-spinner {
        width: 32px;
        height: 32px;
        border: 3px solid var(--border);
        border-top-color: var(--primary);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin-bottom: 1rem;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Stats row */
    .stats-row {
        display: flex;
        gap: 1rem;
        padding: 0.75rem 1.25rem;
        background: #f0fdf4;
        border-bottom: 1px solid #bbf7d0;
    }

    .stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8125rem;
    }

    .stat-item .stat-value {
        font-weight: 600;
        color: var(--success);
    }

    /* View tabs for chart/table selection */
    .view-tabs {
        display: flex;
        gap: 0.25rem;
        padding: 0.5rem;
        background: var(--light);
        border-radius: var(--radius);
    }

    .view-tab {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.5rem 0.75rem;
        border: none;
        background: transparent;
        color: var(--secondary);
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: var(--radius);
        cursor: pointer;
        transition: all 0.15s;
    }

    .view-tab:hover {
        background: white;
        color: var(--text);
    }

    .view-tab.active {
        background: white;
        color: var(--primary);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .view-tab svg {
        width: 14px;
        height: 14px;
    }

    /* Chart container */
    .chart-container {
        padding: 1.5rem;
        min-height: 350px;
    }

    #previewChart {
        width: 100%;
        min-height: 320px;
    }

    /* Summary cards */
    .summary-cards {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--border);
    }

    .summary-card {
        flex: 1;
        min-width: 120px;
        padding: 0.75rem;
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    }

    .summary-card .card-label {
        font-size: 0.6875rem;
        font-weight: 500;
        color: var(--secondary);
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.25rem;
    }

    .summary-card .card-value {
        font-size: 1.125rem;
        font-weight: 700;
        color: var(--text);
    }
</style>
@endsection

@section('content')
<div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 class="page-title">Report Builder</h1>
        <p style="color: var(--secondary); font-size: 0.875rem; margin-top: 0.25rem;">
            Create custom reports by selecting tables and fields
        </p>
    </div>
    <a href="{{ route('visual-reports.dashboard') }}" class="btn btn-secondary">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Back to Dashboard
    </a>
</div>

<div class="builder-layout">
    <!-- Left Panel: Configuration -->
    <div class="config-panel">
        <div class="panel-header">
            <h2>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                Configuration
            </h2>
        </div>

        <div class="panel-body">
            <!-- Step 1: Select Table -->
            <div class="config-step">
                <div class="step-header">
                    <span class="step-number" id="step1">1</span>
                    <span class="step-title">Select Main Table</span>
                </div>
                <p class="step-hint">Choose the primary data source for your report</p>
                <select id="modelSelect" class="form-select">
                    <option value="">-- Select a table --</option>
                </select>
            </div>

            <!-- Step 2: Join Related Tables -->
            <div class="config-step" id="relationshipsStep" style="display: none;">
                <div class="step-header">
                    <span class="step-number" id="step2">2</span>
                    <span class="step-title">Join Related Tables</span>
                </div>
                <p class="step-hint">Select additional tables to combine data (optional)</p>
                <div id="relationshipsList" class="checkbox-list">
                    <div class="chips-placeholder" style="padding: 1rem; text-align: center;">No related tables available</div>
                </div>
            </div>

            <!-- Step 3: Select Fields -->
            <div class="config-step" id="fieldsStep" style="display: none;">
                <div class="step-header">
                    <span class="step-number" id="step3">3</span>
                    <span class="step-title">Select Fields</span>
                </div>
                <p class="step-hint">Click fields to add them to your report</p>

                <div class="fields-panel" id="availableFields">
                    <div class="chips-placeholder" style="padding: 1rem; text-align: center;">Loading fields...</div>
                </div>
            </div>

            <!-- Step 4: Configure Columns -->
            <div class="config-step" id="columnsStep" style="display: none;">
                <div class="step-header">
                    <span class="step-number" id="step4">4</span>
                    <span class="step-title">Report Columns</span>
                </div>
                <p class="step-hint">Fields that will appear in your report</p>

                <div style="margin-bottom: 0.75rem;">
                    <label style="font-size: 0.75rem; font-weight: 500; color: var(--secondary); margin-bottom: 0.375rem; display: block;">Group By (Dimensions)</label>
                    <div id="selectedDimensions" class="field-chips">
                        <span class="chips-placeholder">Click dimension fields above to add</span>
                    </div>
                </div>

                <div>
                    <label style="font-size: 0.75rem; font-weight: 500; color: var(--secondary); margin-bottom: 0.375rem; display: block;">Calculate (Metrics)</label>
                    <div id="selectedMetrics" class="field-chips">
                        <span class="chips-placeholder">Click metric fields above to add</span>
                    </div>
                </div>
            </div>

            <!-- Step 5: Filters -->
            <div class="config-step" id="filtersStep" style="display: none;">
                <div class="step-header">
                    <span class="step-number" id="step5">5</span>
                    <span class="step-title">Filters (Optional)</span>
                </div>
                <p class="step-hint">Add conditions to filter your data</p>
                <div id="filtersList"></div>
                <button id="addFilterBtn" class="btn btn-secondary btn-sm" style="width: 100%; margin-top: 0.5rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                    Add Filter
                </button>
            </div>
        </div>

        <div class="panel-actions">
            <button onclick="runPreview()" class="btn btn-secondary" id="previewBtn" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                Preview
            </button>
            <button onclick="openSaveModal()" class="btn btn-primary" id="saveBtn" disabled>
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Template
            </button>
        </div>
    </div>

    <!-- Right Panel: Preview -->
    <div class="preview-panel">
        <div class="preview-header">
            <h3>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                Data Preview
            </h3>
            <div id="previewActions" style="display: none; align-items: center; gap: 1rem;">
                <div class="view-tabs">
                    <button class="view-tab active" data-view="table" onclick="switchView('table')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                        Table
                    </button>
                    <button class="view-tab" data-view="bar" onclick="switchView('bar')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="18" rx="1"/><rect x="14" y="9" width="7" height="12" rx="1"/></svg>
                        Bar
                    </button>
                    <button class="view-tab" data-view="line" onclick="switchView('line')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                        Line
                    </button>
                    <button class="view-tab" data-view="area" onclick="switchView('area')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3v18h18"/><path d="M3 15l4-4 4 4 5-6 5 6"/></svg>
                        Area
                    </button>
                    <button class="view-tab" data-view="pie" onclick="switchView('pie')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/></svg>
                        Pie
                    </button>
                    <button class="view-tab" data-view="donut" onclick="switchView('donut')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="4"/></svg>
                        Donut
                    </button>
                </div>
                <select id="pageSizeSelect" class="page-size-select" onchange="changePageSize()">
                    <option value="10">10 rows</option>
                    <option value="20" selected>20 rows</option>
                    <option value="50">50 rows</option>
                    <option value="100">100 rows</option>
                </select>
            </div>
        </div>

        <div id="summaryCards" class="summary-cards" style="display: none;"></div>

        <div id="statsRow" class="stats-row" style="display: none;"></div>

        <div class="preview-body" id="previewBody">
            <!-- Table view -->
            <div id="tableView">
                <div class="preview-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    <h4>Configure Your Report</h4>
                    <p>Select a table and add fields to see a preview of your data</p>
                </div>
            </div>

            <!-- Chart view -->
            <div id="chartView" class="chart-container" style="display: none;">
                <div id="previewChart"></div>
            </div>
        </div>

        <div id="paginationBar" class="pagination-bar" style="display: none;">
            <div class="pagination-info">
                Showing <span id="showingFrom">0</span>-<span id="showingTo">0</span> of <span id="totalRecords">0</span> records
            </div>
            <div class="pagination-controls">
                <button class="btn btn-secondary btn-sm" onclick="goToPage(1)" id="firstPageBtn" disabled>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="11 17 6 12 11 7"/><polyline points="18 17 13 12 18 7"/></svg>
                </button>
                <button class="btn btn-secondary btn-sm" onclick="goToPage(currentPage - 1)" id="prevPageBtn" disabled>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <span style="font-size: 0.8125rem;">
                    Page <input type="number" id="currentPageInput" class="page-input" value="1" min="1" onchange="goToPage(parseInt(this.value))"> of <span id="totalPages">1</span>
                </span>
                <button class="btn btn-secondary btn-sm" onclick="goToPage(currentPage + 1)" id="nextPageBtn" disabled>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <button class="btn btn-secondary btn-sm" onclick="goToPage(totalPages)" id="lastPageBtn" disabled>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="13 17 18 12 13 7"/><polyline points="6 17 11 12 6 7"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Save Template Modal -->
<div id="saveModal" class="modal-backdrop">
    <div class="modal" style="max-width: 440px;">
        <div class="modal-header">
            <h2 style="font-size: 1.125rem; font-weight: 600;">Save Report Template</h2>
            <button onclick="closeSaveModal()" class="modal-close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Template Name <span style="color: var(--danger);">*</span></label>
                <input type="text" id="templateName" class="form-input" placeholder="e.g., Monthly Sales Report">
            </div>
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea id="templateDesc" class="form-input" rows="2" placeholder="Brief description of this report" style="resize: vertical;"></textarea>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Category <span style="color: var(--danger);">*</span></label>
                    <select id="templateCategory" class="form-select">
                        <option value="">Select...</option>
                        <option value="Sales">Sales</option>
                        <option value="Finance">Finance</option>
                        <option value="Operations">Operations</option>
                        <option value="HR">HR</option>
                        <option value="Marketing">Marketing</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Icon</label>
                    <input type="text" id="templateIcon" class="form-input" placeholder="📊" maxlength="2" style="text-align: center; font-size: 1.25rem;">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeSaveModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="saveTemplate()" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/></svg>
                Save Template
            </button>
        </div>
    </div>
</div>

<!-- Add Filter Modal -->
<div id="addFilterModal" class="modal-backdrop">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-header">
            <h2 style="font-size: 1.125rem; font-weight: 600;">Add Filter</h2>
            <button onclick="closeAddFilterModal()" class="modal-close">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Field to Filter</label>
                <select id="filterFieldSelect" class="form-select">
                    <option value="">Select field...</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Condition</label>
                <select id="filterOperatorSelect" class="form-select">
                    <option value="=">=  (equals)</option>
                    <option value="!=">!= (not equals)</option>
                    <option value=">">&#62; (greater than)</option>
                    <option value=">=">&#62;= (greater or equal)</option>
                    <option value="<">&#60; (less than)</option>
                    <option value="<=">&#60;= (less or equal)</option>
                    <option value="like">Contains</option>
                    <option value="is_null">Is Empty</option>
                    <option value="is_not_null">Is Not Empty</option>
                </select>
            </div>
            <div class="form-group" id="filterValueGroup">
                <label class="form-label">Value</label>
                <input type="text" id="filterValueInput" class="form-input" placeholder="Enter value...">
            </div>
        </div>
        <div class="modal-footer">
            <button onclick="closeAddFilterModal()" class="btn btn-secondary">Cancel</button>
            <button onclick="confirmAddFilter()" class="btn btn-primary">Add Filter</button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // State
    let currentModel = null;
    let availableDimensions = [];
    let availableMetrics = [];
    let allRelationships = [];
    let selectedRelationships = [];
    let previewData = [];
    let currentPage = 1;
    let pageSize = 20;
    let totalRecords = 0;
    let totalPages = 1;
    let currentView = 'table';
    let chartInstance = null;

    // Chart colors palette
    const chartColors = [
        '#ef4444', '#f97316', '#f59e0b', '#84cc16', '#22c55e',
        '#14b8a6', '#06b6d4', '#3b82f6', '#6366f1', '#8b5cf6',
        '#a855f7', '#d946ef', '#ec4899', '#f43f5e'
    ];

    const reportConfig = {
        model: null,
        relationships: [],
        dimensions: [],
        metrics: [],
        filters: []
    };

    const AGGREGATES = [
        { value: 'sum', label: 'SUM' },
        { value: 'count', label: 'COUNT' },
        { value: 'avg', label: 'AVG' },
        { value: 'min', label: 'MIN' },
        { value: 'max', label: 'MAX' }
    ];

    // Initialize
    document.addEventListener('DOMContentLoaded', loadModels);

    async function loadModels() {
        try {
            const response = await window.apiClient.get('/api/visual-reports/models');
            const models = response || [];
            const select = document.getElementById('modelSelect');

            models.forEach(model => {
                const option = document.createElement('option');
                option.value = model.class;
                option.textContent = model.label || model.name;
                select.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading models:', error);
            alert('Failed to load tables. Please refresh the page.');
        }
    }

    // Model selection
    document.getElementById('modelSelect').addEventListener('change', async (e) => {
        currentModel = e.target.value;
        reportConfig.model = currentModel;

        // Reset state
        selectedRelationships = [];
        reportConfig.relationships = [];
        reportConfig.dimensions = [];
        reportConfig.metrics = [];
        reportConfig.filters = [];
        availableDimensions = [];
        availableMetrics = [];

        updateSelectedFields();
        updateFiltersList();
        hidePreview();
        updateButtons();

        if (!currentModel) {
            document.getElementById('step1').classList.remove('completed');
            document.getElementById('relationshipsStep').style.display = 'none';
            document.getElementById('fieldsStep').style.display = 'none';
            document.getElementById('columnsStep').style.display = 'none';
            document.getElementById('filtersStep').style.display = 'none';
            return;
        }

        document.getElementById('step1').classList.add('completed');

        // Load relationships
        try {
            const encodedModel = encodeURIComponent(currentModel);
            const relResponse = await window.apiClient.get(`/api/visual-reports/models/${encodedModel}/relationships`);
            allRelationships = relResponse.relationships || relResponse || [];
            renderRelationships();
            document.getElementById('relationshipsStep').style.display = 'block';
        } catch (e) {
            document.getElementById('relationshipsStep').style.display = 'none';
        }

        // Load fields
        await loadModelFields(currentModel, 'primary', getModelName(currentModel));

        document.getElementById('fieldsStep').style.display = 'block';
        document.getElementById('columnsStep').style.display = 'block';
        document.getElementById('filtersStep').style.display = 'block';
    });

    function getModelName(modelClass) {
        return modelClass.split('\\').pop();
    }

    async function loadModelFields(modelClass, tableKey, tableLabel) {
        try {
            const encoded = encodeURIComponent(modelClass);
            const [dims, mets] = await Promise.all([
                window.apiClient.get(`/api/visual-reports/models/${encoded}/dimensions`),
                window.apiClient.get(`/api/visual-reports/models/${encoded}/metrics`)
            ]);

            const newDims = (dims || []).map(d => ({
                ...d,
                column: tableKey === 'primary' ? d.column : `${tableKey}.${d.column}`,
                table: tableKey,
                tableLabel: tableLabel
            }));

            const newMets = (mets || []).map(m => ({
                ...m,
                column: tableKey === 'primary' ? m.column : `${tableKey}.${m.column}`,
                table: tableKey,
                tableLabel: tableLabel,
                aggregate: m.default_aggregate || 'sum'
            }));

            // Add/replace fields for this table
            availableDimensions = availableDimensions.filter(d => d.table !== tableKey).concat(newDims);
            availableMetrics = availableMetrics.filter(m => m.table !== tableKey).concat(newMets);

            renderAvailableFields();
        } catch (e) {
            console.error('Error loading fields:', e);
        }
    }

    function removeTableFields(tableKey) {
        availableDimensions = availableDimensions.filter(d => d.table !== tableKey);
        availableMetrics = availableMetrics.filter(m => m.table !== tableKey);

        // Remove from selected
        reportConfig.dimensions = reportConfig.dimensions.filter(d => d.table !== tableKey);
        reportConfig.metrics = reportConfig.metrics.filter(m => m.table !== tableKey);
        reportConfig.filters = reportConfig.filters.filter(f => !f.column.startsWith(tableKey + '.'));

        renderAvailableFields();
        updateSelectedFields();
        updateFiltersList();
    }

    function renderRelationships() {
        const container = document.getElementById('relationshipsList');

        if (!allRelationships.length) {
            container.innerHTML = '<div class="chips-placeholder" style="padding: 1rem; text-align: center;">No related tables available</div>';
            return;
        }

        let html = '';
        allRelationships.forEach(rel => {
            html += `
                <div class="checkbox-item">
                    <input type="checkbox" id="rel_${rel.name}" value="${rel.name}" onchange="toggleRelationship('${rel.name}', this.checked)">
                    <label for="rel_${rel.name}">${rel.label}</label>
                    <span class="item-badge">${rel.type}</span>
                </div>
            `;
        });
        container.innerHTML = html;
    }

    async function toggleRelationship(relName, checked) {
        const rel = allRelationships.find(r => r.name === relName);
        if (!rel) return;

        if (checked) {
            selectedRelationships.push(rel);
            reportConfig.relationships.push(relName);
            await loadModelFields(rel.related_model, relName, rel.label);
        } else {
            selectedRelationships = selectedRelationships.filter(r => r.name !== relName);
            reportConfig.relationships = reportConfig.relationships.filter(r => r !== relName);
            removeTableFields(relName);
        }

        updateButtons();
    }

    function renderAvailableFields() {
        const container = document.getElementById('availableFields');

        // Group by table
        const tables = {};

        availableDimensions.forEach(d => {
            if (!tables[d.table]) tables[d.table] = { label: d.tableLabel, dimensions: [], metrics: [] };
            tables[d.table].dimensions.push(d);
        });

        availableMetrics.forEach(m => {
            if (!tables[m.table]) tables[m.table] = { label: m.tableLabel, dimensions: [], metrics: [] };
            tables[m.table].metrics.push(m);
        });

        let html = '';

        Object.entries(tables).forEach(([tableKey, table]) => {
            html += `<div class="field-group">
                <div class="field-group-title">${table.label} ${tableKey !== 'primary' ? '<span style="color: #6366f1;">(joined)</span>' : ''}</div>
                <div class="field-list">`;

            // Dimensions
            table.dimensions.forEach(d => {
                const isSelected = reportConfig.dimensions.some(sel => sel.column === d.column);
                html += `
                    <button type="button" class="field-button dimension ${isSelected ? 'selected' : ''}"
                        onclick="toggleDimension('${d.column}')"
                        data-column="${d.column}"
                        style="${isSelected ? 'opacity: 0.5;' : ''}">
                        ${d.label}
                    </button>
                `;
            });

            // Metrics
            table.metrics.forEach(m => {
                const isSelected = reportConfig.metrics.some(sel => sel.column === m.column);
                html += `
                    <button type="button" class="field-button metric ${isSelected ? 'selected' : ''}"
                        onclick="toggleMetric('${m.column}')"
                        data-column="${m.column}"
                        style="${isSelected ? 'opacity: 0.5;' : ''}">
                        ${m.label}
                    </button>
                `;
            });

            html += '</div></div>';
        });

        container.innerHTML = html || '<div class="chips-placeholder" style="padding: 1rem; text-align: center;">No fields available</div>';
    }

    function toggleDimension(column) {
        const existing = reportConfig.dimensions.findIndex(d => d.column === column);

        if (existing >= 0) {
            reportConfig.dimensions.splice(existing, 1);
        } else {
            const dim = availableDimensions.find(d => d.column === column);
            if (dim) {
                reportConfig.dimensions.push({ ...dim });
            }
        }

        renderAvailableFields();
        updateSelectedFields();
        updateButtons();
    }

    function toggleMetric(column) {
        const existing = reportConfig.metrics.findIndex(m => m.column === column);

        if (existing >= 0) {
            reportConfig.metrics.splice(existing, 1);
        } else {
            const met = availableMetrics.find(m => m.column === column);
            if (met) {
                reportConfig.metrics.push({
                    ...met,
                    aggregate: met.aggregate || 'sum',
                    alias: `${column.replace('.', '_')}_${met.aggregate || 'sum'}`
                });
            }
        }

        renderAvailableFields();
        updateSelectedFields();
        updateButtons();
    }

    function updateSelectedFields() {
        // Dimensions
        const dimsContainer = document.getElementById('selectedDimensions');
        if (reportConfig.dimensions.length === 0) {
            dimsContainer.innerHTML = '<span class="chips-placeholder">Click dimension fields above to add</span>';
            dimsContainer.classList.remove('has-items');
        } else {
            dimsContainer.classList.add('has-items');
            let html = '';
            reportConfig.dimensions.forEach((d, i) => {
                html += `
                    <div class="field-chip dimension">
                        <span class="chip-label">${d.label}</span>
                        ${d.table !== 'primary' ? `<span class="chip-table">${d.tableLabel}</span>` : ''}
                        <button type="button" class="remove-chip" onclick="removeDimension(${i})">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                `;
            });
            dimsContainer.innerHTML = html;
        }

        // Metrics
        const metsContainer = document.getElementById('selectedMetrics');
        if (reportConfig.metrics.length === 0) {
            metsContainer.innerHTML = '<span class="chips-placeholder">Click metric fields above to add</span>';
            metsContainer.classList.remove('has-items');
        } else {
            metsContainer.classList.add('has-items');
            let html = '';
            reportConfig.metrics.forEach((m, i) => {
                html += `
                    <div class="field-chip metric">
                        <span class="chip-label">${m.label}</span>
                        ${m.table !== 'primary' ? `<span class="chip-table">${m.tableLabel}</span>` : ''}
                        <select onchange="changeAggregate(${i}, this.value)">
                            ${AGGREGATES.map(a => `<option value="${a.value}" ${m.aggregate === a.value ? 'selected' : ''}>${a.label}</option>`).join('')}
                        </select>
                        <button type="button" class="remove-chip" onclick="removeMetric(${i})">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        </button>
                    </div>
                `;
            });
            metsContainer.innerHTML = html;
        }
    }

    function removeDimension(index) {
        reportConfig.dimensions.splice(index, 1);
        renderAvailableFields();
        updateSelectedFields();
        updateButtons();
    }

    function removeMetric(index) {
        reportConfig.metrics.splice(index, 1);
        renderAvailableFields();
        updateSelectedFields();
        updateButtons();
    }

    function changeAggregate(index, value) {
        reportConfig.metrics[index].aggregate = value;
        reportConfig.metrics[index].alias = `${reportConfig.metrics[index].column.replace('.', '_')}_${value}`;
    }

    // Filters
    document.getElementById('addFilterBtn').addEventListener('click', openAddFilterModal);

    document.getElementById('filterOperatorSelect').addEventListener('change', (e) => {
        const valueGroup = document.getElementById('filterValueGroup');
        valueGroup.style.display = ['is_null', 'is_not_null'].includes(e.target.value) ? 'none' : 'block';
    });

    function openAddFilterModal() {
        // Populate field select
        const select = document.getElementById('filterFieldSelect');
        select.innerHTML = '<option value="">Select field...</option>';

        const allFields = [...availableDimensions, ...availableMetrics];
        allFields.forEach(f => {
            select.innerHTML += `<option value="${f.column}">${f.label}${f.table !== 'primary' ? ` (${f.tableLabel})` : ''}</option>`;
        });

        document.getElementById('filterOperatorSelect').value = '=';
        document.getElementById('filterValueInput').value = '';
        document.getElementById('filterValueGroup').style.display = 'block';
        document.getElementById('addFilterModal').classList.add('active');
    }

    function closeAddFilterModal() {
        document.getElementById('addFilterModal').classList.remove('active');
    }

    function confirmAddFilter() {
        const column = document.getElementById('filterFieldSelect').value;
        const operator = document.getElementById('filterOperatorSelect').value;
        const value = document.getElementById('filterValueInput').value;

        if (!column) {
            alert('Please select a field');
            return;
        }

        if (!['is_null', 'is_not_null'].includes(operator) && !value) {
            alert('Please enter a value');
            return;
        }

        const field = [...availableDimensions, ...availableMetrics].find(f => f.column === column);

        reportConfig.filters.push({
            column,
            label: field?.label || column,
            operator,
            value,
            table: field?.table || 'primary'
        });

        updateFiltersList();
        closeAddFilterModal();
    }

    function updateFiltersList() {
        const container = document.getElementById('filtersList');

        if (reportConfig.filters.length === 0) {
            container.innerHTML = '';
            return;
        }

        let html = '';
        reportConfig.filters.forEach((f, i) => {
            const operatorLabels = {
                '=': '=', '!=': '!=', '>': '>', '>=': '>=', '<': '<', '<=': '<=',
                'like': 'contains', 'is_null': 'is empty', 'is_not_null': 'is not empty'
            };

            html += `
                <div class="filter-row">
                    <span class="filter-column">${f.label}</span>
                    <span style="color: var(--secondary); font-size: 0.75rem;">${operatorLabels[f.operator] || f.operator}</span>
                    ${!['is_null', 'is_not_null'].includes(f.operator) ? `<span style="font-size: 0.8125rem; font-weight: 500;">${f.value}</span>` : ''}
                    <button type="button" class="remove-chip" onclick="removeFilter(${i})" style="margin-left: auto;">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function removeFilter(index) {
        reportConfig.filters.splice(index, 1);
        updateFiltersList();
    }

    function updateButtons() {
        const hasMetrics = reportConfig.metrics.length > 0;
        document.getElementById('previewBtn').disabled = !hasMetrics;
        document.getElementById('saveBtn').disabled = !hasMetrics;
    }

    // Preview
    async function runPreview() {
        if (reportConfig.metrics.length === 0) {
            alert('Please add at least one metric field');
            return;
        }

        currentPage = 1;
        await fetchPreviewData();
    }

    async function fetchPreviewData() {
        const tableView = document.getElementById('tableView');
        const chartView = document.getElementById('chartView');

        // Show loading state in current view
        const loadingHtml = `
            <div class="preview-placeholder">
                <div class="loading-spinner"></div>
                <p>Loading data...</p>
            </div>
        `;

        if (currentView === 'table') {
            tableView.innerHTML = loadingHtml;
        } else {
            document.getElementById('previewChart').innerHTML = loadingHtml;
        }

        try {
            const response = await window.apiClient.post('/api/visual-reports/preview', {
                model: reportConfig.model,
                relationships: reportConfig.relationships,
                row_dimensions: reportConfig.dimensions.map(d => d.column),
                column_dimensions: [],
                metrics: reportConfig.metrics,
                filters: reportConfig.filters.filter(f => f.value || ['is_null', 'is_not_null'].includes(f.operator)),
                page: currentPage,
                per_page: pageSize
            });

            if (response.success) {
                previewData = response.data || [];

                // Handle new pagination response format
                if (response.pagination) {
                    totalRecords = response.pagination.total || previewData.length;
                    totalPages = response.pagination.total_pages || Math.ceil(totalRecords / pageSize) || 1;
                    currentPage = response.pagination.current_page || 1;
                } else {
                    // Fallback for old response format
                    totalRecords = response.total || previewData.length;
                    totalPages = Math.ceil(totalRecords / pageSize) || 1;
                }

                // Render appropriate view
                if (currentView === 'table') {
                    renderPreviewTable();
                } else {
                    renderChart(currentView);
                }

                showPagination();
                showStats(response);
            } else {
                const errorHtml = `
                    <div class="preview-placeholder">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><circle cx="12" cy="16" r="1" fill="currentColor"/></svg>
                        <h4>Error</h4>
                        <p>${response.message || 'Failed to load data'}</p>
                    </div>
                `;
                if (currentView === 'table') {
                    tableView.innerHTML = errorHtml;
                } else {
                    document.getElementById('previewChart').innerHTML = errorHtml;
                }
            }
        } catch (error) {
            const errorHtml = `
                <div class="preview-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><circle cx="12" cy="16" r="1" fill="currentColor"/></svg>
                    <h4>Error</h4>
                    <p>${error.message || 'Failed to load data'}</p>
                </div>
            `;
            if (currentView === 'table') {
                tableView.innerHTML = errorHtml;
            } else {
                document.getElementById('previewChart').innerHTML = errorHtml;
            }
        }
    }

    function renderPreviewTable() {
        const tableView = document.getElementById('tableView');

        if (!previewData || previewData.length === 0) {
            tableView.innerHTML = `
                <div class="preview-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/></svg>
                    <h4>No Data Found</h4>
                    <p>No records match your criteria. Try adjusting your filters.</p>
                </div>
            `;
            return;
        }

        const columns = Object.keys(previewData[0]);

        let html = '<div class="data-table-wrapper"><table class="data-table"><thead><tr>';

        columns.forEach(col => {
            const label = col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            html += `<th>${label}</th>`;
        });

        html += '</tr></thead><tbody>';

        previewData.forEach(row => {
            html += '<tr>';
            columns.forEach(col => {
                const val = row[col];
                const isNumber = typeof val === 'number' || (!isNaN(parseFloat(val)) && col.includes('_'));
                const displayVal = val === null || val === undefined ? '-' : (isNumber ? Number(val).toLocaleString() : val);
                html += `<td class="${isNumber ? 'number' : ''}">${displayVal}</td>`;
            });
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        tableView.innerHTML = html;
    }

    function showStats(response) {
        const statsRow = document.getElementById('statsRow');
        statsRow.style.display = 'flex';

        let html = `
            <div class="stat-item">
                <span class="stat-value">${totalRecords.toLocaleString()}</span>
                <span>total records</span>
            </div>
        `;

        if (response.execution_time_ms) {
            html += `
                <div class="stat-item">
                    <span class="stat-value">${response.execution_time_ms}ms</span>
                    <span>query time</span>
                </div>
            `;
        }

        statsRow.innerHTML = html;

        // Render summary cards
        renderSummaryCards();
    }

    function showPagination() {
        document.getElementById('previewActions').style.display = 'flex';
        document.getElementById('paginationBar').style.display = 'flex';

        const from = (currentPage - 1) * pageSize + 1;
        const to = Math.min(currentPage * pageSize, totalRecords);

        document.getElementById('showingFrom').textContent = totalRecords > 0 ? from : 0;
        document.getElementById('showingTo').textContent = to;
        document.getElementById('totalRecords').textContent = totalRecords;
        document.getElementById('currentPageInput').value = currentPage;
        document.getElementById('currentPageInput').max = totalPages;
        document.getElementById('totalPages').textContent = totalPages;

        document.getElementById('firstPageBtn').disabled = currentPage <= 1;
        document.getElementById('prevPageBtn').disabled = currentPage <= 1;
        document.getElementById('nextPageBtn').disabled = currentPage >= totalPages;
        document.getElementById('lastPageBtn').disabled = currentPage >= totalPages;
    }

    function hidePreview() {
        document.getElementById('previewActions').style.display = 'none';
        document.getElementById('statsRow').style.display = 'none';
        document.getElementById('paginationBar').style.display = 'none';
        document.getElementById('summaryCards').style.display = 'none';

        // Reset view to table
        currentView = 'table';
        document.querySelectorAll('.view-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.view === 'table');
        });

        document.getElementById('tableView').style.display = 'block';
        document.getElementById('chartView').style.display = 'none';
        document.getElementById('tableView').innerHTML = `
            <div class="preview-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                <h4>Configure Your Report</h4>
                <p>Select a table and add fields to see a preview of your data</p>
            </div>
        `;

        // Destroy chart if exists
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }
    }

    function goToPage(page) {
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        fetchPreviewData();
    }

    function changePageSize() {
        pageSize = parseInt(document.getElementById('pageSizeSelect').value);
        currentPage = 1;
        fetchPreviewData();
    }

    // Save Modal
    function openSaveModal() {
        if (reportConfig.metrics.length === 0) {
            alert('Please add at least one metric field');
            return;
        }
        document.getElementById('saveModal').classList.add('active');
        document.getElementById('templateName').focus();
    }

    function closeSaveModal() {
        document.getElementById('saveModal').classList.remove('active');
    }

    async function saveTemplate() {
        const name = document.getElementById('templateName').value.trim();
        const category = document.getElementById('templateCategory').value;

        if (!name) {
            alert('Please enter a template name');
            return;
        }

        if (!category) {
            alert('Please select a category');
            return;
        }

        const saveBtn = document.querySelector('#saveModal .btn-primary');
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="loading-spinner" style="width:16px;height:16px;border-width:2px;margin-right:0.5rem;"></span> Saving...';

        try {
            const response = await window.apiClient.post('/api/visual-reports/builder/save-template', {
                name,
                description: document.getElementById('templateDesc').value.trim(),
                category,
                icon: document.getElementById('templateIcon').value.trim() || '📊',
                model: reportConfig.model,
                relationships: reportConfig.relationships,
                row_dimensions: reportConfig.dimensions,
                column_dimensions: [],
                metrics: reportConfig.metrics,
                filters: reportConfig.filters,
                default_view: { type: currentView }
            });

            if (response.success) {
                closeSaveModal();
                alert('Template saved successfully!');
                window.location.href = '{{ route("visual-reports.dashboard") }}';
            } else {
                alert('Error: ' + (response.message || 'Failed to save template'));
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/></svg> Save Template';
            }
        } catch (error) {
            alert('Error: ' + error.message);
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/></svg> Save Template';
        }
    }

    // Close modals on backdrop click
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });
    });

    // ESC to close modals
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.active').forEach(m => m.classList.remove('active'));
        }
    });

    // View switching
    function switchView(view) {
        currentView = view;

        // Update tab active state
        document.querySelectorAll('.view-tab').forEach(tab => {
            tab.classList.toggle('active', tab.dataset.view === view);
        });

        // Show/hide views
        const tableView = document.getElementById('tableView');
        const chartView = document.getElementById('chartView');
        const paginationBar = document.getElementById('paginationBar');
        const pageSizeSelect = document.getElementById('pageSizeSelect');

        if (view === 'table') {
            tableView.style.display = 'block';
            chartView.style.display = 'none';
            paginationBar.style.display = 'flex';
            pageSizeSelect.style.display = 'block';
            renderPreviewTable();
        } else {
            tableView.style.display = 'none';
            chartView.style.display = 'block';
            paginationBar.style.display = 'none';
            pageSizeSelect.style.display = 'none';
            renderChart(view);
        }
    }

    // Chart rendering with ApexCharts
    function renderChart(type) {
        // Destroy existing chart
        if (chartInstance) {
            chartInstance.destroy();
            chartInstance = null;
        }

        if (!previewData || previewData.length === 0) {
            document.getElementById('previewChart').innerHTML = `
                <div class="preview-placeholder">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M8 12h8"/></svg>
                    <h4>No Data Available</h4>
                    <p>Run a preview to see chart visualization</p>
                </div>
            `;
            return;
        }

        // Get dimension and metric columns
        const columns = Object.keys(previewData[0]);
        const dimensionCols = reportConfig.dimensions.map(d => d.column.split('.').pop());
        const metricCols = reportConfig.metrics.map(m => m.alias || `${m.column.replace('.', '_')}_${m.aggregate}`);

        // Find available dimension and metric columns in data
        const labelCol = columns.find(c => dimensionCols.includes(c)) || columns[0];
        const valueCols = columns.filter(c => metricCols.some(m => c.includes(m.replace('.', '_'))) || (!dimensionCols.includes(c) && c !== labelCol));

        // Extract labels and series data
        const labels = previewData.map(row => String(row[labelCol] || 'Unknown'));
        const series = [];

        if (type === 'pie' || type === 'donut') {
            // For pie/donut, use first metric column values
            const valueCol = valueCols[0] || columns.find(c => c !== labelCol);
            const values = previewData.map(row => parseFloat(row[valueCol]) || 0);

            const options = {
                chart: {
                    type: type === 'donut' ? 'donut' : 'pie',
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: true }
                },
                series: values,
                labels: labels,
                colors: chartColors,
                legend: {
                    position: 'bottom',
                    fontSize: '12px'
                },
                dataLabels: {
                    enabled: true,
                    formatter: (val) => val.toFixed(1) + '%'
                },
                tooltip: {
                    y: {
                        formatter: (val) => Number(val).toLocaleString()
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: type === 'donut' ? '55%' : '0%',
                            labels: {
                                show: type === 'donut',
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: (w) => {
                                        const total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        return Number(total).toLocaleString();
                                    }
                                }
                            }
                        }
                    }
                }
            };

            chartInstance = new ApexCharts(document.getElementById('previewChart'), options);
            chartInstance.render();
        } else {
            // For bar, line, area charts
            valueCols.forEach((col, index) => {
                const label = col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                series.push({
                    name: label,
                    data: previewData.map(row => parseFloat(row[col]) || 0)
                });
            });

            // If no value columns found, try to use all numeric columns
            if (series.length === 0) {
                columns.filter(c => c !== labelCol).forEach((col, index) => {
                    const firstVal = previewData[0][col];
                    if (typeof firstVal === 'number' || !isNaN(parseFloat(firstVal))) {
                        series.push({
                            name: col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()),
                            data: previewData.map(row => parseFloat(row[col]) || 0)
                        });
                    }
                });
            }

            const options = {
                chart: {
                    type: getApexChartType(type),
                    height: 350,
                    fontFamily: 'Inter, sans-serif',
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
                    }
                },
                series: series,
                colors: chartColors,
                xaxis: {
                    categories: labels,
                    labels: {
                        rotate: -45,
                        rotateAlways: labels.length > 10,
                        style: { fontSize: '11px' },
                        trim: true,
                        maxHeight: 80
                    }
                },
                yaxis: {
                    labels: {
                        formatter: (val) => {
                            if (val >= 1000000) return (val / 1000000).toFixed(1) + 'M';
                            if (val >= 1000) return (val / 1000).toFixed(1) + 'K';
                            return val.toFixed(0);
                        }
                    }
                },
                dataLabels: {
                    enabled: type === 'bar' && previewData.length <= 15
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    y: {
                        formatter: (val) => Number(val).toLocaleString()
                    }
                },
                grid: {
                    borderColor: '#e2e8f0',
                    strokeDashArray: 4
                },
                stroke: {
                    curve: 'smooth',
                    width: type === 'line' ? 3 : type === 'area' ? 2 : 0
                },
                fill: {
                    type: type === 'area' ? 'gradient' : 'solid',
                    gradient: {
                        opacityFrom: 0.5,
                        opacityTo: 0.1
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        columnWidth: '60%',
                        distributed: series.length === 1 && previewData.length <= 10
                    }
                }
            };

            chartInstance = new ApexCharts(document.getElementById('previewChart'), options);
            chartInstance.render();
        }
    }

    function getApexChartType(type) {
        const typeMap = {
            'bar': 'bar',
            'line': 'line',
            'area': 'area',
            'pie': 'pie',
            'donut': 'donut'
        };
        return typeMap[type] || 'bar';
    }

    // Summary cards
    function renderSummaryCards() {
        const container = document.getElementById('summaryCards');

        if (!previewData || previewData.length === 0 || reportConfig.metrics.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'flex';

        const columns = Object.keys(previewData[0]);
        const metricCols = reportConfig.metrics.map(m => m.alias || `${m.column.replace('.', '_')}_${m.aggregate}`);

        let html = '';

        // Calculate totals for each metric
        metricCols.forEach(metricAlias => {
            const col = columns.find(c => c.includes(metricAlias.replace('.', '_')));
            if (col) {
                const total = previewData.reduce((sum, row) => sum + (parseFloat(row[col]) || 0), 0);
                const metric = reportConfig.metrics.find(m => (m.alias || `${m.column.replace('.', '_')}_${m.aggregate}`) === metricAlias);
                const label = metric ? metric.label : col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

                html += `
                    <div class="summary-card">
                        <div class="card-label">${label}</div>
                        <div class="card-value">${formatNumber(total)}</div>
                    </div>
                `;
            }
        });

        // Also show record count
        html += `
            <div class="summary-card">
                <div class="card-label">Records</div>
                <div class="card-value">${totalRecords.toLocaleString()}</div>
            </div>
        `;

        container.innerHTML = html;
    }

    function formatNumber(num) {
        if (num >= 1000000000) return (num / 1000000000).toFixed(2) + 'B';
        if (num >= 1000000) return (num / 1000000).toFixed(2) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(2) + 'K';
        return Number(num).toLocaleString(undefined, { maximumFractionDigits: 2 });
    }
</script>
@endsection
