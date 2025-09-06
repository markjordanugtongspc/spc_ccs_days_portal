// Students page specific JavaScript

document.addEventListener('DOMContentLoaded', initializeStudentsPage);

// Global variable to store currently selected student
let currentStudent = null;

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
        // Normalize student fields for both API-fetched and PHP-filtered data
        students = students.map(student => ({
            ...student,
            Student_ID: student.Student_ID || student.id,
            Name: student.Name || student.name,
            Year: student.Year || student.year,
            College: student.College || student.course,
            Attendance: student.Attendance !== undefined ? student.Attendance : student.attendance
        }));
        
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
                    <button class="text-teal-light hover:text-teal transition-colors flex items-center" onclick="generateStudentQR('${student.Student_ID}')">
                        <svg
  width="24"
  height="24"
  viewBox="0 0 24 24"
  fill="none"
  xmlns="http://www.w3.org/2000/svg"
>
  <path
    fill-rule="evenodd"
    clip-rule="evenodd"
    d="M9 3H3V9H5V5H9V3ZM3 21V15H5V19H9V21H3ZM15 3V5H19V9H21V3H15ZM19 15H21V21H15V19H19V15ZM7 7H11V11H7V7ZM7 13H11V17H7V13ZM17 7H13V11H17V7ZM13 13H17V17H13V13Z"
    fill="currentColor"
  />
</svg>
                        Generate QR
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
                resetModalState();
            });
        });
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
                resetModalState();
            }
        });
    }
    
    // Setup edit form functionality
    const editStudentForm = document.getElementById('editStudentForm');
    if (editStudentForm) {
        editStudentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            updateStudent();
        });
    }
    
    // Cancel edit button
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    if (cancelEditBtn) {
        cancelEditBtn.addEventListener('click', function() {
            showStudentDetails();
        });
    }
    
    // Cancel delete button
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
            showStudentDetails();
        });
    }
    
    // Confirm delete button
    const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', function() {
            deleteStudent();
        });
    }
}

// Reset modal state to default (showing details)
function resetModalState() {
    const modalContent = document.getElementById('modalContent');
    const editFormContent = document.getElementById('editFormContent');
    const deleteConfirmContent = document.getElementById('deleteConfirmContent');
    
    if (modalContent) modalContent.classList.remove('hidden');
    if (editFormContent) editFormContent.classList.add('hidden');
    if (deleteConfirmContent) deleteConfirmContent.classList.add('hidden');
    
    currentStudent = null;
}

// Show student details view
function showStudentDetails() {
    const modalContent = document.getElementById('modalContent');
    const editFormContent = document.getElementById('editFormContent');
    const deleteConfirmContent = document.getElementById('deleteConfirmContent');
    
    if (modalContent) modalContent.classList.remove('hidden');
    if (editFormContent) editFormContent.classList.add('hidden');
    if (deleteConfirmContent) deleteConfirmContent.classList.add('hidden');
}

// Show edit form with student data pre-filled
function showEditForm() {
    if (!currentStudent) return;
    
    const modalContent = document.getElementById('modalContent');
    const editFormContent = document.getElementById('editFormContent');
    const deleteConfirmContent = document.getElementById('deleteConfirmContent');
    
    // Hide details and delete confirmation, show edit form
    if (modalContent) modalContent.classList.add('hidden');
    if (editFormContent) editFormContent.classList.remove('hidden');
    if (deleteConfirmContent) deleteConfirmContent.classList.add('hidden');
    
    // Fill the form with current student data
    document.getElementById('edit_student_id').value = currentStudent.Student_ID;
    document.getElementById('edit_name').value = currentStudent.Name;
    document.getElementById('edit_year').value = currentStudent.Year;
    document.getElementById('edit_college').value = currentStudent.College || 'CCS';
    document.getElementById('edit_gender').value = currentStudent.Gender;
    document.getElementById('edit_attendance').value = currentStudent.Attendance || 0;
    document.getElementById('edit_email').value = currentStudent.Email || '';
    document.getElementById('edit_phone').value = currentStudent.Phone || '';
    document.getElementById('edit_status').value = currentStudent.Status || 'Active';
}

// Show delete confirmation
function showDeleteConfirmation() {
    if (!currentStudent) return;
    
    const modalContent = document.getElementById('modalContent');
    const editFormContent = document.getElementById('editFormContent');
    const deleteConfirmContent = document.getElementById('deleteConfirmContent');
    
    // Hide details and edit form, show delete confirmation
    if (modalContent) modalContent.classList.add('hidden');
    if (editFormContent) editFormContent.classList.add('hidden');
    if (deleteConfirmContent) deleteConfirmContent.classList.remove('hidden');
}

