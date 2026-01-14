@extends('visual-report-builder::layouts.app')

@section('title', 'Report Dashboard')

@section('content')
    <div style="display: grid; grid-template-columns: 280px 1fr 320px; gap: 1rem; height: calc(100vh - 100px);">
        <!-- LEFT SIDEBAR: Templates -->
        <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow-y: auto; display: flex; flex-direction: column;">
            <div style="padding: 1.5rem 1rem; border-bottom: 1px solid #ddd;">
                <h2 style="font-size: 1.1rem; margin: 0 0 1rem 0;">📊 Report Templates</h2>
                <input type="text" id="templateSearch" placeholder="Search templates..."
                    style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;">
            </div>

            <div id="categoriesList" style="padding: 0.5rem; flex: 1; overflow-y: auto;">
                <!-- Templates loaded here -->
            </div>

            <div style="padding: 1rem; border-top: 1px solid #ddd; display: flex; gap: 0.5rem;">
                <button onclick="toggleFavorites()" class="btn" style="flex: 1; padding: 0.5rem; font-size: 0.9rem;">⭐ Favorites</button>
                <button onclick="toggleFavorites(false)" class="btn btn-secondary" style="flex: 1; padding: 0.5rem; font-size: 0.9rem;">All</button>
            </div>
        </div>

        <!-- CENTER MAIN: Report Display -->
        <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: flex; flex-direction: column; overflow: hidden;">
            <!-- Top Bar: Template Info & Controls -->
            <div style="padding: 1.5rem; border-bottom: 1px solid #ddd; background: #f9f9f9;">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;">
                    <div style="flex: 1;">
                        <h1 id="templateName" style="margin: 0 0 0.5rem 0; font-size: 1.3rem;">Select a template</h1>
                        <p id="templateDesc" style="margin: 0; color: #666; font-size: 0.9rem;"></p>
                    </div>
                    <a href="{{ route('visual-reports.builder') }}" class="btn" style="padding: 0.75rem 1.5rem; white-space: nowrap;">➕ Create Template</a>
                </div>

                <div style="display: grid; grid-template-columns: auto auto auto auto; gap: 1rem; align-items: center;">
                    <div>
                        <label style="display: block; font-weight: bold; margin-bottom: 0.5rem; font-size: 0.9rem;">View Type:</label>
                        <select id="viewType" onchange="updateView()"
                            style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="table">📊 Table</option>
                            <option value="line">📈 Line Chart</option>
                            <option value="bar">📊 Bar Chart</option>
                            <option value="pie">🥧 Pie Chart</option>
                            <option value="area">📈 Area Chart</option>
                        </select>
                    </div>
                    <button onclick="executeReport()" class="btn" style="padding: 0.75rem 1.5rem; align-self: flex-end;">▶️ Execute</button>
                    <button onclick="openExportModal()" class="btn btn-secondary" style="padding: 0.75rem 1.5rem; align-self: flex-end;">📥 Export</button>
                </div>
            </div>

            <!-- Filters Section -->
            <div id="filtersSection" style="padding: 1rem; border-bottom: 1px solid #ddd; background: #f9f9f9; display: none; max-height: 150px; overflow-y: auto;">
                <h3 style="margin: 0 0 1rem 0; font-size: 0.9rem;">🔍 Filters</h3>
                <div id="filterInputs" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <!-- Filters loaded here -->
                </div>
            </div>

            <!-- Report Content -->
            <div id="reportContent" style="flex: 1; padding: 1.5rem; overflow-y: auto; background: white;">
                <p style="text-align: center; color: #999;">Select a template from the left sidebar</p>
            </div>

            <!-- Footer: Summary -->
            <div id="summarySection" style="padding: 1rem; border-top: 1px solid #ddd; background: #f9f9f9; max-height: 100px; overflow-y: auto; display: none;">
                <h4 style="margin: 0 0 0.5rem 0; font-size: 0.9rem;">📈 Summary</h4>
                <div id="summaryContent"></div>
            </div>
        </div>

        <!-- RIGHT SIDEBAR: Saved Reports -->
        <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow-y: auto; display: flex; flex-direction: column;">
            <div style="padding: 1.5rem 1rem; border-bottom: 1px solid #ddd;">
                <h2 style="font-size: 1.1rem; margin: 0;">💾 Saved Reports</h2>
                <p style="margin: 0.5rem 0 0 0; color: #666; font-size: 0.85rem;" id="savedCount">0 reports</p>
            </div>

            <div id="savedReportsList" style="flex: 1; padding: 0.5rem; overflow-y: auto;">
                <!-- Saved reports loaded here -->
            </div>

            <div style="padding: 1rem; border-top: 1px solid #ddd; display: flex; gap: 0.5rem;">
                <button onclick="saveCurrent()" class="btn" style="flex: 1; padding: 0.5rem; font-size: 0.9rem; display: none;" id="saveBtn">💾 Save</button>
                <button onclick="clearAll()" class="btn btn-danger" style="flex: 1; padding: 0.5rem; font-size: 0.9rem;">🗑️ Clear</button>
            </div>
        </div>
    </div>

    <!-- Export Modal -->
    <div id="exportModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 400px;">
            <h2>📥 Export Report</h2>
            <div style="margin: 1.5rem 0;">
                <label style="display: block; margin-bottom: 0.5rem;"><strong>Format:</strong></label>
                <select id="exportFormat" style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="csv">CSV (Spreadsheet)</option>
                    <option value="excel">Excel (XLSX)</option>
                    <option value="pdf">PDF (Professional)</option>
                    <option value="json">JSON (Data)</option>
                </select>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button onclick="performExport()" class="btn" style="flex: 1;">📥 Export</button>
                <button onclick="closeExportModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Save Modal -->
    <div id="saveModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 400px;">
            <h2>💾 Save Report</h2>
            <div style="margin: 1.5rem 0;">
                <label style="display: block; margin-bottom: 0.5rem;"><strong>Report Name:</strong></label>
                <input type="text" id="reportName" placeholder="Enter report name"
                    style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                <label style="display: block; margin-top: 1rem; margin-bottom: 0.5rem;"><strong>Description:</strong></label>
                <textarea id="reportDesc" placeholder="Optional description" rows="3"
                    style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;"></textarea>
            </div>
            <div style="display: flex; gap: 1rem;">
                <button onclick="confirmSave()" class="btn" style="flex: 1;">💾 Save</button>
                <button onclick="closeSaveModal()" class="btn btn-secondary" style="flex: 1;">Cancel</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.35.0/dist/apexcharts.min.js"></script>

    <script>
        let currentTemplate = null;
        let currentData = null;
        let currentFilters = {};
        let currentViewType = 'table';
        let chartInstance = null;

        // Load templates on page load
        document.addEventListener('DOMContentLoaded', loadTemplates);

        async function loadTemplates() {
            try {
                const response = await window.apiClient.get('/api/visual-reports/templates');
                renderTemplates(response);
            } catch (error) {
                console.error('Error loading templates:', error);
            }
        }

        function renderTemplates(data) {
            const templates = data.templates || [];
            const categories = data.categories || [];
            const container = document.getElementById('categoriesList');

            let html = '';

            // Group by category
            const grouped = {};
            templates.forEach(t => {
                const cat = t.category || 'Other';
                if (!grouped[cat]) grouped[cat] = [];
                grouped[cat].push(t);
            });

            // Render categories
            Object.entries(grouped).forEach(([category, temps]) => {
                html += `<div style="margin-bottom: 1rem;">`;
                html += `<div style="padding: 0.5rem 1rem; background: #f0f0f0; font-weight: bold; font-size: 0.85rem; border-radius: 4px; cursor: pointer; user-select: none;"
                    onclick="toggleCategory(this)">${category} (${temps.length})</div>`;
                html += `<div style="padding: 0.5rem 0; display: block;">`;

                temps.forEach(t => {
                    html += `<div onclick="selectTemplate(${t.id})"
                        style="padding: 0.75rem 1rem; cursor: pointer; border-radius: 4px; transition: background 0.2s; user-select: none;"
                        onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='transparent'"
                        class="template-item" data-id="${t.id}">
                        <div style="font-weight: 500; font-size: 0.9rem;">${t.icon || '📊'} ${t.name}</div>
                        <div style="font-size: 0.8rem; color: #666; margin-top: 0.25rem;">${t.description || ''}</div>
                    </div>`;
                });

                html += `</div></div>`;
            });

            container.innerHTML = html;
        }

        async function selectTemplate(id) {
            try {
                const response = await window.apiClient.get(`/api/visual-reports/templates/${id}`);
                currentTemplate = response;
                currentFilters = {};

                // Update UI
                document.getElementById('templateName').textContent = response.name;
                document.getElementById('templateDesc').textContent = response.description || '';
                document.getElementById('saveBtn').style.display = 'block';

                // Render filters
                renderFilters(response.filters);

                // Update view types
                const viewSelect = document.getElementById('viewType');
                viewSelect.value = response.default_view?.type || 'table';

                // Load saved reports
                loadSavedReports(id);

                // Clear current data
                document.getElementById('reportContent').innerHTML = '<p style="text-align: center; color: #999;">Click ▶️ Execute to generate the report</p>';
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
                html += `<div>
                    <label style="display: block; margin-bottom: 0.25rem; font-size: 0.85rem; font-weight: 500;">${f.label}${f.is_required ? ' *' : ''}</label>`;

                if (f.type === 'text') {
                    html += `<input type="text" data-column="${f.column}" value="${f.default_value || ''}"
                        onchange="updateFilter('${f.column}', this.value)"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">`;
                } else if (f.type === 'select' && f.options) {
                    html += `<select data-column="${f.column}" onchange="updateFilter('${f.column}', this.value)"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">-- Select --</option>`;
                    f.options.forEach(o => {
                        html += `<option value="${o.value}">${o.label}</option>`;
                    });
                    html += `</select>`;
                } else if (f.type === 'date') {
                    html += `<input type="date" data-column="${f.column}" value="${f.default_value || ''}"
                        onchange="updateFilter('${f.column}', this.value)"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">`;
                } else if (f.type === 'number') {
                    html += `<input type="number" data-column="${f.column}" value="${f.default_value || ''}"
                        onchange="updateFilter('${f.column}', this.value)"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">`;
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

            try {
                document.getElementById('reportContent').innerHTML = '<p style="text-align: center; color: #999;">⏳ Loading...</p>';

                const response = await window.apiClient.post(
                    `/api/visual-reports/templates/${currentTemplate.id}/execute`,
                    {
                        filters: currentFilters,
                        view_type: document.getElementById('viewType').value,
                    }
                );

                if (response.success) {
                    currentData = response;
                    currentViewType = document.getElementById('viewType').value;
                    updateView();
                } else {
                    document.getElementById('reportContent').innerHTML = `<p style="color: red;">❌ ${response.message}</p>`;
                }
            } catch (error) {
                document.getElementById('reportContent').innerHTML = `<p style="color: red;">❌ ${error.message}</p>`;
            }
        }

        function updateView() {
            if (!currentData) return;

            const viewType = document.getElementById('viewType').value;
            const container = document.getElementById('reportContent');

            if (viewType === 'table') {
                renderTable(currentData.data.rows);
            } else {
                renderChart(viewType, currentData);
            }

            // Show summary
            document.getElementById('summarySection').style.display = 'block';
            renderSummary(currentData.data.summary);
        }

        function renderTable(rows) {
            if (!rows || rows.length === 0) {
                document.getElementById('reportContent').innerHTML = '<p style="text-align: center; color: #999;">No data</p>';
                return;
            }

            let html = '<table class="table" style="font-size: 0.9rem;"><thead><tr>';
            Object.keys(rows[0]).forEach(col => {
                html += `<th>${col}</th>`;
            });
            html += '</tr></thead><tbody>';

            rows.forEach(row => {
                html += '<tr>';
                Object.values(row).forEach(val => {
                    html += `<td>${val}</td>`;
                });
                html += '</tr>';
            });

            html += '</tbody></table>';
            document.getElementById('reportContent').innerHTML = html;
        }

        function renderChart(type, data) {
            const rows = data.data.rows;
            if (!rows || rows.length === 0) return;

            const labels = rows.map((r, i) => Object.values(r)[0]);
            const datasets = [];

            data.data.metrics.forEach((metric, i) => {
                const values = rows.map(r => r[metric.alias] || 0);
                datasets.push({
                    label: metric.label,
                    data: values,
                    borderColor: `hsl(${i * 60}, 70%, 50%)`,
                    backgroundColor: `hsla(${i * 60}, 70%, 50%, 0.1)`,
                    tension: 0.4,
                });
            });

            const ctx = document.createElement('canvas');
            document.getElementById('reportContent').innerHTML = '';
            document.getElementById('reportContent').appendChild(ctx);

            if (chartInstance) chartInstance.destroy();

            const chartType = {
                'line': 'line',
                'bar': 'bar',
                'pie': 'pie',
                'area': 'line',
                'scatter': 'scatter'
            }[type] || 'line';

            chartInstance = new Chart(ctx, {
                type: chartType,
                data: { labels, datasets },
                options: { responsive: true, maintainAspectRatio: true }
            });
        }

        function renderSummary(summary) {
            if (!summary) return;

            let html = '<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">';

            Object.entries(summary).forEach(([key, stats]) => {
                html += `<div style="background: #f9f9f9; padding: 0.75rem; border-radius: 4px; font-size: 0.85rem;">
                    <div style="font-weight: bold;">${key}</div>
                    <div>Sum: ${Math.round(stats.sum)}</div>
                    <div>Avg: ${Math.round(stats.avg)}</div>
                    <div>Count: ${stats.count}</div>
                </div>`;
            });

            html += '</div>';
            document.getElementById('summaryContent').innerHTML = html;
        }

        async function loadSavedReports(templateId) {
            try {
                const response = await window.apiClient.get(`/api/visual-reports/templates/${templateId}/saved`);
                renderSavedReports(response);
            } catch (error) {
                console.error('Error loading saved reports:', error);
            }
        }

        function renderSavedReports(reports) {
            const container = document.getElementById('savedReportsList');
            document.getElementById('savedCount').textContent = `${reports.length} reports`;

            if (reports.length === 0) {
                container.innerHTML = '<p style="padding: 1rem; text-align: center; color: #999; font-size: 0.9rem;">No saved reports</p>';
                return;
            }

            let html = '';
            reports.forEach(r => {
                html += `<div onclick="loadResult(${r.id})"
                    style="padding: 0.75rem 1rem; cursor: pointer; border-radius: 4px; transition: background 0.2s; border-bottom: 1px solid #eee; user-select: none;"
                    onmouseover="this.style.background='#f0f0f0'" onmouseout="this.style.background='transparent'">
                    <div style="font-weight: 500; font-size: 0.9rem;">${r.name}</div>
                    <div style="font-size: 0.75rem; color: #666; margin-top: 0.25rem;">${r.created_at}</div>
                    <div style="font-size: 0.75rem; color: #999; margin-top: 0.25rem;">📊 ${r.view_type}</div>
                </div>`;
            });

            container.innerHTML = html;
        }

        async function loadResult(id) {
            try {
                const response = await window.apiClient.get(`/api/visual-reports/results/${id}`);
                currentData = { data: { rows: response.data, metrics: [], summary: {} }, metadata: response };
                currentFilters = response.applied_filters;
                document.getElementById('viewType').value = response.view_type;
                updateView();
            } catch (error) {
                alert('Error loading report');
            }
        }

        function saveCurrent() {
            document.getElementById('saveModal').style.display = 'flex';
        }

        async function confirmSave() {
            if (!currentData || !currentTemplate) {
                alert('No report to save');
                return;
            }

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
                        view_type: currentViewType,
                        view_config: {},
                        data: currentData.data.rows,
                    }
                );

                alert('Report saved successfully');
                closeSaveModal();
                loadSavedReports(currentTemplate.id);
            } catch (error) {
                alert('Error saving report');
            }
        }

        function closeSaveModal() {
            document.getElementById('saveModal').style.display = 'none';
            document.getElementById('reportName').value = '';
            document.getElementById('reportDesc').value = '';
        }

        function openExportModal() {
            if (!currentData) {
                alert('Please execute a report first');
                return;
            }
            document.getElementById('exportModal').style.display = 'flex';
        }

        function closeExportModal() {
            document.getElementById('exportModal').style.display = 'none';
        }

        async function performExport() {
            const format = document.getElementById('exportFormat').value;
            // TODO: Implement export
            alert('Export in ' + format);
            closeExportModal();
        }

        function toggleCategory(element) {
            const next = element.nextElementSibling;
            next.style.display = next.style.display === 'none' ? 'block' : 'none';
        }

        function toggleFavorites(show = true) {
            // TODO: Implement favorites filter
        }

        function clearAll() {
            if (confirm('Clear all reports?')) {
                document.getElementById('savedReportsList').innerHTML = '';
            }
        }
    </script>
@endsection
