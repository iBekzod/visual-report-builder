@extends('visual-report-builder::layouts.app')

@section('title', 'Dashboard - Visual Report Builder')

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
    <div style="display: grid; grid-template-columns: 320px 1fr; gap: 1.5rem;">
        <!-- Left Sidebar: Templates -->
        <div class="card" style="height: fit-content; max-height: calc(100vh - 380px); display: flex; flex-direction: column;">
            <div class="card-header flex justify-between items-center">
                <h3 class="card-title">Templates</h3>
                <span class="badge badge-secondary" id="templateCount">0</span>
            </div>
            <div style="padding: 1rem; border-bottom: 1px solid var(--border);">
                <div style="position: relative;">
                    <svg style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 1rem; height: 1rem; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" id="templateSearch" placeholder="Search templates..."
                        class="form-input" style="padding-left: 2.25rem;"
                        oninput="filterTemplates(this.value)">
                </div>
            </div>
            <div id="templatesList" style="flex: 1; overflow-y: auto; padding: 0.5rem;">
                <div class="empty-state">
                    <div class="empty-state-icon">
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
                        <p class="text-muted mt-1" id="reportDescription">Choose a template from the sidebar to get started</p>
                    </div>
                    <div class="flex gap-2" id="reportActions" style="display: none;">
                        <select id="viewType" class="form-select" style="width: auto;" onchange="updateView()">
                            <option value="table">Table</option>
                            <option value="bar">Bar Chart</option>
                            <option value="line">Line Chart</option>
                            <option value="pie">Pie Chart</option>
                        </select>
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

            <!-- Filters Section (hidden by default) -->
            <div id="filtersSection" style="display: none; padding: 1rem 1.5rem; background: var(--light); border-bottom: 1px solid var(--border);">
                <div class="flex items-center gap-2 mb-4">
                    <svg style="width: 1rem; height: 1rem; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                    <span class="font-medium">Filters</span>
                </div>
                <div id="filterInputs" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;"></div>
            </div>

            <!-- Report Content -->
            <div id="reportContent" class="card-body" style="flex: 1; overflow: auto;">
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

            <!-- Summary Footer -->
            <div id="summarySection" style="display: none; padding: 1rem 1.5rem; border-top: 1px solid var(--border); background: var(--light);">
                <div class="flex items-center gap-2 mb-4">
                    <svg style="width: 1rem; height: 1rem; color: var(--text-muted);" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20V10"/>
                        <path d="M18 20V4"/>
                        <path d="M6 20v-4"/>
                    </svg>
                    <span class="font-medium">Summary</span>
                </div>
                <div id="summaryContent"></div>
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

    <!-- Save Modal -->
    <div id="saveModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3 class="modal-title">Save Report</h3>
                <button onclick="closeSaveModal()" class="modal-close">
                    <svg style="width: 1.25rem; height: 1.25rem;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 6L6 18M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Report Name</label>
                    <input type="text" id="reportName" class="form-input" placeholder="Enter a name for this report">
                </div>
                <div class="form-group">
                    <label class="form-label">Description (optional)</label>
                    <textarea id="reportDesc" class="form-textarea" rows="3" placeholder="Add a description..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="closeSaveModal()" class="btn btn-secondary">Cancel</button>
                <button onclick="confirmSave()" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Save Report
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        let currentTemplate = null;
        let currentData = null;
        let currentFilters = {};
        let chartInstance = null;
        let allTemplates = [];

        document.addEventListener('DOMContentLoaded', async () => {
            await loadDashboardData();
        });

        async function loadDashboardData() {
            try {
                const response = await window.apiClient.get('/api/visual-reports/templates');
                allTemplates = response.templates || [];

                // Update stats
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
                    <div class="empty-state">
                        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M9 17H7A5 5 0 017 7h2"/>
                            <path d="M15 7h2a5 5 0 010 10h-2"/>
                            <path d="M8 12h8"/>
                        </svg>
                        <h3 class="empty-state-title">No Templates</h3>
                        <p class="empty-state-text">Create your first template to get started</p>
                        <a href="{{ route('visual-reports.builder') }}" class="btn btn-primary btn-sm">Create Template</a>
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
                    <div style="margin-bottom: 0.5rem;">
                        <button onclick="toggleCategory(this)" class="btn btn-ghost" style="width: 100%; justify-content: space-between; padding: 0.5rem 0.75rem;">
                            <span class="font-medium">${category}</span>
                            <span class="badge badge-secondary">${temps.length}</span>
                        </button>
                        <div class="category-items" style="padding-left: 0.5rem;">
                `;

                temps.forEach(t => {
                    html += `
                        <div onclick="selectTemplate(${t.id})" class="template-item" data-id="${t.id}"
                            style="padding: 0.75rem; border-radius: var(--radius); cursor: pointer; margin: 0.25rem 0; transition: all 0.15s ease;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span style="font-size: 1.25rem;">${t.icon || '📊'}</span>
                                <div>
                                    <div class="font-medium" style="color: var(--dark);">${t.name}</div>
                                    <div class="text-muted" style="font-size: 0.75rem;">${t.description ? t.description.substring(0, 50) + '...' : 'No description'}</div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `</div></div>`;
            });

            container.innerHTML = html;

            // Add hover effects
            document.querySelectorAll('.template-item').forEach(item => {
                item.addEventListener('mouseenter', () => item.style.background = 'var(--light)');
                item.addEventListener('mouseleave', () => {
                    if (!item.classList.contains('active')) {
                        item.style.background = 'transparent';
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

                // Update UI
                document.getElementById('reportTitle').textContent = response.name;
                document.getElementById('reportDescription').textContent = response.description || 'No description';
                document.getElementById('reportActions').style.display = 'flex';
                document.getElementById('viewType').value = response.default_view?.type || 'table';

                // Highlight selected template
                document.querySelectorAll('.template-item').forEach(item => {
                    item.classList.remove('active');
                    item.style.background = 'transparent';
                });
                const selectedItem = document.querySelector(`.template-item[data-id="${id}"]`);
                if (selectedItem) {
                    selectedItem.classList.add('active');
                    selectedItem.style.background = '#fef2f2';
                }

                // Render filters if any
                renderFilters(response.filters);

                // Show placeholder
                document.getElementById('reportContent').innerHTML = `
                    <div class="empty-state">
                        <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <polygon points="5 3 19 12 5 21 5 3"/>
                        </svg>
                        <h3 class="empty-state-title">Ready to Execute</h3>
                        <p class="empty-state-text">Click the Execute button to generate the report</p>
                    </div>
                `;
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
                html += `<div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">${f.label}${f.is_required ? ' *' : ''}</label>`;

                if (f.type === 'select' && f.options) {
                    html += `<select class="form-select" data-column="${f.column}" onchange="updateFilter('${f.column}', this.value)">
                        <option value="">Select...</option>`;
                    f.options.forEach(o => {
                        html += `<option value="${o.value}">${o.label}</option>`;
                    });
                    html += `</select>`;
                } else if (f.type === 'date') {
                    html += `<input type="date" class="form-input" data-column="${f.column}" onchange="updateFilter('${f.column}', this.value)">`;
                } else {
                    html += `<input type="text" class="form-input" data-column="${f.column}" placeholder="Enter value..." onchange="updateFilter('${f.column}', this.value)">`;
                }

                html += `</div>`;
            });

            container.innerHTML = html;
        }

        function updateFilter(column, value) {
            currentFilters[column] = value || null;
        }

        async function executeReport() {
            if (!currentTemplate) {
                alert('Please select a template first');
                return;
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
                    { filters: currentFilters, view_type: document.getElementById('viewType').value }
                );

                if (response.success) {
                    currentData = response;
                    updateView();
                    document.getElementById('statRecent').textContent = 'Just now';
                } else {
                    container.innerHTML = `
                        <div class="alert alert-danger">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 8v4"/>
                                <circle cx="12" cy="16" r="1" fill="currentColor"/>
                            </svg>
                            <span>${response.message}</span>
                        </div>
                    `;
                }
            } catch (error) {
                container.innerHTML = `
                    <div class="alert alert-danger">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 8v4"/>
                            <circle cx="12" cy="16" r="1" fill="currentColor"/>
                        </svg>
                        <span>Error executing report: ${error.message}</span>
                    </div>
                `;
            }
        }

        function updateView() {
            if (!currentData) return;

            const viewType = document.getElementById('viewType').value;

            if (viewType === 'table') {
                renderTable(currentData.data.rows);
            } else {
                renderChart(viewType, currentData);
            }

            // Show summary
            document.getElementById('summarySection').style.display = 'block';
            renderSummary(currentData.data.summary, currentData.metadata);
        }

        function renderTable(rows) {
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

            let html = '<div class="table-container"><table class="table"><thead><tr>';
            Object.keys(rows[0]).forEach(col => {
                const label = col.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                html += `<th>${label}</th>`;
            });
            html += '</tr></thead><tbody>';

            rows.forEach(row => {
                html += '<tr>';
                Object.values(row).forEach(val => {
                    const displayVal = typeof val === 'boolean'
                        ? (val ? '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-secondary">No</span>')
                        : (val ?? '-');
                    html += `<td>${displayVal}</td>`;
                });
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            container.innerHTML = html;
        }

        function renderChart(type, data) {
            const container = document.getElementById('reportContent');
            const rows = data.data.rows;

            if (!rows || rows.length === 0) return;

            container.innerHTML = '<canvas id="reportChart" style="max-height: 400px;"></canvas>';
            const ctx = document.getElementById('reportChart').getContext('2d');

            const labels = rows.map(r => Object.values(r)[0]);
            const datasets = [];
            const colors = ['#f53003', '#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ec4899'];

            data.data.metrics.forEach((metric, i) => {
                const alias = metric.alias || `${metric.column}_${metric.aggregate}`;
                const values = rows.map(r => parseFloat(r[alias]) || 0);
                datasets.push({
                    label: metric.label,
                    data: values,
                    borderColor: colors[i % colors.length],
                    backgroundColor: type === 'pie'
                        ? colors.map(c => c + '99')
                        : colors[i % colors.length] + '20',
                    borderWidth: 2,
                    tension: 0.4,
                });
            });

            if (chartInstance) chartInstance.destroy();

            chartInstance = new Chart(ctx, {
                type: type === 'pie' ? 'pie' : type,
                data: { labels, datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: { position: 'bottom' }
                    },
                    scales: type !== 'pie' ? {
                        y: { beginAtZero: true }
                    } : {}
                }
            });
        }

        function renderSummary(summary, metadata) {
            const container = document.getElementById('summaryContent');

            let html = `<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem;">`;

            html += `
                <div style="text-align: center; padding: 0.75rem; background: white; border-radius: var(--radius); border: 1px solid var(--border);">
                    <div class="font-bold text-primary" style="font-size: 1.25rem;">${metadata?.record_count || 0}</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Records</div>
                </div>
                <div style="text-align: center; padding: 0.75rem; background: white; border-radius: var(--radius); border: 1px solid var(--border);">
                    <div class="font-bold" style="font-size: 1.25rem; color: var(--success);">${metadata?.execution_time_ms || 0}ms</div>
                    <div class="text-muted" style="font-size: 0.75rem;">Exec Time</div>
                </div>
            `;

            if (summary) {
                Object.entries(summary).forEach(([key, stats]) => {
                    if (stats.count > 0) {
                        html += `
                            <div style="text-align: center; padding: 0.75rem; background: white; border-radius: var(--radius); border: 1px solid var(--border);">
                                <div class="font-bold" style="font-size: 1.25rem;">${Number(stats.sum).toLocaleString()}</div>
                                <div class="text-muted" style="font-size: 0.75rem;">Total ${key.replace(/_/g, ' ')}</div>
                            </div>
                        `;
                    }
                });
            }

            html += '</div>';
            container.innerHTML = html;
        }

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

        function performExport() {
            const format = document.getElementById('exportFormat').value;
            alert(`Exporting as ${format.toUpperCase()}...`);
            closeExportModal();
        }

        function saveCurrent() {
            if (!currentData) {
                alert('Please execute a report first');
                return;
            }
            document.getElementById('saveModal').classList.add('active');
        }

        function closeSaveModal() {
            document.getElementById('saveModal').classList.remove('active');
        }

        async function confirmSave() {
            const name = document.getElementById('reportName').value;
            if (!name) {
                alert('Please enter a report name');
                return;
            }

            try {
                await window.apiClient.post(
                    `/api/visual-reports/templates/${currentTemplate.id}/save`,
                    {
                        name,
                        description: document.getElementById('reportDesc').value,
                        applied_filters: currentFilters,
                        view_type: document.getElementById('viewType').value,
                        view_config: {},
                        data: currentData.data.rows,
                    }
                );
                alert('Report saved successfully!');
                closeSaveModal();
            } catch (error) {
                alert('Error saving report');
            }
        }
    </script>
@endsection
