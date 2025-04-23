// Common JavaScript functionality for all dashboard pages

document.addEventListener('DOMContentLoaded', function() {
    // Get current page path
    const currentPath = window.location.pathname;
    
    // Find all sidebar links
    const sidebarLinks = document.querySelectorAll('.sidebar-link');
    
    // Loop through links and add active class to the matching one
    sidebarLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        
        // Check if current page matches the link
        if (currentPath.includes(linkPath) && linkPath !== "#") {
            link.classList.add('active');
        } else {
            link.classList.remove('active');
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