// Update student data
async function updateStudent() {
    const formData = new FormData(document.getElementById('editStudentForm'));
    formData.append('action', 'update');
    
    try {
        const response = await fetch('../includes/student_handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            const modal = document.getElementById('studentModal');
            const modalContent = document.getElementById('modalContent');
            const editFormContent = document.getElementById('editFormContent');
            
            if (editFormContent) editFormContent.classList.add('hidden');
            if (modalContent) {
                modalContent.classList.remove('hidden');
                modalContent.innerHTML = `
                    <div class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-green-500 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xl font-medium text-light mb-2">Student Updated</h3>
                        <p class="text-gray-400 mb-4">The student information has been updated successfully.</p>
                        <button class="w-full py-2 px-4 bg-teal text-dark font-medium rounded-md hover:bg-teal-light transition-colors close-modal">
                            Close
                        </button>
                    </div>
                `;
                
                // Add event listener to new close button
                document.querySelectorAll('.close-modal').forEach(button => {
                    button.addEventListener('click', () => {
                        modal.classList.add('hidden');
                        resetModalState();
                        // Refresh the page to show updated data
                        window.location.reload();
                    });
                });
                
                // Auto close after 2 seconds
                setTimeout(() => {
                    modal.classList.add('hidden');
                    resetModalState();
                    // Refresh the page to show updated data
                    window.location.reload();
                }, 2000);
            }
        } else {
            alert(result.message || 'Error updating student.');
        }
    } catch (error) {
        console.error('Error updating student:', error);
        alert('An error occurred while updating the student.');
    }
}

// Delete student
async function deleteStudent() {
    if (!currentStudent) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('student_id', currentStudent.Student_ID);
    
    try {
        const response = await fetch('../includes/student_handler.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show success message
            const modal = document.getElementById('studentModal');
            const modalContent = document.getElementById('modalContent');
            const deleteConfirmContent = document.getElementById('deleteConfirmContent');
            
            if (deleteConfirmContent) deleteConfirmContent.classList.add('hidden');
            if (modalContent) {
                modalContent.classList.remove('hidden');
                modalContent.innerHTML = `
                    <div class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-green-500 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xl font-medium text-light mb-2">Student Deleted</h3>
                        <p class="text-gray-400 mb-4">The student has been deleted successfully.</p>
                        <button class="w-full py-2 px-4 bg-teal text-dark font-medium rounded-md hover:bg-teal-light transition-colors close-modal">
                            Close
                        </button>
                    </div>
                `;
                
                // Add event listener to new close button
                document.querySelectorAll('.close-modal').forEach(button => {
                    button.addEventListener('click', () => {
                        modal.classList.add('hidden');
                        resetModalState();
                        // Refresh the page to show updated data
                        window.location.reload();
                    });
                });
                
                // Auto close after 2 seconds
                setTimeout(() => {
                    modal.classList.add('hidden');
                    resetModalState();
                    // Refresh the page to show updated data
                    window.location.reload();
                }, 2000);
            }
        } else {
            alert(result.message || 'Error deleting student.');
        }
    } catch (error) {
        console.error('Error deleting student:', error);
        alert('An error occurred while deleting the student.');
    }
}

