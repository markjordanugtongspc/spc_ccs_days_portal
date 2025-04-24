// Students page specific JavaScript

document.addEventListener('DOMContentLoaded', initializeStudentsPage);

// Extract initialization into a named function so it can be called both on page load
// and when loaded via AJAX in dashboard tabs
function initializeStudentsPage() {
    // Check if we're in the context of a loaded tab or the full page
    const isInDashboard = document.getElementById('tab-content') !== null;
    const containerSelector = isInDashboard ? '#tab-content' : '.dashboard-container';
    
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let currentSearchTerm = '';
    
    // Function to fetch students
    async function fetchStudents(page = 1, search = '') {
        try {
            isLoading = true;
            const response = await fetch(`../includes/api/fetch_students.php?page=${page}&search=${encodeURIComponent(search)}`);
            const data = await response.json();
            
            if (data.error) {
                throw new Error(data.error);
            }
            
            hasMore = data.hasMore;
            return data;
        } catch (error) {
            console.error('Error fetching students:', error);
            return null;
        } finally {
            isLoading = false;
        }
    }
    
    // Function to display students
    function displayStudents(students, replace = true) {
        const container = document.getElementById('studentContainer');
        const showMoreContainer = document.getElementById('showMoreContainer');
        
        if (replace) {
            container.innerHTML = '';
        }
        
        if (!students || students.length === 0) {
            if (replace) {
                container.innerHTML = '<div class="col-span-full text-center p-8 bg-dark-2 rounded-lg">No students found matching your criteria.</div>';
            }
            showMoreContainer.style.display = 'none';
            return;
        }
        
        students.forEach(student => {
            const card = document.createElement('div');
            card.className = 'bg-dark-2 rounded-lg p-4 hover:shadow-md transition-all student-card';
            
            let yearLabel;
            switch(student.Year) {
                case '1': yearLabel = '1st'; break;
                case '2': yearLabel = '2nd'; break;
                case '3': yearLabel = '3rd'; break;
                case '4': yearLabel = '4th'; break;
                case 1: yearLabel = '1st'; break;
                case 2: yearLabel = '2nd'; break;
                case 3: yearLabel = '3rd'; break;
                case 4: yearLabel = '4th'; break;
                default: yearLabel = student.Year;
            }
            
            let attendanceClass = 'text-yellow-500';
            const attendance = parseInt(student.Attendance) || 0;
            if (attendance > 6) attendanceClass = 'text-green-500';
            else if (attendance < 3) attendanceClass = 'text-red-500';
            
            card.innerHTML = `
                <div class="flex items-start justify-between">
                    <div class="flex-grow overflow-hidden">
                        <h3 class="text-lg font-semibold text-light truncate student-name">${student.Name}</h3>
                        <p class="text-gray-400 text-sm student-id">${student.Student_ID}</p>
                    </div>
                    <div class="flex-shrink-0 ml-3">
                        <span class="student-year-tag inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-900 text-teal-light">
                            ${yearLabel} Year
                        </span>
                    </div>
                </div>
                <div class="mt-3 flex-grow">
                    <p class="text-gray-300"><span class="text-gray-400">Course:</span> ${student.College || 'CCS'}</p>
                    <p class="text-gray-300"><span class="text-gray-400">Attendance:</span> <span class="${attendanceClass}">${attendance} events</span></p>
                </div>
                <div class="mt-4 flex justify-between">
                    <button class="text-teal-light hover:text-teal transition-colors flex items-center" onclick="viewStudentDetails('${student.Student_ID}')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Details
                    </button>
                    <button class="text-teal-light hover:text-teal transition-colors flex items-center" onclick="markStudentAttendance('${student.Student_ID}')">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Attendance
                    </button>
                </div>
            `;
            container.appendChild(card);
        });
        
        showMoreContainer.style.display = hasMore ? 'flex' : 'none';
    }
    
    // Initial load
    fetchStudents().then(data => {
        if (data) {
            displayStudents(data.students);
        }
    });
    
    // Set up search functionality
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(async () => {
                currentSearchTerm = this.value.trim();
                currentPage = 1;
                const data = await fetchStudents(1, currentSearchTerm);
                if (data) {
                    displayStudents(data.students);
                }
            }, 300);
        });
    }
    
    // Set up show more functionality
    const showMoreBtn = document.getElementById('showMoreBtn');
    if (showMoreBtn) {
        showMoreBtn.addEventListener('click', async () => {
            if (isLoading || !hasMore) return;
            
            currentPage++;
            const data = await fetchStudents(currentPage, currentSearchTerm);
            if (data) {
                displayStudents(data.students, false);
            }
        });
    }
    
    // Set up modal functionality
    const modal = document.getElementById('studentModal');
    const closeButtons = document.querySelectorAll('.close-modal');
    
    if (modal && closeButtons) {
        closeButtons.forEach(button => {
            button.addEventListener('click', () => {
                modal.classList.add('hidden');
            });
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
            }
        });
    }
}

// Make the initialization function available globally
window.initializeStudentsPage = initializeStudentsPage; 