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
				<a href="#" class="sidebar-link">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
						<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.431-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.28z" />
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
										<div class="stat-value" id="totalEventsCount">28</div>
										<div class="stat-change positive">+5 from last week</div>
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
		</script>
	</body>
</html> 