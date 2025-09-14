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
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
		<link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" /> <!-- Flowbite Plugin -->
		<title>CCS Days - Admin Dashboard</title>
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
				<a href="dashboard.php" class="sidebar-link active">
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
						<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
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
					<button id="notificationBadgeBtn" type="button" class="notification-badge"
						data-toast-target="#toast-notification"
						data-toast-show="fade-in"
						data-toast-hide="fade-out">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-light">
							<path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
						</svg>
						<span class="badge">1</span>
					</button>
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
				<div class="tab-nav">
					<div class="tab-item active" data-tab="dashboard">Dashboard</div>
					<div class="tab-item" data-tab="students">Students</div>
					<div class="tab-item" data-tab="events">Events</div>
				</div>
				
				<div id="tab-content">
					<!-- Tab content will be loaded here -->
					<div id="dashboard-content" class="tab-panel active" style="display: block;">
						<h1 class="page-title">Dashboard</h1>
						
						<div class="stats-container">
							<div class="stat-card">
								<div class="flex justify-between">
									<div>
										<div class="stat-title">
											<span>Current Time</span>
											<span id="timeOfDay" class="time-of-day">
												<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
												</svg>
												<span id="timeOfDayText">Morning</span>
											</span>
										</div>
										<div class="stat-value" id="currentTime">--:--:-- --</div>
										<div class="stat-date">
											<div>
												<div id="currentDate">----, --- --, ----</div>
												<div id="numericDate" class="numeric-date">--/--/----</div>
											</div>
										</div>
									</div>
									<div class="card-icon">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-teal-light">
											<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
										</svg>
									</div>
								</div>
							</div>
							
							<div class="stat-card">
								<div class="flex justify-between">
									<div>
										<div class="stat-title">Students Signed In</div>
										<div class="stat-value" id="studentsSignedInCount">254</div>
										<div class="stat-change positive">+17 from last week</div>
									</div>
									<div class="card-icon text-teal">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-teal">
											<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
										</svg>
									</div>
								</div>
							</div>
							
							<div class="stat-card">
								<div class="flex justify-between">
									<div>
										<div class="stat-title">Total Events</div>
										<div class="stat-value" id="totalEventsCount">
											<div class="inline-flex items-center">
												<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-teal-light" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
													<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
													<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
												</svg>
												Loading...
											</div>
										</div>
										<div class="stat-change">Loading...</div>
									</div>
									<div class="card-icon">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-teal-light">
											<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
										</svg>
									</div>
								</div>
							</div>
							
							<div class="stat-card">
								<div class="flex justify-between">
									<div>
										<div class="stat-title">Pending Approvals</div>
										<div class="stat-value" id="pendingApprovalsCount">7</div>
										<div class="stat-change negative">-2 from last week</div>
									</div>
									<div class="card-icon">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 text-teal-light">
											<path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.113-.285-2.16-.786-3.07M15 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
										</svg>
									</div>
								</div>
							</div>
						</div>
						
						<div class="dashboard-grid">
							<div class="main-section">
								<div class="upcoming-visits">
									<div class="section-title">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
											<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
										</svg>
										Upcoming Events
									</div>
									
									<div id="upcomingEventsContainer">
										<!-- Loading indicator -->
										<div class="flex justify-center items-center p-4">
											<svg class="animate-spin -ml-1 mr-3 h-8 w-8 text-teal-light" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
												<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
												<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
											</svg>
											<span>Loading events...</span>
										</div>
									</div>
									
									<a href="events.php" class="view-all">View All Events</a>
								</div>
								
								<div class="quick-actions">
									<div class="section-title">
										Quick Actions
									</div>
									<div class="grid grid-cols-2 gap-4">
										<button id="addEventBtn" class="action-button">Add New Event</button>
										<button id="approveEventsBtn" class="action-button">Approve Events</button>
										<button class="action-button">Generate Reports</button>
										<button class="action-button">Manage Users</button>
									</div>
								</div>
							</div>
							
							<div class="sidebar-section">
								<div class="search-container">
									<div class="section-title">
										Quick Search
									</div>
									<input type="text" class="search-input" placeholder="Search for attendee or event...">
									<div class="search-help">
										Quick search participants, visitors, or staff
									</div>
								</div>
								
								<div class="upcoming-visits">
									<div class="section-title">
										Recent Activities
									</div>
									
									<div class="visit-item">
										<div class="visit-details">
											<div class="visitor-name">
												New Registration
											</div>
											<div class="visit-info">John Smith registered for Programming Competition</div>
											<div class="visit-time">
												<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 icon">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
												</svg>
												15 minutes ago
											</div>
										</div>
									</div>
									
									<div class="visit-item">
										<div class="visit-details">
											<div class="visitor-name">
												Event Updated
											</div>
											<div class="visit-info">Web Development Workshop venue changed</div>
											<div class="visit-time">
												<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 icon">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
												</svg>
												1 hour ago
											</div>
										</div>
									</div>
									
									<div class="visit-item">
										<div class="visit-details">
											<div class="visitor-name">
												New Event
											</div>
											<div class="visit-info">Industry Talk: AI Trends has been added</div>
											<div class="visit-time">
												<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 icon">
													<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
												</svg>
												3 hours ago
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				</div>
			</div>
		</div>

		<script src="../js/common.js"></script>
		<script src="../js/dashboard.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>

