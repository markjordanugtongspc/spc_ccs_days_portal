document.addEventListener('DOMContentLoaded', function() {
    // SweetAlert2 custom functions
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });
    
    function showSuccessAlert(title, text = '') {
        return Swal.fire({
            icon: 'success',
            title: title,
            text: text,
            confirmButtonColor: '#14b8a6', // teal-light color
            background: 'var(--color-dark-2)', // dark-2 color
            color: '#f8fafc' // light color
        });
    }
    
    function showErrorAlert(title, text = '') {
        return Swal.fire({
            icon: 'error',
            title: title,
            text: text,
            confirmButtonColor: '#14b8a6', // teal-light color
            background: 'var(--color-dark-2)', // dark-2 color
            color: '#f8fafc' // light color
        });
    }
    
    function showConfirmAlert(title, text = '', confirmButtonText = 'Yes', cancelButtonText = 'No') {
        return Swal.fire({
            icon: 'question',
            title: title,
            text: text,
            showCancelButton: true,
            confirmButtonColor: '#14b8a6', // teal-light color
            cancelButtonColor: '#475569', // gray-600 color
            confirmButtonText: confirmButtonText,
            cancelButtonText: cancelButtonText,
            background: 'var(--color-dark-2)', // dark-2 color
            color: '#f8fafc' // light color
        });
    }
    // DOM elements
    const createEventBtn = document.getElementById('createEventBtn');
    const pendingEventsBtn = document.getElementById('pendingEventsBtn');
    const allEventsBtn = document.getElementById('allEventsBtn');
    const approvedEventsBtn = document.getElementById('approvedEventsBtn');
    const createEventModal = document.getElementById('createEventModal');
    const viewEventModal = document.getElementById('viewEventModal');
    const modalCloseButtons = document.querySelectorAll('.modal-close');
    const enableReminderCheckbox = document.getElementById('enableReminder');
    const reminderOptions = document.getElementById('reminderOptions');
    const createEventForm = document.getElementById('createEventForm');
    
    // API endpoints
    const API_URL = '../includes/api/events_api.php';
    
    // Event listeners
    if (createEventBtn) {
        createEventBtn.addEventListener('click', () => {
            // Reset form when opening the create modal
            if (createEventForm) {
                createEventForm.reset();
                createEventForm.removeAttribute('data-mode');
                createEventForm.removeAttribute('data-id');
                document.querySelector('.modal-content p.text-2xl').textContent = 'Create Event';
                document.querySelector('button[type="submit"]').textContent = 'Create Event';
            }
            createEventModal.classList.remove('hidden');
        });
    }

    if (pendingEventsBtn) {
        pendingEventsBtn.addEventListener('click', () => {
            filterEventsByStatus('pending');
        });
    }

    if (allEventsBtn) {
        allEventsBtn.addEventListener('click', () => {
            filterEventsByStatus('all');
        });
    }

    if (approvedEventsBtn) {
        approvedEventsBtn.addEventListener('click', () => {
            filterEventsByStatus('approved');
        });
    }

    modalCloseButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        });
    });

    if (enableReminderCheckbox && reminderOptions) {
        enableReminderCheckbox.addEventListener('change', function() {
            reminderOptions.classList.toggle('hidden', !this.checked);
        });
    }

    // Form submission handler
    if (createEventForm) {
        createEventForm.addEventListener('submit', e => {
            e.preventDefault();
            
            // Get form values
            const eventName = document.getElementById('eventName').value.trim();
            const eventDate = document.getElementById('eventDate').value.trim();
            const eventVenue = document.getElementById('eventVenue').value.trim();
            const eventDescription = document.getElementById('eventDescription').value.trim();
            const enableReminder = document.getElementById('enableReminder').checked;
            const reminderOption = enableReminder ? getSelectedReminderOption() : null;
            
            console.log('Form values:', { eventName, eventDate, eventVenue, eventDescription, enableReminder, reminderOption });
            
            // Validate required fields
            if (!eventName) {
                showErrorAlert('Missing Information', 'Please enter an event name');
                return;
            }
            
            if (!eventDate) {
                showErrorAlert('Missing Information', 'Please select a date and time');
                return;
            }
            
            if (!eventVenue) {
                showErrorAlert('Missing Information', 'Please enter a venue');
                return;
            }
            
            // Prepare event data
            const eventData = {
                name: eventName,
                date: eventDate,
                venue: eventVenue,
                description: eventDescription,
                reminder: enableReminder,
                reminderOption: reminderOption
            };
            
            // Log the data being sent
            console.log('Sending event data:', eventData);
            
            // Check if we're editing or creating
            const isEditing = createEventForm.getAttribute('data-mode') === 'edit';
            
            if (isEditing) {
                // Add the event ID for updating
                eventData.id = createEventForm.getAttribute('data-id');
                updateEvent(eventData);
            } else {
                // Create new event
                createEvent(eventData);
            }
        });
    }

    // CRUD Functions
    function createEvent(eventData) {
        // Show loading state
        const submitBtn = createEventForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Creating...';
        submitBtn.disabled = true;
        
        // Send API request
        fetch(API_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(eventData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response:', data);
            
            if (data.success) {
                // Close modal and reset form
                createEventModal.classList.add('hidden');
                createEventForm.reset();
                
                // Show success message
                showSuccessAlert('Success!', 'Event created successfully! Waiting for approval.')
                .then(() => {
                    // Reload the page to show the new event
                    window.location.reload();
                });
            } else {
                console.error('API Error:', data);
                showErrorAlert('Error', data.error || 'Failed to create event');
            }
        })
        .catch(error => {
            console.error('Error creating event:', error);
            showErrorAlert('Error', 'Failed to create event. Please try again. Error: ' + error.message);
        })
        .finally(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }
    
    function updateEvent(eventData) {
        // Show loading state
        const submitBtn = createEventForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = 'Updating...';
        submitBtn.disabled = true;
        
        // Send API request
        fetch(API_URL, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(eventData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Close modal and reset form
                createEventModal.classList.add('hidden');
                createEventForm.reset();
                
                // Show success message
                showSuccessAlert('Success!', 'Event updated successfully!')
                .then(() => {
                    // Reload the page to show the updated event
                    window.location.reload();
                });
            } else {
                showErrorAlert('Error', data.error || 'Failed to update event');
            }
        })
        .catch(error => {
            console.error('Error updating event:', error);
            showErrorAlert('Error', 'Failed to update event. Please try again.');
        })
        .finally(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
    }
    
    function approveEvent(eventId) {
        showConfirmAlert('Approve Event', 'Are you sure you want to approve this event?', 'Yes, Approve', 'Cancel')
        .then((result) => {
            if (result.isConfirmed) {
            // Prepare data
            const eventData = {
                id: eventId,
                status: 'approved'
            };
            
            // Send API request
            fetch(API_URL, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(eventData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showSuccessAlert('Success!', 'Event approved successfully!')
                    .then(() => {
                        // Reload the page to show the updated status
                        window.location.reload();
                    });
                } else {
                    showErrorAlert('Error', data.error || 'Failed to approve event');
                }
            })
            .catch(error => {
                console.error('Error approving event:', error);
                showErrorAlert('Error', 'Failed to approve event. Please try again.');
            });
            }
        });
    }
    
    function deleteEvent(eventId) {
        showConfirmAlert('Delete Event', 'Are you sure you want to delete this event? This action cannot be undone.', 'Yes, Delete', 'Cancel')
        .then((result) => {
            if (result.isConfirmed) {
            // Prepare data
            const eventData = {
                id: eventId
            };
            
            // Send API request
            fetch(API_URL, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(eventData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showSuccessAlert('Success!', 'Event deleted successfully!')
                    .then(() => {
                        // Reload the page to update the list
                        window.location.reload();
                    });
                } else {
                    showErrorAlert('Error', data.error || 'Failed to delete event');
                }
            })
            .catch(error => {
                console.error('Error deleting event:', error);
                showErrorAlert('Error', 'Failed to delete event. Please try again.');
            });
            }
        });
    }
    
    function viewEvent(eventId) {
        // Fetch event details from the API
        fetch(`${API_URL}?id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                const event = data.data[0];
                
                // Format date for display
                const dateObj = new Date(event.event_date);
                const formattedDate = dateObj.toLocaleDateString() + ' • ' +
                                     dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                
                // Populate event details
                const detailsContainer = document.getElementById('eventDetails');
                if (detailsContainer) {
                    detailsContainer.innerHTML = `
                        <div class="mb-4">
                            <h3 class="text-xl font-bold">${event.name}</h3>
                            <p class="text-gray-400">${formattedDate} at ${event.venue}</p>
                        </div>
                        <div class="mb-4">
                            <p class="text-sm text-gray-300">${event.description}</p>
                        </div>
                        <div class="flex justify-between mb-4">
                            <div>
                                <span class="text-sm font-bold">Status:</span>
                                <span class="status-badge ${event.status}">${event.status}</span>
                            </div>
                            <div>
                                <span class="text-sm font-bold">Reminder:</span>
                                <span class="text-sm">${event.reminder_enabled ? 'Active' : 'Not set'}</span>
                            </div>
                        </div>
                    `;
                }
                const container = document.querySelector('#viewEventModal .modal-container');
                container.style.backgroundImage = '';
                const savedBg = BackgroundManager.load(eventId);
                if (savedBg) {
                    container.style.backgroundImage = `url('${savedBg}')`;
                    container.style.backgroundSize = 'cover';
                }
                const bgInput = document.getElementById('eventBgInput');
                const uploadLabel = document.querySelector('label[for="eventBgInput"]');
                const nameSpan = document.getElementById('eventBgName');
                if (bgInput) {
                    bgInput.value = '';
                    // File selection handler
                    bgInput.onchange = function(e) {
                        const file = e.target.files[0];
                        if (file) {
                            const reader = new FileReader();
                            reader.onload = function(evt) {
                                const dataURL = evt.target.result;
                                BackgroundManager.save(eventId, dataURL);
                                container.style.backgroundImage = `url('${dataURL}')`;
                                container.style.backgroundSize = 'cover';
                            };
                            reader.readAsDataURL(file);
                            nameSpan.textContent = file.name;
                            uploadLabel.style.display = 'none';
                            removeBtn.style.display = 'inline-flex';
                        }
                    };
                    // Remove button
                    let removeBtn = document.getElementById('removeBgBtn');
                    if (!removeBtn) {
                        removeBtn = document.createElement('button');
                        removeBtn.id = 'removeBgBtn';
                        removeBtn.type = 'button';
                        removeBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-2 0v4" /></svg> Remove`;
                        removeBtn.className = 'inline-flex items-center px-3 py-2 bg-red-500 text-white rounded hover:bg-red-600 ml-2 text-sm';
                        uploadLabel.parentNode.appendChild(removeBtn);
                    }
                    // Initial visibility
                    if (savedBg) {
                        uploadLabel.style.display = 'none';
                        nameSpan.textContent = '';
                        removeBtn.style.display = 'inline-flex';
                    } else {
                        uploadLabel.style.display = 'inline-flex';
                        nameSpan.textContent = '';
                        removeBtn.style.display = 'none';
                    }
                    // Remove handler
                    removeBtn.onclick = function() {
                        BackgroundManager.remove(eventId);
                        container.style.backgroundImage = '';
                        removeBtn.style.display = 'none';
                        uploadLabel.style.display = 'inline-flex';
                    };
                }
                viewEventModal.classList.remove('hidden');
            } else {
                showErrorAlert('Error', 'Failed to load event details');
            }
        })
        .catch(error => {
            console.error('Error fetching event details:', error);
            showErrorAlert('Error', 'Failed to load event details. Please try again.');
        });
    }
    
    function editEvent(eventId) {
        // Fetch event details from the API
        fetch(`${API_URL}?id=${eventId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                const event = data.data[0];
                
                // Update modal title and button
                document.querySelector('.modal-content p.text-2xl').textContent = 'Edit Event';
                document.querySelector('button[type="submit"]').textContent = 'Update Event';
                
                // Populate form fields
                document.getElementById('eventName').value = event.name;
                
                // Format date for datetime-local input
                const dateObj = new Date(event.event_date);
                const formattedDate = dateObj.toISOString().slice(0, 16); // Format: YYYY-MM-DDTHH:MM
                document.getElementById('eventDate').value = formattedDate;
                
                document.getElementById('eventVenue').value = event.venue;
                document.getElementById('eventDescription').value = event.description;
                
                // Set reminder checkbox and options
                document.getElementById('enableReminder').checked = event.reminder_enabled == 1;
                reminderOptions.classList.toggle('hidden', !event.reminder_enabled);
                
                if (event.reminder_time) {
                    const reminderSelect = document.querySelector('#reminderOptions select');
                    if (reminderSelect) {
                        reminderSelect.value = event.reminder_time;
                    }
                }
                
                // Set form mode to edit
                createEventForm.setAttribute('data-mode', 'edit');
                createEventForm.setAttribute('data-id', event.id);
                
                // Show the modal
                createEventModal.classList.remove('hidden');
            } else {
                showErrorAlert('Error', 'Failed to load event details for editing');
            }
        })
        .catch(error => {
            console.error('Error fetching event details for editing:', error);
            showErrorAlert('Error', 'Failed to load event details. Please try again.');
        });
    }

    // Background Manager
    class BackgroundManager {
        static save(eventId, dataURL) {
            localStorage.setItem(`eventBg_${eventId}`, dataURL);
        }
        static load(eventId) {
            return localStorage.getItem(`eventBg_${eventId}`);
        }
        static remove(eventId) {
            localStorage.removeItem(`eventBg_${eventId}`);
        }
    }

    // Helper Functions
    function getSelectedReminderOption() {
        const reminderSelect = document.querySelector('#reminderOptions select');
        return reminderSelect ? reminderSelect.value : '1d';
    }

    function filterEventsByStatus(status) {
        const eventRows = document.querySelectorAll('tbody tr');
        
        eventRows.forEach(row => {
            const statusCell = row.querySelector('td:nth-child(4) span');
            if (statusCell) {
                row.style.display = (status === 'all' || statusCell.classList.contains(status)) ? '' : 'none';
            }
        });
    }

    // Set up event action buttons
    function setupEventActionButtons() {
        document.querySelectorAll('.view-event').forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-id');
                viewEvent(eventId);
            });
        });

        document.querySelectorAll('.edit-event').forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-id');
                editEvent(eventId);
            });
        });

        document.querySelectorAll('.approve-event').forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-id');
                approveEvent(eventId);
            });
        });

        document.querySelectorAll('.delete-event').forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-id');
                deleteEvent(eventId);
            });
        });
    }
    
    // Initialize
    setupEventActionButtons();
});