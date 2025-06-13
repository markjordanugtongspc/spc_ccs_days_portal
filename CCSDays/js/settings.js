document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.tab-item[data-tab]');
    const tabContents = document.querySelectorAll('[id$="-settings"]');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active state from all buttons
            tabButtons.forEach(btn => {
                btn.classList.remove('active');
            });

            // Add active state to clicked button
            button.classList.add('active');

            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.add('hidden');
            });

            // Show selected tab content
            const tabId = button.getAttribute('data-tab');
            const targetContent = document.getElementById(`${tabId}-settings`);
            if (targetContent) {
                targetContent.classList.remove('hidden');
                
                // Add fade-in animation
                targetContent.style.opacity = '0';
                targetContent.style.transform = 'translateY(10px)';
                
                // Trigger animation
                setTimeout(() => {
                    targetContent.style.transition = 'all 0.3s ease-in-out';
                    targetContent.style.opacity = '1';
                    targetContent.style.transform = 'translateY(0)';
                }, 10);
            }
        });
    });

    // Toggle switches functionality
    const toggleSwitches = document.querySelectorAll('input[type="checkbox"]');
    
    toggleSwitches.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const settingId = this.id;
            const isEnabled = this.checked;
            
            // Save setting to localStorage
            localStorage.setItem(settingId, isEnabled);
            
            // Handle specific settings
            switch(settingId) {
                case 'systemStatus':
                    handleSystemStatus(isEnabled);
                    break;
                case 'maintenanceMode':
                    handleMaintenanceMode(isEnabled);
                    break;
                case 'darkMode':
                    handleDarkMode(isEnabled);
                    break;
                // Add more cases for other settings
            }
        });

        // Load saved settings
        const savedState = localStorage.getItem(toggle.id);
        if (savedState !== null) {
            toggle.checked = savedState === 'true';
        }
    });

    // Select dropdowns functionality
    const selectDropdowns = document.querySelectorAll('select');
    
    selectDropdowns.forEach(select => {
        select.addEventListener('change', function() {
            const settingId = this.id || this.getAttribute('data-setting');
            const value = this.value;
            
            // Save setting to localStorage
            localStorage.setItem(settingId, value);
            
            // Handle specific settings
            switch(settingId) {
                case 'sessionTimeout':
                    handleSessionTimeout(value);
                    break;
                case 'fontSize':
                    handleFontSize(value);
                    break;
                // Add more cases for other settings
            }
        });

        // Load saved settings
        const savedValue = localStorage.getItem(select.id || select.getAttribute('data-setting'));
        if (savedValue !== null) {
            select.value = savedValue;
        }
    });
});

// Settings handlers
function handleSystemStatus(enabled) {
    // Implement system status change logic
    console.log('System status:', enabled ? 'enabled' : 'disabled');
}

function handleMaintenanceMode(enabled) {
    // Implement maintenance mode logic
    console.log('Maintenance mode:', enabled ? 'enabled' : 'disabled');
}

function handleDarkMode(enabled) {
    if (enabled) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
}

function handleSessionTimeout(value) {
    // Implement session timeout logic
    console.log('Session timeout set to:', value, 'minutes');
}

function handleFontSize(value) {
    // Implement font size change logic
    document.documentElement.style.fontSize = {
        'small': '14px',
        'medium': '16px',
        'large': '18px'
    }[value];
} 