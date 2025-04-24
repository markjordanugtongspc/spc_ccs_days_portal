document.addEventListener('DOMContentLoaded', function () {
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
                        <svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-teal-light" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
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
                    fetch(`../includes/api/fetch_students.php?page=${currentPage}&search=${encodeURIComponent(searchTerm)}&limit=8`)
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
                                        <p class="text-gray-300"><span class="text-gray-400">Course:</span> ${student.College || 'CCS'}</p>
                                        <p class="text-gray-300"><span class="text-gray-400">Attendance:</span> <span class="${attendanceClass}">${attendance} events</span></p>
                                    </div>
                                    <div class="mt-4 flex justify-between">
                                        <button class="text-teal-light hover:text-teal transition-colors flex items-center view-student" data-id="${student.Student_ID}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            Details
                                        </button>
                                        <button class="text-teal-light hover:text-teal transition-colors flex items-center mark-attendance" data-id="${student.Student_ID}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Attendance
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
                    
                    // Mark attendance
                    document.querySelectorAll('.mark-attendance').forEach(button => {
                        button.addEventListener('click', function() {
                            const studentId = this.getAttribute('data-id');
                            markStudentAttendance(studentId);
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
                            
                            let badgeClass;
                            switch (student.Status) {
                                case 'Active':
                                    badgeClass = 'bg-green-800 text-green-200';
                                    break;
                                case 'Inactive':
                                    badgeClass = 'bg-gray-800 text-gray-200';
                                    break;
                                case 'Alumni':
                                    badgeClass = 'bg-blue-800 text-blue-200';
                                    break;
                                default:
                                    badgeClass = 'bg-gray-800 text-gray-200';
                            }
                            
                            let attendanceBadge;
                            if (student.Attendance === '100%') {
                                attendanceBadge = 'bg-green-800 text-green-200';
                            } else if (student.Attendance === '0%') {
                                attendanceBadge = 'bg-red-800 text-red-200';
                            } else {
                                attendanceBadge = 'bg-yellow-800 text-yellow-200';
                            }
                            
                            modalContent.innerHTML = `
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-xl font-medium text-light">${student.Name}</h3>
                                        <p class="text-gray-400">${student.Student_ID}</p>
                                    </div>
                                    <span class="px-2 py-1 text-sm rounded ${badgeClass}">${student.Status}</span>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div>
                                        <p class="text-sm text-gray-400">Year Level</p>
                                        <p class="text-light">${student.Year_Level}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-400">Course</p>
                                        <p class="text-light">${student.Course}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-400">Gender</p>
                                        <p class="text-light">${student.Gender}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-400">Attendance</p>
                                        <p class="inline-block px-2 py-1 text-sm rounded ${attendanceBadge}">${student.Attendance}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-400">Email</p>
                                        <p class="text-light">${student.Email}</p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-400">Phone</p>
                                        <p class="text-light">${student.Phone || 'N/A'}</p>
                                    </div>
                                </div>
                                
                                <div class="flex space-x-3">
                                    <button class="flex-1 py-2 px-4 bg-dark-3 text-light rounded hover:bg-dark-4 transition-colors close-modal">
                                        Close
                                    </button>
                                    <button class="flex-1 py-2 px-4 bg-teal text-dark rounded hover:bg-teal-light transition-colors mark-attendance" data-id="${student.Student_ID}">
                                        Mark Attendance
                                    </button>
                                    <button class="flex-1 py-2 px-4 bg-blue-600 text-white rounded hover:bg-blue-700 transition-colors edit-student" data-id="${student.Student_ID}">
                                        Edit
                                    </button>
                                    <button class="flex-1 py-2 px-4 bg-red-600 text-white rounded hover:bg-red-700 transition-colors delete-student" data-id="${student.Student_ID}">
                                        Delete
                                    </button>
                                </div>
                            `;
                            
                            modal.classList.remove('hidden');
                            
                            // Close modal when clicking close button
                            document.querySelectorAll('.close-modal').forEach(button => {
                                button.addEventListener('click', function() {
                                    modal.classList.add('hidden');
                                });
                            });
                            
                            // Mark attendance button
                            document.querySelectorAll('.mark-attendance').forEach(button => {
                                button.addEventListener('click', function() {
                                    const studentId = this.getAttribute('data-id');
                                    markStudentAttendance(studentId);
                                });
                            });
                            
                            // Edit student button
                            document.querySelectorAll('.edit-student').forEach(button => {
                                button.addEventListener('click', function() {
                                    const studentId = this.getAttribute('data-id');
                                    editStudentDetails(student);
                                });
                            });
                            
                            // Delete student button
                            document.querySelectorAll('.delete-student').forEach(button => {
                                button.addEventListener('click', function() {
                                    const studentId = this.getAttribute('data-id');
                                    deleteStudentConfirm(student);
                                });
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching student details:', error);
                        });
                }
                
                // Edit student details modal
                function editStudentDetails(student) {
                    const modal = document.getElementById('studentModal');
                    const modalContent = document.getElementById('studentModalContent');
                    
                    modalContent.innerHTML = `
                        <div class="text-center mb-4">
                            <h3 class="text-xl font-medium text-light">Edit Student</h3>
                            <p class="text-gray-400">ID: ${student.Student_ID}</p>
                        </div>
                        
                        <form id="editStudentForm" class="space-y-4">
                            <input type="hidden" id="editStudentId" value="${student.Student_ID}">
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1" for="editName">Name</label>
                                    <input type="text" id="editName" class="w-full bg-dark-1 border border-gray-700 rounded px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light" value="${student.Name}">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1" for="editYear">Year Level</label>
                                    <select id="editYear" class="w-full bg-dark-1 border border-gray-700 rounded px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                        <option value="1" ${student.Year === '1' ? 'selected' : ''}>1st Year</option>
                                        <option value="2" ${student.Year === '2' ? 'selected' : ''}>2nd Year</option>
                                        <option value="3" ${student.Year === '3' ? 'selected' : ''}>3rd Year</option>
                                        <option value="4" ${student.Year === '4' ? 'selected' : ''}>4th Year</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1" for="editCollege">Course</label>
                                    <input type="text" id="editCollege" class="w-full bg-dark-1 border border-gray-700 rounded px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light" value="${student.College || 'CCS'}">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1" for="editGender">Gender</label>
                                    <select id="editGender" class="w-full bg-dark-1 border border-gray-700 rounded px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                        <option value="M" ${student.Gender === 'M' ? 'selected' : ''}>Male</option>
                                        <option value="F" ${student.Gender === 'F' ? 'selected' : ''}>Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1" for="editEmail">Email</label>
                                    <input type="email" id="editEmail" class="w-full bg-dark-1 border border-gray-700 rounded px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light" value="${student.Email || ''}">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1" for="editPhone">Phone</label>
                                    <input type="tel" id="editPhone" class="w-full bg-dark-1 border border-gray-700 rounded px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light" value="${student.Phone || ''}">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1" for="editStatus">Status</label>
                                    <select id="editStatus" class="w-full bg-dark-1 border border-gray-700 rounded px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                        <option value="Active" ${student.Status === 'Active' ? 'selected' : ''}>Active</option>
                                        <option value="Inactive" ${student.Status === 'Inactive' ? 'selected' : ''}>Inactive</option>
                                        <option value="On Leave" ${student.Status === 'On Leave' ? 'selected' : ''}>On Leave</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-400 mb-1" for="editAttendance">Attendance</label>
                                    <input type="number" id="editAttendance" min="0" class="w-full bg-dark-1 border border-gray-700 rounded px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light" value="${student.Attendance || 0}">
                                </div>
                            </div>
                            
                            <div class="flex space-x-3 mt-6">
                                <button type="button" class="flex-1 py-2 px-4 bg-dark-3 text-light rounded hover:bg-dark-4 transition-colors back-to-details" data-id="${student.Student_ID}">
                                    Cancel
                                </button>
                                <button type="submit" class="flex-1 py-2 px-4 bg-teal text-dark rounded hover:bg-teal-light transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    `;
                    
                    // Back to details button
                    document.querySelectorAll('.back-to-details').forEach(button => {
                        button.addEventListener('click', function() {
                            const studentId = this.getAttribute('data-id');
                            viewStudentDetails(studentId);
                        });
                    });
                    
                    // Handle form submission
                    const editForm = document.getElementById('editStudentForm');
                    editForm.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        // Get values from form
                        const studentId = document.getElementById('editStudentId').value;
                        const name = document.getElementById('editName').value;
                        const year = document.getElementById('editYear').value;
                        const college = document.getElementById('editCollege').value;
                        const gender = document.getElementById('editGender').value;
                        const email = document.getElementById('editEmail').value;
                        const phone = document.getElementById('editPhone').value;
                        const status = document.getElementById('editStatus').value;
                        const attendance = document.getElementById('editAttendance').value;
                        
                        // Create form data
                        const formData = new FormData();
                        formData.append('action', 'update');
                        formData.append('student_id', studentId);
                        formData.append('name', name);
                        formData.append('year', year);
                        formData.append('college', college);
                        formData.append('gender', gender);
                        formData.append('email', email);
                        formData.append('phone', phone);
                        formData.append('status', status);
                        formData.append('attendance', attendance);
                        
                        // Send AJAX request
                        fetch('../includes/student_handler.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Show success message
                                modalContent.innerHTML = `
                                    <div class="text-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-green-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <h3 class="text-lg font-medium text-light mb-2">Student Updated</h3>
                                        <p class="text-gray-400 mb-4">The student information has been updated successfully.</p>
                                        <div class="flex space-x-3">
                                            <button class="flex-1 py-2 px-4 bg-dark-3 text-light rounded hover:bg-dark-4 transition-colors close-modal">
                                                Close
                                            </button>
                                            <button class="flex-1 py-2 px-4 bg-teal text-dark rounded hover:bg-teal-light transition-colors view-updated" data-id="${studentId}">
                                                View Details
                                            </button>
                                        </div>
                                    </div>
                                `;
                                
                                // Reload student data
                                if (typeof loadStudents === 'function') {
                                    loadStudents(true);
                                }
                                
                                // Set up close button
                                document.querySelectorAll('.close-modal').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        modal.classList.add('hidden');
                                    });
                                });
                                
                                // Set up view updated button
                                document.querySelectorAll('.view-updated').forEach(btn => {
                                    btn.addEventListener('click', function() {
                                        const studentId = this.getAttribute('data-id');
                                        viewStudentDetails(studentId);
                                    });
                                });
                            } else {
                                alert(data.message || 'Error updating student.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while updating the student.');
                        });
                    });
                }
                
                // Delete student confirmation
                function deleteStudentConfirm(student) {
                    const modal = document.getElementById('confirmationModal');
                    const modalContent = document.getElementById('confirmationModalContent');
                    
                    modalContent.innerHTML = `
                        <div class="p-6">
                            <h3 class="text-xl font-medium text-light mb-4">Delete Student</h3>
                            <p class="text-gray-400 mb-6">Are you sure you want to delete <span class="text-light">${student.Name}</span>? This action cannot be undone.</p>
                            
                            <div class="flex space-x-3">
                                <button class="flex-1 py-2 px-4 bg-dark-3 text-light rounded hover:bg-dark-4 transition-colors cancel-delete">
                                    Cancel
                                </button>
                                <button class="flex-1 py-2 px-4 bg-red-600 text-white rounded hover:bg-red-700 transition-colors confirm-delete" data-id="${student.Student_ID}">
                                    Delete
                                </button>
                            </div>
                        </div>
                    `;
                    
                    modal.classList.remove('hidden');
                    
                    // Cancel button event
                    document.querySelectorAll('.cancel-delete').forEach(button => {
                        button.addEventListener('click', function() {
                            modal.classList.add('hidden');
                        });
                    });
                    
                    // Confirm delete button event
                    document.querySelectorAll('.confirm-delete').forEach(button => {
                        button.addEventListener('click', function() {
                            const studentId = this.getAttribute('data-id');
                            
                            fetch(`../includes/api/delete_student.php?id=${studentId}`, {
                                method: 'DELETE'
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Hide the confirmation modal
                                    modal.classList.add('hidden');
                                    
                                    // Hide the student details modal if it's open
                                    document.getElementById('studentModal').classList.add('hidden');
                                    
                                    // Show success notification
                                    showNotification('Student deleted successfully', 'success');
                                    
                                    // Refresh the student list
                                    fetchStudentsList();
                                } else {
                                    showNotification('Error deleting student: ' + data.message, 'error');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                showNotification('An error occurred while deleting the student', 'error');
                            });
                        });
                    });
                }
                
                // Mark attendance modal
                function markStudentAttendance(studentId) {
                    fetch(`../includes/api/fetch_student_details.php?id=${studentId}`)
                        .then(response => response.json())
                        .then(student => {
                            if (!student) {
                                console.error('Student not found');
                                return;
                            }
                            
                            const modal = document.getElementById('studentModal');
                            const modalContent = document.getElementById('studentModalContent');
                            
                            modalContent.innerHTML = `
                                <div class="text-center mb-4">
                                    <h3 class="text-lg font-medium text-light">Mark Attendance</h3>
                                    <p class="text-gray-400">${student.Name} (${student.Student_ID})</p>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="block text-sm text-gray-400 mb-1">Event</label>
                                    <select class="w-full bg-dark-1 border border-gray-700 rounded px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                        <option>Programming Competition</option>
                                        <option>Web Development Workshop</option>
                                        <option>Industry Talk: AI Trends</option>
                                    </select>
                                </div>
                                
                                <div class="flex space-x-3 mt-6">
                                    <button class="flex-1 py-2 px-4 bg-dark-3 text-light rounded hover:bg-dark-4 transition-colors close-modal">
                                        Cancel
                                    </button>
                                    <button class="flex-1 py-2 px-4 bg-teal text-dark rounded hover:bg-teal-light transition-colors save-attendance" data-id="${student.Student_ID}">
                                        Save
                                    </button>
                                </div>
                            `;
                            
                            modal.classList.remove('hidden');
                            
                            // Set up modal actions
                            document.querySelectorAll('.close-modal').forEach(button => {
                                button.addEventListener('click', function() {
                                    modal.classList.add('hidden');
                                });
                            });
                            
                            document.querySelectorAll('.save-attendance').forEach(button => {
                                button.addEventListener('click', function() {
                                    // Show success message
                                    modalContent.innerHTML = `
                                        <div class="text-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-green-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <h3 class="text-lg font-medium text-light mb-2">Attendance Recorded</h3>
                                            <p class="text-gray-400 mb-4">The attendance has been saved successfully.</p>
                                            <button class="w-full py-2 px-4 bg-teal text-dark rounded hover:bg-teal-light transition-colors close-modal">
                                                Close
                                            </button>
                                        </div>
                                    `;
                                    
                                    // Setup close button again
                                    document.querySelectorAll('.close-modal').forEach(btn => {
                                        btn.addEventListener('click', function() {
                                            modal.classList.add('hidden');
                                        });
                                    });
                                    
                                    // Auto close after 2 seconds
                                    setTimeout(() => {
                                        modal.classList.add('hidden');
                                    }, 2000);
                                });
                            });
                        })
                        .catch(error => {
                            console.error('Error fetching student details:', error);
                        });
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
                
                if (typeof window.initializeEventsPage === 'function') {
                    window.initializeEventsPage();
                } else {
                    console.error('Events page initialization function not found');
                }
            })
            .catch(error => {
                console.error('Error loading events content:', error);
                document.getElementById('tab-content').innerHTML = '<div class="p-4">Error loading content. Please try again.</div>';
            });
    }
});