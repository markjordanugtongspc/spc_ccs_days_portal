document.addEventListener('DOMContentLoaded', function () {
    // Load upcoming events if on dashboard page
    const upcomingEventsContainer = document.getElementById('upcomingEventsContainer');
    if (upcomingEventsContainer) {
        loadUpcomingEvents();
    }
    // Initialize quick action buttons (includes Approve Events handler)
    setupQuickActionButtons();
    // Update total events count
    updateTotalEventsCount();
    // Dynamic pending approvals count
    function refreshPendingApprovalsCount() {
        fetch('../includes/api/events_api.php?status=pending')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data) {
                    const elem = document.getElementById('pendingApprovalsCount');
                    if (elem) elem.textContent = data.data.length;
                }
            })
            .catch(error => console.error('Error fetching pending approvals count:', error));
    }
    // Initial load of pending count
    refreshPendingApprovalsCount();
    // Notification toast: show on click and auto-hide after 5s
    const notifBtn = document.getElementById('notificationBadgeBtn');
    const toastEl = document.getElementById('toast-notification');
    if (notifBtn && toastEl) {
        notifBtn.addEventListener('click', () => {
            toastEl.classList.remove('hidden');
            setTimeout(() => toastEl.classList.add('hidden'), 5000);
        });
    }
    // Form elements
    const loginForm = document.getElementById('loginForm');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const loginMessage = document.getElementById('loginMessage');
    const themeToggle = document.getElementById('themeToggle');
    const rememberCheckbox = document.getElementById('remember');

    // Check if saved credentials exist and populate fields
    loadSavedCredentials();

    // Initialize time elements
    const timeElement = document.getElementById('currentTime');
    const dateElement = document.getElementById('currentDate');
    const numericDateElement = document.getElementById('numericDate');
    const timeOfDayElement = document.getElementById('timeOfDay');
    const timeOfDayTextElement = document.getElementById('timeOfDayText');
    
    // Update time if elements exist
    if (timeElement && dateElement && numericDateElement) {
        updatePhilippinesTime();
        // Update every second
        setInterval(updatePhilippinesTime, 1000);
    }

    // Email validation regex pattern
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // Function to validate email
    function validateEmail(email) {
        return emailPattern.test(email);
    }
    
    // Function to load saved credentials from localStorage
    function loadSavedCredentials() {
        if (emailInput && passwordInput && rememberCheckbox) {
            const savedEmail = localStorage.getItem('rememberedEmail');
            const savedPassword = localStorage.getItem('rememberedPassword');
            const rememberMe = localStorage.getItem('rememberMe');
            
            if (savedEmail) {
                emailInput.value = savedEmail;
            }
            
            if (rememberMe === 'true' && savedPassword) {
                passwordInput.value = savedPassword;
                rememberCheckbox.checked = true;
            }
        }
    }
    
    // Function to save credentials to localStorage
    function saveCredentials(email, password, remember) {
        if (remember) {
            localStorage.setItem('rememberedEmail', email);
            localStorage.setItem('rememberedPassword', password);
            localStorage.setItem('rememberMe', 'true');
        } else {
            localStorage.setItem('rememberedEmail', email);
            localStorage.removeItem('rememberedPassword');
            localStorage.removeItem('rememberMe');
        }
    }

    // Function to update Philippines time
    function updatePhilippinesTime() {
        const options = {
            timeZone: 'Asia/Manila',
            hour12: true,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        
        const dateOptions = {
            timeZone: 'Asia/Manila',
            weekday: 'short',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        };
        
        const now = new Date();
        const hours = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Manila', hour12: false })).getHours();
        
        updateTimeOfDayIndicator(hours);
        
        const timeString = now.toLocaleTimeString('en-US', options);
        if (timeElement) {
            timeElement.textContent = timeString;
        }
        
        const dateString = now.toLocaleDateString('en-US', dateOptions);
        const numericDate = `${(now.getMonth() + 1).toString().padStart(2, '0')}/${now.getDate().toString().padStart(2, '0')}/${now.getFullYear()}`;
        
        if (dateElement) {
            dateElement.textContent = dateString;
        }
        
        if (numericDateElement) {
            numericDateElement.textContent = numericDate;
        }
    }
    
    // Function to update time of day indicator
    function updateTimeOfDayIndicator(hours) {
        if (!timeOfDayElement) return;
        
        let timeOfDayText = '';
        let timeOfDayIcon = '';
        
        if (hours >= 5 && hours < 12) {
            timeOfDayText = 'Morning';
            timeOfDayIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-yellow-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>`;
        } else if (hours >= 12 && hours < 17) {
            timeOfDayText = 'Afternoon';
            timeOfDayIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-orange-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>`;
        } else if (hours >= 17 && hours < 20) {
            timeOfDayText = 'Evening';
            timeOfDayIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-blue-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>`;
        } else {
            timeOfDayText = 'Night';
            timeOfDayIcon = `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-indigo-300">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
            </svg>`;
        }
        
        timeOfDayElement.innerHTML = timeOfDayIcon + `<span id="timeOfDayText">${timeOfDayText}</span>`;
    }

    // Function to validate login form
    function validateLoginForm(e) {
        e.preventDefault();
        let isValid = true;

        emailError.classList.add('hidden');
        passwordError.classList.add('hidden');
        loginMessage.classList.add('hidden');

        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();
        const rememberMe = rememberCheckbox.checked;

        if (!validateEmail(email)) {
            emailError.classList.remove('hidden');
            isValid = false;
        }

        if (password === '') {
            passwordError.classList.remove('hidden');
            isValid = false;
        }

        if (isValid) {
            localStorage.setItem('rememberedEmail', email);
            
            if (rememberMe) {
                saveCredentials(email, password, rememberMe);
            } else {
                localStorage.removeItem('rememberedPassword');
                localStorage.removeItem('rememberMe');
            }
            
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('remember', rememberMe ? '1' : '0');
            
            fetch('includes/login_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    loginMessage.textContent = data.message || 'Invalid credentials. Please try again.';
                    loginMessage.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                loginMessage.textContent = 'An error occurred during login. Please try again.';
                loginMessage.classList.remove('hidden');
            });
        }
    }

    // Add event listeners
    if (loginForm) {
        loginForm.addEventListener('submit', validateLoginForm);
    }

    // Handle tab switching in dashboard
    const tabItems = document.querySelectorAll('.tab-item');
    const tabContent = document.getElementById('tab-content');
    
    if (tabItems.length > 0) {
        tabItems.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabItems.forEach(item => item.classList.remove('active'));
                // Add active class to clicked tab
                this.classList.add('active');

                // If we're in the dashboard, handle content switching
                if (tabContent) {
                    const tabName = this.getAttribute('data-tab');
                
                // Show loading indicator
                tabContent.innerHTML = `
                    <div class="flex justify-center items-center p-8">
                        <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-teal-light" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Loading content...</span>
                    </div>
                `;
                
                if (tabName === 'dashboard') {
                    loadDashboardContent();
                    startDashboardAutoRefresh();
                } else if (tabName === 'students') {
                    loadStudentsContent();
                } else if (tabName === 'events') {
                    loadEventsContent();
                    }
                }
            });
        });
    }
    
    // Auto-refresh intervals
    let dashboardRefreshInterval = null;
    let studentsRefreshInterval = null;
    
    // Function to start dashboard auto-refresh
    function startDashboardAutoRefresh() {
        if (dashboardRefreshInterval) {
            clearInterval(dashboardRefreshInterval);
        }
        
        // Initial load of upcoming events
        loadUpcomingEvents();
        
        dashboardRefreshInterval = setInterval(() => {
            const activeTab = document.querySelector('.tab-item.active');
            if (activeTab && activeTab.getAttribute('data-tab') === 'dashboard') {
                refreshDashboardStats();
            }
        }, 30000);
    }
    
    // Function to refresh dashboard statistics
    function refreshDashboardStats() {
        updatePhilippinesTime();
        loadUpcomingEvents();
    }
    
    // Function to load upcoming events
    function loadUpcomingEvents() {
        const eventsContainer = document.getElementById('upcomingEventsContainer');
        if (!eventsContainer) return;
        
        fetch('../includes/api/fetch_upcoming_events.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.events.length > 0) {
                    // Clear loading indicator
                    eventsContainer.innerHTML = '';
                    
                    // Add events to container
                    data.events.forEach(event => {
                        const eventElement = document.createElement('div');
                        eventElement.className = 'visit-item';
                        eventElement.innerHTML = `
                            <div class="visit-details">
                                <div class="visitor-name">
                                    ${event.name}
                                    <span class="status-badge ${event.status}">${event.status}</span>
                                </div>
                                <div class="visit-info">Venue: ${event.venue}</div>
                                <div class="visit-time">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 icon">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    ${event.formatted_date}
                                </div>
                            </div>
                            <div class="visit-actions flex gap-2 mt-2">
                                <button class="action-button view-event cursor-pointer" data-id="${event.id}">View</button>
                                ${event.status === 'pending' ? `<button class="action-button approve-event cursor-pointer" data-id="${event.id}">Approve</button>` : ''}
                                <button class="action-button edit-event cursor-pointer" data-id="${event.id}">Edit</button>
                                <button class="action-button delete-event cursor-pointer" data-id="${event.id}">Delete</button>
                            </div>
                        `;
                        eventsContainer.appendChild(eventElement);
                    });
                    
                    // Add event listeners to detail buttons
                    document.querySelectorAll('.view-event').forEach(button => {
                        button.addEventListener('click', function() {
                            const eventId = this.getAttribute('data-id');
                            viewEventDetails(eventId);
                        });
                    });
                    document.querySelectorAll('.approve-event').forEach(button => {
                        button.addEventListener('click', function() {
                            const eventId = this.getAttribute('data-id');
                            approveEventDashboard(eventId);
                        });
                    });
                    document.querySelectorAll('.edit-event').forEach(button => {
                        button.addEventListener('click', function() {
                            const eventId = this.getAttribute('data-id');
                            window.location.href = `events.php?edit=true&id=${eventId}`;
                        });
                    });
                    document.querySelectorAll('.delete-event').forEach(button => {
                        button.addEventListener('click', function() {
                            const eventId = this.getAttribute('data-id');
                            deleteEventDashboard(eventId);
                        });
                    });
                } else {
                    eventsContainer.innerHTML = `
                        <div class="text-center p-4 text-gray-400">
                            No upcoming events found. <a href="#" id="createEventLink" class="text-teal-light hover:underline">Create an event</a>
                        </div>
                    `;
                    
                    // Add event listener to the "Create an event" link
                    document.getElementById('createEventLink').addEventListener('click', function(e) {
                        e.preventDefault(); // Prevent default navigation
                        
                        // Find the create event modal
                        const createEventModal = document.getElementById('createEventModal');
                        if (createEventModal) {
                            // Show the modal directly
                            createEventModal.classList.remove('hidden');
                        } else {
                            // If we're not on the events page, navigate to it
                            window.location.href = 'events.php';
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error loading upcoming events:', error);
                eventsContainer.innerHTML = `
                    <div class="text-center p-4 text-red-500">
                        Error loading events. Please try again.
                    </div>
                `;
            });
    }
    
    // Function to view event details
    function viewEventDetails(eventId) {
        // Fetch event details
        fetch(`../includes/api/events_api.php?id=${eventId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success && data.data.length > 0) {
                    const event = data.data[0];
                    
                    // Format date for display
                    const dateObj = new Date(event.event_date);
                    const formattedDate = dateObj.toLocaleDateString() + ' • ' +
                                         dateObj.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    
                    // Determine reminder status
                    const reminderStatus = event.reminder_enabled == 1 ? 'Active' : 'Not set';
                    
                    // Show event details in SweetAlert2
                    Swal.fire({
                        title: event.name,
                        html: `
                            <div class="text-left">
                                <p class="mb-2"><strong>Date & Time:</strong> ${formattedDate}</p>
                                <p class="mb-2"><strong>Venue:</strong> ${event.venue}</p>
                                <p class="mb-2"><strong>Status:</strong> <span class="status-badge ${event.status}">${event.status}</span></p>
                                <p class="mb-2"><strong>Reminder:</strong> ${reminderStatus}</p>
                                <p class="mb-4"><strong>Description:</strong></p>
                                <p class="text-gray-300">${event.description || 'No description provided.'}</p>
                            </div>
                        `,
                        confirmButtonText: 'Close',
                        confirmButtonColor: '#14b8a6', // teal-light color
                        background: 'var(--color-dark-2)', // CSS var for dark-2
                        color: '#f8fafc' // light color
                    });
                } else {
                    // Show error message
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to load event details',
                        confirmButtonColor: '#14b8a6', // teal-light color
                        background: 'var(--color-dark-2)', // CSS var for dark-2
                        color: '#f8fafc' // light color
                    });
                }
            })
            .catch(error => {
                console.error('Error fetching event details:', error);
                
                // Show error message
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to load event details. Please try again.',
                    confirmButtonColor: '#14b8a6', // teal-light color
                    background: 'var(--color-dark-2)', // CSS var for dark-2
                    color: '#f8fafc' // light color
                });
            });
    }
    
    // Function to load dashboard content
    function loadDashboardContent() {
        const dashboardContent = document.getElementById('dashboard-content');
        if (dashboardContent) {
            const content = dashboardContent.cloneNode(true);
            document.getElementById('tab-content').innerHTML = '';
            document.getElementById('tab-content').appendChild(content);
            content.classList.add('active');
            content.style.display = 'block';
            
            // Load upcoming events
            loadUpcomingEvents();
            
            // Set up quick action buttons
            setupQuickActionButtons();
        }
    }
    
    // Function to set up quick action buttons
    function setupQuickActionButtons() {
        const addEventBtn = document.getElementById('addEventBtn');
        if (addEventBtn) {
            addEventBtn.addEventListener('click', function() {
                // Check if we're already on the events page
                if (window.location.pathname.includes('events.php')) {
                    // If we have the createEventModal, open it
                    const createEventModal = document.getElementById('createEventModal');
                    if (createEventModal) {
                        createEventModal.classList.remove('hidden');
                    } else {
                        window.location.href = 'events.php';
                    }
                } else {
                    // Navigate to events page
                    window.location.href = 'events.php';
                }
            });
        }
        
        const approveEventsBtn = document.getElementById('approveEventsBtn');
        if (approveEventsBtn) {
            approveEventsBtn.addEventListener('click', function() {
                CCSModal.show({
                    title: 'Approve All Events?',
                    text: 'Are you sure you want to approve all pending events? This action cannot be undone.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Approve',
                    cancelButtonText: 'Cancel',
                }).then(result => {
                    if (result.isConfirmed) {
                        // Approve all pending events via API
                        fetch('../includes/api/events_api.php?action=approve_all')
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    CCSModal.show({
                                        title: 'Approved!',
                                        text: data.message,
                                        icon: 'success',
                                    });
                                    // Refresh events table
                                    loadEventsContent();
                                    // Refresh pending approvals count
                                    refreshPendingApprovalsCount();
                                } else {
                                    CCSModal.show({
                                        icon: 'error',
                                        title: 'Error',
                                        text: data.error || 'Failed to approve all pending events.',
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Error approving all events:', error);
                                CCSModal.show({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Failed to approve all pending events. Please try again.',
                                });
                            });
                    }
                });
            });
        }
    }
    
    // Function to load students content with live data
    function loadStudentsContent() {
        fetch('students.php?partial=true')
            .then(response => response.text())
            .then(html => {
                const tabContent = document.getElementById('tab-content');
                tabContent.innerHTML = html;
                
                // Load students data using fetch_students.php
                const searchInput = document.getElementById('searchInput');
                const studentContainer = document.getElementById('studentContainer');
                const showMoreContainer = document.getElementById('showMoreContainer');
                const showMoreBtn = document.getElementById('showMoreBtn');
                
                // Initialize pagination variables
                let currentPage = 1;
                let hasMoreStudents = false;
                
                // Load initial batch of students
                loadStudents();
                
                // Set up search functionality
                if (searchInput) {
                    searchInput.addEventListener('input', debounce(function() {
                        currentPage = 1;
                        loadStudents(true);
                    }, 300));
                }
                
                // Set up show more button
                if (showMoreBtn) {
                    showMoreBtn.addEventListener('click', function() {
                        currentPage++;
                        loadStudents(false);
                    });
                }
                
                // Function to load students with optional reset
                function loadStudents(reset = false) {
                    if (reset && studentContainer) {
                        studentContainer.innerHTML = '';
                    }
                    
                    const searchTerm = searchInput ? searchInput.value : '';
                    
                    // Show loading indicator
                    if (reset || !studentContainer.children.length) {
                        studentContainer.innerHTML = `
                            <div class="col-span-full flex justify-center items-center p-4">
                                <svg class="animate-spin h-8 w-8 text-teal-light" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                        `;
                    }
                    
                    // Fetch students from API
                    fetch(`../includes/api/fetch_students.php?page=${currentPage}&search=${encodeURIComponent(searchTerm)}&limit=16`)
                        .then(response => response.json())
                        .then(data => {
                            if (reset) {
                                studentContainer.innerHTML = '';
                            } else {
                                // Remove loading indicator if it exists
                                const loadingIndicator = studentContainer.querySelector('div.col-span-full');
                                if (loadingIndicator) {
                                    loadingIndicator.remove();
                                }
                            }
                            
                            // Update pagination info
                            hasMoreStudents = data.hasMore;
                            
                            // Update UI based on results
                            if (data.students.length === 0 && reset) {
                                studentContainer.innerHTML = `
                                    <div class="col-span-full text-center p-4">
                                        <p class="text-gray-400">No students found matching your search criteria.</p>
                                    </div>
                                `;
                                if (showMoreContainer) showMoreContainer.style.display = 'none';
                                return;
                            }
                            
                            // Display students
                            data.students.forEach(student => {
                                // Determine the proper year label
                                let yearLabel;
                                switch(student.Year) {
                                    case '1': yearLabel = '1st'; break;
                                    case '2': yearLabel = '2nd'; break;
                                    case '3': yearLabel = '3rd'; break;
                                    case '4': yearLabel = '4th'; break;
                                    default: yearLabel = student.Year;
                                }
                                
                                // Determine attendance class for color coding
                                const attendance = parseInt(student.Attendance) || 0;
                                let attendanceClass = 'text-yellow-500';
                                if (attendance > 6) attendanceClass = 'text-green-500';
                                else if (attendance < 3) attendanceClass = 'text-red-500';
                                
                                const studentCard = document.createElement('div');
                                studentCard.className = 'bg-dark-2 rounded-lg p-4 hover:shadow-md transition-all';
                                studentCard.innerHTML = `
                                    <div class="flex items-start justify-between">
                                        <div class="flex-grow overflow-hidden">
                                            <h3 class="text-lg font-semibold text-light truncate">${student.Name}</h3>
                                            <p class="text-gray-400 text-sm">${student.Student_ID}</p>
                                        </div>
                                        <div class="flex-shrink-0 ml-3">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-900 text-teal-light">
                                                ${yearLabel} Year
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mt-3 flex-grow">
                                        <p class="text-gray-300"><span class="text-gray-400">Course:</span> ${student.Course}</p>
                                        <p class="text-gray-300"><span class="text-gray-400">Attendance:</span> <span class="${attendanceClass}">${attendance} events</span></p>
                                    </div>
                                    <div class="mt-4 flex justify-between space-x-4">
                                        <button class="cursor-pointer flex-1 px-3 py-2 rounded-md bg-dark-3 hover:bg-dark-4 text-teal-light hover:text-teal transition-all duration-200 flex items-center justify-center group view-student" data-id="${student.Student_ID}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1.5 group-hover:scale-110 transition-transform">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Details
                                        </button>
                                        <button class="cursor-pointer flex-1 px-3 py-2 rounded-md bg-dark-3 hover:bg-dark-4 text-teal-light hover:text-teal transition-all duration-200 flex items-center justify-center group generate-qr" data-id="${student.Student_ID}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1.5 group-hover:scale-110 transition-transform">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Generate QR
                                        </button>
                                    </div>
                                `;
                                studentContainer.appendChild(studentCard);
                            });
                            
                            // Show/hide "Show More" button
                            if (showMoreContainer) {
                                showMoreContainer.style.display = hasMoreStudents ? 'flex' : 'none';
                            }
                            
                            // Add event handlers for view and attendance buttons
                            setupStudentButtonHandlers();
                        })
                        .catch(error => {
                            console.error('Error loading students:', error);
                            studentContainer.innerHTML = `
                                <div class="col-span-full text-center p-4">
                                    <p class="text-red-500">Error loading students. Please try again.</p>
                                </div>
                            `;
                            if (showMoreContainer) showMoreContainer.style.display = 'none';
                        });
                }
                
                // Setup handlers for view and attendance buttons
                function setupStudentButtonHandlers() {
                    // View student details
                    document.querySelectorAll('.view-student').forEach(button => {
                        button.addEventListener('click', function() {
                            const studentId = this.getAttribute('data-id');
                            viewStudentDetails(studentId);
                        });
                    });
                    
                    // Generate QR
                    document.querySelectorAll('.generate-qr').forEach(button => {
                        button.addEventListener('click', function() {
                            const studentId = this.getAttribute('data-id');
                            generateStudentQR(studentId);
                        });
                    });
                }
                
                // View student details modal
                function viewStudentDetails(studentId) {
                    fetch(`../includes/api/fetch_student_details.php?id=${studentId}`)
                        .then(response => response.json())
                        .then(student => {
                            if (!student) {
                                console.error('Student not found');
                                return;
                            }
                            
                            const modal = document.getElementById('studentModal');
                            const modalContent = document.getElementById('studentModalContent');
                            
                            // Create initials
                            const names = student.Name.split(' ');
                            const initials = names[0].charAt(0) + (names.length > 1 ? names[names.length - 1].charAt(0) : '');
                            
                            // Determine attendance class
                            const attendance = parseInt(student.Attendance) || 0;
                            let attendanceClass = '';
                            if (attendance > 6) attendanceClass = 'text-green-500';
                            else if (attendance < 3) attendanceClass = 'text-red-500';
                            else attendanceClass = 'text-yellow-500';
                            
                            modalContent.innerHTML = `
                                <div class="flex items-start mb-4">
                                    <div class="h-12 w-12 rounded-full bg-teal-900/30 flex items-center justify-center text-lg font-bold text-teal-light">
                                        ${initials}
                                    </div>
                                    <div class="ml-4">
                                        <h3 class="text-xl font-medium text-light">${student.Name}</h3>
                                        <div class="flex items-center mt-1">
                                            <span class="text-gray-400">${student.Student_ID}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                                    <div>
                                        <p class="text-gray-400 mb-1">Year Level</p>
                                        <p class="text-light">${student.Year} Year</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 mb-1">Course</p>
                                        <p class="text-light">${student.Course}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 mb-1">Gender</p>
                                        <p class="text-light">${student.Gender === 'M' ? 'Male' : 'Female'}</p>
                                    </div>
                                    <div>
                                        <p class="text-gray-400 mb-1">Attendance</p>
                                        <p class="text-light ${attendanceClass}">${attendance} events</p>
                                    </div>
                                </div>
                            `;
                            
                            modal.classList.remove('hidden');
                            
                            // Set up modal close
                            document.querySelectorAll('.close-modal').forEach(button => {
                                button.addEventListener('click', function() {
                                    modal.classList.add('hidden');
                                });
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching student details:', error);
                        });
                }
                
                // Mark attendance modal
                async function generateStudentQR(studentId) {
                    const modal = document.getElementById('studentModal');
                    const modalContent = document.getElementById('studentModalContent');
                    
                    // Show loading state first
                    modalContent.innerHTML = `
                        <div class="text-center p-4">
                            <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-teal-900/20 mb-4">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="w-8 h-8 text-teal-light animate-spin">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </div>
                            <div class="text-light">Generating QR code...</div>
                        </div>
                    `;
                    
                    modal.classList.remove('hidden');
                    
                    try {
                        // Use the same API endpoint and parameters as in students.js
                        const res = await fetch(`../includes/api/qr_generator.php?student_id=${encodeURIComponent(studentId)}&size=300x300&ecc=L&qzone=4`);
                        const data = await res.json();
                        
                        if (!data.success) throw new Error(data.message || 'QR generation failed.');
                        
                        // Use the cached file path instead of URL
                        const qrPath = `../${data.qr_path}`;
                        
                        modalContent.innerHTML = `
                            <div class="text-center">
                                <h3 class="text-xl font-medium text-light mb-4">Student QR Code</h3>
                                <div class="flex justify-center mb-4">
                                    <img src="${qrPath}" alt="QR Code" class="w-64 h-64 bg-white p-2 rounded" />
                                </div>
                                <div class="flex gap-3 justify-center">
                                    <a href="${qrPath}" download title="Download QR"
                                       class="px-4 py-2 bg-teal-900 text-teal-light rounded-md hover:bg-teal-800 transition-colors">Download</a>
                                    <button class="px-4 py-2 bg-dark-3 text-light rounded-md hover:bg-dark-4 transition-colors close-qr-modal cursor-pointer">Close</button>
                                </div>
                            </div>
                        `;
                        
                        // Add event listener to close button
                        document.querySelectorAll('.close-qr-modal').forEach(btn => {
                            btn.addEventListener('click', () => modal.classList.add('hidden'));
                        });
                        
                    } catch (error) {
                        console.error('Error generating QR:', error);
                        
                        modalContent.innerHTML = `
                            <div class="text-center p-4">
                                <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-red-900/20 mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-red-500 cursor-pointer">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                </div>
                                <div class="text-light mb-4">Failed to generate QR. Please try again.</div>
                                <button class="px-4 py-2 bg-dark-3 text-light rounded-md hover:bg-dark-4 transition-colors close-qr-modal">Close</button>
                            </div>
                        `;
                        
                        document.querySelectorAll('.close-qr-modal').forEach(btn => {
                            btn.addEventListener('click', () => modal.classList.add('hidden'));
                        });
                    }
                }
                
                // Debounce function to limit how often a function can run
                function debounce(func, wait) {
                    let timeout;
                    return function executedFunction(...args) {
                        const later = () => {
                            clearTimeout(timeout);
                            func(...args);
                        };
                        clearTimeout(timeout);
                        timeout = setTimeout(later, wait);
                    };
                }
                
                if (typeof window.initializeStudentsPage === 'function') {
                    window.initializeStudentsPage();
                }
            })
            .catch(error => {
                console.error('Error loading students content:', error);
                document.getElementById('tab-content').innerHTML = '<div class="p-4">Error loading content. Please try again.</div>';
            });
    }
    
    // Function to load events content
    function loadEventsContent() {
        fetch('events.php?partial=true')
            .then(response => response.text())
            .then(html => {
                const tabContent = document.getElementById('tab-content');
                tabContent.innerHTML = html;
                
                // Attach action handlers for event actions in loaded table
                const container = tabContent;
                container.querySelectorAll('.view-event').forEach(button => {
                    button.addEventListener('click', () => viewEventDetails(button.dataset.id));
                });
                container.querySelectorAll('.edit-event').forEach(button => {
                    button.addEventListener('click', () => window.location.href = `events.php?edit=true&id=${button.dataset.id}`);
                });
                container.querySelectorAll('.approve-event').forEach(button => {
                    button.addEventListener('click', () => approveEventDashboard(button.dataset.id));
                });
                container.querySelectorAll('.delete-event').forEach(button => {
                    button.addEventListener('click', () => deleteEventDashboard(button.dataset.id));
                });
            })
            .catch(error => {
                console.error('Error loading events content:', error);
                document.getElementById('tab-content').innerHTML = '<div class="p-4">Error loading content. Please try again.</div>';
            });
    }
});

