// Scanner page specific JavaScript

document.addEventListener('DOMContentLoaded', function() {
    const startScannerBtn = document.getElementById('startScanner');
    const stopScannerBtn = document.getElementById('stopScanner');
    const scanResult = document.getElementById('scanResult');
    let html5QrCodeScanner;
    
    // Tab switching functionality
    const tabItems = document.querySelectorAll('.tab-item');
    const scannerTab = document.querySelector('.scanner-content');
    const manualTab = document.querySelector('.manual-content');
    
    tabItems.forEach((tab, index) => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabItems.forEach(item => item.classList.remove('active'));
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Toggle content visibility based on selected tab
            if (this.textContent.trim() === 'QR Scanner') {
                scannerTab.classList.remove('hidden');
                manualTab.classList.add('hidden');
                scannerTab.classList.add('fade-in');
                setTimeout(() => {
                    scannerTab.classList.remove('fade-in');
                }, 500);
            } else if (this.textContent.trim() === 'Manual Entry') {
                scannerTab.classList.add('hidden');
                manualTab.classList.remove('hidden');
                manualTab.classList.add('fade-in');
                setTimeout(() => {
                    manualTab.classList.remove('fade-in');
                    document.getElementById('bulkStudentId').focus();
                }, 500);
            }
        });
    });
    
    // Add camera preview functions
    let cameraStream;
    function startCameraPreview() {
        const video = document.getElementById('qr-preview');
        video.classList.remove('hidden');
        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showNotification('Camera not supported', 'error');
            return;
        }
        navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } })
            .then(stream => {
                cameraStream = stream;
                video.srcObject = stream;
                return video.play();
            })
            .catch(err => {
                console.error('Error accessing camera:', err);
                showNotification('Error accessing camera', 'error');
            });
    }

    function stopCameraPreview() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        const video = document.getElementById('qr-preview');
        if (video) {
            video.srcObject = null;
            video.classList.add('hidden');
        }
    }

    startScannerBtn.addEventListener('click', function() {
        Swal.fire({
            title: 'Enable Camera?',
            text: 'Allow camera access for QR scanning.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Enable',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'bg-dark-1 text-light',
                confirmButton: 'bg-teal-900 text-teal-light px-4 py-2 rounded-md',
                cancelButton: 'bg-dark-3 text-light px-4 py-2 rounded-md'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                startCameraPreview();
                startScannerBtn.disabled = true;
                stopScannerBtn.disabled = false;
            }
        });
    });

    stopScannerBtn.addEventListener('click', function() {
        stopCameraPreview();
        startScannerBtn.disabled = false;
        stopScannerBtn.disabled = true;
    });
    
    // Manual sign in/out buttons
    const signInBtn = document.getElementById('signInBtn');
    const signOutBtn = document.getElementById('signOutBtn');
    const studentIdInput = document.getElementById('studentId');
    
    // Add event listener for Enter key on the student ID input
    studentIdInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            // Perform sign in by default when Enter is pressed
            signInBtn.click();
        }
    });
    
    // Notification function (smaller, less intrusive than modal)
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-icon">
                ${type === 'success' ? 
                `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>` : 
                `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>`}
            </div>
            <div class="notification-message">${message}</div>
        `;
        
        document.body.appendChild(notification);
        
        // Slide in animation
        setTimeout(() => {
            notification.classList.add('show');
        }, 10);
        
        // Auto-remove after 3 seconds
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(notification)) {
                    document.body.removeChild(notification);
                }
            }, 300); // Wait for the slide out animation
        }, 3000);
    }
    
    // Success modal function for sign in/out operations
    function showSuccessModal(message) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.innerHTML = `
            <div class="modal-container">
                <div class="modal-icon modal-success">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="modal-title">Success</div>
                <div class="modal-message">${message}</div>
                <button class="modal-button close-modal">OK</button>
            </div>
        `;
        
        document.body.appendChild(modal);
        modal.querySelector('.close-modal').addEventListener('click', function() {
            document.body.removeChild(modal);
        });
        
        // Auto-close after 3 seconds
        setTimeout(() => {
            if (document.body.contains(modal)) {
                document.body.removeChild(modal);
            }
        }, 3000);
    }
    
    signInBtn.addEventListener('click', function() {
        const studentId = studentIdInput.value.trim();
        if (studentId) {
            // Handle sign in (will be connected to backend later)
            showSuccessModal(`Student ${studentId} signed in successfully!`);
            
            // Add to recent activity (demo functionality)
            addToRecentActivity(studentId, 'Sign In');
            
            studentIdInput.value = '';
            studentIdInput.focus();
        } else {
            // Show error for empty input
            studentIdInput.classList.add('border-red-500');
            showNotification('Please enter a student ID', 'error');
            setTimeout(() => {
                studentIdInput.classList.remove('border-red-500');
            }, 2000);
        }
    });
    
    signOutBtn.addEventListener('click', function() {
        const studentId = studentIdInput.value.trim();
        if (studentId) {
            // Handle sign out (will be connected to backend later)
            showSuccessModal(`Student ${studentId} signed out successfully!`);
            
            // Add to recent activity (demo functionality)
            addToRecentActivity(studentId, 'Sign Out');
            
            studentIdInput.value = '';
            studentIdInput.focus();
        } else {
            // Show error for empty input
            studentIdInput.classList.add('border-red-500');
            showNotification('Please enter a student ID', 'error');
            setTimeout(() => {
                studentIdInput.classList.remove('border-red-500');
            }, 2000);
        }
    });
    
    // Function to add entry to the recent activity table (for demo)
    function addToRecentActivity(studentId, status) {
        const activityContainer = document.querySelector('.divide-y.divide-dark-3');
        if (!activityContainer) return;
        
        // Sample names and years for demo
        const names = ['John Smith', 'Maria Garcia', 'Robert Johnson', 'Emily Wilson', 'Michael Brown', 'Sophia Martinez'];
        const randomName = names[Math.floor(Math.random() * names.length)];
        const years = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        const randomYear = years[Math.floor(Math.random() * years.length)];
        
        // Create the new activity entry
        const newActivity = document.createElement('div');
        newActivity.className = 'grid grid-cols-5 gap-4 p-4';
        newActivity.innerHTML = `
            <div>${studentId}</div>
            <div>${randomName}</div>
            <div>${randomYear}</div>
            <div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${status === 'Sign In' ? 'bg-green-900 text-green-300' : 'bg-red-900 text-red-300'}">${status}</span></div>
            <div>${new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</div>
        `;
        
        // Add to the top of the list
        activityContainer.insertBefore(newActivity, activityContainer.firstChild);
        
        // Highlight with animation
        newActivity.classList.add('activity-highlight');
        setTimeout(() => {
            newActivity.classList.remove('activity-highlight');
        }, 2000);
    }
    
    // Bulk Entry functionality
    const bulkStudentIdInput = document.getElementById('bulkStudentId');
    const bulkSubmitBtn = document.getElementById('bulkSubmitBtn');
    const pendingEntriesContainer = document.getElementById('pendingEntries');
    const clearBtn = document.getElementById('clearBtn');
    const submitAllBtn = document.getElementById('submitAllBtn');
    let pendingEntries = [];
    
    bulkSubmitBtn.addEventListener('click', function() {
        const studentId = bulkStudentIdInput.value.trim();
        if (!studentId) {
            showNotification('Please enter a student ID', 'error');
            return;
        }
        
        // Get selected event
        const eventSelect = document.querySelector('.manual-content select');
        const selectedEvent = eventSelect.options[eventSelect.selectedIndex].text;
        if (eventSelect.selectedIndex === 0) {
            showNotification('Please select an event', 'error');
            return;
        }
        
        // Get selected status
        const statusRadios = document.querySelectorAll('input[name="bulk-status"]');
        let selectedStatus;
        statusRadios.forEach(radio => {
            if (radio.checked) {
                selectedStatus = radio.nextElementSibling.textContent.trim();
            }
        });
        
        const entry = {
            id: Date.now(),
            studentId,
            event: selectedEvent,
            status: selectedStatus,
            timestamp: new Date()
        };
        
        pendingEntries.push(entry);
        updatePendingEntries();
        
        bulkStudentIdInput.value = '';
        bulkStudentIdInput.focus();
        
        showNotification('Entry added to pending list', 'success');
    });
    
    clearBtn.addEventListener('click', function() {
        pendingEntries = [];
        updatePendingEntries();
        showNotification('All pending entries cleared', 'success');
    });
    
    submitAllBtn.addEventListener('click', function() {
        const count = pendingEntries.length;
        pendingEntries.forEach(entry => {
            addActivityEntry(entry.studentId, entry.status);
        });
        
        pendingEntries = [];
        updatePendingEntries();
        showNotification(`${count} entries submitted successfully`, 'success');
    });
    
    function updatePendingEntries() {
        if (pendingEntries.length === 0) {
            pendingEntriesContainer.innerHTML = '<div class="text-gray-500">No pending entries</div>';
            clearBtn.disabled = true;
            submitAllBtn.disabled = true;
        } else {
            let html = '';
            pendingEntries.forEach(entry => {
                const time = entry.timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                
                let statusClass = 'bg-green-900 text-green-300';
                if (entry.status === 'Absent') {
                    statusClass = 'bg-red-900 text-red-300';
                } else if (entry.status === 'Late') {
                    statusClass = 'bg-yellow-900 text-yellow-300';
                }
                
                html += `
                    <div class="mb-3 bg-dark-2 p-3 rounded-md">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium">${entry.studentId}</div>
                                <div class="text-sm text-gray-400">${entry.event}</div>
                            </div>
                            <button class="delete-entry text-gray-400 hover:text-red-400" data-id="${entry.id}">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium ${statusClass}">
                                ${entry.status}
                            </span>
                            <span class="text-sm text-gray-400">${time}</span>
                        </div>
                    </div>
                `;
            });
            
            pendingEntriesContainer.innerHTML = html;
            clearBtn.disabled = false;
            submitAllBtn.disabled = false;
            
            // Add delete functionality for individual entries
            document.querySelectorAll('.delete-entry').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = parseInt(this.getAttribute('data-id'));
                    pendingEntries = pendingEntries.filter(entry => entry.id !== id);
                    updatePendingEntries();
                });
            });
        }
    }
    
    // CSV file handling
    const csvFileInput = document.getElementById('csvFile');
    const importBtn = document.getElementById('importBtn');
    
    csvFileInput.addEventListener('change', function(e) {
        if (e.target.files.length > 0) {
            importBtn.disabled = false;
            const fileName = e.target.files[0].name;
            this.nextElementSibling.querySelector('span').textContent = fileName;
        } else {
            importBtn.disabled = true;
            this.nextElementSibling.querySelector('span').textContent = 'Click to upload CSV file';
        }
    });
    
    importBtn.addEventListener('click', function() {
        const file = csvFileInput.files[0];
        if (!file) {
            showNotification('Please select a CSV file', 'error');
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const content = e.target.result;
            const rows = content.split('\n');
            
            let importCount = 0;
            
            // Skip header row
            for (let i = 1; i < rows.length; i++) {
                if (!rows[i].trim()) continue;
                
                const columns = rows[i].split(',');
                if (columns.length >= 3) {
                    const studentId = columns[0].trim();
                    const event = columns[1].trim();
                    const status = columns[2].trim();
                    
                    if (studentId && event && status) {
                        const entry = {
                            id: Date.now() + i,
                            studentId,
                            event,
                            status,
                            timestamp: new Date()
                        };
                        
                        pendingEntries.push(entry);
                        importCount++;
                    }
                }
            }
            
            updatePendingEntries();
            showNotification(`Imported ${importCount} entries from CSV`, 'success');
            
            // Reset file input
            csvFileInput.value = '';
            csvFileInput.nextElementSibling.querySelector('span').textContent = 'Click to upload CSV file';
            importBtn.disabled = true;
        };
        
        reader.onerror = function() {
            showNotification('Error reading CSV file', 'error');
        };
        
        reader.readAsText(file);
    });

    // Add entry to activity table
    function addActivityEntry(studentId, status) {
        const activityTable = document.querySelector('.divide-y.divide-dark-3');
        const now = new Date();
        const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        
        // Simulate getting student data - In a real app, this would come from your database
        const studentName = getRandomName();
        const yearLevel = getRandomYearLevel();
        
        // Create status class based on sign in/out
        let statusClass = 'bg-green-900 text-green-300';
        if (status === 'Sign Out') {
            statusClass = 'bg-blue-900 text-blue-300';
        } else if (status === 'Absent') {
            statusClass = 'bg-red-900 text-red-300';
        } else if (status === 'Late') {
            statusClass = 'bg-yellow-900 text-yellow-300';
        }
        
        // Create and add the new entry
        const newEntry = document.createElement('div');
        newEntry.className = 'grid grid-cols-5 gap-4 p-4 activity-highlight';
        newEntry.innerHTML = `
            <div>${studentId}</div>
            <div>${studentName}</div>
            <div>${yearLevel}</div>
            <div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}">${status}</span></div>
            <div>${timeString}</div>
        `;
        
        // Add to the beginning of the table
        if (activityTable.firstChild) {
            activityTable.insertBefore(newEntry, activityTable.firstChild);
        } else {
            activityTable.appendChild(newEntry);
        }
        
        // Remove highlight after animation completes
        setTimeout(() => {
            newEntry.classList.remove('activity-highlight');
        }, 2000);
    }
    
    // Helper functions for demo purposes
    function getRandomName() {
        const firstNames = ['John', 'Jane', 'Michael', 'Sarah', 'David', 'Emily', 'Robert', 'Lisa'];
        const lastNames = ['Smith', 'Johnson', 'Williams', 'Jones', 'Brown', 'Davis', 'Miller', 'Wilson'];
        
        const firstName = firstNames[Math.floor(Math.random() * firstNames.length)];
        const lastName = lastNames[Math.floor(Math.random() * lastNames.length)];
        
        return `${firstName} ${lastName}`;
    }
    
    function getRandomYearLevel() {
        const yearLevels = ['1st Year', '2nd Year', '3rd Year', '4th Year'];
        return yearLevels[Math.floor(Math.random() * yearLevels.length)];
    }
}); 