<!-- Temporary Commented Out to Fix Glitching
		<script>
				// Update clock
				function updateClock() {
					const now = new Date();
					const timeElement = document.getElementById('currentTime');
					const dateElement = document.getElementById('currentDate');
					const numericDateElement = document.getElementById('numericDate');
					const timeOfDayTextElement = document.getElementById('timeOfDayText');
					
					if (timeElement && dateElement && numericDateElement && timeOfDayTextElement) {
						// Format time
						let hours = now.getHours();
						const minutes = String(now.getMinutes()).padStart(2, '0');
						const seconds = String(now.getSeconds()).padStart(2, '0');
						const ampm = hours >= 12 ? 'PM' : 'AM';
						hours = hours % 12;
						hours = hours ? hours : 12; // the hour '0' should be '12'
						const formattedTime = `${hours}:${minutes}:${seconds} ${ampm}`;
						
						// Format date
						const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric' };
						const formattedDate = now.toLocaleDateString('en-US', options);
						
						// Format numeric date
						const month = String(now.getMonth() + 1).padStart(2, '0');
						const day = String(now.getDate()).padStart(2, '0');
						const year = now.getFullYear();
						const numericDate = `${month}/${day}/${year}`;
						
						// Determine time of day
						let timeOfDay = 'Morning';
						const hour = now.getHours();
						if (hour >= 12 && hour < 17) {
							timeOfDay = 'Afternoon';
						} else if (hour >= 17) {
							timeOfDay = 'Evening';
						}
						
						// Update elements
						timeElement.textContent = formattedTime;
						dateElement.textContent = formattedDate;
						numericDateElement.textContent = numericDate;
						timeOfDayTextElement.textContent = timeOfDay;
					}
				}
				
				// Initial clock update
				updateClock();
				
				// Update clock every second
				setInterval(updateClock, 1000);
		</script> -->

		<!-- Flowbite Notification Toast -->
		<div id="toast-notification" class="hidden fixed bottom-5 right-5 w-full max-w-xs p-4 text-gray-900 bg-white rounded-lg shadow-sm dark:bg-gray-800 dark:text-gray-300" role="alert">
			<div class="flex items-center mb-3">
				<span class="mb-1 text-sm font-semibold text-gray-900 dark:text-white">New notification</span>
				<button type="button" class="ms-auto -mx-1.5 -my-1.5 bg-white justify-center items-center shrink-0 text-gray-400 hover:text-gray-900 rounded-lg focus:ring-2 focus:ring-gray-300 p-1.5 hover:bg-gray-100 inline-flex h-8 w-8 dark:text-gray-500 dark:hover:text-white dark:bg-gray-800 dark:hover:bg-gray-700" data-dismiss-target="#toast-notification" aria-label="Close">
					<span class="sr-only">Close</span>
					<svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
						<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
					</svg>
				</button>
			</div>
			<div class="flex items-center">
				<div class="relative inline-block shrink-0">
					<img class="w-12 h-12 rounded-full" src="/docs/images/people/profile-picture-3.jpg" alt="Jese Leos image"/>
					<span class="absolute bottom-0 right-0 inline-flex items-center justify-center w-6 h-6 bg-blue-600 rounded-full">
						<svg class="w-3 h-3 text-white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 18" fill="currentColor">
							<path d="M18 4H16V9C16 10.0609 15.5786 11.0783 14.8284 11.8284C14.0783 12.5786 13.0609 13 12 13H9L6.846 14.615C7.17993 14.8628 7.58418 14.9977 8 15H11.667L15.4 17.8C15.5731 17.9298 15.7836 18 16 18C16.2652 18 16.5196 17.8946 16.7071 17.7071C16.8946 17.5196 17 17.2652 17 17V15H18C18.5304 15 19.0391 14.7893 19.4142 14.4142C19.7893 14.0391 20 13.5304 20 13V6C20 5.46957 19.7893 4.96086 19.4142 4.58579C19.0391 4.21071 18.5304 4 18 4Z"/>
							<path d="M12 0H2C1.46957 0 0.960859 0.210714 0.585786 0.585786C0.210714 0.960859 0 1.46957 0 2V9C0 9.53043 0.210714 10.0391 0.585786 10.4142C0.960859 10.7893 1.46957 11 2 11H3V13C3 13.1857 3.05171 13.3678 3.14935 13.5257C3.24698 13.6837 3.38668 13.8114 3.55279 13.8944C3.71889 13.9775 3.90484 14.0126 4.08981 13.996C4.27477 13.9793 4.45143 13.9114 4.6 13.8L8.333 11H12C12.5304 11 13.0391 10.7893 13.4142 10.4142C13.7893 10.0391 14 9.53043 14 9V2C14 1.46957 13.7893 0.960859 13.4142 0.585786C13.0391 0.210714 12.5304 0 12 0Z"/>
						</svg>
						<span class="sr-only">Message icon</span>
					</span>
				</div>
				<div class="ms-3 text-sm font-normal">
					<div class="text-sm font-semibold text-gray-900 dark:text-white">Bonnie Green</div>
					<div class="text-sm font-normal">commented on your photo</div>
					<span class="text-xs font-medium text-blue-600 dark:text-blue-500">a few seconds ago</span>
				</div>
			</div>
		</div>
	</body>
</html>