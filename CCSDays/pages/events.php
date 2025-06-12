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

// Include database configuration after session_start
require_once '../includes/config.php';

// Initialize variables
$events = [];
$pendingCount = 0;
$approvedCount = 0;
$totalCount = 0;

// Check if database table exists
$tableExists = false;
try {
    // Log connection attempt
    error_log("Connecting to database: $servername, $username, $database, $port");
    $conn = new mysqli($servername, $username, $password, $database, $port);
    
    if ($conn->connect_error) {
        // Connection failed, but we'll continue with empty events
        $connectionError = $conn->connect_error;
    } else {
        // Check if events table exists
        $result = $conn->query("SHOW TABLES LIKE 'events'");
        if ($result && $result->num_rows > 0) {
            $tableExists = true;
            
            // Fetch events from database
            $sql = "SELECT * FROM events ORDER BY event_date DESC";
            $result = $conn->query($sql);
            
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $events[] = $row;
                }
            }
            
            // Count pending and approved events
            foreach ($events as $event) {
                if ($event['status'] === 'pending') {
                    $pendingCount++;
                }
                if ($event['status'] === 'approved') {
                    $approvedCount++;
                }
                $totalCount++;
            }
        }
        
        // Close connection
        $conn->close();
    }
} catch (Exception $e) {
    // Handle any exceptions
    $connectionError = $e->getMessage();
}

// Check if this is a partial request (for embedding in dashboard)
$isPartial = isset($_GET['partial']) && $_GET['partial'] === 'true';