// View student details - Updated to include edit and delete buttons
function viewStudentDetails(studentId) {
    fetch(`../includes/api/fetch_student_details.php?id=${studentId}`)
        .then(response => response.json())
        .then(student => {
            if (!student || student.error) {
                console.error('Student not found');
                return;
            }
            
            // Store current student for edit/delete operations
            currentStudent = student;
            
            const modal = document.getElementById('studentModal');
            const modalContent = document.getElementById('modalContent');
            
            // Create initials
            const names = student.Name.split(' ');
            const initials = names[0].charAt(0) + (names.length > 1 ? names[names.length - 1].charAt(0) : '');
            
            // Determine attendance class
            const attendance = parseInt(student.Attendance) || 0;
            let attendanceClass = 'text-yellow-500';
            if (attendance > 6) attendanceClass = 'text-green-500';
            else if (attendance < 3) attendanceClass = 'text-red-500';
            
            modalContent.innerHTML = `
                <div class="flex items-start mb-8">
                    <div class="h-14 w-14 rounded-full bg-gradient-to-r from-teal-800 to-teal-700 flex items-center justify-center text-lg font-bold text-teal-light">
                        ${initials}
                    </div>
                    <div class="ml-4">
                        <h3 class="text-xl font-medium text-light">${student.Name}</h3>
                        <div class="flex items-center mt-1">
                            <span class="text-gray-400">${student.Student_ID}</span>
                            <span class="mx-2 text-gray-600">•</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-900/30 text-teal-light">
                                ${student.Year} Year
                            </span>
                        </div>
                    </div>
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Course</p>
                        <p class="text-light">${student.College || 'CCS'}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Gender</p>
                        <p class="text-light">${student.Gender === 'M' ? 'Male' : 'Female'}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Email</p>
                        <p class="text-light">${student.Email || 'Not provided'}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Phone</p>
                        <p class="text-light">${student.Phone || 'Not provided'}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Status</p>
                        <p class="text-light">${student.Status || 'Active'}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-sm mb-1">Attendance</p>
                        <p class="text-light ${attendanceClass}">${attendance} events</p>
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button id="editStudentBtn" class="px-4 py-2 bg-teal text-dark rounded-md hover:bg-teal-light transition-colors flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21h-9.5A2.25 2.25 0 014 18.75v-9.5A2.25 2.25 0 016.25 7H11" />
                        </svg>
                        Edit
                    </button>
                    <button id="deleteStudentBtn" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        Delete
                    </button>
                </div>
            `;
            
            // Create the edit form content if it doesn't exist
            let editFormContent = document.getElementById('editFormContent');
            if (!editFormContent) {
                editFormContent = document.createElement('div');
                editFormContent.id = 'editFormContent';
                editFormContent.className = 'hidden p-6';
                modal.appendChild(editFormContent);
            }
            
            // Create edit form
            editFormContent.innerHTML = `
                <h3 class="text-xl font-medium text-light mb-6">Edit Student Information</h3>
                <form id="editStudentForm" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <input type="hidden" id="edit_student_id" name="student_id" value="${student.Student_ID}">
                        
                        <div class="col-span-2">
                            <label for="edit_name" class="block text-sm font-medium text-gray-400 mb-1">Full Name</label>
                            <input type="text" id="edit_name" name="name" value="${student.Name}" class="w-full p-2 rounded-md bg-dark-1 border border-gray-700 text-light focus:border-teal-light focus:ring-0">
                        </div>
                        
                        <div>
                            <label for="edit_year" class="block text-sm font-medium text-gray-400 mb-1">Year Level</label>
                            <select id="edit_year" name="year" class="w-full p-2 rounded-md bg-dark-1 border border-gray-700 text-light focus:border-teal-light focus:ring-0">
                                <option value="1" ${student.Year == 1 ? 'selected' : ''}>1st Year</option>
                                <option value="2" ${student.Year == 2 ? 'selected' : ''}>2nd Year</option>
                                <option value="3" ${student.Year == 3 ? 'selected' : ''}>3rd Year</option>
                                <option value="4" ${student.Year == 4 ? 'selected' : ''}>4th Year</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="edit_college" class="block text-sm font-medium text-gray-400 mb-1">College/Course</label>
                            <input type="text" id="edit_college" name="college" value="${student.College || 'CCS'}" class="w-full p-2 rounded-md bg-dark-1 border border-gray-700 text-light focus:border-teal-light focus:ring-0">
                        </div>
                        
                        <div>
                            <label for="edit_gender" class="block text-sm font-medium text-gray-400 mb-1">Gender</label>
                            <select id="edit_gender" name="gender" class="w-full p-2 rounded-md bg-dark-1 border border-gray-700 text-light focus:border-teal-light focus:ring-0">
                                <option value="M" ${student.Gender === 'M' ? 'selected' : ''}>Male</option>
                                <option value="F" ${student.Gender === 'F' ? 'selected' : ''}>Female</option>
                            </select>
                        </div>
                        
                        <div>
                            <label for="edit_attendance" class="block text-sm font-medium text-gray-400 mb-1">Attendance</label>
                            <input type="number" id="edit_attendance" name="attendance" value="${attendance}" min="0" class="w-full p-2 rounded-md bg-dark-1 border border-gray-700 text-light focus:border-teal-light focus:ring-0">
                        </div>
                        
                        <div class="col-span-2">
                            <label for="edit_email" class="block text-sm font-medium text-gray-400 mb-1">Email</label>
                            <input type="email" id="edit_email" name="email" value="${student.Email || ''}" class="w-full p-2 rounded-md bg-dark-1 border border-gray-700 text-light focus:border-teal-light focus:ring-0">
                        </div>
                        
                        <div>
                            <label for="edit_phone" class="block text-sm font-medium text-gray-400 mb-1">Phone</label>
                            <input type="text" id="edit_phone" name="phone" value="${student.Phone || ''}" class="w-full p-2 rounded-md bg-dark-1 border border-gray-700 text-light focus:border-teal-light focus:ring-0">
                        </div>
                        
                        <div>
                            <label for="edit_status" class="block text-sm font-medium text-gray-400 mb-1">Status</label>
                            <select id="edit_status" name="status" class="w-full p-2 rounded-md bg-dark-1 border border-gray-700 text-light focus:border-teal-light focus:ring-0">
                                <option value="Active" ${(student.Status === 'Active' || !student.Status) ? 'selected' : ''}>Active</option>
                                <option value="Inactive" ${student.Status === 'Inactive' ? 'selected' : ''}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" id="cancelEditBtn" class="px-4 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-teal text-dark rounded-md hover:bg-teal-light transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            `;
            
            // Create the delete confirmation content if it doesn't exist
            let deleteConfirmContent = document.getElementById('deleteConfirmContent');
            if (!deleteConfirmContent) {
                deleteConfirmContent = document.createElement('div');
                deleteConfirmContent.id = 'deleteConfirmContent';
                deleteConfirmContent.className = 'hidden p-6';
                modal.appendChild(deleteConfirmContent);
            }
            
            // Create delete confirmation
            deleteConfirmContent.innerHTML = `
                <div class="text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <h3 class="text-xl font-medium text-light mb-2">Confirm Deletion</h3>
                    <p class="text-gray-400 mb-6">Are you sure you want to delete this student? This action cannot be undone.</p>
                    <div class="flex justify-center space-x-4">
                        <button id="cancelDeleteBtn" class="px-6 py-2 bg-gray-700 text-white rounded-md hover:bg-gray-600 transition-colors">
                            Cancel
                        </button>
                        <button id="confirmDeleteBtn" class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                            Delete
                        </button>
                    </div>
                </div>
            `;
            
            // Make sure we're viewing the details panel (not edit or delete)
            showStudentDetails();
            
            // Open the modal
            modal.classList.remove('hidden');
            
            // Add event listeners for edit and delete buttons
            const editBtn = document.getElementById('editStudentBtn');
            if (editBtn) {
                editBtn.addEventListener('click', showEditForm);
            }
            
            const deleteBtn = document.getElementById('deleteStudentBtn');
            if (deleteBtn) {
                deleteBtn.addEventListener('click', showDeleteConfirmation);
            }
            
            // Setup edit form functionality
            const editStudentForm = document.getElementById('editStudentForm');
            if (editStudentForm) {
                editStudentForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    updateStudent();
                });
            }
            
            // Cancel edit button
            const cancelEditBtn = document.getElementById('cancelEditBtn');
            if (cancelEditBtn) {
                cancelEditBtn.addEventListener('click', function() {
                    showStudentDetails();
                });
            }
            
            // Cancel delete button
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            if (cancelDeleteBtn) {
                cancelDeleteBtn.addEventListener('click', function() {
                    showStudentDetails();
                });
            }
            
            // Confirm delete button
            const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', function() {
                    deleteStudent();
                });
            }
        })
        .catch(error => {
            console.error('Error fetching student details:', error);
        });
}

// Function to mark attendance (remains the same)
function generateStudentQR(studentId) {
    // Implementation remains unchanged
}

// QR Code Generator Function (uses cached local path from backend)
async function generateStudentQR(studentId) {
    const modal = document.getElementById('studentModal');
    const modalContent = document.getElementById('modalContent');
    try {
        const res = await fetch(`../includes/api/qr_generator.php?student_id=${encodeURIComponent(studentId)}&size=300x300&ecc=L&qzone=4`);
        const data = await res.json();
        if (!data.success) throw new Error(data.message || 'QR generation failed.');
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
                    <button class="px-4 py-2 bg-dark-3 text-light rounded-md hover:bg-dark-4 transition-colors close-modal">Close</button>
                </div>
            </div>
        `;
        modal.classList.remove('hidden');
        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => modal.classList.add('hidden'));
        });
    } catch (error) {
        console.error('Error generating QR:', error);
        alert(error.message);
    }
}

// Make these functions globally available
window.initializeStudentsPage = initializeStudentsPage; 
window.viewStudentDetails = viewStudentDetails;
window.generateStudentQR = generateStudentQR;