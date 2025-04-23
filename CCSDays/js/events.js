document.addEventListener('DOMContentLoaded', function() {
    // Modal elements
    const createEventBtn = document.getElementById('createEventBtn');
    const pendingEventsBtn = document.getElementById('pendingEventsBtn');
    const createEventModal = document.getElementById('createEventModal');
    const viewEventModal = document.getElementById('viewEventModal');
    const modalCloseButtons = document.querySelectorAll('.modal-close');
    const enableReminderCheckbox = document.getElementById('enableReminder');
    const reminderOptions = document.getElementById('reminderOptions');

    // Event listeners for opening modals
    if (createEventBtn) {
        createEventBtn.addEventListener('click', function() {
            createEventModal.classList.remove('hidden');
        });
    }

    // Event listener for pending events button
    if (pendingEventsBtn) {
        pendingEventsBtn.addEventListener('click', function() {
            // Filter to show only pending events
            filterEventsByStatus('pending');
        });
    }

    // Event listeners for closing modals
    modalCloseButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Find the closest modal parent and hide it
            const modal = button.closest('.modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        });
    });

    // Event listener for reminder checkbox
    if (enableReminderCheckbox && reminderOptions) {
        enableReminderCheckbox.addEventListener('change', function() {
            if (this.checked) {
                reminderOptions.classList.remove('hidden');
            } else {
                reminderOptions.classList.add('hidden');
            }
        });
    }

    // Event form submission
    const createEventForm = document.getElementById('createEventForm');
    if (createEventForm) {
        createEventForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get form values
            const eventName = document.getElementById('eventName').value;
            const eventDate = document.getElementById('eventDate').value;
            const eventVenue = document.getElementById('eventVenue').value;
            const eventDescription = document.getElementById('eventDescription').value;
            const enableReminder = document.getElementById('enableReminder').checked;
            
            // Validate form
            if (!eventName || !eventDate || !eventVenue) {
                alert('Please fill in all required fields');
                return;
            }
            
            // Create event object
            const eventData = {
                name: eventName,
                date: eventDate,
                venue: eventVenue,
                description: eventDescription,
                reminder: enableReminder ? getSelectedReminderOption() : null,
                status: 'pending' // New events are pending by default
            };
            
            // Send data to server (this would be an AJAX call in a real application)
            console.log('Creating event:', eventData);
            
            // For demo purposes, simulate a successful creation
            alert('Event created successfully! Waiting for approval.');
            
            // Close the modal and reset form
            createEventModal.classList.add('hidden');
            createEventForm.reset();
            
            // In a real application, you would refresh the events list or add the new event to the table
            // For demo purposes, we'll just reload the page
            // window.location.reload();
        });
    }

    // Function to get selected reminder option
    function getSelectedReminderOption() {
        const reminderSelect = document.querySelector('#reminderOptions select');
        return reminderSelect ? reminderSelect.value : '1d'; // Default to 1 day if not found
    }

    // Function to filter events by status
    function filterEventsByStatus(status) {
        const eventRows = document.querySelectorAll('tbody tr');
        
        eventRows.forEach(row => {
            const statusCell = row.querySelector('td:nth-child(4) span');
            if (statusCell) {
                if (status === 'all' || statusCell.classList.contains(status)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }

    // Event listeners for view, edit, approve buttons
    setupEventActionButtons();

    function setupEventActionButtons() {
        // View event buttons
        const viewButtons = document.querySelectorAll('.view-event');
        viewButtons.forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-id');
                viewEvent(eventId);
            });
        });

        // Edit event buttons
        const editButtons = document.querySelectorAll('.edit-event');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-id');
                editEvent(eventId);
            });
        });

        // Approve event buttons
        const approveButtons = document.querySelectorAll('.approve-event');
        approveButtons.forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-id');
                approveEvent(eventId);
            });
        });

        // Delete event buttons
        const deleteButtons = document.querySelectorAll('.delete-event');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const eventId = this.getAttribute('data-id');
                deleteEvent(eventId);
            });
        });
    }

    // Function to view event details
    function viewEvent(eventId) {
        console.log('Viewing event:', eventId);
        
        // In a real application, you would fetch event details from the server
        // For demo purposes, we'll use hardcoded data
        let eventDetails;
        
        if (eventId === '1') {
            eventDetails = {
                name: 'Programming Competition',
                date: '2023-04-19 10:00 AM',
                venue: 'CCS Laboratory',
                description: 'A competitive programming event for all CCS students. Participants will solve algorithmic problems within a time limit.',
                status: 'approved',
                reminder: 'Active (1 day before)'
            };
        } else if (eventId === '2') {
            eventDetails = {
                name: 'Web Development Workshop',
                date: '2023-04-19 2:30 PM',
                venue: 'Multi-Purpose Hall',
                description: 'Learn the basics of web development using HTML, CSS, and JavaScript. Bring your own laptop.',
                status: 'pending',
                reminder: 'Not set'
            };
        } else {
            eventDetails = {
                name: 'Unknown Event',
                date: 'N/A',
                venue: 'N/A',
                description: 'Event details not found',
                status: 'unknown',
                reminder: 'N/A'
            };
        }
        
        // Populate the event details in the modal
        const detailsContainer = document.getElementById('eventDetails');
        if (detailsContainer) {
            detailsContainer.innerHTML = `
                <div class="mb-4">
                    <h3 class="text-xl font-bold">${eventDetails.name}</h3>
                    <p class="text-gray-400">${eventDetails.date} at ${eventDetails.venue}</p>
                </div>
                <div class="mb-4">
                    <p class="text-sm text-gray-300">${eventDetails.description}</p>
                </div>
                <div class="flex justify-between mb-4">
                    <div>
                        <span class="text-sm font-bold">Status:</span>
                        <span class="status-badge ${eventDetails.status}">${eventDetails.status}</span>
                    </div>
                    <div>
                        <span class="text-sm font-bold">Reminder:</span>
                        <span class="text-sm">${eventDetails.reminder}</span>
                    </div>
                </div>
            `;
        }
        
        // Show the modal
        viewEventModal.classList.remove('hidden');
    }

    // Function to edit an event
    function editEvent(eventId) {
        console.log('Editing event:', eventId);
        // In a real application, you would fetch event details and populate a form
        alert('Edit functionality would open a form with event details for editing');
    }

    // Function to approve an event
    function approveEvent(eventId) {
        console.log('Approving event:', eventId);
        
        // In a real application, you would send an AJAX request to update the event status
        // For demo purposes, we'll just show an alert
        if (confirm('Are you sure you want to approve this event?')) {
            alert('Event approved successfully!');
            
            // Update the UI to reflect the change
            const eventRow = document.querySelector(`.approve-event[data-id="${eventId}"]`).closest('tr');
            const statusCell = eventRow.querySelector('td:nth-child(4) span');
            
            if (statusCell) {
                statusCell.classList.remove('pending');
                statusCell.classList.add('approved');
                statusCell.textContent = 'approved';
            }
            
            // Replace the approve button with an edit button
            const actionCell = eventRow.querySelector('td:nth-child(6) div');
            if (actionCell) {
                const approveButton = actionCell.querySelector(`.approve-event[data-id="${eventId}"]`);
                if (approveButton) {
                    const editButton = document.createElement('button');
                    editButton.className = 'icon-button edit-event';
                    editButton.setAttribute('data-id', eventId);
                    editButton.textContent = 'Edit';
                    editButton.addEventListener('click', function() {
                        editEvent(eventId);
                    });
                    
                    actionCell.replaceChild(editButton, approveButton);
                }
            }
        }
    }

    // Function to delete an event
    function deleteEvent(eventId) {
        console.log('Deleting event:', eventId);
        
        // In a real application, you would send an AJAX request to delete the event
        // For demo purposes, we'll just show an alert
        if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
            alert('Event deleted successfully!');
            
            // Remove the event row from the table
            const eventRow = document.querySelector(`.delete-event[data-id="${eventId}"]`).closest('tr');
            if (eventRow) {
                eventRow.remove();
            }
        }
    }
});