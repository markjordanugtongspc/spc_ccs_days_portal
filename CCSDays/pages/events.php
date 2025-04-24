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

// Get user info
$userName = $_SESSION['user_name'] ?? 'Admin';
$userRole = ucfirst($_SESSION['user_role'] ?? 'Admin');
$userInitials = strtoupper(substr($userName, 0, 1)) . (strpos($userName, ' ') !== false ? strtoupper(substr($userName, strpos($userName, ' ') + 1, 1)) : '');

// Check if this is a partial request (for embedding in dashboard)
$isPartial = isset($_GET['partial']) && $_GET['partial'] === 'true';

// If it's a partial request, we'll only render the main content
if ($isPartial) {
    // Output just the main content for embedding in dashboard
?>
    <h1 class="page-title">Events Management</h1>
    
    <!-- Action Buttons -->
    <div class="flex gap-4 mb-6">
        <button id="createEventBtn" class="action-button bg-teal-light text-dark-1 hover:bg-teal-dark transition-all flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Create Event
        </button>
        <button id="pendingEventsBtn" class="action-button flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Pending Approvals
            <span class="ml-2 bg-teal-light text-dark-1 px-2 py-0.5 rounded-full text-xs">7</span>
        </button>
    </div>
    
    <!-- Events Table -->
    <div class="bg-dark-2 rounded-lg overflow-hidden mb-6">
        <table class="w-full text-left">
            <thead class="bg-dark-3 text-teal-light">
                <tr>
                    <th class="px-4 py-3">Event Name</th>
                    <th class="px-4 py-3">Date & Time</th>
                    <th class="px-4 py-3">Venue</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Reminder</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-3">
                <tr class="hover:bg-dark-3 transition-all">
                    <td class="px-4 py-3">Programming Competition</td>
                    <td class="px-4 py-3">2023-04-19 • 10:00 AM</td>
                    <td class="px-4 py-3">CCS Laboratory</td>
                    <td class="px-4 py-3"><span class="status-badge approved">approved</span></td>
                    <td class="px-4 py-3"><span class="text-teal-light">Active</span></td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button class="icon-button view-event" data-id="1">View</button>
                            <button class="icon-button edit-event hover:bg-teal-900 hover:text-teal-light transition-colors cursor-pointer active:scale-95 transform duration-100" data-id="1">Edit</button>
                        </div>
                    </td>
                </tr>
                <tr class="hover:bg-dark-3 transition-all">
                    <td class="px-4 py-3">Web Development Workshop</td>
                    <td class="px-4 py-3">2023-04-19 • 2:30 PM</td>
                    <td class="px-4 py-3">Multi-Purpose Hall</td>
                    <td class="px-4 py-3"><span class="status-badge pending">pending</span></td>
                    <td class="px-4 py-3"><span class="text-gray-400">Not set</span></td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <button class="icon-button view-event" data-id="2">View</button>
                            <button class="icon-button approve-event" data-id="2">Approve</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        // Initialize events page when loaded in dashboard
        window.initializeEventsPage = function() {
            // Set up event handlers for buttons
            document.querySelectorAll('.view-event').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.getAttribute('data-id');
                    // View event logic
                    console.log('View event:', eventId);
                });
            });
            
            document.querySelectorAll('.edit-event').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.getAttribute('data-id');
                    // Edit event logic
                    console.log('Edit event:', eventId);
                });
            });
            
            document.querySelectorAll('.approve-event').forEach(button => {
                button.addEventListener('click', function() {
                    const eventId = this.getAttribute('data-id');
                    // Approve event logic
                    console.log('Approve event:', eventId);
                });
            });
            
            // Create event button
            const createEventBtn = document.getElementById('createEventBtn');
            if (createEventBtn) {
                createEventBtn.addEventListener('click', function() {
                    // Create event logic
                    console.log('Create new event');
                });
            }
            
            // Pending events button
            const pendingEventsBtn = document.getElementById('pendingEventsBtn');
            if (pendingEventsBtn) {
                pendingEventsBtn.addEventListener('click', function() {
                    // Show pending events logic
                    console.log('Show pending events');
                });
            }
        };
    </script>
