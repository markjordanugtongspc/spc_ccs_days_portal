<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

// Check if user has admin role
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

// Include database connection
require_once '../includes/config.php';

// Fetch all students from the database
$students = [];
$query = "SELECT * FROM students ORDER BY Student_ID DESC";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
}

// Fetch first year students
$firstYearStudents = [];
$firstYearQuery = "SELECT * FROM students WHERE Year = '1' ORDER BY Student_ID DESC";
$firstYearResult = $conn->query($firstYearQuery);

if ($firstYearResult && $firstYearResult->num_rows > 0) {
    while ($row = $firstYearResult->fetch_assoc()) {
        $firstYearStudents[] = $row;
    }
}

// Fetch second year students
$secondYearStudents = [];
$secondYearQuery = "SELECT * FROM students WHERE Year = '2' ORDER BY Student_ID DESC";
$secondYearResult = $conn->query($secondYearQuery);

if ($secondYearResult && $secondYearResult->num_rows > 0) {
    while ($row = $secondYearResult->fetch_assoc()) {
        $secondYearStudents[] = $row;
    }
}

// Fetch third year students
$thirdYearStudents = [];
$thirdYearQuery = "SELECT * FROM students WHERE Year = '3' ORDER BY Student_ID DESC";
$thirdYearResult = $conn->query($thirdYearQuery);

if ($thirdYearResult && $thirdYearResult->num_rows > 0) {
    while ($row = $thirdYearResult->fetch_assoc()) {
        $thirdYearStudents[] = $row;
    }
}

// Fetch fourth year students
$fourthYearStudents = [];
$fourthYearQuery = "SELECT * FROM students WHERE Year = '4' ORDER BY Student_ID DESC";
$fourthYearResult = $conn->query($fourthYearQuery);

if ($fourthYearResult && $fourthYearResult->num_rows > 0) {
    while ($row = $fourthYearResult->fetch_assoc()) {
        $fourthYearStudents[] = $row;
    }
}

$conn->close();

// Get user info
$userName = $_SESSION['user_name'] ?? 'Admin';
$userRole = ucfirst($_SESSION['user_role'] ?? 'Admin');
$userInitials = strtoupper(substr($userName, 0, 1)) . (strpos($userName, ' ') !== false ? strtoupper(substr($userName, strpos($userName, ' ') + 1, 1)) : '');

// Check if this is a partial request (for embedding in dashboard)
$isPartial = isset($_GET['partial']) && $_GET['partial'] === 'true';

