// Common JavaScript functionality for all dashboard pages

document.addEventListener('DOMContentLoaded', function() {
    // Get current page path
    const currentPath = window.location.pathname;
    
    // Find all sidebar links
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    
    // Loop through links and add active class to the matching one
    sidebarLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        
        // Special case for events.php to ensure it stays active
        if (linkPath === 'events.php' && currentPath.endsWith('/events.php')) {
            // Keep or add the active class for the Events link on the events page
            link.classList.add('active');
            // Let the CSS handle the styling through the active class
        }
        // For other links, add active class if the current path matches
        else if (currentPath.includes(linkPath) && linkPath !== "#") {
            link.classList.add('active');
        } else {
            // Don't remove active class from Events link on events.php
            if (!(linkPath === 'events.php' && currentPath.endsWith('/events.php'))) {
                link.classList.remove('active');
            }
        }
    });
    
    // Tab navigation
    const tabItems = document.querySelectorAll('.tab-item');
    
    tabItems.forEach((tab, index) => {
        tab.addEventListener('click', function() {
            tabItems.forEach(item => item.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Theme toggle functionality
    const themeToggle = document.getElementById('themeToggle');
    if (themeToggle) {
        themeToggle.addEventListener('click', function() {
            document.body.classList.toggle('light-theme');
            
            // Save theme preference to localStorage
            if (document.body.classList.contains('light-theme')) {
                localStorage.setItem('theme', 'light');
            } else {
                localStorage.setItem('theme', 'dark');
            }
        });
        
        // Check for saved theme preference
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'light') {
            document.body.classList.add('light-theme');
        }
    }
    
    // Logout functionality
    const logoutForm = document.querySelector('form[action="../includes/logout.php"]');
    if (logoutForm) {
        logoutForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Clear localStorage items related to login
            localStorage.removeItem('rememberedEmail');
            localStorage.removeItem('rememberedPassword');
            localStorage.removeItem('rememberMe');
            
            // Submit the form to process server-side logout
            this.submit();
        });
    }
}); 