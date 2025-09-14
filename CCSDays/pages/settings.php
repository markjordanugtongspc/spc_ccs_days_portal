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

// Get user info
$userName = $_SESSION['user_name'] ?? 'Admin';
$userRole = ucfirst($_SESSION['user_role'] ?? 'Admin');
$userInitials = strtoupper(substr($userName, 0, 1)) . (strpos($userName, ' ') !== false ? strtoupper(substr($userName, strpos($userName, ' ') + 1, 1)) : '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>CCS Days - Settings</title>
    <link rel="icon" href="../includes/images/spc-ccs-logo.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/dashboard.css">
    <link rel="stylesheet" href="./css/settings.css">
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
				<a href="settings.php" class="sidebar-link active">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
						<path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
						<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
					</svg>
					Settings
				</a>
				<a href="credits.php" class="sidebar-link">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
						<path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
					</svg>
					Credits
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
                <a href="../includes/logout.php" class="text-light hover:text-teal-light transition-all" title="Logout">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
							<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
                </a>
            </div>
        </div>

        <div class="dashboard-container">
            <h1 class="page-title">Settings</h1>

            <!-- Settings Navigation -->
            <div class="tab-nav">
                <div class="tab-item active" data-tab="general">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    General
                </div>
                <div class="tab-item" data-tab="security">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                    Security
                </div>
                <div class="tab-item" data-tab="notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    Notifications
                </div>
                <div class="tab-item" data-tab="appearance">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z" />
                    </svg>
                    Appearance
                </div>
                <div class="tab-item" data-tab="user-management">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 inline mr-2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    User Management
                </div>
            </div>

            <!-- Settings Content -->
            <div class="bg-dark-2 rounded-lg border border-dark-3">
                <!-- General Settings -->
                <div class="p-6" id="general-settings">
                    <h2 class="text-lg font-medium text-light mb-4">General Settings</h2>
                    
                    <!-- System Status -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">System Status</h3>
                                <p class="text-sm text-gray-400">Enable or disable the entire system</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="systemStatus" checked>
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Maintenance Mode -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Maintenance Mode</h3>
                                <p class="text-sm text-gray-400">Put the system in maintenance mode</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="maintenanceMode">
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Auto Backup -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Auto Backup</h3>
                                <p class="text-sm text-gray-400">Enable automatic daily backups</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="autoBackup" checked>
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Event Registration -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Event Registration</h3>
                                <p class="text-sm text-gray-400">Allow students to register for events</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="eventRegistration" checked>
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Security Settings -->
                <div class="p-6 hidden" id="security-settings">
                    <h2 class="text-lg font-medium text-light mb-4">Security Settings</h2>
                    
                    <!-- Two-Factor Authentication -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Two-Factor Authentication</h3>
                                <p class="text-sm text-gray-400">Enable 2FA for additional security</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="twoFactorAuth">
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Session Timeout -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Session Timeout</h3>
                                <p class="text-sm text-gray-400">Set automatic logout time</p>
                            </div>
                            <select id="sessionTimeout" class="bg-dark-3 border border-dark-4 text-light text-sm rounded-lg focus:ring-teal focus:border-teal block p-2.5">
                                <option value="15">15 minutes</option>
                                <option value="30" selected>30 minutes</option>
                                <option value="60">1 hour</option>
                                <option value="120">2 hours</option>
                            </select>
                        </div>
                    </div>

                    <!-- Password Requirements -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Strong Password Requirements</h3>
                                <p class="text-sm text-gray-400">Enforce strong password policies</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="strongPasswords" checked>
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Notification Settings -->
                <div class="p-6 hidden" id="notification-settings">
                    <h2 class="text-lg font-medium text-light mb-4">Notification Settings</h2>
                    
                    <!-- Email Notifications -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Email Notifications</h3>
                                <p class="text-sm text-gray-400">Receive notifications via email</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="emailNotifications" checked>
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Push Notifications -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Push Notifications</h3>
                                <p class="text-sm text-gray-400">Receive browser notifications</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="pushNotifications">
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>

                    <!-- SMS Notifications -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">SMS Notifications</h3>
                                <p class="text-sm text-gray-400">Receive notifications via SMS</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="smsNotifications">
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Appearance Settings -->
                <div class="p-6 hidden" id="appearance-settings">
                    <h2 class="text-lg font-medium text-light mb-4">Appearance Settings</h2>
                    
                    <!-- Dark Mode -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Dark Mode</h3>
                                <p class="text-sm text-gray-400">Enable dark mode theme</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="darkMode" checked>
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Font Size -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Font Size</h3>
                                <p class="text-sm text-gray-400">Adjust the text size</p>
                            </div>
                            <select id="fontSize" class="bg-dark-3 border border-dark-4 text-light text-sm rounded-lg focus:ring-teal focus:border-teal block p-2.5">
                                <option value="small">Small</option>
                                <option value="medium" selected>Medium</option>
                                <option value="large">Large</option>
                            </select>
                        </div>
                    </div>

                    <!-- Sidebar Position -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Sidebar Position</h3>
                                <p class="text-sm text-gray-400">Choose sidebar position</p>
                            </div>
                            <select id="sidebarPosition" class="bg-dark-3 border border-dark-4 text-light text-sm rounded-lg focus:ring-teal focus:border-teal block p-2.5">
                                <option value="left" selected>Left</option>
                                <option value="right">Right</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- User Management Settings -->
                <div class="p-6 hidden" id="user-management-settings">
                    <h2 class="text-lg font-medium text-light mb-4">User Management</h2>
                    
                    <!-- User Registration -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Student Registration</h3>
                                <p class="text-sm text-gray-400">Allow new student registrations</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="studentRegistration" checked>
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Account Approval -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Account Approval Required</h3>
                                <p class="text-sm text-gray-400">Require admin approval for new accounts</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" class="sr-only peer" id="accountApproval">
                                <div class="w-11 h-6 bg-dark-3 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-teal rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal"></div>
                            </label>
                        </div>
                    </div>

                    <!-- Default User Role -->
                    <div class="mb-6 p-4 bg-dark-1 rounded-lg border border-dark-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-medium text-light">Default User Role</h3>
                                <p class="text-sm text-gray-400">Default role for new users</p>
                            </div>
                            <select id="defaultUserRole" class="bg-dark-3 border border-dark-4 text-light text-sm rounded-lg focus:ring-teal focus:border-teal block p-2.5">
                                <option value="student" selected>Student</option>
                                <option value="faculty">Faculty</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/common.js"></script>
    <script src="../js/settings.js"></script>
</body>
</html> 