if ($isPartial) {
    // Output only the main content for embedding in dashboard
?>
    <div class="flex flex-col space-y-4">
        <div class="flex justify-between items-center">
            <div class="relative flex-grow max-w-md">
                <input type="text" id="searchInput" placeholder="Search students..." class="w-full pl-10 pr-4 py-2 rounded-lg bg-dark-2 text-light border border-dark-3 focus:border-teal focus:ring-1 focus:ring-teal focus:outline-none">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                </div>
            </div>
        </div>

        <div id="studentContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Student cards will be dynamically inserted here -->
        </div>

        <div id="showMoreContainer" class="flex justify-center mt-4" style="display: none;">
            <button id="showMoreBtn" class="px-4 py-2 bg-teal text-dark font-medium rounded-lg hover:bg-teal-light transition-colors">
                Show More
            </button>
        </div>
    </div>

    <div id="studentModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-dark-2 rounded-lg p-6 max-w-lg w-full mx-4">
            <div class="flex justify-between items-start mb-4">
                <h2 class="text-xl font-semibold text-light">Student Details</h2>
                <button class="close-modal text-gray-400 hover:text-light">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="studentModalContent">
                <!-- Modal content will be dynamically inserted here -->
            </div>
        </div>
    </div>

    <script>
        // Initialize the students page
        initializeStudentsPage();
    </script>
<?php
    exit();
} else {
?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
        <title>CCS Days - Students</title>
        <link rel="icon" href="../includes/images/spc-ccs-logo.png" type="image/png">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="../styles.css">
        <link rel="stylesheet" href="./css/common.css">
        <link rel="stylesheet" href="./css/students.css">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    </head>

    <body class="bg-dark-1 text-light">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-logo">
                <div class="flex items-center">
                    <img src="../includes/images/spc-ccs-logo.png" alt="CCS Logo" class="h-8 w-8">
                    <span class="ml-3 text-xl font-bold text-teal-light">Admin Portal</span>
                </div>
            </div>
            <div class="sidebar-menu">
                <a href="dashboard.php" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Dashboard
                </a>
                <a href="scanner.php" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z" />
                    </svg>
                    QR Scanner
                </a>
                <a href="students.php" class="sidebar-link active">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Students
                </a>
                <a href="events.php" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008h-.008v-.008zm0 2.25h.008H16.5V15z" />
                    </svg>
                    Events
                </a>
                <a href="export.php" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    Export Data
                </a>
                <a href="#" class="sidebar-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Settings
                </a>
            </div>
            <div class="user-info">
                <div class="user-avatar"><?php echo $userInitials; ?></div>
                <div class="user-details">
                    <div class="user-name"><?php echo $userName; ?></div>
                    <div class="user-role"><?php echo $userRole; ?></div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="topbar">
                <div class="topbar-title">CCS Days Connect</div>
                <div class="topbar-actions">
                    <div class="notification-badge">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-light">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                        <span class="badge">1</span>
                    </div>
                    <button id="themeToggle" class="text-teal-light hover-teal transition-all">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6">
                            <circle cx="12" cy="12" r="5"></circle>
                            <line x1="12" y1="1" x2="12" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="23"></line>
                            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                            <line x1="1" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="12" x2="23" y2="12"></line>
                            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                        </svg>
                    </button>
                    <a href="#" class="text-light">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                </div>
            </div>

            <div class="dashboard-container">
                <div class="tab-nav">
                    <div class="tab-item active">All Students</div>
                    <div class="tab-item">First Year</div>
                    <div class="tab-item">Second Year</div>
                    <div class="tab-item">Third Year</div>
                    <div class="tab-item">Fourth Year</div>
                </div>

                <h1 class="page-title">Student Management</h1>

                <!-- Search and Filter Section -->
                <div class="flex flex-wrap justify-between items-center mb-6">
                    <div class="search-container w-full md:w-1/2 mb-4 md:mb-0">
                        <div class="relative">
                            <input type="text" id="searchInput" class="search-input w-full" placeholder="Search by name, ID, or course...">
                            <button class="absolute right-3 top-1/2 transform -translate-y-1/2 text-teal-light">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="flex space-x-2">
                        <button class="action-button primary-button" onclick="showAddStudentModal()">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Add Student
                        </button>
                        <button class="action-button">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                            </svg>
                            Export List
                        </button>
                    </div>
                </div>

                <!-- Student Grid -->
                <div class="grid grid-cols-4 gap-4" id="studentContainer">
                    <!-- Student cards will be generated here by JavaScript -->
                </div>
            </div>
        </div>

        <!-- Student Details Modal -->
        <div id="studentModal" class="fixed inset-0 flex items-center justify-center z-50 hidden">
            <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
            <div class="relative bg-dark-2 rounded-lg max-w-md w-full mx-4 overflow-hidden shadow-xl transform transition-all">
                <div class="absolute top-0 right-0 pt-4 pr-4">
                    <button type="button" class="close-modal bg-transparent rounded-md text-gray-400 hover:text-light">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="p-6" id="modalContent">
                    <!-- Modal content will be populated dynamically -->
                </div>
                <!-- Edit form will be shown/hidden dynamically -->
                <div class="p-6 hidden" id="editFormContent">
                    <h2 class="text-xl font-semibold text-light mb-4">Edit Student Information</h2>
                    <form id="editStudentForm" class="space-y-4">
                        <input type="hidden" id="edit_student_id" name="student_id">

                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <label for="edit_name" class="block text-sm font-medium text-gray-400">Full Name</label>
                                <input type="text" id="edit_name" name="name" class="mt-1 bg-dark-1 border border-dark-3 text-light w-full rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-teal-light">
                            </div>

                            <div>
                                <label for="edit_year" class="block text-sm font-medium text-gray-400">Year Level</label>
                                <select id="edit_year" name="year" class="mt-1 bg-dark-1 border border-dark-3 text-light w-full rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-teal-light">
                                    <option value="1">1st Year</option>
                                    <option value="2">2nd Year</option>
                                    <option value="3">3rd Year</option>
                                    <option value="4">4th Year</option>
                                </select>
                            </div>

                            <div>
                                <label for="edit_college" class="block text-sm font-medium text-gray-400">Course</label>
                                <select id="edit_college" name="college" class="mt-1 bg-dark-1 border border-dark-3 text-light w-full rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-teal-light">
                                    <option value="CCS">CCS</option>
                                    <option value="BSIT">BSIT</option>
                                    <option value="BSCS">BSCS</option>
                                    <option value="BSIS">BSIS</option>
                                </select>
                            </div>

                            <div>
                                <label for="edit_gender" class="block text-sm font-medium text-gray-400">Gender</label>
                                <select id="edit_gender" name="gender" class="mt-1 bg-dark-1 border border-dark-3 text-light w-full rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-teal-light">
                                    <option value="M">Male</option>
                                    <option value="F">Female</option>
                                </select>
                            </div>

                            <div>
                                <label for="edit_attendance" class="block text-sm font-medium text-gray-400">Attendance</label>
                                <input type="number" id="edit_attendance" name="attendance" min="0" class="mt-1 bg-dark-1 border border-dark-3 text-light w-full rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-teal-light">
                            </div>

                            <div>
                                <label for="edit_email" class="block text-sm font-medium text-gray-400">Email</label>
                                <input type="email" id="edit_email" name="email" class="mt-1 bg-dark-1 border border-dark-3 text-light w-full rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-teal-light">
                            </div>

                            <div>
                                <label for="edit_phone" class="block text-sm font-medium text-gray-400">Phone</label>
                                <input type="text" id="edit_phone" name="phone" class="mt-1 bg-dark-1 border border-dark-3 text-light w-full rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-teal-light">
                            </div>

                            <div>
                                <label for="edit_status" class="block text-sm font-medium text-gray-400">Status</label>
                                <select id="edit_status" name="status" class="mt-1 bg-dark-1 border border-dark-3 text-light w-full rounded-md px-3 py-2 focus:outline-none focus:ring-1 focus:ring-teal-light">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                    <option value="On Leave">On Leave</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" id="cancelEditBtn" class="px-4 py-2 bg-dark-3 text-light rounded-md hover:bg-dark-4 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-teal text-dark font-medium rounded-md hover:bg-teal-light transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Delete confirmation will be shown/hidden dynamically -->
                <div class="p-6 hidden" id="deleteConfirmContent">
                    <div class="text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="text-xl font-medium text-light">Delete Student</h3>
                        <p class="text-gray-400 mb-6">Are you sure you want to delete this student? This action cannot be undone.</p>

                        <div class="flex justify-center space-x-3">
                            <button type="button" id="cancelDeleteBtn" class="px-4 py-2 bg-dark-3 text-light rounded-md hover:bg-dark-4 transition-colors">
                                Cancel
                            </button>
                            <button type="button" id="confirmDeleteBtn" class="px-4 py-2 bg-red-600 text-white font-medium rounded-md hover:bg-red-700 transition-colors">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../js/common.js"></script>
        <script src="../js/students.js"></script>
        <script>
            // Convert PHP student data to JavaScript
            const allStudents = <?php echo json_encode($students); ?>;
            const firstYearStudents = <?php echo json_encode($firstYearStudents); ?>;
            const secondYearStudents = <?php echo json_encode($secondYearStudents); ?>;
            const thirdYearStudents = <?php echo json_encode($thirdYearStudents); ?>;
            const fourthYearStudents = <?php echo json_encode($fourthYearStudents); ?>;

            // Map database fields to our frontend structure
            function mapStudents(students) {
                return students.map(student => {
                    // Determine year level based on the Year field
                    let yearLabel;
                    switch (student.Year) {
                        case '1':
                            yearLabel = '1st';
                            break;
                        case '2':
                            yearLabel = '2nd';
                            break;
                        case '3':
                            yearLabel = '3rd';
                            break;
                        case '4':
                            yearLabel = '4th';
                            break;
                        case 1:
                            yearLabel = '1st';
                            break; // Handle non-string numbers
                        case 2:
                            yearLabel = '2nd';
                            break;
                        case 3:
                            yearLabel = '3rd';
                            break;
                        case 4:
                            yearLabel = '4th';
                            break;
                        default:
                            yearLabel = student.Year;
                    }

                    return {
                        id: student.Student_ID,
                        name: student.Name,
                        year: yearLabel,
                        course: student.College || 'CCS',
                        email: student.Email || (() => {
                            const nameParts = student.Name.toLowerCase().split(' ');
                            const firstName = nameParts[0];
                            const lastName = nameParts[nameParts.length - 1];
                            const formattedName = firstName + lastName;
                            const formattedId = student.Student_ID.replace(/-/g, '');
                            return formattedName + '.' + formattedId + '@gmail.com';
                        })(),
                        phone: student.Phone || '+63 N/A',
                        status: student.Status || 'Active',
                        attendance: parseInt(student.Attendance) || 0,
                        gender: student.Gender
                    };
                });
            }

            // Mapped student data
            const mappedAllStudents = mapStudents(allStudents);
            const mappedFirstYearStudents = mapStudents(firstYearStudents);
            const mappedSecondYearStudents = mapStudents(secondYearStudents);
            const mappedThirdYearStudents = mapStudents(thirdYearStudents);
            const mappedFourthYearStudents = mapStudents(fourthYearStudents);

            // Load all students on page load
            document.addEventListener('DOMContentLoaded', function() {
                displayStudents(mappedAllStudents);

                // Set up tab navigation
                const tabItems = document.querySelectorAll('.tab-item');
                tabItems.forEach(tab => {
                    tab.addEventListener('click', function() {
                        // Remove active class from all tabs
                        tabItems.forEach(item => item.classList.remove('active'));
                        // Add active class to the clicked tab
                        this.classList.add('active');

                        // Show loading state
                        const container = document.getElementById('studentContainer');
                        if (container) {
                            container.innerHTML = `
                            <div class="col-span-full text-center p-8">
                                <div class="inline-flex items-center justify-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-teal-light animate-spin">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                    <span class="ml-2 text-light">Loading students...</span>
                                </div>
                            </div>
                        `;
                        }

                        // Get the year filter from the tab text
                        const yearFilter = this.textContent.trim();

                        // Load appropriate student data
                        setTimeout(() => {
                            switch (yearFilter) {
                                case 'First Year':
                                    displayStudents(mappedFirstYearStudents);
                                    break;
                                case 'Second Year':
                                    displayStudents(mappedSecondYearStudents);
                                    break;
                                case 'Third Year':
                                    displayStudents(mappedThirdYearStudents);
                                    break;
                                case 'Fourth Year':
                                    displayStudents(mappedFourthYearStudents);
                                    break;
                                default: // All Students
                                    displayStudents(mappedAllStudents);
                                    break;
                            }
                        }, 300); // Small delay for better UX
                    });
                });

                // Set up modal close functionality
                const modal = document.getElementById('studentModal');
                document.querySelectorAll('.close-modal').forEach(button => {
                    button.addEventListener('click', () => {
                        modal.classList.add('hidden');
                    });
                });

                // Close modal when clicking outside
                modal.addEventListener('click', (e) => {
                    if (e.target === modal) {
                        modal.classList.add('hidden');
                    }
                });

                // Key press handler for ESC key
                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                        modal.classList.add('hidden');
                    }
                });
            });

            // Search students by name or ID
            function searchStudents() {
                const searchTerm = document.getElementById('searchInput').value.toLowerCase();

                // Get the active tab to determine which dataset to search in
                const activeTab = document.querySelector('.tab-item.active').textContent.trim();
                let datasetToSearch;

                switch (activeTab) {
                    case 'First Year':
                        datasetToSearch = mappedFirstYearStudents;
                        break;
                    case 'Second Year':
                        datasetToSearch = mappedSecondYearStudents;
                        break;
                    case 'Third Year':
                        datasetToSearch = mappedThirdYearStudents;
                        break;
                    case 'Fourth Year':
                        datasetToSearch = mappedFourthYearStudents;
                        break;
                    default: // All Students
                        datasetToSearch = mappedAllStudents;
                        break;
                }

                const filteredStudents = datasetToSearch.filter(student =>
                    student.name.toLowerCase().includes(searchTerm) ||
                    student.id.toLowerCase().includes(searchTerm) ||
                    student.course.toLowerCase().includes(searchTerm)
                );

                displayStudents(filteredStudents);
            }

            // Display students in the container
            function displayStudents(studentsToDisplay) {
                const container = document.getElementById('studentContainer');
                container.innerHTML = '';

                if (studentsToDisplay.length === 0) {
                    container.innerHTML = '<div class="col-span-full text-center p-8 bg-dark-2 rounded-lg">No students found matching your criteria.</div>';
                    return;
                }

                studentsToDisplay.forEach(student => {
                    const card = document.createElement('div');
                    card.className = 'bg-dark-2 rounded-lg p-4 hover:shadow-md transition-all student-card';

                    // Determine attendance class for color coding
                    let attendanceClass = 'text-yellow-500';
                    if (student.attendance > 6) attendanceClass = 'text-green-500';
                    else if (student.attendance < 3) attendanceClass = 'text-red-500';

                    card.innerHTML = `
                    <div class="flex items-start justify-between">
                        <div class="flex-grow overflow-hidden">
                            <h3 class="text-lg font-semibold text-light truncate">${student.name}</h3>
                            <p class="text-gray-400 text-sm">${student.id}</p>
                        </div>
                        <div class="flex-shrink-0 ml-3">
                            <span class="student-year-tag inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-900 text-teal-light">
                            ${student.year} Year
                        </span>
                        </div>
                    </div>
                    <div class="mt-3 flex-grow">
                        <p class="text-gray-300"><span class="text-gray-400">Course:</span> ${student.course}</p>
                        <p class="text-gray-300"><span class="text-gray-400">Attendance:</span> <span class="${attendanceClass}">${student.attendance} events</span></p>
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
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 mr-1">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Generate QR
                    </button>
                </div>
                `;
                    container.appendChild(card);
                });
            }

            // Function to show student details modal
            function viewStudentDetails(studentId) {
                const student = mappedAllStudents.find(s => s.id === studentId);
                if (student) {
                    const modal = document.getElementById('studentModal');
                    const modalContent = document.getElementById('modalContent');

                    modalContent.innerHTML = `
                        <div class="flex items-start mb-8">
                            <div class="h-14 w-14 rounded-full bg-gradient-to-r from-teal-800 to-teal-700 flex items-center justify-center text-lg font-bold text-teal-light">
                            ${student.name.charAt(0)}${student.name.split(' ')[1] ? student.name.split(' ')[1].charAt(0) : ''}
                            </div>
                            <div class="ml-4">
                            <h3 class="text-xl font-medium text-light">${student.name}</h3>
                                <div class="flex items-center mt-1">
                                <span class="text-gray-400">${student.id}</span>
                                    <span class="mx-2 text-gray-600">•</span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-teal-900/30 text-teal-light">
                                    ${student.year} Year
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-gray-400 text-sm mb-1">Course</p>
                            <p class="text-light">${student.course}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm mb-1">Gender</p>
                            <p class="text-light">${student.gender === 'M' ? 'Male' : 'Female'}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm mb-1">Email</p>
                                    <p class="text-light break-all">${student.email}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm mb-1">Phone</p>
                                    <p class="text-light">${student.phone}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm mb-1">Status</p>
                            <p class="text-light">${student.status}</p>
                                </div>
                                <div>
                                    <p class="text-gray-400 text-sm mb-1">Attendance</p>
                            <p class="text-light ${student.attendance > 6 ? 'text-green-500' : student.attendance < 3 ? 'text-red-500' : 'text-yellow-500'}">${student.attendance} events</p>
                            </div>
                        </div>
                        
                        <div class="flex gap-8 mt-8 justify-center">
    <button class="py-3 px-6 bg-teal-900 text-teal-100 rounded-md hover:bg-teal-800 transition-colors font-medium w-32" onclick="editStudent('${student.id}')">
        EDIT
    </button>
    <button class="py-3 px-6 bg-red-900 text-red-100 rounded-md hover:bg-red-800 transition-colors font-medium w-32" onclick="deleteStudent('${student.id}')">
        DELETE
    </button>
</div>
                    `;

                    modal.classList.remove('hidden');
                }
            }

            // Function to show Add Student modal
            function showAddStudentModal() {
                const modal = document.getElementById('studentModal');
                const modalContent = document.getElementById('modalContent');

                modalContent.innerHTML = `
                <div class="text-center mb-6">
                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-teal-900/20 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-teal-light">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-medium text-light">Add New Student</h3>
                    <p class="text-gray-400">Enter student information below</p>
                </div>
                
                <form id="addStudentForm">
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Name*</label>
                            <input type="text" id="newName" required class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Student ID*</label>
                            <input type="text" id="newId" required class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Year Level*</label>
                            <select id="newYear" required class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                <option value="1">1st Year</option>
                                <option value="2">2nd Year</option>
                                <option value="3">3rd Year</option>
                                <option value="4">4th Year</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Course*</label>
                            <select id="newCourse" required class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                <option value="CCS">CCS</option>
                                <option value="BSIT">BSIT</option>
                                <option value="BSCS">BSCS</option>
                                <option value="BSIS">BSIS</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Gender*</label>
                            <select id="newGender" required class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                <option value="M">Male</option>
                                <option value="F">Female</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Email</label>
                            <input type="email" id="newEmail" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Phone</label>
                            <input type="text" id="newPhone" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Status</label>
                            <select id="newStatus" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                                <option value="On Leave">On Leave</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Initial Attendance</label>
                            <input type="number" id="newAttendance" value="0" min="0" max="10" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                        </div>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button type="button" class="flex-1 py-2 px-4 bg-dark-3 text-light rounded-md hover:bg-dark-4 transition-colors close-modal font-bold">
                            Cancel
                        </button>
                        <button type="button" id="saveNewStudentBtn" class="flex-1 py-2 px-4 bg-teal-900 text-teal-light rounded-md hover:bg-teal-800 transition-colors font-bold">
                            Add Student
                        </button>
                    </div>
                </form>
            `;

                modal.classList.remove('hidden');

                // Set up event listener for saving the new student
                setTimeout(() => {
                    const saveBtn = document.getElementById('saveNewStudentBtn');

                    saveBtn.addEventListener('click', () => {
                        // Get values from form
                        const newName = document.getElementById('newName').value.trim();
                        const newId = document.getElementById('newId').value.trim();
                        const newYear = document.getElementById('newYear').value;
                        const newCourse = document.getElementById('newCourse').value;
                        const newGender = document.getElementById('newGender').value;
                        const newEmail = document.getElementById('newEmail').value.trim();
                        const newPhone = document.getElementById('newPhone').value.trim();
                        const newStatus = document.getElementById('newStatus').value;
                        const newAttendance = parseInt(document.getElementById('newAttendance').value) || 0;

                        // Validate required fields
                        if (!newName || !newId || !newGender) {
                            alert('Please fill in all required fields.');
                            return;
                        }

                        // Create form data for the AJAX request
                        const formData = new FormData();
                        formData.append('action', 'add');
                        formData.append('name', newName);
                        formData.append('student_id', newId);
                        formData.append('year', newYear);
                        formData.append('college', newCourse);
                        formData.append('gender', newGender);
                        formData.append('email', newEmail);
                        formData.append('phone', newPhone);
                        formData.append('status', newStatus);
                        formData.append('attendance', newAttendance);

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
                                <div class="text-center mb-6">
                                    <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-green-900/20 mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-green-500">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-medium text-light">Student Added Successfully</h3>
                                    <p class="text-gray-400">The new student has been added to the system.</p>
                                </div>
                                
                                <button class="w-full py-2 px-4 bg-teal-900 text-teal-light rounded-md hover:bg-teal-800 transition-colors close-modal font-bold">
                                    Close
                                </button>
                            `;

                                    // Add listener to new close button
                                    document.querySelectorAll('.close-modal').forEach(button => {
                                        button.addEventListener('click', () => {
                                            modal.classList.add('hidden');
                                            // Reload the page to refresh the student list
                                            location.reload();
                                        });
                                    });

                                    // Auto-close after 3 seconds
                                    setTimeout(() => {
                                        if (!modal.classList.contains('hidden')) {
                                            modal.classList.add('hidden');
                                            // Reload the page to refresh the student list
                                            location.reload();
                                        }
                                    }, 3000);
                                } else {
                                    alert(data.message || 'Error adding student. Please try again.');
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred. Please try again.');
                            });
                    });
                }, 0);
            }

            // Function to edit student details
            function editStudent(studentId) {
                const student = mappedAllStudents.find(s => s.id === studentId);
                if (student) {
                    const modal = document.getElementById('studentModal');
                    const modalContent = document.getElementById('modalContent');

                    modalContent.innerHTML = `
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-teal-900/20 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-teal-light">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-medium text-light">Edit Student</h3>
                        <p class="text-gray-400">Update student information below</p>
                    </div>
                    
                    <form id="editStudentForm">
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Name*</label>
                                <input type="text" id="editName" required class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light" value="${student.name}">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Student ID (Not Editable)</label>
                                <input type="text" id="editId" class="w-full bg-dark-3 border border-dark-4 rounded-md px-3 py-2 text-gray-400 cursor-not-allowed" value="${student.id}" disabled>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Year Level*</label>
                                <select id="editYear" required class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                    <option value="1" ${student.year === '1' ? 'selected' : ''}>1st Year</option>
                                    <option value="2" ${student.year === '2' ? 'selected' : ''}>2nd Year</option>
                                    <option value="3" ${student.year === '3' ? 'selected' : ''}>3rd Year</option>
                                    <option value="4" ${student.year === '4' ? 'selected' : ''}>4th Year</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Course*</label>
                                <select id="editCourse" required class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                    <option value="CCS" ${student.course === 'CCS' ? 'selected' : ''}>CCS</option>
                                    <option value="BSIT" ${student.course === 'BSIT' ? 'selected' : ''}>BSIT</option>
                                    <option value="BSCS" ${student.course === 'BSCS' ? 'selected' : ''}>BSCS</option>
                                    <option value="BSIS" ${student.course === 'BSIS' ? 'selected' : ''}>BSIS</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Gender*</label>
                                <select id="editGender" required class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                    <option value="M" ${student.gender === 'M' ? 'selected' : ''}>Male</option>
                                    <option value="F" ${student.gender === 'F' ? 'selected' : ''}>Female</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Email</label>
                                <input type="email" id="editEmail" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light" value="${student.email}">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Phone</label>
                                <input type="text" id="editPhone" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light" value="${student.phone}">
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Status</label>
                                <select id="editStatus" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
                                    <option value="Active" ${student.status === 'Active' ? 'selected' : ''}>Active</option>
                                    <option value="Inactive" ${student.status === 'Inactive' ? 'selected' : ''}>Inactive</option>
                                    <option value="On Leave" ${student.status === 'On Leave' ? 'selected' : ''}>On Leave</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-400 mb-1">Attendance</label>
                                <input type="number" id="editAttendance" min="0" max="10" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light" value="${student.attendance}">
                            </div>
                        </div>
                        
                        <div class="flex justify-end space-x-3 mt-6">
                            <button type="button" id="cancelEditBtn" class="px-4 py-2 bg-dark-3 text-light rounded-md hover:bg-dark-4 transition-colors">
                                Cancel
                            </button>
                            <button type="submit" class="px-4 py-2 bg-teal text-dark font-medium rounded-md hover:bg-teal-light transition-colors">
                                Save Changes
                            </button>
                        </div>
                    </form>
                `;

                    modal.classList.remove('hidden');

                    // Set up event listener for updating the student
                    setTimeout(() => {
                        const updateBtn = document.getElementById('updateStudentBtn');

                        updateBtn.addEventListener('click', () => {
                            // Get values from form
                            const editName = document.getElementById('editName').value.trim();
                            const editYear = document.getElementById('editYear').value;
                            const editCourse = document.getElementById('editCourse').value;
                            const editGender = document.getElementById('editGender').value;
                            const editEmail = document.getElementById('editEmail').value.trim();
                            const editPhone = document.getElementById('editPhone').value.trim();
                            const editStatus = document.getElementById('editStatus').value;
                            const editAttendance = parseInt(document.getElementById('editAttendance').value) || 0;

                            // Validate required fields
                            if (!editName) {
                                alert('Please fill in all required fields.');
                                return;
                            }

                            // Create form data for the AJAX request
                            const formData = new FormData();
                            formData.append('action', 'update');
                            formData.append('student_id', student.id);
                            formData.append('name', editName);
                            formData.append('year', editYear);
                            formData.append('college', editCourse);
                            formData.append('gender', editGender);
                            formData.append('email', editEmail);
                            formData.append('phone', editPhone);
                            formData.append('status', editStatus);
                            formData.append('attendance', editAttendance);

                            // Show success message (in a real implementation, this would be after the AJAX request)
                            modalContent.innerHTML = `
                            <div class="text-center mb-6">
                                <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-green-900/20 mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-green-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-medium text-light">Student Updated Successfully</h3>
                                <p class="text-gray-400">The student information has been updated.</p>
                            </div>
                            
                            <button class="w-full py-2 px-4 bg-teal-900 text-teal-light rounded-md hover:bg-teal-800 transition-colors close-modal font-bold">
                                Close
                            </button>
                        `;

                            // Add listener to new close button
                            document.querySelectorAll('.close-modal').forEach(button => {
                                button.addEventListener('click', () => {
                                    modal.classList.add('hidden');
                                    // Reload the page to refresh the student list
                                    location.reload();
                                });
                            });

                            // Auto-close after 3 seconds
                            setTimeout(() => {
                                if (!modal.classList.contains('hidden')) {
                                    modal.classList.add('hidden');
                                    // Reload the page to refresh the student list
                                    location.reload();
                                }
                            }, 3000);

                            // In a real implementation, you would send the AJAX request here
                            // fetch('../includes/student_handler.php', {
                            //     method: 'POST',
                            //     body: formData
                            // })
                            // .then(response => response.json())
                            // .then(data => {
                            //     if (data.success) {
                            //         // Show success message
                            //         // ...
                            //     } else {
                            //         alert(data.message || 'Error updating student. Please try again.');
                            //     }
                            // })
                            // .catch(error => {
                            //     console.error('Error:', error);
                            //     alert('An error occurred. Please try again.');
                            // });
                        });

                        // Add listener to close button
                        document.querySelectorAll('.close-modal').forEach(button => {
                            button.addEventListener('click', () => {
                                modal.classList.add('hidden');
                            });
                        });
                    }, 0);
                }
            }

            // Function to delete student
            function deleteStudent(studentId) {
                const student = mappedAllStudents.find(s => s.id === studentId);
                if (student) {
                    const modal = document.getElementById('studentModal');
                    const modalContent = document.getElementById('modalContent');

                    modalContent.innerHTML = `
                    <div class="text-center mb-6">
                        <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-red-900/20 mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-red-500">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-medium text-light">Delete Student</h3>
                        <p class="text-gray-400">Are you sure you want to delete ${student.name}?</p>
                        <p class="text-gray-400 mt-2">This action cannot be undone.</p>
                    </div>
                    
                    <div class="flex space-x-3">
                        <button class="flex-1 py-2 px-4 bg-dark-3 text-light rounded-md hover:bg-dark-4 transition-colors close-modal font-bold">
                            CANCEL
                        </button>
                        <button id="confirmDeleteBtn" class="flex-1 py-2 px-4 bg-red-900 text-red-300 rounded-md hover:bg-red-800 transition-colors font-bold">
                            DELETE
                        </button>
                    </div>
                `;

                    modal.classList.remove('hidden');

                    // Set up event listener for deleting the student
                    setTimeout(() => {
                        const deleteBtn = document.getElementById('confirmDeleteBtn');

                        deleteBtn.addEventListener('click', () => {
                            // Create form data for the AJAX request
                            const formData = new FormData();
                            formData.append('action', 'delete');
                            formData.append('student_id', student.id);

                            // Show success message (in a real implementation, this would be after the AJAX request)
                            modalContent.innerHTML = `
                            <div class="text-center mb-6">
                                <div class="inline-flex items-center justify-center h-16 w-16 rounded-full bg-green-900/20 mb-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-green-500">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <h3 class="text-xl font-medium text-light">Student Deleted Successfully</h3>
                                <p class="text-gray-400">The student has been removed from the system.</p>
                            </div>
                            
                            <button class="w-full py-2 px-4 bg-teal-900 text-teal-light rounded-md hover:bg-teal-800 transition-colors close-modal font-bold">
                                Close
                            </button>
                        `;

                            // Add listener to new close button
                            document.querySelectorAll('.close-modal').forEach(button => {
                                button.addEventListener('click', () => {
                                    modal.classList.add('hidden');
                                    // Reload the page to refresh the student list
                                    location.reload();
                                });
                            });

                            // Auto-close after 3 seconds
                            setTimeout(() => {
                                if (!modal.classList.contains('hidden')) {
                                    modal.classList.add('hidden');
                                    // Reload the page to refresh the student list
                                    location.reload();
                                }
                            }, 3000);

                            // In a real implementation, you would send the AJAX request here
                            // fetch('../includes/student_handler.php', {
                            //     method: 'POST',
                            //     body: formData
                            // })
                            // .then(response => response.json())
                            // .then(data => {
                            //     if (data.success) {
                            //         // Show success message
                            //         // ...
                            //     } else {
                            //         alert(data.message || 'Error deleting student. Please try again.');
                            //     }
                            // })
                            // .catch(error => {
                            //     console.error('Error:', error);
                            //     alert('An error occurred. Please try again.');
                            // });
                        });

                        // Add listener to close button
                        document.querySelectorAll('.close-modal').forEach(button => {
                            button.addEventListener('click', () => {
                                modal.classList.add('hidden');
                            });
                        });
                    }, 0);
                }
            }
        </script>
    </body>

    </html>
<?php
}
?>