// Function to approve an event from dashboard
function approveEventDashboard(eventId) {
    Swal.fire({
        icon: 'question',
        title: 'Approve Event',
        text: 'Are you sure you want to approve this event?',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('../includes/api/events_api.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: eventId, status: 'approved' })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Approved!', text: 'Event approved successfully' })
                      .then(() => loadUpcomingEvents());
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: data.error || 'Failed to approve event' });
                }
            })
            .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'Failed to approve event. Please try again.' }));
        }
    });
}

// Function to delete an event from dashboard
function deleteEventDashboard(eventId) {
    Swal.fire({
        icon: 'warning',
        title: 'Delete Event',
        text: 'Are you sure you want to delete this event? This action cannot be undone.',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#14b8a6',
        cancelButtonColor: '#6b7280',
        background: 'var(--color-dark-2)',
        color: '#f8fafc'
    }).then(result => {
        if (result.isConfirmed) {
            fetch('../includes/api/events_api.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: eventId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Event deleted successfully',
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#14b8a6',
                        background: 'var(--color-dark-2)',
                        color: '#f8fafc'
                    }).then(() => loadUpcomingEvents());
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.error || 'Failed to delete event',
                        confirmButtonText: 'Close',
                        confirmButtonColor: '#14b8a6',
                        background: 'var(--color-dark-2)',
                        color: '#f8fafc'
                    });
                }
            })
            .catch(() => Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Failed to delete event. Please try again.',
                confirmButtonText: 'Close',
                confirmButtonColor: '#14b8a6',
                background: 'var(--color-dark-2)',
                color: '#f8fafc'
            }));
        }
    });
}

