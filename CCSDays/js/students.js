// Students page specific JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.querySelector('.student-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Implement student search functionality here
            const searchTerm = this.value.toLowerCase().trim();
            
            // For demonstration, we would filter student cards
            const studentCards = document.querySelectorAll('.student-card');
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
    const filterDropdown = document.querySelector('.filter-dropdown');
    if (filterDropdown) {
        filterDropdown.addEventListener('change', function() {
            const filterValue = this.value;
            const studentCards = document.querySelectorAll('.student-card');
            
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
    const pageButtons = document.querySelectorAll('.page-button');
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
}); 