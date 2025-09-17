<?php
/**
 * Export Data Navbar Component
 * Provides PDF and Excel export functionality with consistent system design
 */
?>

<div class="export-navbar">
    <div class="export-navbar-content">
        <div class="export-title">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            Export Data
        </div>
        
        <div class="export-actions">
            <div class="export-filters">
                <select id="exportDataType" class="export-select">
                    <option value="all">All Data</option>
                    <option value="students">Students</option>
                    <option value="events">Events</option>
                    <option value="attendance">Attendance</option>
                </select>
                
                <select id="exportDateRange" class="export-select">
                    <option value="all">All Time</option>
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="custom">Custom Range</option>
                </select>
                
                <div id="customDateRange" class="custom-date-range" style="display: none;">
                    <input type="date" id="startDate" class="date-input">
                    <span class="date-separator">to</span>
                    <input type="date" id="endDate" class="date-input">
                </div>
            </div>
            
            <div class="export-buttons">
                <button id="exportPdfBtn" class="export-btn pdf-btn" data-format="pdf">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                    Export PDF
                </button>
                
                <button id="exportExcelBtn" class="export-btn excel-btn" data-format="excel">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.375 19.5h17.25m-17.25 0a1.125 1.125 0 01-1.125-1.125M3.375 19.5h7.5c.621 0 1.125-.504 1.125-1.125m-9.75 0V5.625m0 0A2.25 2.25 0 015.625 3.375h13.5A2.25 2.25 0 0121.75 5.625m0 0v12.75m0 0a2.25 2.25 0 01-2.25 2.25M3.375 19.5a1.125 1.125 0 001.125 1.125m0-17.25v17.25m0 0a1.125 1.125 0 001.125 1.125M19.5 19.5V5.625" />
                    </svg>
                    Export Excel
                </button>
            </div>
        </div>
    </div>
    
    <!-- Export Progress Modal -->
    <div id="exportModal" class="export-modal" style="display: none;">
        <div class="export-modal-content">
            <div class="export-modal-header">
                <h3>Exporting Data</h3>
                <button id="closeExportModal" class="close-modal-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="export-modal-body">
                <div class="export-progress">
                    <div class="progress-bar">
                        <div id="progressFill" class="progress-fill"></div>
                    </div>
                    <div id="progressText" class="progress-text">Preparing export...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Export Navbar Styles */
