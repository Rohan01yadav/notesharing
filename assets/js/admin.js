/**
 * Student Notes Sharing Platform
 * Admin Dashboard JavaScript & Visualizations
 */

document.addEventListener('DOMContentLoaded', function () {
    // 1. Mobile Sidebar Toggle
    const sidebarToggle = document.getElementById('adminSidebarToggle');
    const sidebar = document.getElementById('adminSidebar');

    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });
    }

    // 2. Initialize Department Notes Distribution Chart (Doughnut)
    const deptChartCanvas = document.getElementById('deptDistributionChart');
    if (deptChartCanvas && window.Chart && window.ADMIN_CHART_DATA) {
        new Chart(deptChartCanvas, {
            type: 'doughnut',
            data: {
                labels: window.ADMIN_CHART_DATA.deptLabels || ['CSE', 'IT', 'ECE', 'EEE', 'MECH', 'CIVIL'],
                datasets: [{
                    data: window.ADMIN_CHART_DATA.deptCounts || [12, 19, 7, 5, 8, 4],
                    backgroundColor: [
                        '#4f46e5',
                        '#06b6d4',
                        '#10b981',
                        '#f59e0b',
                        '#8b5cf6',
                        '#ec4899'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 12 }
                        }
                    }
                },
                cutout: '70%'
            }
        });
    }

    // 3. Initialize Top Downloaded Notes Chart (Bar)
    const notesChartCanvas = document.getElementById('topNotesChart');
    if (notesChartCanvas && window.Chart && window.ADMIN_CHART_DATA) {
        new Chart(notesChartCanvas, {
            type: 'bar',
            data: {
                labels: window.ADMIN_CHART_DATA.topNoteTitles || [],
                datasets: [{
                    label: 'Total Downloads',
                    data: window.ADMIN_CHART_DATA.topNoteDownloads || [],
                    backgroundColor: 'rgba(79, 70, 229, 0.85)',
                    hoverBackgroundColor: '#4338ca',
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0,
                            font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { stepSize: 20 }
                    }
                }
            }
        });
    }

    // 4. Client-side quick filter for tables
    const tableSearch = document.getElementById('adminTableSearch');
    if (tableSearch) {
        tableSearch.addEventListener('input', function () {
            const query = this.value.toLowerCase();
            const rows = document.querySelectorAll('.admin-data-table tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
});