// =============================
// SweetAlert2 Popup Modal Class
// =============================
// This class provides a reusable method for showing SweetAlert2 modals
// that match the dashboard's dark color palette and button spacing.
class CCSModal {
    /**
     * Shows a SweetAlert2 modal with dark palette and spaced buttons.
     * @param {Object} options - SweetAlert2 options (title, text, etc.)
     * @returns {Promise} - SweetAlert2 result promise
     */
    static show(options) {
        return Swal.fire({
            // Defaults for dark palette
            background: '#181c23', // fallback if CSS vars not available
            color: '#e5e7eb', // fallback for light text
            ...options,
            customClass: {
                popup: 'swal2-dark-popup',
                confirmButton: 'swal2-confirm-dark',
                cancelButton: 'swal2-cancel-dark',
                actions: 'swal2-actions-spaced',
                ...options.customClass
            },
            buttonsStyling: false // Use our custom classes
        });
    }
}

// =============================
// SweetAlert2 Custom CSS Inject
// =============================
// Injects custom styles for SweetAlert2 dark modal and button spacing.
(function injectSwal2DarkStyles() {
    if (document.getElementById('swal2-dark-style')) return;
    const style = document.createElement('style');
    style.id = 'swal2-dark-style';
    style.innerHTML = `
        .swal2-dark-popup {
            background: var(--color-dark-2, #181c23) !important;
            color: var(--color-light, #e5e7eb) !important;
            border-radius: 0.75rem;
            box-shadow: 0 4px 32px 0 rgba(0,0,0,0.5);
        }
        .swal2-actions-spaced {
            display: flex !important;
            gap: 1rem !important;
            justify-content: center;
        }
        .swal2-confirm-dark, .swal2-cancel-dark {
            background: var(--color-dark-1, #23272f) !important;
            color: var(--color-light, #e5e7eb) !important;
            border: 1px solid rgba(134,185,176,0.3) !important;
            border-radius: 0.375rem !important;
            font-size: 1rem !important;
            padding: 0.5rem 1.5rem !important;
            margin: 0 0.25rem !important;
            transition: background 0.2s;
        }
        .swal2-confirm-dark:hover, .swal2-cancel-dark:hover {
            background: rgba(134,185,176,0.1) !important;
            color: var(--color-teal-light, #5eead4) !important;
        }
    `;
    document.head.appendChild(style);
})();