.export-navbar {
    background-color: var(--color-dark-2);
    border: 1px solid rgba(134, 185, 176, 0.1);
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.export-navbar-content {
    padding: 1rem 1.5rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
}

.export-title {
    display: flex;
    align-items: center;
    font-weight: 600;
    color: var(--color-teal-light);
    font-size: 1.1rem;
}

.export-title .icon {
    margin-right: 0.5rem;
}

.export-actions {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
}

.export-filters {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.export-select {
    background-color: var(--color-dark-1);
    color: var(--color-light);
    border: 1px solid rgba(134, 185, 176, 0.3);
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    min-width: 120px;
    transition: all 0.2s ease;
}

.export-select:focus {
    outline: none;
    border-color: var(--color-teal-light);
    box-shadow: 0 0 0 2px rgba(134, 185, 176, 0.2);
}

.export-select:hover {
    border-color: var(--color-teal-light);
}

.custom-date-range {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-left: 0.5rem;
}

.date-input {
    background-color: var(--color-dark-1);
    color: var(--color-light);
    border: 1px solid rgba(134, 185, 176, 0.3);
    padding: 0.5rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

.date-input:focus {
    outline: none;
    border-color: var(--color-teal-light);
    box-shadow: 0 0 0 2px rgba(134, 185, 176, 0.2);
}

.date-separator {
    color: var(--color-light);
    font-size: 0.875rem;
    opacity: 0.7;
}

.export-buttons {
    display: flex;
    gap: 0.75rem;
}

.export-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.2s ease;
    cursor: pointer;
    border: none;
    position: relative;
    overflow: hidden;
}

.export-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.pdf-btn {
    background-color: #dc2626;
    color: white;
}

.pdf-btn:hover:not(:disabled) {
    background-color: #b91c1c;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.3);
}

.excel-btn {
    background-color: #059669;
    color: white;
}

.excel-btn:hover:not(:disabled) {
    background-color: #047857;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
}

.export-btn:active {
    transform: translateY(0);
}

/* Export Modal Styles */
.export-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.export-modal-content {
    background-color: var(--color-dark-2);
    border-radius: 0.5rem;
    width: 90%;
    max-width: 400px;
    border: 1px solid rgba(134, 185, 176, 0.1);
}

.export-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(134, 185, 176, 0.1);
}

.export-modal-header h3 {
    color: var(--color-light);
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.close-modal-btn {
    background: none;
    border: none;
    color: var(--color-light);
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 0.25rem;
    transition: all 0.2s ease;
}

.close-modal-btn:hover {
    background-color: rgba(134, 185, 176, 0.1);
}

.export-modal-body {
    padding: 1.5rem;
}

.export-progress {
    text-align: center;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background-color: var(--color-dark-1);
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 1rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, var(--color-teal) 0%, var(--color-teal-light) 100%);
    width: 0%;
    transition: width 0.3s ease;
    border-radius: 4px;
}

.progress-text {
    color: var(--color-light);
    font-size: 0.875rem;
    opacity: 0.8;
}

/* Responsive Design */
@media (max-width: 768px) {
    .export-navbar-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .export-actions {
        flex-direction: column;
        gap: 1rem;
    }
    
    .export-filters {
        justify-content: center;
    }
    
    .export-buttons {
        justify-content: center;
    }
    
    .custom-date-range {
        flex-direction: column;
        gap: 0.5rem;
        margin-left: 0;
        margin-top: 0.5rem;
    }
}

@media (max-width: 480px) {
    .export-buttons {
        flex-direction: column;
        width: 100%;
    }
    
    .export-btn {
        justify-content: center;
        width: 100%;
    }
}
</style>

<script>
// Export Navbar JavaScript Functionality
document.addEventListener('DOMContentLoaded', function() {
    const exportDateRange = document.getElementById('exportDateRange');
    const customDateRange = document.getElementById('customDateRange');
    const exportPdfBtn = document.getElementById('exportPdfBtn');
    const exportExcelBtn = document.getElementById('exportExcelBtn');
    const exportModal = document.getElementById('exportModal');
    const closeExportModal = document.getElementById('closeExportModal');
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');

    // Show/hide custom date range
    exportDateRange.addEventListener('change', function() {
        if (this.value === 'custom') {
            customDateRange.style.display = 'flex';
        } else {
            customDateRange.style.display = 'none';
        }
    });

    // Close modal functionality
    closeExportModal.addEventListener('click', function() {
        exportModal.style.display = 'none';
    });

    // Close modal when clicking outside
    exportModal.addEventListener('click', function(e) {
        if (e.target === exportModal) {
            exportModal.style.display = 'none';
        }
    });

    // PDF Export functionality
    exportPdfBtn.addEventListener('click', function() {
        handleExport('pdf');
    });

    // Excel Export functionality
    exportExcelBtn.addEventListener('click', function() {
        handleExport('excel');
    });

    function handleExport(format) {
        const dataType = document.getElementById('exportDataType').value;
        const dateRange = document.getElementById('exportDateRange').value;
        let startDate = '';
        let endDate = '';

        if (dateRange === 'custom') {
            startDate = document.getElementById('startDate').value;
            endDate = document.getElementById('endDate').value;
            
            if (!startDate || !endDate) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Dates',
                    text: 'Please select both start and end dates for custom range.',
                    background: '#042630',
                    color: '#d0d6d6',
                    confirmButtonColor: '#86b9b0'
                });
                return;
            }
        }

        // Show export modal
        exportModal.style.display = 'flex';
        progressFill.style.width = '0%';
        progressText.textContent = 'Preparing export...';

        // Simulate export progress
        simulateExportProgress(format, dataType, dateRange, startDate, endDate);
    }

    function simulateExportProgress(format, dataType, dateRange, startDate, endDate) {
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 100) progress = 100;

            progressFill.style.width = progress + '%';

            if (progress < 30) {
                progressText.textContent = 'Gathering data...';
            } else if (progress < 60) {
                progressText.textContent = 'Processing records...';
            } else if (progress < 90) {
                progressText.textContent = `Generating ${format.toUpperCase()} file...`;
            } else if (progress >= 100) {
                progressText.textContent = 'Export complete!';
                clearInterval(interval);
                
                setTimeout(() => {
                    exportModal.style.display = 'none';
                    
                    // Trigger actual download
                    triggerDownload(format, dataType, dateRange, startDate, endDate);
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Export Successful',
                        text: `Your ${format.toUpperCase()} file has been downloaded.`,
                        background: '#042630',
                        color: '#d0d6d6',
                        confirmButtonColor: '#86b9b0'
                    });
                }, 1000);
            }
        }, 200);
    }

    function triggerDownload(format, dataType, dateRange, startDate, endDate) {
        // Create form data for the export request
        const formData = new FormData();
        formData.append('format', format);
        formData.append('dataType', dataType);
        formData.append('dateRange', dateRange);
        formData.append('startDate', startDate);
        formData.append('endDate', endDate);

        // Create and submit form for download
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '../includes/api/export_data.php';
        form.style.display = 'none';

        for (let [key, value] of formData.entries()) {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }
});
</script>
