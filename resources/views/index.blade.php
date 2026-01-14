@extends('visual-report-builder::layouts.app')

@section('title', 'Reports Dashboard')

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1>My Reports</h1>
        <a href="{{ route('visual-reports.builder') }}" class="btn">+ Create New Report</a>
    </div>

    <div class="card">
        <div id="reports-container" style="min-height: 400px; display: flex; align-items: center; justify-content: center;">
            <p>Loading reports...</p>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        async function loadReports() {
            try {
                const response = await window.apiClient.get('/api/visual-reports/reports');
                const reports = response.data || [];
                const container = document.getElementById('reports-container');

                if (reports.length === 0) {
                    container.innerHTML = '<p>No reports yet. <a href="{{ route('visual-reports.builder') }}">Create one now</a></p>';
                    return;
                }

                let html = '<table class="table"><thead><tr><th>Name</th><th>Model</th><th>Created</th><th>Actions</th></tr></thead><tbody>';

                reports.forEach(report => {
                    html += `<tr>
                        <td><strong>${report.name}</strong></td>
                        <td>${report.model}</td>
                        <td>${new Date(report.created_at).toLocaleDateString()}</td>
                        <td>
                            <a href="/visual-reports/builder?report=${report.id}" class="btn" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Edit</a>
                            <button onclick="deleteReport(${report.id})" class="btn btn-danger" style="padding: 0.25rem 0.5rem; font-size: 0.875rem;">Delete</button>
                        </td>
                    </tr>`;
                });

                html += '</tbody></table>';
                container.innerHTML = html;
            } catch (error) {
                document.getElementById('reports-container').innerHTML = '<p style="color: red;">Error loading reports</p>';
                console.error('Error:', error);
            }
        }

        async function deleteReport(id) {
            if (!confirm('Are you sure you want to delete this report?')) {
                return;
            }

            try {
                await window.apiClient.delete(`/api/visual-reports/reports/${id}`);
                loadReports();
            } catch (error) {
                alert('Error deleting report');
                console.error('Error:', error);
            }
        }

        document.addEventListener('DOMContentLoaded', loadReports);
    </script>
@endsection