<?php
} else {
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>CCS Days - Events Management</title>
    <link rel="icon" href="../includes/images/spc-ccs-logo.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/dashboard.css">
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
            <a href="students.php" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
                Students
            </a>
            <a href="events.php" class="sidebar-link active">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
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
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>
                <a href="../includes/logout.php" class="text-light hover:text-teal-light transition-all" title="Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="dashboard-container">
            <h1 class="page-title">Events Management</h1>
            
            <!-- Action Buttons -->
            <div class="flex gap-4 mb-6">
                <button id="createEventBtn" class="action-button bg-teal-light text-dark-1 hover:bg-teal-dark transition-all flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Create Event
                </button>
                <button id="pendingEventsBtn" class="action-button flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Pending Approvals
                    <span class="ml-2 bg-teal-light text-dark-1 px-2 py-0.5 rounded-full text-xs">7</span>
                </button>
            </div>
            
            <!-- Events Table -->
            <div class="bg-dark-2 rounded-lg overflow-hidden mb-6">
                <table class="w-full text-left">
                    <thead class="bg-dark-3 text-teal-light">
                        <tr>
                            <th class="px-4 py-3">Event Name</th>
                            <th class="px-4 py-3">Date & Time</th>
                            <th class="px-4 py-3">Venue</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3">Reminder</th>
                            <th class="px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-3">
                        <tr class="hover:bg-dark-3 transition-all">
                            <td class="px-4 py-3">Programming Competition</td>
                            <td class="px-4 py-3">2023-04-19 • 10:00 AM</td>
                            <td class="px-4 py-3">CCS Laboratory</td>
                            <td class="px-4 py-3"><span class="status-badge approved">approved</span></td>
                            <td class="px-4 py-3"><span class="text-teal-light">Active</span></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button class="icon-button view-event" data-id="1">View</button>
                                    <button class="icon-button edit-event" data-id="1">Edit</button>
                                </div>
                            </td>
                        </tr>
                        <tr class="hover:bg-dark-3 transition-all">
                            <td class="px-4 py-3">Web Development Workshop</td>
                            <td class="px-4 py-3">2023-04-19 • 2:30 PM</td>
                            <td class="px-4 py-3">Multi-Purpose Hall</td>
                            <td class="px-4 py-3"><span class="status-badge pending">pending</span></td>
                            <td class="px-4 py-3"><span class="text-gray-400">Not set</span></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button class="icon-button view-event hover:bg-teal-900 hover:text-teal-light transition-colors cursor-pointer active:scale-95 transform duration-100" data-id="2">View</button>
                                    <button class="icon-button approve-event hover:bg-teal-900 hover:text-teal-light transition-colors cursor-pointer active:scale-95 transform duration-100" data-id="2">Approve</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Event Modal -->
    <div id="createEventModal" class="modal hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="modal-overlay absolute inset-0 bg-black opacity-70"></div>
        <div class="modal-container bg-dark-2 w-11/12 md:max-w-md mx-auto rounded-lg shadow-2xl z-50 overflow-y-auto transform transition-all">
            <div class="modal-content py-4 text-left px-6">
                <div class="flex justify-between items-center pb-3">
                    <p class="text-2xl font-bold text-teal-light">Create Event</p>
                    <div class="modal-close cursor-pointer z-50 hover:bg-dark-3 p-1 rounded-full transition-all">
                        <svg class="fill-current text-white" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18">
                            <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                        </svg>
                    </div>
                </div>
                <form id="createEventForm">
                    <div class="mb-4">
                        <label class="block text-light text-sm font-bold mb-2" for="eventName">Event Name</label>
                        <input class="bg-dark-1 appearance-none border border-gray-700 rounded w-full py-2 px-3 text-light leading-tight focus:outline-none focus:border-teal-light" id="eventName" type="text" placeholder="Enter event name">
                    </div>
                    <div class="mb-4">
                        <label class="block text-light text-sm font-bold mb-2" for="eventDate">Date & Time</label>
                        <input class="bg-dark-1 appearance-none border border-gray-700 rounded w-full py-2 px-3 text-light leading-tight focus:outline-none focus:border-teal-light" id="eventDate" type="datetime-local">
                    </div>
                    <div class="mb-4">
                        <label class="block text-light text-sm font-bold mb-2" for="eventVenue">Venue</label>
                        <input class="bg-dark-1 appearance-none border border-gray-700 rounded w-full py-2 px-3 text-light leading-tight focus:outline-none focus:border-teal-light" id="eventVenue" type="text" placeholder="Enter venue">
                    </div>
                    <div class="mb-4">
                        <label class="block text-light text-sm font-bold mb-2" for="eventDescription">Description</label>
                        <textarea class="bg-dark-1 appearance-none border border-gray-700 rounded w-full py-2 px-3 text-light leading-tight focus:outline-none focus:border-teal-light" id="eventDescription" placeholder="Enter event description" rows="3"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-light text-sm font-bold mb-2">Set Reminder</label>
                        <div class="flex items-center">
                            <input type="checkbox" id="enableReminder" class="mr-2">
                            <label for="enableReminder">Enable automatic reminder</label>
                        </div>
                        <div id="reminderOptions" class="mt-2 hidden">
                            <select class="bg-dark-1 border border-gray-700 rounded w-full py-2 px-3 text-light focus:outline-none focus:border-teal-light">
                                <option value="1h">1 hour before</option>
                                <option value="3h">3 hours before</option>
                                <option value="1d">1 day before</option>
                                <option value="3d">3 days before</option>
                                <option value="1w">1 week before</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="button" class="modal-close px-4 bg-transparent p-3 rounded-lg text-gray-400 hover:text-white mr-2">Cancel</button>
                        <button type="submit" class="px-4 bg-teal-light p-3 rounded-lg text-dark-1 hover:bg-teal-dark">Create Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Event Modal -->
    <div id="viewEventModal" class="modal hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="modal-overlay absolute inset-0 bg-black opacity-70"></div>
        <div class="modal-container bg-dark-2 w-11/12 md:max-w-md mx-auto rounded-lg shadow-2xl z-50 overflow-hidden transform transition-all">
            <div class="modal-content py-6 text-left px-8">
                <div class="flex justify-between items-center pb-4 border-b border-dark-3">
                    <h3 class="text-2xl font-bold text-teal-light select-text">Event Details</h3>
                    <div class="modal-close cursor-pointer z-50 hover:bg-dark-3 p-1 rounded-full transition-all">
                        <svg class="fill-current text-white" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 18 18">
                            <path d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z"></path>
                        </svg>
                    </div>
                </div>
                <div id="eventDetails" class="py-4 select-text">
                    <!-- Event details will be populated here -->
                </div>
                <div class="flex justify-end pt-3 border-t border-dark-3">
                    <button type="button" class="modal-close px-6 bg-teal-light p-3 rounded-lg text-dark-1 hover:bg-teal-dark font-bold transition-all">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/common.js"></script>
    <script src="../js/events.js"></script>
</body>
</html>
<?php
}
?>
