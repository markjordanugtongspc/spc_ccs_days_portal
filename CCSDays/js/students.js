// Students page specific JavaScript

document.addEventListener('DOMContentLoaded', initializeStudentsPage);

// Extract initialization into a named function so it can be called both on page load
// and when loaded via AJAX in dashboard tabs
function initializeStudentsPage() {
    // Check if we're in the context of a loaded tab or the full page
    const isTabContent = document.getElementById('students-content') !== null;
    const containerSelector = isTabContent ? '#students-content' : '.dashboard-container';
    
    // Search functionality
    const searchInput = document.querySelector(`${containerSelector} .student-search`);
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Implement student search functionality here
            const searchTerm = this.value.toLowerCase().trim();
            
            // For demonstration, we would filter student cards
            const studentCards = document.querySelectorAll(`${containerSelector} .student-card`);
            studentCards.forEach(card => {
                const studentName = card.querySelector('.student-name').textContent.toLowerCase();
                const studentId = card.querySelector('.student-id').textContent.toLowerCase();
                
                if (studentName.includes(searchTerm) || studentId.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Filter dropdown
    const filterDropdown = document.querySelector(`${containerSelector} .filter-dropdown`);
    if (filterDropdown) {
        filterDropdown.addEventListener('change', function() {
            const filterValue = this.value;
            const studentCards = document.querySelectorAll(`${containerSelector} .student-card`);
            
            if (filterValue === 'all') {
                studentCards.forEach(card => {
                    card.style.display = 'block';
                });
                return;
            }
            
            studentCards.forEach(card => {
                const cardStatus = card.dataset.status;
                if (cardStatus === filterValue) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Pagination
    const pageButtons = document.querySelectorAll(`${containerSelector} .page-button`);
    if (pageButtons.length) {
        pageButtons.forEach(button => {
            button.addEventListener('click', function() {
                // Remove active class from all buttons
                pageButtons.forEach(btn => btn.classList.remove('active'));
                // Add active class to clicked button
                this.classList.add('active');
                
                // Here you would implement logic to show the corresponding page of students
                // For demonstration only
                const pageNum = this.textContent;
                console.log(`Showing page ${pageNum}`);
            });
        });
    }
    
    // Ensure tab navigation works within the students content
    const tabItems = document.querySelectorAll(`${containerSelector} .tab-item`);
    if (tabItems.length) {
        tabItems.forEach(tab => {
            tab.addEventListener('click', function() {
                // Remove active class from all tabs
                tabItems.forEach(item => item.classList.remove('active'));
                // Add active class to the clicked tab
                this.classList.add('active');
                
                // Implement tab content switching logic here
                // This would depend on your specific implementation
            });
        });
    }
}

// Make the initialization function available globally so it can be called from dashboard.php
window.initializeStudentsPage = initializeStudentsPage; 