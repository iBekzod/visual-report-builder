@extends('visual-report-builder::layouts.app')

@section('title', 'Report Builder')

@section('content')
    <div style="padding: 2rem;">
        <h1 style="margin-bottom: 2rem;">📋 Build Report Template</h1>

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">
            <!-- Left Panel: Configuration -->
            <div class="card">
                <h2 style="font-size: 1.2rem; margin-bottom: 1rem;">Configuration</h2>

                <!-- Data Source Selection -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">📊 Data Source</label>
                    <select id="modelSelect" style="width: 100%; padding: 0.75rem; border-radius: 4px; border: 1px solid #ddd; font-size: 0.9rem;">
                        <option value="">Select a model...</option>
                    </select>
                </div>

                <!-- Relationship Support -->
                <div id="relationshipsContainer" style="margin-bottom: 1.5rem; display: none;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">🔗 Join Related Table</label>
                    <select id="relationshipSelect" style="width: 100%; padding: 0.75rem; border-radius: 4px; border: 1px solid #ddd; font-size: 0.9rem;">
                        <option value="">-- None --</option>
                    </select>
                    <small style="color: #666; display: block; margin-top: 0.25rem;">Select a related table to add its columns</small>
                </div>

                <!-- Row Dimensions -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">📌 Row Dimensions</label>
                    <div id="rowDimensions" style="border: 2px dashed #007bff; border-radius: 4px; padding: 1rem; min-height: 60px; background-color: #f0f7ff;">
                        <small style="color: #666;">Drag dimensions here from the right panel</small>
                    </div>
                    <small style="color: #999; display: block; margin-top: 0.25rem;">Fields to group rows by</small>
                </div>

                <!-- Column Dimensions -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">📊 Column Dimensions</label>
                    <div id="columnDimensions" style="border: 2px dashed #6c757d; border-radius: 4px; padding: 1rem; min-height: 60px; background-color: #f5f5f5;">
                        <small style="color: #666;">Drag dimensions here from the right panel</small>
                    </div>
                    <small style="color: #999; display: block; margin-top: 0.25rem;">Fields to group columns by</small>
                </div>

                <!-- Metrics -->
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">📈 Metrics</label>
                    <div id="metrics" style="border: 2px dashed #28a745; border-radius: 4px; padding: 1rem; min-height: 60px; background-color: #f0fff4;">
                        <small style="color: #666;">Drag metrics here from the right panel</small>
                    </div>
                    <small style="color: #999; display: block; margin-top: 0.25rem;">Numeric fields to aggregate</small>
                </div>

                <!-- Buttons -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                    <button onclick="previewReport()" class="btn" style="padding: 0.75rem;">👁️ Preview</button>
                    <button onclick="saveTemplate()" class="btn btn-secondary" style="padding: 0.75rem;">💾 Save Template</button>
                </div>
            </div>

            <!-- Right Panel: Available Fields & Preview -->
            <div class="card">
                <h2 style="font-size: 1.2rem; margin-bottom: 1rem;">📋 Available Fields</h2>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                    <!-- Dimensions -->
                    <div>
                        <h4 style="margin-bottom: 0.75rem;">📌 Dimensions</h4>
                        <div id="dimensionsList" style="border: 1px solid #ddd; border-radius: 4px; padding: 0.75rem; max-height: 250px; overflow-y: auto; background-color: #fafafa;">
                            <small style="color: #999;">Select a model first</small>
                        </div>
                    </div>

                    <!-- Metrics -->
                    <div>
                        <h4 style="margin-bottom: 0.75rem;">📈 Metrics</h4>
                        <div id="metricsList" style="border: 1px solid #ddd; border-radius: 4px; padding: 0.75rem; max-height: 250px; overflow-y: auto; background-color: #fafafa;">
                            <small style="color: #999;">Select a model first</small>
                        </div>
                    </div>
                </div>

                <!-- Preview -->
                <h3 style="margin-bottom: 0.75rem;">👁️ Preview</h3>
                <div id="previewContainer" style="border: 1px solid #ddd; border-radius: 4px; padding: 1rem; background-color: #f9f9f9; min-height: 300px; max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 0.85rem;">
                    <p style="color: #999;">Configure your report and click "Preview" to see the data</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Save Template Modal -->
    <div id="saveModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 500px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
            <h2 style="margin-bottom: 1.5rem;">💾 Save as Template</h2>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Template Name *</label>
                <input type="text" id="templateName" placeholder="e.g., Sales Dashboard" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Description</label>
                <textarea id="templateDesc" placeholder="What does this report show?" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px; font-family: inherit;" rows="3"></textarea>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Category *</label>
                <select id="templateCategory" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
                    <option value="">Select category...</option>
                    <option value="Sales">Sales</option>
                    <option value="Finance">Finance</option>
                    <option value="Operations">Operations</option>
                    <option value="HR">HR</option>
                    <option value="Marketing">Marketing</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">Icon (emoji)</label>
                <input type="text" id="templateIcon" placeholder="📊" maxlength="2" style="width: 100%; padding: 0.75rem; border: 1px solid #ddd; border-radius: 4px;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <button onclick="confirmSave()" class="btn" style="padding: 0.75rem;">💾 Save Template</button>
                <button onclick="closeSaveModal()" class="btn btn-secondary" style="padding: 0.75rem;">Cancel</button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <!-- SortableJS for Drag-and-Drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

    <script>
        let currentModel = null;
        let currentRelationship = null;
        let availableDimensions = [];
        let availableMetrics = [];
        let reportConfig = {
            model: null,
            row_dimensions: [],
            column_dimensions: [],
            metrics: [],
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
                alert('Failed to load models. Please refresh the page.');
            }
        }

        // When model is selected
        document.getElementById('modelSelect').addEventListener('change', async (e) => {
            currentModel = e.target.value;
            reportConfig.model = currentModel;
            currentRelationship = null;

            // Reset configuration
            reportConfig.row_dimensions = [];
            reportConfig.column_dimensions = [];
            reportConfig.metrics = [];
            updateDimensionsDisplay();
            updateMetricsDisplay();

            // Clear preview
            document.getElementById('previewContainer').innerHTML = '<p style="color: #999;">Configure your report and click "Preview" to see the data</p>';

            if (!currentModel) {
                document.getElementById('dimensionsList').innerHTML = '<small style="color: #999;">Select a model first</small>';
                document.getElementById('metricsList').innerHTML = '<small style="color: #999;">Select a model first</small>';
                document.getElementById('relationshipsContainer').style.display = 'none';
                return;
            }

            // Show loading state
            document.getElementById('dimensionsList').innerHTML = '<small style="color: #999;">⏳ Loading...</small>';
            document.getElementById('metricsList').innerHTML = '<small style="color: #999;">⏳ Loading...</small>';

            try {
                // Load dimensions and metrics
                const [dimensions, metrics] = await Promise.all([
                    window.apiClient.get(`/api/visual-reports/models/${currentModel}/dimensions`),
                    window.apiClient.get(`/api/visual-reports/models/${currentModel}/metrics`)
                ]);

                availableDimensions = dimensions || [];
                availableMetrics = metrics || [];

                displayDimensions(availableDimensions);
                displayMetrics(availableMetrics);

                // Load relationships
                try {
                    const relationshipsResponse = await window.apiClient.get(`/api/visual-reports/models/${currentModel}/relationships`);
                    loadRelationships(relationshipsResponse.relationships || relationshipsResponse || []);
                } catch (err) {
                    console.log('No relationships found for this model');
                    document.getElementById('relationshipsContainer').style.display = 'none';
                }
            } catch (error) {
                console.error('Error loading metadata:', error);
                alert('Failed to load dimensions and metrics for this model');
                document.getElementById('dimensionsList').innerHTML = '<small style="color: red;">Error loading dimensions</small>';
                document.getElementById('metricsList').innerHTML = '<small style="color: red;">Error loading metrics</small>';
            }
        });

        // Display dimensions as draggable items
        function displayDimensions(dimensions) {
            const container = document.getElementById('dimensionsList');
            container.innerHTML = '';

            if (!dimensions || dimensions.length === 0) {
                container.innerHTML = '<small style="color: #999;">No dimensions available</small>';
                return;
            }

            dimensions.forEach(dim => {
                const item = document.createElement('div');
                item.style.cssText = 'background-color: #e7f3ff; border: 1px solid #007bff; padding: 0.5rem 0.75rem; border-radius: 4px; margin-bottom: 0.5rem; cursor: move; user-select: none; font-size: 0.9rem;';
                item.textContent = dim.label || dim.column;
                item.draggable = true;
                item.dataset.column = dim.column;
                item.dataset.label = dim.label || dim.column;
                item.dataset.type = 'dimension';
                item.addEventListener('dragstart', handleDragStart);
                container.appendChild(item);
            });
        }

        // Display metrics as draggable items
        function displayMetrics(metrics) {
            const container = document.getElementById('metricsList');
            container.innerHTML = '';

            if (!metrics || metrics.length === 0) {
                container.innerHTML = '<small style="color: #999;">No metrics available</small>';
                return;
            }

            metrics.forEach(metric => {
                const item = document.createElement('div');
                item.style.cssText = 'background-color: #f0fff4; border: 1px solid #28a745; padding: 0.5rem 0.75rem; border-radius: 4px; margin-bottom: 0.5rem; cursor: move; user-select: none; font-size: 0.9rem;';
                item.textContent = `${metric.label || metric.column} (${metric.default_aggregate || 'sum'})`;
                item.draggable = true;
                item.dataset.column = metric.column;
                item.dataset.label = metric.label || metric.column;
                item.dataset.aggregate = metric.default_aggregate || 'sum';
                item.dataset.type = 'metric';
                item.addEventListener('dragstart', handleDragStart);
                container.appendChild(item);
            });
        }

        // Load relationships dropdown
        function loadRelationships(relationships) {
            const container = document.getElementById('relationshipsContainer');
            const select = document.getElementById('relationshipSelect');

            if (!relationships || relationships.length === 0) {
                container.style.display = 'none';
                return;
            }

            container.style.display = 'block';
            select.innerHTML = '<option value="">-- None --</option>';

            relationships.forEach(rel => {
                const option = document.createElement('option');
                option.value = JSON.stringify(rel);
                option.textContent = `${rel.label} (${rel.type})`;
                option.dataset.relatedModel = rel.related_model;
                select.appendChild(option);
            });

            // Handle relationship selection
            select.addEventListener('change', async (e) => {
                if (!e.target.value) {
                    currentRelationship = null;
                    return;
                }

                try {
                    const rel = JSON.parse(e.target.value);
                    currentRelationship = rel;

                    // Load related model's dimensions and metrics
                    document.getElementById('dimensionsList').innerHTML = '<small style="color: #999;">⏳ Loading related fields...</small>';
                    document.getElementById('metricsList').innerHTML = '<small style="color: #999;">⏳ Loading related fields...</small>';

                    const [dimensions, metrics] = await Promise.all([
                        window.apiClient.get(`/api/visual-reports/models/${rel.related_model}/dimensions`),
                        window.apiClient.get(`/api/visual-reports/models/${rel.related_model}/metrics`)
                    ]);

                    // Add relationship prefix to related model's fields
                    const prefixedDimensions = (dimensions || []).map(d => ({
                        ...d,
                        column: `${rel.name}.${d.column}`,
                        label: `${rel.label} → ${d.label || d.column}`
                    }));

                    const prefixedMetrics = (metrics || []).map(m => ({
                        ...m,
                        column: `${rel.name}.${m.column}`,
                        label: `${rel.label} → ${m.label || m.column}`
                    }));

                    // Merge with base model's fields
                    displayDimensions([...availableDimensions, ...prefixedDimensions]);
                    displayMetrics([...availableMetrics, ...prefixedMetrics]);
                } catch (error) {
                    console.error('Error loading related model fields:', error);
                    alert('Failed to load related model fields');
                }
            });
        }

        // Drag and drop handlers
        function handleDragStart(e) {
            e.dataTransfer.effectAllowed = 'copy';
            e.dataTransfer.setData('column', e.target.dataset.column);
            e.dataTransfer.setData('label', e.target.dataset.label);
            e.dataTransfer.setData('type', e.target.dataset.type);
            e.dataTransfer.setData('aggregate', e.target.dataset.aggregate);
        }

        // Setup drop zones
        setupDropZone('rowDimensions', 'dimension');
        setupDropZone('columnDimensions', 'dimension');
        setupDropZone('metrics', 'metric');

        function setupDropZone(elementId, type) {
            const zone = document.getElementById(elementId);

            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.dataTransfer.dropEffect = 'copy';
                zone.style.backgroundColor = type === 'dimension' ? '#e8f4f8' : '#f5fff9';
                zone.style.borderColor = type === 'dimension' ? '#0066cc' : '#1a8847';
            });

            zone.addEventListener('dragleave', () => {
                zone.style.backgroundColor = type === 'dimension' ? '#f0f7ff' : '#f0fff4';
                zone.style.borderColor = type === 'dimension' ? '#007bff' : '#28a745';
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                zone.style.backgroundColor = type === 'dimension' ? '#f0f7ff' : '#f0fff4';
                zone.style.borderColor = type === 'dimension' ? '#007bff' : '#28a745';

                const dragType = e.dataTransfer.getData('type');
                const column = e.dataTransfer.getData('column');
                const label = e.dataTransfer.getData('label');
                const aggregate = e.dataTransfer.getData('aggregate');

                if (type === 'dimension' && dragType === 'dimension') {
                    if (elementId === 'rowDimensions') {
                        // Check for duplicates
                        const isDuplicate = reportConfig.row_dimensions.some(d => d.column === column);
                        if (!isDuplicate) {
                            reportConfig.row_dimensions.push({column, label});
                            updateDimensionsDisplay();
                        } else {
                            alert(`"${label}" is already in Row Dimensions`);
                        }
                    } else if (elementId === 'columnDimensions') {
                        // Check for duplicates
                        const isDuplicate = reportConfig.column_dimensions.some(d => d.column === column);
                        if (!isDuplicate) {
                            reportConfig.column_dimensions.push({column, label});
                            updateDimensionsDisplay();
                        } else {
                            alert(`"${label}" is already in Column Dimensions`);
                        }
                    }
                } else if (type === 'metric' && dragType === 'metric') {
                    // Check if metric with same column and aggregate already exists
                    const isDuplicate = reportConfig.metrics.some(m => m.column === column && m.aggregate === aggregate);
                    if (isDuplicate) {
                        alert(`"${label} (${aggregate})" is already added`);
                    } else {
                        reportConfig.metrics.push({
                            column: column,
                            label: label,
                            aggregate: aggregate,
                            alias: `${column}_${aggregate}`
                        });
                        updateMetricsDisplay();
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
                rowDiv.innerHTML = '<small style="color: #666;">Drag dimensions here from the right panel</small>';
            } else {
                reportConfig.row_dimensions.forEach((dim, i) => {
                    const tag = createTag(dim.label || dim.column || dim, i, () => {
                        reportConfig.row_dimensions.splice(i, 1);
                        updateDimensionsDisplay();
                    }, '#007bff');
                    rowDiv.appendChild(tag);
                });
            }

            if (reportConfig.column_dimensions.length === 0) {
                colDiv.innerHTML = '<small style="color: #666;">Drag dimensions here from the right panel</small>';
            } else {
                reportConfig.column_dimensions.forEach((dim, i) => {
                    const tag = createTag(dim.label || dim.column || dim, i, () => {
                        reportConfig.column_dimensions.splice(i, 1);
                        updateDimensionsDisplay();
                    }, '#6c757d');
                    colDiv.appendChild(tag);
                });
            }
        }

        // Update display of selected metrics
        function updateMetricsDisplay() {
            const div = document.getElementById('metrics');
            div.innerHTML = '';

            if (reportConfig.metrics.length === 0) {
                div.innerHTML = '<small style="color: #666;">Drag metrics here from the right panel</small>';
            } else {
                reportConfig.metrics.forEach((metric, i) => {
                    const tag = createTag(
                        `${metric.label} (${metric.aggregate})`,
                        i,
                        () => {
                            reportConfig.metrics.splice(i, 1);
                            updateMetricsDisplay();
                        },
                        '#28a745'
                    );
                    div.appendChild(tag);
                });
            }
        }

        // Create a tag element
        function createTag(text, index, onRemove, color) {
            const tag = document.createElement('span');
            tag.style.cssText = `display: inline-block; background-color: ${color}; color: white; padding: 0.5rem 0.75rem; border-radius: 4px; margin-right: 0.5rem; margin-bottom: 0.5rem;`;

            const textSpan = document.createElement('span');
            textSpan.textContent = text + ' ';
            tag.appendChild(textSpan);

            const btn = document.createElement('button');
            btn.textContent = '×';
            btn.style.cssText = 'background: none; border: none; color: white; cursor: pointer; font-weight: bold; margin-left: 0.25rem;';
            btn.onclick = (e) => {
                e.preventDefault();
                onRemove();
            };

            tag.appendChild(btn);
            return tag;
        }

        // Preview the report
        async function previewReport() {
            if (!reportConfig.model) {
                alert('Please select a data source');
                return;
            }

            if (reportConfig.metrics.length === 0) {
                alert('Please add at least one metric to preview');
                return;
            }

            try {
                const container = document.getElementById('previewContainer');
                container.innerHTML = '<p style="text-align: center; color: #999;">⏳ Loading preview...</p>';

                const response = await window.apiClient.post('/api/visual-reports/preview', {
                    model: reportConfig.model,
                    row_dimensions: reportConfig.row_dimensions.map(d => d.column),
                    column_dimensions: reportConfig.column_dimensions.map(d => d.column),
                    metrics: reportConfig.metrics
                });

                if (response.success) {
                    const data = response.data || [];
                    if (Array.isArray(data) && data.length > 0) {
                        const summary = `<p style="color: #28a745; margin-bottom: 1rem; font-weight: bold;">✅ Preview loaded (${data.length} rows)</p>`;
                        container.innerHTML = summary + '<pre>' + JSON.stringify(data.slice(0, 10), null, 2) + '</pre>';
                        if (data.length > 10) {
                            container.innerHTML += '<p style="color: #999; margin-top: 1rem;"><em>Showing first 10 rows of ' + data.length + ' total</em></p>';
                        }
                    } else {
                        container.innerHTML = '<p style="color: #ff9800;">⚠️ No data found for this configuration</p>';
                    }
                } else {
                    container.innerHTML = `<p style="color: red;">❌ Error: ${response.message || 'Unknown error'}</p>`;
                }
            } catch (error) {
                console.error('Error previewing:', error);
                document.getElementById('previewContainer').innerHTML = '<p style="color: red;">❌ Error previewing report:<br>' + (error.message || 'Unknown error') + '</p>';
            }
        }

        // Open save template modal
        function saveTemplate() {
            if (!reportConfig.model) {
                alert('Please select a data source');
                return;
            }

            if (reportConfig.metrics.length === 0) {
                alert('Please add at least one metric');
                return;
            }

            document.getElementById('saveModal').style.display = 'flex';
            document.getElementById('templateName').focus();
        }

        // Close modal
        function closeSaveModal() {
            document.getElementById('saveModal').style.display = 'none';
        }

        // Confirm save
        async function confirmSave() {
            const name = document.getElementById('templateName').value.trim();
            const description = document.getElementById('templateDesc').value.trim();
            const category = document.getElementById('templateCategory').value;
            const icon = document.getElementById('templateIcon').value.trim() || '📊';

            // Validation
            if (!name) {
                alert('❌ Please enter a template name');
                document.getElementById('templateName').focus();
                return;
            }

            if (name.length < 3) {
                alert('❌ Template name must be at least 3 characters');
                document.getElementById('templateName').focus();
                return;
            }

            if (!category) {
                alert('❌ Please select a category');
                document.getElementById('templateCategory').focus();
                return;
            }

            if (!reportConfig.model) {
                alert('❌ No data source selected');
                return;
            }

            if (reportConfig.metrics.length === 0) {
                alert('❌ Please add at least one metric');
                return;
            }

            // Disable button to prevent double-submit
            const saveBtn = event.target;
            saveBtn.disabled = true;
            saveBtn.textContent = '⏳ Saving...';

            try {
                const response = await window.apiClient.post('/api/visual-reports/builder/save-template', {
                    name: name,
                    description: description,
                    category: category,
                    icon: icon,
                    model: reportConfig.model,
                    row_dimensions: reportConfig.row_dimensions.map(d => d.column),
                    column_dimensions: reportConfig.column_dimensions.map(d => d.column),
                    metrics: reportConfig.metrics,
                    filters: [],
                    default_view: { type: 'table' }
                });

                if (response.success) {
                    closeSaveModal();
                    alert('✅ Template "' + name + '" created successfully!\n\nRedirecting to dashboard...');
                    setTimeout(() => {
                        window.location.href = '/visual-reports';
                    }, 1500);
                } else {
                    saveBtn.disabled = false;
                    saveBtn.textContent = '💾 Save Template';
                    alert('❌ Error: ' + (response.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('Error saving template:', error);
                saveBtn.disabled = false;
                saveBtn.textContent = '💾 Save Template';
                alert('❌ Error saving template:\n' + (error.message || 'Unknown error'));
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
            if (e.key === 'Escape' && document.getElementById('saveModal').style.display === 'flex') {
                closeSaveModal();
            }
        });

        // Clear form when opening modal
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.attributeName === 'style') {
                    const modal = document.getElementById('saveModal');
                    if (modal.style.display === 'flex') {
                        // Clear previous values
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
