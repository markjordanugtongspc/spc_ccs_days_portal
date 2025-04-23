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
            // Keep email but remove password if "Remember Me" is unchecked
            localStorage.setItem('rememberedEmail', email);
            localStorage.removeItem('rememberedPassword');
            localStorage.removeItem('rememberMe');
        }
    }

    // Function to update Philippines time
    function updatePhilippinesTime() {
        // Set to Philippines timezone (Asia/Manila)
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
        
        // Get hours for time of day indicator (in 24-hour format)
        const hours = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Manila', hour12: false })).getHours();
        
        // Update time of day indicator
        updateTimeOfDayIndicator(hours);
        
        // Format time
        const timeString = now.toLocaleTimeString('en-US', options);
        if (timeElement) {
            timeElement.textContent = timeString;
        }
        
        // Format date in two ways
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

        // Reset error messages
        emailError.classList.add('hidden');
        passwordError.classList.add('hidden');
        loginMessage.classList.add('hidden');

        const email = emailInput.value.trim();
        const password = passwordInput.value.trim();
        const rememberMe = rememberCheckbox.checked;

        // Validate email
        if (!validateEmail(email)) {
            emailError.classList.remove('hidden');
            isValid = false;
        }

        // Validate password
        if (password === '') {
            passwordError.classList.remove('hidden');
            isValid = false;
        }

        // If validation passes, check credentials
        if (isValid) {
            // Always save the email for convenience
            localStorage.setItem('rememberedEmail', email);
            
            // Save credentials if Remember Me is checked
            if (rememberMe) {
                saveCredentials(email, password, rememberMe);
            } else {
                // If not checked, remove saved password and remember flag
                localStorage.removeItem('rememberedPassword');
                localStorage.removeItem('rememberMe');
            }
            
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            formData.append('remember', rememberMe ? '1' : '0');

            // Show loading indicator or disable button here if needed
            
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

    // Theme toggle functionality (placeholder for future implementation)
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            // This will be implemented later
            console.log('Theme toggle clicked');
        });
    }
    // Handle tab switching in dashboard if elements exist
    const tabItems = document.querySelectorAll('.tab-item');
    const tabContent = document.getElementById('tab-content');
    
    if (tabItems.length > 0 && tabContent) {
        tabItems.forEach(tab => {
            tab.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                
                // Update active tab
                tabItems.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
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
                    // Show dashboard content
                    loadDashboardContent();
                    // Set up auto-refresh for dashboard content
                    startDashboardAutoRefresh();
                } else if (tabName === 'students') {
                    // Fetch students content
                    loadStudentsContent();
                } else if (tabName === 'events') {
                    // Fetch events content
                    loadEventsContent();
                }
            });
        });
    }
    
    // Auto-refresh intervals
    let dashboardRefreshInterval = null;
    let studentsRefreshInterval = null;
    
    // Function to start dashboard auto-refresh
    function startDashboardAutoRefresh() {
        // Clear any existing interval
        if (dashboardRefreshInterval) {
            clearInterval(dashboardRefreshInterval);
        }
        
        // Set up new interval - refresh every 30 seconds
        dashboardRefreshInterval = setInterval(() => {
            // Only refresh if dashboard tab is active
            const activeTab = document.querySelector('.tab-item.active');
            if (activeTab && activeTab.getAttribute('data-tab') === 'dashboard') {
                refreshDashboardStats();
            }
        }, 30000); // 30 seconds
    }
    
    // Function to refresh dashboard statistics
    function refreshDashboardStats() {
        // Update time elements
        updatePhilippinesTime();
        
        // In a real implementation, you would fetch updated stats from the server
        // For now, we'll just update the time elements
    }
    
    // Function to load dashboard content
    function loadDashboardContent() {
        // Get the dashboard content from the page
        const dashboardContent = document.getElementById('dashboard-content');
        if (dashboardContent) {
            // Clone the dashboard content
            const content = dashboardContent.cloneNode(true);
            // Display the content
            document.getElementById('tab-content').innerHTML = '';
            document.getElementById('tab-content').appendChild(content);
            // Make sure the content is visible
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
                
                // Initialize students page functionality
                if (typeof window.initializeStudentsPage === 'function') {
                    window.initializeStudentsPage();
                } else {
                    console.error('Students page initialization function not found');
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
                
                // Initialize events page functionality
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