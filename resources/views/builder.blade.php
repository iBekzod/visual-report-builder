@extends('visual-report-builder::layouts.app')

@section('title', 'Report Builder')

@section('content')
<style>
    /* Builder specific styles */
    .builder-container {
        display: grid;
        grid-template-columns: 360px 1fr;
        gap: 1.5rem;
        height: calc(100vh - 140px);
    }

    .config-panel {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        overflow-y: auto;
        display: flex;
        flex-direction: column;
    }

    .config-panel-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
    }

    .config-panel-header h2 {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .config-panel-body {
        padding: 1.5rem;
        flex: 1;
        overflow-y: auto;
    }

    .config-section {
        margin-bottom: 1.5rem;
    }

    .config-section:last-child {
        margin-bottom: 0;
    }

    .config-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 0.5rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--text);
    }

    .config-label svg {
        width: 16px;
        height: 16px;
        color: var(--secondary);
    }

    .config-hint {
        font-size: 0.75rem;
        color: var(--secondary);
        margin-top: 0.375rem;
    }

    /* Right panel */
    .fields-panel {
        background: white;
        border-radius: var(--radius);
        border: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .fields-panel-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .fields-panel-header h2 {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--text);
        margin: 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .fields-panel-body {
        flex: 1;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0;
        overflow: hidden;
    }

    .fields-column {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border-right: 1px solid var(--border);
    }

    .fields-column:last-child {
        border-right: none;
    }

    .fields-column-header {
        padding: 0.875rem 1.25rem;
        background: #fafbfc;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .fields-column-header h3 {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text);
        margin: 0;
    }

    .fields-column-header .badge {
        font-size: 0.6875rem;
        padding: 0.125rem 0.5rem;
    }

    .fields-list {
        flex: 1;
        overflow-y: auto;
        padding: 0.75rem;
    }

    /* Draggable items */
    .field-item {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        padding: 0.625rem 0.875rem;
        background: #f8fafc;
        border: 1px solid var(--border);
        border-radius: calc(var(--radius) - 2px);
        margin-bottom: 0.5rem;
        cursor: grab;
        transition: all 0.15s ease;
        font-size: 0.8125rem;
        color: var(--text);
    }

    .field-item:hover {
        background: white;
        border-color: var(--primary);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .field-item:active {
        cursor: grabbing;
    }

    .field-item.dimension {
        border-left: 3px solid #3b82f6;
    }

    .field-item.metric {
        border-left: 3px solid var(--success);
    }

    .field-item.related {
        background: #fef3c7;
    }

    .field-item svg {
        width: 14px;
        height: 14px;
        color: var(--secondary);
        flex-shrink: 0;
    }

    .field-item .field-name {
        flex: 1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .field-item .field-table {
        font-size: 0.6875rem;
        color: #6366f1;
        background: #eef2ff;
        padding: 0.125rem 0.375rem;
        border-radius: 3px;
    }

    .field-item .field-aggregate {
        font-size: 0.6875rem;
        color: var(--secondary);
        background: #f1f5f9;
        padding: 0.125rem 0.375rem;
        border-radius: 3px;
    }

    /* Drop zones */
    .drop-zone {
        min-height: 60px;
        border: 2px dashed #e2e8f0;
        border-radius: calc(var(--radius) - 2px);
        padding: 0.5rem;
        background: #fafbfc;
        transition: all 0.2s ease;
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-content: flex-start;
    }

    .drop-zone.row-dims {
        border-color: #bfdbfe;
        background: #eff6ff;
    }

    .drop-zone.col-dims {
        border-color: #e2e8f0;
        background: #f8fafc;
    }

    .drop-zone.metrics-zone {
        border-color: #bbf7d0;
        background: #f0fdf4;
    }

    .drop-zone.filters-zone {
        border-color: #fde68a;
        background: #fffbeb;
    }

    .drop-zone.drag-over {
        border-color: var(--primary);
        background: #fef2f2;
        border-style: solid;
    }

    .drop-zone-empty {
        width: 100%;
        text-align: center;
        padding: 0.75rem;
        color: var(--secondary);
        font-size: 0.75rem;
    }

    .drop-zone-empty svg {
        width: 20px;
        height: 20px;
        margin: 0 auto 0.375rem;
        opacity: 0.5;
    }

    /* Tags in drop zones */
    .zone-tag {
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.375rem 0.625rem;
        border-radius: calc(var(--radius) - 2px);
        font-size: 0.75rem;
        font-weight: 500;
        background: white;
        border: 1px solid var(--border);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .zone-tag.dimension-tag {
        border-left: 3px solid #3b82f6;
    }

    .zone-tag.metric-tag {
        border-left: 3px solid var(--success);
    }

    .zone-tag.filter-tag {
        border-left: 3px solid #f59e0b;
    }

    .zone-tag .tag-info {
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
    }

    .zone-tag .tag-name {
        font-weight: 500;
    }

    .zone-tag .tag-detail {
        font-size: 0.625rem;
        color: var(--secondary);
    }

    .zone-tag select {
        font-size: 0.6875rem;
        padding: 0.125rem 0.25rem;
        border: 1px solid var(--border);
        border-radius: 3px;
        background: #f8fafc;
        cursor: pointer;
    }

    .zone-tag .remove-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        border: none;
        background: transparent;
        color: var(--secondary);
        cursor: pointer;
        border-radius: 3px;
        padding: 0;
        margin-left: 0.25rem;
        transition: all 0.15s ease;
    }

    .zone-tag .remove-btn:hover {
        background: #fee2e2;
        color: #ef4444;
    }

    /* Filter item */
    .filter-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem;
        background: white;
        border: 1px solid var(--border);
        border-left: 3px solid #f59e0b;
        border-radius: calc(var(--radius) - 2px);
        font-size: 0.75rem;
        width: 100%;
    }

    .filter-item select,
    .filter-item input {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border: 1px solid var(--border);
        border-radius: 3px;
        background: #f8fafc;
    }

    .filter-item select {
        min-width: 60px;
    }

    .filter-item input {
        flex: 1;
        min-width: 80px;
    }

    /* Related tables multi-select */
    .related-tables-list {
        max-height: 150px;
        overflow-y: auto;
        border: 1px solid var(--border);
        border-radius: var(--radius);
        background: white;
    }

    .related-table-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        border-bottom: 1px solid var(--border);
        font-size: 0.8125rem;
        cursor: pointer;
        transition: background 0.15s ease;
    }

    .related-table-item:last-child {
        border-bottom: none;
    }

    .related-table-item:hover {
        background: #f8fafc;
    }

    .related-table-item input[type="checkbox"] {
        accent-color: var(--primary);
    }

    .related-table-item .rel-type {
        font-size: 0.6875rem;
        color: var(--secondary);
        background: #f1f5f9;
        padding: 0.125rem 0.375rem;
        border-radius: 3px;
    }

    /* Preview section */
    .preview-section {
        border-top: 1px solid var(--border);
        background: #fafbfc;
    }

    .preview-header {
        padding: 0.875rem 1.25rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .preview-header h3 {
        font-size: 0.8125rem;
        font-weight: 600;
        color: var(--text);
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .preview-content {
        padding: 1rem;
        max-height: 250px;
        overflow-y: auto;
        font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', monospace;
        font-size: 0.75rem;
        background: #1e293b;
        color: #e2e8f0;
    }

    .preview-content pre {
        margin: 0;
        white-space: pre-wrap;
        word-break: break-word;
    }

    .preview-placeholder {
        padding: 2rem;
        text-align: center;
        color: var(--secondary);
        font-size: 0.8125rem;
    }

    .preview-placeholder svg {
        width: 32px;
        height: 32px;
        margin: 0 auto 0.75rem;
        opacity: 0.4;
    }

    /* Action buttons in config panel */
    .config-actions {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid var(--border);
        background: #f8fafc;
        display: flex;
        gap: 0.75rem;
    }

    .config-actions .btn {
        flex: 1;
        justify-content: center;
    }

    /* Loading states */
    .loading-text {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: var(--secondary);
        font-size: 0.8125rem;
        padding: 1rem;
    }

    .loading-text svg {
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Preview success/error states */
    .preview-success {
        color: var(--success);
        font-weight: 500;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .preview-error {
        color: #ef4444;
        padding: 1rem;
        background: #fef2f2;
        border-radius: var(--radius);
    }

    .preview-warning {
        color: #f59e0b;
        padding: 1rem;
        background: #fffbeb;
        border-radius: var(--radius);
    }

    /* Add filter button */
    .add-filter-btn {
        width: 100%;
        padding: 0.5rem;
        border: 2px dashed #fde68a;
        background: transparent;
        color: #d97706;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: calc(var(--radius) - 2px);
        cursor: pointer;
        transition: all 0.15s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
    }

    .add-filter-btn:hover {
        background: #fffbeb;
        border-color: #f59e0b;
    }

    .add-filter-btn svg {
        width: 14px;
        height: 14px;
    }
</style>

<div class="page-header">
    <div>
        <h1 class="page-title">Report Builder</h1>
        <p style="color: var(--secondary); font-size: 0.875rem; margin-top: 0.25rem;">
            Create custom reports by combining tables and configuring aggregations
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="{{ route('visual-reports.dashboard') }}" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Back to Dashboard
        </a>
    </div>
</div>

<div class="builder-container">
    <!-- Left Panel: Configuration -->
    <div class="config-panel">
        <div class="config-panel-header">
            <h2>Configuration</h2>
        </div>

        <div class="config-panel-body">
            <!-- Data Source Selection -->
            <div class="config-section">
                <label class="config-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"/><path d="M3 5V19A9 3 0 0 0 21 19V5"/><path d="M3 12A9 3 0 0 0 21 12"/></svg>
                    Primary Table
                </label>
                <select id="modelSelect" class="form-select">
                    <option value="">Select a model...</option>
                </select>
            </div>

            <!-- Related Tables (Multi-select) -->
            <div id="relationshipsContainer" class="config-section" style="display: none;">
                <label class="config-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    Join Related Tables
                </label>
                <div id="relationshipsList" class="related-tables-list">
                    <!-- Relationships will be loaded here -->
                </div>
                <p class="config-hint">Select tables to join with the primary table</p>
            </div>

            <!-- Row Dimensions -->
            <div class="config-section">
                <label class="config-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><path d="M3 9h18"/><path d="M3 15h18"/></svg>
                    Row Dimensions
                </label>
                <div id="rowDimensions" class="drop-zone row-dims">
                    <div class="drop-zone-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        <div>Drag dimensions here</div>
                    </div>
                </div>
                <p class="config-hint">Fields to group rows by</p>
            </div>

            <!-- Column Dimensions -->
            <div class="config-section">
                <label class="config-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><path d="M9 3v18"/><path d="M15 3v18"/></svg>
                    Column Dimensions
                </label>
                <div id="columnDimensions" class="drop-zone col-dims">
                    <div class="drop-zone-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        <div>Drag dimensions here</div>
                    </div>
                </div>
                <p class="config-hint">Fields to group columns by (pivot)</p>
            </div>

            <!-- Metrics -->
            <div class="config-section">
                <label class="config-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                    Metrics
                </label>
                <div id="metrics" class="drop-zone metrics-zone">
                    <div class="drop-zone-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        <div>Drag metrics here</div>
                    </div>
                </div>
                <p class="config-hint">Select aggregation type for each metric</p>
            </div>

            <!-- Filters -->
            <div class="config-section">
                <label class="config-label">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filters
                </label>
                <div id="filters" class="drop-zone filters-zone">
                    <div class="drop-zone-empty">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                        <div>Drag fields to filter by</div>
                    </div>
                </div>
                <p class="config-hint">Add WHERE conditions to filter data</p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="config-actions">
            <button onclick="previewReport()" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                Preview
            </button>
            <button onclick="saveTemplate()" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Template
            </button>
        </div>
    </div>

    <!-- Right Panel: Available Fields & Preview -->
    <div class="fields-panel">
        <div class="fields-panel-header">
            <h2>Available Fields</h2>
            <span id="fieldCount" class="badge badge-secondary" style="display: none;">0 fields</span>
        </div>

        <div class="fields-panel-body">
            <!-- Dimensions Column -->
            <div class="fields-column">
                <div class="fields-column-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    <h3>Dimensions</h3>
                    <span id="dimCount" class="badge badge-secondary">0</span>
                </div>
                <div id="dimensionsList" class="fields-list">
                    <div class="loading-text">
                        <span>Select a model to load fields</span>
                    </div>
                </div>
            </div>

            <!-- Metrics Column -->
            <div class="fields-column">
                <div class="fields-column-header">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="m19 9-5 5-4-4-3 3"/></svg>
                    <h3>Metrics</h3>
                    <span id="metricCount" class="badge badge-secondary">0</span>
                </div>
                <div id="metricsList" class="fields-list">
                    <div class="loading-text">
                        <span>Select a model to load fields</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div class="preview-section">
            <div class="preview-header">
                <h3>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    Data Preview
                </h3>
                <span id="previewStatus" class="badge badge-secondary" style="display: none;"></span>
            </div>
            <div id="previewContainer">
                <div class="preview-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                    <p>Configure your report and click "Preview" to see the data</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Save Template Modal -->
<div id="saveModal" class="modal-backdrop">
    <div class="modal" style="max-width: 480px;">
        <div class="modal-header">
            <h2>Save Template</h2>
            <button onclick="closeSaveModal()" class="modal-close">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="form-label">Template Name <span style="color: #ef4444;">*</span></label>
                <input type="text" id="templateName" class="form-input" placeholder="e.g., Sales Dashboard">
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea id="templateDesc" class="form-input" placeholder="What does this report show?" rows="3" style="resize: vertical;"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label class="form-label">Category <span style="color: #ef4444;">*</span></label>
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
            <button onclick="confirmSave()" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Template
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- SortableJS for Drag-and-Drop -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
    let currentModel = null;
    let selectedRelationships = []; // Array of selected relationships
    let allRelationships = []; // All available relationships
    let availableDimensions = [];
    let availableMetrics = [];
    let allDimensions = []; // Combined dimensions from all tables
    let allMetrics = []; // Combined metrics from all tables

    // Aggregation types
    const AGGREGATION_TYPES = [
        { value: 'sum', label: 'SUM' },
        { value: 'count', label: 'COUNT' },
        { value: 'avg', label: 'AVG' },
        { value: 'min', label: 'MIN' },
        { value: 'max', label: 'MAX' },
    ];

    // Filter operators
    const FILTER_OPERATORS = [
        { value: '=', label: '=' },
        { value: '!=', label: '!=' },
        { value: '>', label: '>' },
        { value: '>=', label: '>=' },
        { value: '<', label: '<' },
        { value: '<=', label: '<=' },
        { value: 'like', label: 'LIKE' },
        { value: 'in', label: 'IN' },
        { value: 'not_in', label: 'NOT IN' },
        { value: 'is_null', label: 'IS NULL' },
        { value: 'is_not_null', label: 'IS NOT NULL' },
    ];

    let reportConfig = {
        model: null,
        relationships: [], // Array of relationship names to join
        row_dimensions: [],
        column_dimensions: [],
        metrics: [],
        filters: [],
    };

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', loadModels);

    // Load all available models
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
            showNotification('Failed to load models. Please refresh the page.', 'error');
        }
    }

    // Show notification
    function showNotification(message, type = 'info') {
        alert(message);
    }

    // When model is selected
    document.getElementById('modelSelect').addEventListener('change', async (e) => {
        currentModel = e.target.value;
        reportConfig.model = currentModel;
        selectedRelationships = [];
        reportConfig.relationships = [];

        // Reset configuration
        reportConfig.row_dimensions = [];
        reportConfig.column_dimensions = [];
        reportConfig.metrics = [];
        reportConfig.filters = [];
        updateDimensionsDisplay();
        updateMetricsDisplay();
        updateFiltersDisplay();

        // Clear preview
        document.getElementById('previewContainer').innerHTML = `
            <div class="preview-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="M3 9h18"/><path d="M9 21V9"/></svg>
                <p>Configure your report and click "Preview" to see the data</p>
            </div>
        `;

        if (!currentModel) {
            document.getElementById('dimensionsList').innerHTML = '<div class="loading-text"><span>Select a model to load fields</span></div>';
            document.getElementById('metricsList').innerHTML = '<div class="loading-text"><span>Select a model to load fields</span></div>';
            document.getElementById('relationshipsContainer').style.display = 'none';
            updateFieldCounts(0, 0);
            return;
        }

        // Show loading state
        document.getElementById('dimensionsList').innerHTML = `
            <div class="loading-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                <span>Loading dimensions...</span>
            </div>
        `;
        document.getElementById('metricsList').innerHTML = `
            <div class="loading-text">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                <span>Loading metrics...</span>
            </div>
        `;

        try {
            // Load dimensions and metrics (URL encode the model class name)
            const encodedModel = encodeURIComponent(currentModel);
            const [dimensions, metrics] = await Promise.all([
                window.apiClient.get(`/api/visual-reports/models/${encodedModel}/dimensions`),
                window.apiClient.get(`/api/visual-reports/models/${encodedModel}/metrics`)
            ]);

            // Store base model fields with table info
            availableDimensions = (dimensions || []).map(d => ({
                ...d,
                table: 'primary',
                tableLabel: getModelName(currentModel)
            }));
            availableMetrics = (metrics || []).map(m => ({
                ...m,
                table: 'primary',
                tableLabel: getModelName(currentModel)
            }));

            // Initialize all fields with base model
            allDimensions = [...availableDimensions];
            allMetrics = [...availableMetrics];

            displayDimensions(allDimensions);
            displayMetrics(allMetrics);
            updateFieldCounts(allDimensions.length, allMetrics.length);

            // Load relationships
            try {
                const relationshipsResponse = await window.apiClient.get(`/api/visual-reports/models/${encodedModel}/relationships`);
                allRelationships = relationshipsResponse.relationships || relationshipsResponse || [];
                loadRelationships(allRelationships);
            } catch (err) {
                console.log('No relationships found for this model');
                document.getElementById('relationshipsContainer').style.display = 'none';
            }
        } catch (error) {
            console.error('Error loading metadata:', error);
            showNotification('Failed to load dimensions and metrics for this model', 'error');
            document.getElementById('dimensionsList').innerHTML = '<div class="loading-text" style="color: #ef4444;"><span>Error loading dimensions</span></div>';
            document.getElementById('metricsList').innerHTML = '<div class="loading-text" style="color: #ef4444;"><span>Error loading metrics</span></div>';
        }
    });

    // Get model name from class
    function getModelName(modelClass) {
        const parts = modelClass.split('\\');
        return parts[parts.length - 1];
    }

    // Update field counts
    function updateFieldCounts(dimCount, metricCount) {
        document.getElementById('dimCount').textContent = dimCount;
        document.getElementById('metricCount').textContent = metricCount;

        const totalCount = dimCount + metricCount;
        const fieldCountBadge = document.getElementById('fieldCount');
        if (totalCount > 0) {
            fieldCountBadge.textContent = `${totalCount} fields`;
            fieldCountBadge.style.display = 'inline-flex';
        } else {
            fieldCountBadge.style.display = 'none';
        }
    }

    // Display dimensions as draggable items
    function displayDimensions(dimensions) {
        const container = document.getElementById('dimensionsList');
        container.innerHTML = '';

        if (!dimensions || dimensions.length === 0) {
            container.innerHTML = '<div class="loading-text"><span>No dimensions available</span></div>';
            return;
        }

        dimensions.forEach(dim => {
            const item = document.createElement('div');
            item.className = `field-item dimension ${dim.table !== 'primary' ? 'related' : ''}`;
            item.draggable = true;
            item.dataset.column = dim.column;
            item.dataset.label = dim.label || dim.column;
            item.dataset.type = 'dimension';
            item.dataset.table = dim.table || 'primary';
            item.dataset.tableLabel = dim.tableLabel || '';
            item.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                <span class="field-name">${dim.label || dim.column}</span>
                ${dim.table !== 'primary' ? `<span class="field-table">${dim.tableLabel}</span>` : ''}
            `;
            item.addEventListener('dragstart', handleDragStart);
            container.appendChild(item);
        });
    }

    // Display metrics as draggable items
    function displayMetrics(metrics) {
        const container = document.getElementById('metricsList');
        container.innerHTML = '';

        if (!metrics || metrics.length === 0) {
            container.innerHTML = '<div class="loading-text"><span>No metrics available</span></div>';
            return;
        }

        metrics.forEach(metric => {
            const item = document.createElement('div');
            item.className = `field-item metric ${metric.table !== 'primary' ? 'related' : ''}`;
            item.draggable = true;
            item.dataset.column = metric.column;
            item.dataset.label = metric.label || metric.column;
            item.dataset.aggregate = metric.default_aggregate || 'sum';
            item.dataset.type = 'metric';
            item.dataset.table = metric.table || 'primary';
            item.dataset.tableLabel = metric.tableLabel || '';
            item.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="12" r="1"/><circle cx="9" cy="5" r="1"/><circle cx="9" cy="19" r="1"/><circle cx="15" cy="12" r="1"/><circle cx="15" cy="5" r="1"/><circle cx="15" cy="19" r="1"/></svg>
                <span class="field-name">${metric.label || metric.column}</span>
                ${metric.table !== 'primary' ? `<span class="field-table">${metric.tableLabel}</span>` : ''}
            `;
            item.addEventListener('dragstart', handleDragStart);
            container.appendChild(item);
        });
    }

    // Load relationships as multi-select checkboxes
    function loadRelationships(relationships) {
        const container = document.getElementById('relationshipsContainer');
        const list = document.getElementById('relationshipsList');

        if (!relationships || relationships.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        list.innerHTML = '';

        relationships.forEach(rel => {
            const item = document.createElement('label');
            item.className = 'related-table-item';
            item.innerHTML = `
                <input type="checkbox" value="${rel.name}" data-relationship='${JSON.stringify(rel)}'>
                <span>${rel.label}</span>
                <span class="rel-type">${rel.type}</span>
            `;

            const checkbox = item.querySelector('input[type="checkbox"]');
            checkbox.addEventListener('change', async (e) => {
                if (e.target.checked) {
                    await addRelationship(rel);
                } else {
                    removeRelationship(rel);
                }
            });

            list.appendChild(item);
        });
    }

    // Add relationship and load its fields
    async function addRelationship(rel) {
        selectedRelationships.push(rel);
        reportConfig.relationships.push(rel.name);

        try {
            const encodedRelatedModel = encodeURIComponent(rel.related_model);
            const [dimensions, metrics] = await Promise.all([
                window.apiClient.get(`/api/visual-reports/models/${encodedRelatedModel}/dimensions`),
                window.apiClient.get(`/api/visual-reports/models/${encodedRelatedModel}/metrics`)
            ]);

            // Add relationship prefix to related model's fields
            const prefixedDimensions = (dimensions || []).map(d => ({
                ...d,
                column: `${rel.name}.${d.column}`,
                label: d.label || d.column,
                table: rel.name,
                tableLabel: rel.label
            }));

            const prefixedMetrics = (metrics || []).map(m => ({
                ...m,
                column: `${rel.name}.${m.column}`,
                label: m.label || m.column,
                table: rel.name,
                tableLabel: rel.label
            }));

            // Merge with existing fields
            allDimensions = [...availableDimensions, ...allDimensions.filter(d => d.table !== rel.name), ...prefixedDimensions];
            allMetrics = [...availableMetrics, ...allMetrics.filter(m => m.table !== rel.name), ...prefixedMetrics];

            displayDimensions(allDimensions);
            displayMetrics(allMetrics);
            updateFieldCounts(allDimensions.length, allMetrics.length);
        } catch (error) {
            console.error('Error loading related model fields:', error);
            showNotification('Failed to load related model fields', 'error');
        }
    }

    // Remove relationship and its fields
    function removeRelationship(rel) {
        selectedRelationships = selectedRelationships.filter(r => r.name !== rel.name);
        reportConfig.relationships = reportConfig.relationships.filter(r => r !== rel.name);

        // Remove fields from this relationship
        allDimensions = allDimensions.filter(d => d.table !== rel.name);
        allMetrics = allMetrics.filter(m => m.table !== rel.name);

        // Remove from config if any were selected
        reportConfig.row_dimensions = reportConfig.row_dimensions.filter(d => !d.column.startsWith(rel.name + '.'));
        reportConfig.column_dimensions = reportConfig.column_dimensions.filter(d => !d.column.startsWith(rel.name + '.'));
        reportConfig.metrics = reportConfig.metrics.filter(m => !m.column.startsWith(rel.name + '.'));
        reportConfig.filters = reportConfig.filters.filter(f => !f.column.startsWith(rel.name + '.'));

        displayDimensions(allDimensions);
        displayMetrics(allMetrics);
        updateFieldCounts(allDimensions.length, allMetrics.length);
        updateDimensionsDisplay();
        updateMetricsDisplay();
        updateFiltersDisplay();
    }

    // Drag and drop handlers
    function handleDragStart(e) {
        e.dataTransfer.effectAllowed = 'copy';
        e.dataTransfer.setData('column', e.target.dataset.column);
        e.dataTransfer.setData('label', e.target.dataset.label);
        e.dataTransfer.setData('type', e.target.dataset.type);
        e.dataTransfer.setData('aggregate', e.target.dataset.aggregate || 'sum');
        e.dataTransfer.setData('table', e.target.dataset.table || 'primary');
        e.dataTransfer.setData('tableLabel', e.target.dataset.tableLabel || '');
        e.target.style.opacity = '0.5';

        // Reset opacity after drag ends
        e.target.addEventListener('dragend', () => {
            e.target.style.opacity = '1';
        }, { once: true });
    }

    // Setup drop zones
    setupDropZone('rowDimensions', 'dimension');
    setupDropZone('columnDimensions', 'dimension');
    setupDropZone('metrics', 'metric');
    setupDropZone('filters', 'filter');

    function setupDropZone(elementId, type) {
        const zone = document.getElementById(elementId);

        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'copy';
            zone.classList.add('drag-over');
        });

        zone.addEventListener('dragleave', (e) => {
            if (!zone.contains(e.relatedTarget)) {
                zone.classList.remove('drag-over');
            }
        });

        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            zone.classList.remove('drag-over');

            const dragType = e.dataTransfer.getData('type');
            const column = e.dataTransfer.getData('column');
            const label = e.dataTransfer.getData('label');
            const aggregate = e.dataTransfer.getData('aggregate');
            const table = e.dataTransfer.getData('table');
            const tableLabel = e.dataTransfer.getData('tableLabel');

            if (type === 'dimension' && dragType === 'dimension') {
                if (elementId === 'rowDimensions') {
                    const isDuplicate = reportConfig.row_dimensions.some(d => d.column === column);
                    if (!isDuplicate) {
                        reportConfig.row_dimensions.push({column, label, table, tableLabel});
                        updateDimensionsDisplay();
                    } else {
                        showNotification(`"${label}" is already in Row Dimensions`, 'warning');
                    }
                } else if (elementId === 'columnDimensions') {
                    const isDuplicate = reportConfig.column_dimensions.some(d => d.column === column);
                    if (!isDuplicate) {
                        reportConfig.column_dimensions.push({column, label, table, tableLabel});
                        updateDimensionsDisplay();
                    } else {
                        showNotification(`"${label}" is already in Column Dimensions`, 'warning');
                    }
                }
            } else if (type === 'metric' && dragType === 'metric') {
                // Add with default aggregate, user can change it
                reportConfig.metrics.push({
                    column: column,
                    label: label,
                    aggregate: aggregate,
                    alias: `${column.replace('.', '_')}_${aggregate}`,
                    table: table,
                    tableLabel: tableLabel
                });
                updateMetricsDisplay();
            } else if (type === 'filter') {
                // Both dimensions and metrics can be used as filters
                const isDuplicate = reportConfig.filters.some(f => f.column === column);
                if (!isDuplicate) {
                    reportConfig.filters.push({
                        column: column,
                        label: label,
                        operator: '=',
                        value: '',
                        table: table,
                        tableLabel: tableLabel
                    });
                    updateFiltersDisplay();
                } else {
                    showNotification(`"${label}" is already added as a filter`, 'warning');
                }
            }
        });
    }

    // Update display of selected dimensions
    function updateDimensionsDisplay() {
        const rowDiv = document.getElementById('rowDimensions');
        const colDiv = document.getElementById('columnDimensions');

        rowDiv.innerHTML = '';
        colDiv.innerHTML = '';

        if (reportConfig.row_dimensions.length === 0) {
            rowDiv.innerHTML = `
                <div class="drop-zone-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    <div>Drag dimensions here</div>
                </div>
            `;
        } else {
            reportConfig.row_dimensions.forEach((dim, i) => {
                const tag = createDimensionTag(dim, i, () => {
                    reportConfig.row_dimensions.splice(i, 1);
                    updateDimensionsDisplay();
                });
                rowDiv.appendChild(tag);
            });
        }

        if (reportConfig.column_dimensions.length === 0) {
            colDiv.innerHTML = `
                <div class="drop-zone-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    <div>Drag dimensions here</div>
                </div>
            `;
        } else {
            reportConfig.column_dimensions.forEach((dim, i) => {
                const tag = createDimensionTag(dim, i, () => {
                    reportConfig.column_dimensions.splice(i, 1);
                    updateDimensionsDisplay();
                });
                colDiv.appendChild(tag);
            });
        }
    }

    // Create dimension tag
    function createDimensionTag(dim, index, onRemove) {
        const tag = document.createElement('span');
        tag.className = 'zone-tag dimension-tag';
        tag.innerHTML = `
            <div class="tag-info">
                <span class="tag-name">${dim.label || dim.column}</span>
                ${dim.table !== 'primary' ? `<span class="tag-detail">${dim.tableLabel}</span>` : ''}
            </div>
            <button type="button" class="remove-btn" title="Remove">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
            </button>
        `;
        tag.querySelector('.remove-btn').onclick = (e) => {
            e.preventDefault();
            onRemove();
        };
        return tag;
    }

    // Update display of selected metrics with aggregate selector
    function updateMetricsDisplay() {
        const div = document.getElementById('metrics');
        div.innerHTML = '';

        if (reportConfig.metrics.length === 0) {
            div.innerHTML = `
                <div class="drop-zone-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    <div>Drag metrics here</div>
                </div>
            `;
        } else {
            reportConfig.metrics.forEach((metric, i) => {
                const tag = document.createElement('span');
                tag.className = 'zone-tag metric-tag';
                tag.innerHTML = `
                    <div class="tag-info">
                        <span class="tag-name">${metric.label}</span>
                        ${metric.table !== 'primary' ? `<span class="tag-detail">${metric.tableLabel}</span>` : ''}
                    </div>
                    <select class="aggregate-select" data-index="${i}">
                        ${AGGREGATION_TYPES.map(agg =>
                            `<option value="${agg.value}" ${metric.aggregate === agg.value ? 'selected' : ''}>${agg.label}</option>`
                        ).join('')}
                    </select>
                    <button type="button" class="remove-btn" title="Remove">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                `;

                // Handle aggregate change
                tag.querySelector('.aggregate-select').addEventListener('change', (e) => {
                    reportConfig.metrics[i].aggregate = e.target.value;
                    reportConfig.metrics[i].alias = `${reportConfig.metrics[i].column.replace('.', '_')}_${e.target.value}`;
                });

                tag.querySelector('.remove-btn').onclick = (e) => {
                    e.preventDefault();
                    reportConfig.metrics.splice(i, 1);
                    updateMetricsDisplay();
                };

                div.appendChild(tag);
            });
        }
    }

    // Update display of filters
    function updateFiltersDisplay() {
        const div = document.getElementById('filters');
        div.innerHTML = '';

        if (reportConfig.filters.length === 0) {
            div.innerHTML = `
                <div class="drop-zone-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    <div>Drag fields to filter by</div>
                </div>
            `;
        } else {
            reportConfig.filters.forEach((filter, i) => {
                const item = document.createElement('div');
                item.className = 'filter-item';

                const needsValue = !['is_null', 'is_not_null'].includes(filter.operator);

                item.innerHTML = `
                    <span style="font-weight: 500; min-width: 80px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${filter.label}</span>
                    <select class="operator-select" data-index="${i}">
                        ${FILTER_OPERATORS.map(op =>
                            `<option value="${op.value}" ${filter.operator === op.value ? 'selected' : ''}>${op.label}</option>`
                        ).join('')}
                    </select>
                    <input type="text" class="value-input" data-index="${i}" placeholder="Value..." value="${filter.value || ''}" ${needsValue ? '' : 'style="display:none;"'}>
                    <button type="button" class="remove-btn" title="Remove">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    </button>
                `;

                // Handle operator change
                item.querySelector('.operator-select').addEventListener('change', (e) => {
                    reportConfig.filters[i].operator = e.target.value;
                    const needsVal = !['is_null', 'is_not_null'].includes(e.target.value);
                    item.querySelector('.value-input').style.display = needsVal ? '' : 'none';
                });

                // Handle value change
                item.querySelector('.value-input').addEventListener('input', (e) => {
                    reportConfig.filters[i].value = e.target.value;
                });

                // Handle remove
                item.querySelector('.remove-btn').onclick = (e) => {
                    e.preventDefault();
                    reportConfig.filters.splice(i, 1);
                    updateFiltersDisplay();
                };

                div.appendChild(item);
            });
        }
    }

    // Preview the report
    async function previewReport() {
        if (!reportConfig.model) {
            showNotification('Please select a data source', 'warning');
            return;
        }

        if (reportConfig.metrics.length === 0) {
            showNotification('Please add at least one metric to preview', 'warning');
            return;
        }

        try {
            const container = document.getElementById('previewContainer');
            const statusBadge = document.getElementById('previewStatus');

            container.innerHTML = `
                <div class="preview-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                    <p>Loading preview...</p>
                </div>
            `;
            statusBadge.style.display = 'none';

            const response = await window.apiClient.post('/api/visual-reports/preview', {
                model: reportConfig.model,
                relationships: reportConfig.relationships,
                row_dimensions: reportConfig.row_dimensions.map(d => d.column),
                column_dimensions: reportConfig.column_dimensions.map(d => d.column),
                metrics: reportConfig.metrics,
                filters: reportConfig.filters.filter(f => f.value || ['is_null', 'is_not_null'].includes(f.operator))
            });

            if (response.success) {
                const data = response.data || [];
                if (Array.isArray(data) && data.length > 0) {
                    statusBadge.textContent = `${data.length} rows`;
                    statusBadge.className = 'badge badge-success';
                    statusBadge.style.display = 'inline-flex';

                    container.innerHTML = `
                        <div class="preview-content">
                            <pre>${JSON.stringify(data.slice(0, 10), null, 2)}</pre>
                        </div>
                    `;
                    if (data.length > 10) {
                        container.innerHTML += `
                            <div style="padding: 0.75rem 1rem; background: #f8fafc; border-top: 1px solid var(--border); font-size: 0.75rem; color: var(--secondary);">
                                Showing first 10 rows of ${data.length} total
                            </div>
                        `;
                    }
                } else {
                    statusBadge.textContent = 'No data';
                    statusBadge.className = 'badge badge-warning';
                    statusBadge.style.display = 'inline-flex';
                    container.innerHTML = '<div class="preview-warning">No data found for this configuration</div>';
                }
            } else {
                statusBadge.textContent = 'Error';
                statusBadge.className = 'badge badge-error';
                statusBadge.style.display = 'inline-flex';
                container.innerHTML = `<div class="preview-error">Error: ${response.message || 'Unknown error'}</div>`;
            }
        } catch (error) {
            console.error('Error previewing:', error);
            document.getElementById('previewStatus').textContent = 'Error';
            document.getElementById('previewStatus').className = 'badge badge-error';
            document.getElementById('previewStatus').style.display = 'inline-flex';
            document.getElementById('previewContainer').innerHTML = `<div class="preview-error">Error previewing report: ${error.message || 'Unknown error'}</div>`;
        }
    }

    // Open save template modal
    function saveTemplate() {
        if (!reportConfig.model) {
            showNotification('Please select a data source', 'warning');
            return;
        }

        if (reportConfig.metrics.length === 0) {
            showNotification('Please add at least one metric', 'warning');
            return;
        }

        document.getElementById('saveModal').classList.add('active');
        document.getElementById('templateName').focus();
    }

    // Close modal
    function closeSaveModal() {
        document.getElementById('saveModal').classList.remove('active');
    }

    // Confirm save
    async function confirmSave() {
        const name = document.getElementById('templateName').value.trim();
        const description = document.getElementById('templateDesc').value.trim();
        const category = document.getElementById('templateCategory').value;
        const icon = document.getElementById('templateIcon').value.trim() || '📊';

        // Validation
        if (!name) {
            showNotification('Please enter a template name', 'warning');
            document.getElementById('templateName').focus();
            return;
        }

        if (name.length < 3) {
            showNotification('Template name must be at least 3 characters', 'warning');
            document.getElementById('templateName').focus();
            return;
        }

        if (!category) {
            showNotification('Please select a category', 'warning');
            document.getElementById('templateCategory').focus();
            return;
        }

        // Find the save button
        const saveBtn = document.querySelector('#saveModal .btn-primary');
        const originalContent = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="animation: spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
            Saving...
        `;

        try {
            const response = await window.apiClient.post('/api/visual-reports/builder/save-template', {
                name: name,
                description: description,
                category: category,
                icon: icon,
                model: reportConfig.model,
                relationships: reportConfig.relationships,
                row_dimensions: reportConfig.row_dimensions,
                column_dimensions: reportConfig.column_dimensions,
                metrics: reportConfig.metrics,
                filters: reportConfig.filters.filter(f => f.value || ['is_null', 'is_not_null'].includes(f.operator)),
                default_view: { type: 'table' }
            });

            if (response.success) {
                closeSaveModal();
                showNotification(`Template "${name}" created successfully!`, 'success');
                setTimeout(() => {
                    window.location.href = '{{ route("visual-reports.dashboard") }}';
                }, 1000);
            } else {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalContent;
                // Handle validation errors
                if (response.errors) {
                    const errorMessages = Object.values(response.errors).flat().join(', ');
                    showNotification('Validation error: ' + errorMessages, 'error');
                } else {
                    showNotification('Error: ' + (response.message || 'Unknown error'), 'error');
                }
            }
        } catch (error) {
            console.error('Error saving template:', error);
            saveBtn.disabled = false;
            saveBtn.innerHTML = originalContent;
            showNotification('Error saving template: ' + (error.message || 'Unknown error'), 'error');
        }
    }

    // Close modal when clicking outside
    document.getElementById('saveModal').addEventListener('click', (e) => {
        if (e.target.id === 'saveModal') {
            closeSaveModal();
        }
    });

    // Handle Escape key to close modal
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && document.getElementById('saveModal').classList.contains('active')) {
            closeSaveModal();
        }
    });

    // Clear form when opening modal
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.attributeName === 'class') {
                const modal = document.getElementById('saveModal');
                if (modal.classList.contains('active')) {
                    document.getElementById('templateName').value = '';
                    document.getElementById('templateDesc').value = '';
                    document.getElementById('templateCategory').value = '';
                    document.getElementById('templateIcon').value = '';
                }
            }
        });
    });

    observer.observe(document.getElementById('saveModal'), { attributes: true });
</script>
@endsection
