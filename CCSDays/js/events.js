document.addEventListener('DOMContentLoaded', function() {
    const createEventBtn = document.getElementById('createEventBtn');
    const pendingEventsBtn = document.getElementById('pendingEventsBtn');
    const createEventModal = document.getElementById('createEventModal');
    const viewEventModal = document.getElementById('viewEventModal');
    const modalCloseButtons = document.querySelectorAll('.modal-close');
    const enableReminderCheckbox = document.getElementById('enableReminder');
    const reminderOptions = document.getElementById('reminderOptions');

    if (createEventBtn) {
        createEventBtn.addEventListener('click', () => {
            createEventModal.classList.remove('hidden');
        });
    }

    if (pendingEventsBtn) {
        pendingEventsBtn.addEventListener('click', () => {
            filterEventsByStatus('pending');
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

    const createEventForm = document.getElementById('createEventForm');
    if (createEventForm) {
        createEventForm.addEventListener('submit', e => {
            e.preventDefault();
            
            const formData = new FormData(createEventForm);
            const eventData = {
                name: formData.get('eventName'),
                date: formData.get('eventDate'),
                venue: formData.get('eventVenue'),
                description: formData.get('eventDescription'),
                reminder: formData.get('enableReminder') ? getSelectedReminderOption() : null,
                status: 'pending'
            };
            
            if (!eventData.name || !eventData.date || !eventData.venue) {
                alert('Please fill in all required fields');
                return;
            }
            
            fetch('/api/events', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(eventData)
            })
            .then(response => response.json())
            .then(data => {
                alert('Event created successfully! Waiting for approval.');
                createEventModal.classList.add('hidden');
                createEventForm.reset();
                loadEvents();
            })
            .catch(error => {
                console.error('Error creating event:', error);
                alert('Failed to create event. Please try again.');
            });
        });
    }

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

    function loadEvents() {
        setupEventActionButtons();
    }

    function renderEvents(events) {
        const tableBody = document.querySelector('table tbody');
        if (!tableBody) return;
        
        tableBody.innerHTML = events.map(event => `
            <tr>
                <td>${event.name}</td>
                <td>${event.date}</td>
                <td>${event.venue}</td>
                <td><span class="status-badge ${event.status}">${event.status}</span></td>
                <td>${event.description.substring(0, 50)}${event.description.length > 50 ? '...' : ''}</td>
                <td>
                    <div class="flex space-x-2">
                        <button class="icon-button view-event" data-id="${event.id}">View</button>
                        ${event.status === 'pending' ? 
                            `<button class="icon-button approve-event" data-id="${event.id}">Approve</button>` : 
                            `<button class="icon-button edit-event" data-id="${event.id}">Edit</button>`
                        }
                        <button class="icon-button delete-event" data-id="${event.id}">Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    setupEventActionButtons();

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

    function viewEvent(eventId) {
        const eventRow = document.querySelector(`.view-event[data-id="${eventId}"]`).closest('tr');
        if (eventRow) {
            const name = eventRow.querySelector('td:nth-child(1)').textContent;
            const dateTime = eventRow.querySelector('td:nth-child(2)').textContent;
            const venue = eventRow.querySelector('td:nth-child(3)').textContent;
            const status = eventRow.querySelector('td:nth-child(4) span').textContent;
            const description = eventRow.querySelector('td:nth-child(5)').textContent;
            
            const detailsContainer = document.getElementById('eventDetails');
            if (detailsContainer) {
                detailsContainer.innerHTML = `
                    <div class="mb-4">
                        <h3 class="text-xl font-bold">${name}</h3>
                        <p class="text-gray-400">${dateTime} at ${venue}</p>
                    </div>
                    <div class="mb-4">
                        <p class="text-sm text-gray-300">${description}</p>
                    </div>
                    <div class="flex justify-between mb-4">
                        <div>
                            <span class="text-sm font-bold">Status:</span>
                            <span class="status-badge ${status}">${status}</span>
                        </div>
                        <div>
                            <span class="text-sm font-bold">Reminder:</span>
                            <span class="text-sm">Not set</span>
                        </div>
                    </div>
                `;
            }
            viewEventModal.classList.remove('hidden');
        }
    }

    function editEvent(eventId) {
        const eventRow = document.querySelector(`.edit-event[data-id="${eventId}"]`).closest('tr');
        if (eventRow) {
            const name = eventRow.querySelector('td:nth-child(1)').textContent;
            const dateTime = eventRow.querySelector('td:nth-child(2)').textContent;
            const venue = eventRow.querySelector('td:nth-child(3)').textContent;
            const description = eventRow.querySelector('td:nth-child(5)').textContent;
            
            createEventModal.classList.remove('hidden');
            
            document.getElementById('eventName').value = name;
            document.getElementById('eventDate').value = dateTime;
            document.getElementById('eventVenue').value = venue;
            document.getElementById('eventDescription').value = description;
            
            document.getElementById('enableReminder').checked = false;
            reminderOptions.classList.add('hidden');
            
            createEventForm.setAttribute('data-mode', 'edit');
            createEventForm.setAttribute('data-id', eventId);
        }
    }

    function approveEvent(eventId) {
        if (confirm('Are you sure you want to approve this event?')) {
            const eventRow = document.querySelector(`.approve-event[data-id="${eventId}"]`).closest('tr');
            const statusCell = eventRow.querySelector('td:nth-child(4) span');
            
            if (statusCell) {
                statusCell.classList.remove('pending');
                statusCell.classList.add('approved');
                statusCell.textContent = 'approved';
            }
            
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
            
            alert('Event approved successfully!');
        }
    }

    function deleteEvent(eventId) {
        if (confirm('Are you sure you want to delete this event? This action cannot be undone.')) {
            const eventRow = document.querySelector(`.delete-event[data-id="${eventId}"]`).closest('tr');
            if (eventRow) {
                eventRow.remove();
                alert('Event deleted successfully!');
            }
        }
    }
    
    loadEvents();
});