// Add this function to update the total events count in the dashboard
function updateTotalEventsCount() {
    const totalEventsCountElement = document.getElementById('totalEventsCount');
    if (totalEventsCountElement) {
        // Determine the correct API path based on current location
        const apiPath = window.location.pathname.includes('/pages/') 
            ? '../includes/api/events_api.php?action=count'
            : 'includes/api/events_api.php?action=count';
            
        fetch(apiPath)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the count
                    totalEventsCountElement.textContent = data.count || '0';
                    
                    // Update the change indicator
                    const changeElement = document.querySelector('.stat-change');
                    if (changeElement && data.change !== undefined) {
                        // Remove existing classes
                        changeElement.classList.remove('positive', 'negative', 'neutral');
                        
                        if (data.change > 0) {
                            changeElement.classList.add('positive');
                            changeElement.textContent = `+${data.change} from last week`;
                        } else if (data.change < 0) {
                            changeElement.classList.add('negative');
                            changeElement.textContent = `${data.change} from last week`;
                        } else {
                            changeElement.classList.add('neutral');
                            changeElement.textContent = 'No change from last week';
                        }
                    }
                } else {
                    console.error('Error fetching event count:', data.error);
                    totalEventsCountElement.textContent = '0';
                }
            })
            .catch(error => {
                console.error('Error fetching event count:', error);
                totalEventsCountElement.textContent = '0';
            });
    }
}

// Expose the function globally
window.updateTotalEventsCount = updateTotalEventsCount;