// If it's a partial request, we'll only render the main content
if ($isPartial) {
    // Output just the main content for embedding in dashboard
?>
    <h1 class="page-title">Events Management</h1>
    
    <!-- Action Buttons -->
    <div class="flex gap-4 mb-6">
        <button id="createEventBtn" class="action-button bg-teal-light text-dark-1 hover:bg-teal-dark transition-all flex items-center cursor-pointer">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Create Event
        </button>
        <button id="allEventsBtn" class="action-button flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18M3 12h18M3 16h18" />
            </svg>
            All Events
            <span class="ml-2 bg-teal-light text-dark-1 px-2 py-0.5 rounded-full text-xs"><?php echo $totalCount; ?></span>
        </button>
        <button id="approvedEventsBtn" class="action-button flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            Approved Events
            <span class="ml-2 bg-teal-light text-dark-1 px-2 py-0.5 rounded-full text-xs"><?php echo $approvedCount; ?></span>
        </button>
        <button id="pendingEventsBtn" class="action-button flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Pending Approvals
            <span class="ml-2 bg-teal-light text-dark-1 px-2 py-0.5 rounded-full text-xs"><?php echo $pendingCount; ?></span>
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
                    <th class="px-4 py-2 text-center pl-4 pr-47">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-dark-3">
                <?php if (count($events) > 0): ?>
                    <?php foreach ($events as $event): ?>
                        <?php
                            // Format date for display
                            $dateObj = new DateTime($event['event_date']);
                            $formattedDate = $dateObj->format('Y-m-d • g:i A');
                            
                            // Determine reminder status
                            $reminderStatus = $event['reminder_enabled'] ?
                                '<span class="text-teal-light">Active</span>' :
                                '<span class="text-gray-400">Not set</span>';
                        ?>
                        <tr class="hover:bg-dark-3 transition-all">
                            <td class="px-4 py-3"><?php echo htmlspecialchars($event['name']); ?></td>
                            <td class="px-4 py-3"><?php echo $formattedDate; ?></td>
                            <td class="px-4 py-3"><?php echo htmlspecialchars($event['venue']); ?></td>
                            <td class="px-4 py-3"><span class="status-badge <?php echo $event['status']; ?>"><?php echo $event['status']; ?></span></td>
                            <td class="px-4 py-3"><?php echo $reminderStatus; ?></td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2">
                                    <button class="icon-button view-event inline-flex items-center cursor-pointer bg-dark-1 text-teal-light px-2 py-1 rounded-full hover:bg-dark-3 transition-colors" data-id="<?php echo $event['id']; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                                        </svg>
                                        View
                                    </button>
                                    <?php if ($event['status'] === 'pending'): ?>
                                        <button class="icon-button approve-event inline-flex items-center cursor-pointer bg-dark-1 text-teal-light px-2 py-1 rounded-full hover:bg-dark-3 transition-colors" data-id="<?php echo $event['id']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                            Approve
                                        </button>
                                    <?php else: ?>
                                        <button class="icon-button edit-event inline-flex items-center cursor-pointer bg-dark-1 text-teal-light px-2 py-1 rounded-full hover:bg-dark-3 transition-colors" data-id="<?php echo $event['id']; ?>">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M16.768 8.768L8 17.536V20h2.464l8.768-8.768-2.464-2.464z" />
                                            </svg>
                                            Edit
                                        </button>
                                    <?php endif; ?>
                                    <button class="icon-button delete-event inline-flex items-center cursor-pointer bg-dark-1 text-teal-light px-2 py-1 rounded-full hover:bg-dark-3 transition-colors" data-id="<?php echo $event['id']; ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-2 0v4m0 8v4m-4-4h8" />
                                        </svg>
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-400">No events found. Create your first event!</td>
                    </tr>
                <?php endif; ?>
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
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0121 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                </svg>
                Events
            </a>
            <a href="export.php" class="sidebar-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Export Data
            </a>
				<a href="settings.php" class="sidebar-link">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
						<path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V9M12 9l-3 3m0 0l3 3m-3-3h12.75" />
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
                <button id="allEventsBtn" class="action-button flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18M3 12h18M3 16h18" />
                    </svg>
                    All Events
                    <span class="ml-2 bg-teal-light text-dark-1 px-2 py-0.5 rounded-full text-xs"><?php echo $totalCount; ?></span>
                </button>
                <button id="approvedEventsBtn" class="action-button flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Approved Events
                    <span class="ml-2 bg-teal-light text-dark-1 px-2 py-0.5 rounded-full text-xs"><?php echo $approvedCount; ?></span>
                </button>
                <button id="pendingEventsBtn" class="action-button flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Pending Approvals
                    <span class="ml-2 bg-teal-light text-dark-1 px-2 py-0.5 rounded-full text-xs"><?php echo $pendingCount; ?></span>
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
                            <th class="px-4 py-3 text-center pl-4 pr-47">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-dark-3">
                        <?php if (count($events) > 0): ?>
                            <?php foreach ($events as $event): ?>
                                <?php
                                    // Format date for display
                                    $dateObj = new DateTime($event['event_date']);
                                    $formattedDate = $dateObj->format('Y-m-d • g:i A');
                                    
                                    // Determine reminder status
                                    $reminderStatus = $event['reminder_enabled'] ?
                                        '<span class="text-teal-light">Active</span>' :
                                        '<span class="text-gray-400">Not set</span>';
                                ?>
                                <tr class="hover:bg-dark-3 transition-all">
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($event['name']); ?></td>
                                    <td class="px-4 py-3"><?php echo $formattedDate; ?></td>
                                    <td class="px-4 py-3"><?php echo htmlspecialchars($event['venue']); ?></td>
                                    <td class="px-4 py-3"><span class="status-badge <?php echo $event['status']; ?>"><?php echo $event['status']; ?></span></td>
                                    <td class="px-4 py-3"><?php echo $reminderStatus; ?></td>
                                    <td class="px-4 py-3">
                                        <div class="flex gap-2">
                                            <button class="icon-button view-event inline-flex items-center cursor-pointer bg-dark-1 text-teal-light px-2 py-1 rounded-full hover:bg-dark-3 transition-colors" data-id="<?php echo $event['id']; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5s8.268 2.943 9.542 7c-1.274 4.057-5.065 7-9.542 7s-8.268-2.943-9.542-7z" />
                                                </svg>
                                                View
                                            </button>
                                            <?php if ($event['status'] === 'pending'): ?>
                                                <button class="icon-button approve-event inline-flex items-center cursor-pointer bg-dark-1 text-teal-light px-2 py-1 rounded-full hover:bg-dark-3 transition-colors" data-id="<?php echo $event['id']; ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                    </svg>
                                                    Approve
                                                </button>
                                            <?php else: ?>
                                                <button class="icon-button edit-event inline-flex items-center cursor-pointer bg-dark-1 text-teal-light px-2 py-1 rounded-full hover:bg-dark-3 transition-colors" data-id="<?php echo $event['id']; ?>">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M16.768 8.768L8 17.536V20h2.464l8.768-8.768-2.464-2.464z" />
                                                    </svg>
                                                    Edit
                                                </button>
                                            <?php endif; ?>
                                            <button class="icon-button delete-event inline-flex items-center cursor-pointer bg-dark-1 text-teal-light px-2 py-1 rounded-full hover:bg-dark-3 transition-colors" data-id="<?php echo $event['id']; ?>">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4m-2 0v4m0 8v4m-4-4h8" />
                                                </svg>
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-4 py-6 text-center text-gray-400">No events found. Create your first event!</td>
                            </tr>
                        <?php endif; ?>
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
                        <input class="bg-dark-1 appearance-none border border-gray-700 rounded w-full py-2 px-3 text-light leading-tight focus:outline-none focus:border-teal-light" id="eventName" name="eventName" type="text" placeholder="Enter event name" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-light text-sm font-bold mb-2" for="eventDate">Date & Time</label>
                        <input class="bg-dark-1 appearance-none border border-gray-700 rounded w-full py-2 px-3 text-light leading-tight focus:outline-none focus:border-teal-light" id="eventDate" name="eventDate" type="datetime-local" required>
                        <script>
                            // Set default value to current date and time
                            document.addEventListener('DOMContentLoaded', function() {
                                const now = new Date();
                                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                                const defaultDate = now.toISOString().slice(0, 16);
                                document.getElementById('eventDate').value = defaultDate;
                            });
                        </script>
                    </div>
                    <div class="mb-4">
                        <label class="block text-light text-sm font-bold mb-2" for="eventVenue">Venue</label>
                        <input class="bg-dark-1 appearance-none border border-gray-700 rounded w-full py-2 px-3 text-light leading-tight focus:outline-none focus:border-teal-light" id="eventVenue" name="eventVenue" type="text" placeholder="Enter venue" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-light text-sm font-bold mb-2" for="eventDescription">Description</label>
                        <textarea class="bg-dark-1 appearance-none border border-gray-700 rounded w-full py-2 px-3 text-light leading-tight focus:outline-none focus:border-teal-light" id="eventDescription" name="eventDescription" placeholder="Enter event description" rows="3"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-light text-sm font-bold mb-2">Set Reminder</label>
                        <div class="flex items-center">
                            <input type="checkbox" id="enableReminder" name="enableReminder" class="mr-2">
                            <label for="enableReminder">Enable automatic reminder</label>
                        </div>
                        <div id="reminderOptions" class="mt-2 hidden">
                            <select name="reminderOption" class="bg-dark-1 border border-gray-700 rounded w-full py-2 px-3 text-light focus:outline-none focus:border-teal-light">
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
                <div class="mb-4">
                    <label class="block text-light text-sm font-bold mb-2">Background Image (optional)</label>
                    <div>
                        <label for="eventBgInput" class="inline-flex items-center px-3 py-2 bg-dark-1 border border-teal-light text-teal-light rounded hover:bg-dark-3 text-sm cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M12 12v8M9 15l3-3 3 3M12 3v10" />
                            </svg>
                            Upload
                        </label>
                        <span id="eventBgName" class="ml-2 text-sm text-light"></span>
                        <input type="file" id="eventBgInput" accept="image/*" class="hidden">
                    </div>
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-dark-3">
                    <button type="button" class="edit-event inline-flex items-center cursor-pointer bg-dark-1 text-teal-light px-6 py-3 rounded-full hover:bg-dark-3 transition-all cursor-pointer">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M16.768 8.768L8 17.536V20h2.464l8.768-8.768-2.464-2.464z" />
                        </svg>
                        Edit
                    </button>
                    <button type="button" class="modal-close inline-flex items-center px-6 py-3 bg-red-500 text-white rounded-full hover:bg-red-600 font-bold transition-all cursor-pointer">Close</button>
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