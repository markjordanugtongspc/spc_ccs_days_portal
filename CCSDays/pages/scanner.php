<?php
date_default_timezone_set('Asia/Manila');
session_start();
$qrCodeValue = '';

// Check if we need to refresh the page
if (isset($_GET['refresh']) && $_GET['refresh'] === 'true') {
	// Get the QR code value if provided
	if (isset($_GET['qr'])) {
		$qrCodeValue = htmlspecialchars($_GET['qr']);

		// Store the QR code value in session to persist after refresh
		$_SESSION['last_scanned_qr'] = $qrCodeValue;
		// Persist status (in/out) so server can render the latest student card
		if (isset($_GET['status'])) {
			$statusParam = $_GET['status'] === 'out' ? 'out' : 'in';
			$_SESSION['last_status'] = $statusParam;
		}

		// You can add your processing logic here
		// For example: update database, log attendance, etc.

		// Redirect to self without the GET parameters to avoid refresh loop
		header("Location: scanner.php");
		exit;
	}
}

// Check if we have a stored QR code value from previous scan
if (isset($_SESSION['last_scanned_qr'])) {
	$qrCodeValue = $_SESSION['last_scanned_qr'];
}

// Get approved events for the dropdown
require_once __DIR__ . '/../includes/config.php';
$pdo = getDbConnection();
$events = [];
try {
	$stmt = $pdo->query("SELECT id, name FROM events WHERE status = 'approved' ORDER BY event_date DESC");
	$events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
	// Handle error or leave events array empty
	error_log("Error fetching events: " . $e->getMessage());
}

// pull all attendance logs joined to students
// In scanner.php, update the SQL query for recent entries:
$sql = "
	 SELECT
    a.Attendance_ID,
    a.Student_ID,
    a.QR_Code,
    a.Sign_In_Time,
    a.Sign_Out_Time,
    a.Event_ID,
    s.Name,
    s.Year,
    e.name as Event_Name
  FROM attendance a
  JOIN students s ON a.Student_ID = s.Student_ID
  LEFT JOIN events e ON a.Event_ID = e.id
  ORDER BY a.Attendance_ID DESC
  LIMIT 6
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$recentEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch latest student details for right panel if available in session
$latestStudent = null;
$latestStatusLabel = null;
$latestAttendanceTime = null;
try {
	if (!empty($_SESSION['last_scanned_qr'])) {
		$lastId = $_SESSION['last_scanned_qr'];
		$stmtStudent = $pdo->prepare("
            SELECT s.*, a.Sign_In_Time, a.Sign_Out_Time 
            FROM students s 
            LEFT JOIN attendance a ON s.Student_ID = a.Student_ID 
            WHERE s.Student_ID = ? 
            ORDER BY a.Attendance_ID DESC 
            LIMIT 1
        ");
		$stmtStudent->execute([$lastId]);
		$latestStudent = $stmtStudent->fetch(PDO::FETCH_ASSOC) ?: null;

		// Determine the latest time (sign in or out)
		if ($latestStudent) {
			$latestAttendanceTime = !empty($latestStudent['Sign_Out_Time']) ?
				$latestStudent['Sign_Out_Time'] : $latestStudent['Sign_In_Time'];
		}
		$latestStatusLabel = (isset($_SESSION['last_status']) && $_SESSION['last_status'] === 'out') ? 'Sign Out' : 'Sign In';
	}
} catch (Throwable $e) {
	// fail silently; right panel simply won't render
}
?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
		<title>CCS Days - QR Scanner</title>
		<link rel="icon" href="../includes/images/spc-ccs-logo.png" type="image/png">
		<link rel="preconnect" href="https://fonts.googleapis.com" />
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
		<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
		<link rel="stylesheet" href="../styles.css">
		<link rel="stylesheet" href="./css/common.css">
		<link rel="stylesheet" href="./css/scanner.css">
		<link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
		<!-- <script src="https://unpkg.com/html5-qrcode"></script> -->
		<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
		<!-- Load QR library from node_modules -->
		<script src="../../node_modules/html5-qrcode/html5-qrcode.min.js"></script>
	</head>
	<body class="bg-dark-1 text-light">
		<!-- Preload success audio for reliable playback -->
		<audio id="success-audio" src="../assets/audio/success.mp3" preload="auto"></audio>
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
				<a href="scanner.php" class="sidebar-link active">
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
				<div class="user-avatar">A</div>
				<div class="user-details">
					<div class="user-name">Administrator</div>
					<div class="user-role">Admin</div>
				</div>
			</div>
		</div>

<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
	<title>CCS Days - QR Scanner</title>
	<link rel="icon" href="../includes/images/spc-ccs-logo.png" type="image/png">
	<link rel="preconnect" href="https://fonts.googleapis.com" />
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
	<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
	<link rel="stylesheet" href="../styles.css">
	<link rel="stylesheet" href="./css/common.css">
	<link rel="stylesheet" href="./css/scanner.css">
	<link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
	<!-- Load QR library from node_modules -->
	<script src="../../node_modules/html5-qrcode/html5-qrcode.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="bg-dark-1 text-light">
	<!-- Preload success audio for reliable playback -->
	<audio id="success-audio" src="../assets/audio/success.mp3" preload="auto"></audio>
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
			<a href="scanner.php" class="sidebar-link active">
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
					<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008z" />
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
					<path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.250.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z" />
					<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
				</svg>
				Settings
			</a>
		</div>
		<div class="user-info">
			<div class="user-avatar">A</div>
			<div class="user-details">
				<div class="user-name">Administrator</div>
				<div class="user-role">Admin</div>
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
						<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 00113.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
					</svg>
				</a>
			</div>
		</div>

		<div class="dashboard-container">
			<div class="tab-nav">
				<div class="tab-item active">QR Scanner</div>
				<div class="tab-item">Manual Entry</div>
			</div>

			<h1 class="page-title">Student Sign In/Sign Out</h1>

			<!-- Event Selection Section -->
			<div class="mb-6 bg-dark-2 rounded-lg p-4">
				<div class="flex items-center mb-2">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 text-teal-light">
						<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008z" />
					</svg>
					<h2 class="text-lg font-medium">Event Selection</h2>
				</div>
				<select id="eventSelection" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
					<option value="">-- Select an Event --</option>
					<?php foreach ($events as $event): ?>
						<option value="<?php echo $event['id']; ?>"><?php echo htmlspecialchars($event['name']); ?></option>
					<?php endforeach; ?>
				</select>
				<div id="selectedEventInfo" class="mt-3 p-3 bg-dark-3 rounded-md hidden">
					<p class="text-teal-light font-medium">Selected Event: <span id="selectedEventName"></span></p>
				</div>
			</div>

			<!-- QR Scanner Content -->
			<div class="scanner-content">
				<div class="flex items-center mb-6">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2 text-teal-light">
						<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
						<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5z" />
					</svg>
					<h2 class="text-xl font-medium">QR Code Scanner</h2>
				</div>

				<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
					<div class="lg:col-span-2 bg-dark-2 rounded-lg p-6">
						<div id="reader" class="aspect-video bg-dark-1 rounded-lg relative overflow-hidden mb-6">
							<video id="qr-preview" class="hidden absolute inset-0 w-full h-full object-cover" autoplay muted playsinline></video>
							<div class="absolute inset-0 flex items-center justify-center">
								<div class="w-80 h-80 relative">
									<div class="scanner-corner absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-teal-light"></div>
									<div class="scanner-corner absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-teal-light"></div>
									<div class="scanner-corner absolute bottom-0 left-0 w-8 h-8 border-b-2 border-l-2 border-teal-light"></div>
									<div class="scanner-corner absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-teal-light"></div>
								</div>
							</div>
						</div>

						<div class="flex space-x-3">
							<button id="startScanner" class="flex items-center justify-center px-4 py-2 rounded-md bg-teal-900 text-teal-light hover:bg-teal-800 cursor-pointer transition-colors" disabled>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.348a1.125 1.125 0 010 1.971l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z" />
								</svg>
								Start Scanner
							</button>
							<button id="stopScanner" class="flex items-center justify-center px-4 py-2 rounded-md bg-dark-3 text-light hover:bg-dark-4 cursor-pointer transition-colors" disabled>
								<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
									<path stroke-linecap="round" stroke-linejoin="round" d="M5.25 7.5A2.25 2.25 0 017.5 5.25h9a2.25 2.25 0 012.25 2.25v9a2.25 2.25 0 01-2.25 2.25h-9a2.25 2.25 0 01-2.25-2.25v-9z" />
								</svg>
								Stop Scanner
							</button>
						</div>

						<div>Reminder: Stop and restart the scanner before use.</div>

						<div class="mt-6">
							<div class="text-lg font-medium mb-2">Last Scan Result</div>
							<div id="scanResult" class="bg-dark-1 rounded-lg p-4 min-h-[80px] flex items-center justify-center">
								<?php if (!empty($qrCodeValue)): ?>
									<span class="text-light"><?php echo $qrCodeValue; ?></span>
								<?php else: ?>
									<span class="text-gray-500">No recent scans</span>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<div class="bg-dark-2 rounded-lg p-6">
						<div class="flex items-center mb-4">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 text-teal-light">
								<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
							</svg>
							<h3 class="text-lg font-medium">Manual Entry</h3>
						</div>

						<div class="mb-4">
							<label for="studentId" class="block text-sm font-medium text-gray-400 mb-1">Student ID</label>
							<input type="text" id="studentId" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light" placeholder="Enter student ID">
						</div>

						<div class="flex space-x-3">
							<button id="signInBtn" class="flex-1 flex items-center justify-center px-4 py-2 rounded-md bg-teal-900 text-teal-light hover:bg-teal-800 cursor-pointer">
								Sign In
							</button>
							<button id="signOutBtn" class="flex-1 flex items-center justify-center px-4 py-2 rounded-md bg-dark-3 text-light hover:bg-dark-4 cursor-pointer">
								Sign Out
							</button>
						</div>
						<div style="margin-bottom: 60px;"></div>

						<br>
					</div>
				</div>
			</div>

			<!-- Manual Entry Content -->
			<div class="manual-content hidden">
				<!-- Latest Person (auto-populated after scan) -->
				<div id="latestPersonCard" class="hidden bg-dark-2 rounded-lg p-6 mb-6"></div>
				<div class="flex items-center mb-6">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 mr-2 text-teal-light">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
					</svg>
					<h2 class="text-xl font-medium">Bulk Student Entry</h2>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<div class="bg-dark-2 rounded-lg p-6">
						<div class="mb-6">
							<h3 class="text-lg font-medium mb-4">Quick Entry Form</h3>
							<div class="mb-4">
								<label for="bulkStudentId" class="block text-sm font-medium text-gray-400 mb-1">Student ID</label>
								<input type="text" id="bulkStudentId" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light" placeholder="Enter student ID">
							</div>
							<div class="mb-4">
								<label for="bulkYear" class="block text-sm font-medium text-gray-400 mb-1">Year Level</label>
								<select id="bulkYear" class="w-full bg-dark-1 border border-dark-4 rounded-md px-3 py-2 text-light focus:outline-none focus:ring-1 focus:ring-teal-light">
									<option value="">Select Year Level</option>
									<option value="1st Year">1st Year</option>
									<option value="2nd Year">2nd Year</option>
									<option value="3rd Year">3rd Year</option>
									<option value="4th Year">4th Year</option>
								</select>
							</div>
							<div class="mb-4">
								<label class="block text-sm font-medium text-gray-400 mb-1">Status</label>
								<div class="flex space-x-4">
									<label class="inline-flex items-center">
										<input type="radio" name="bulk-status" class="h-4 w-4 text-teal-light focus:ring-teal-light" checked>
										<span class="ml-2 text-light">Present</span>
									</label>
									<label class="inline-flex items-center">
										<input type="radio" name="bulk-status" class="h-4 w-4 text-teal-light focus:ring-teal-light">
										<span class="ml-2 text-light">Absent</span>
									</label>
									<label class="inline-flex items-center">
										<input type="radio" name="bulk-status" class="h-4 w-4 text-teal-light focus:ring-teal-light">
										<span class="ml-2 text-light">Late</span>
									</label>
								</div>
							</div>
							<button id="bulkSubmitBtn" class="w-full px-4 py-2 rounded-md bg-teal-900 text-teal-light hover:bg-teal-800">
								Submit Entry
							</button>
						</div>
						<button id="addYearBtn" class="w-full px-4 py-2 rounded-md bg-teal-900 text-teal-light hover:bg-teal-800 cursor-pointer">
							Add by Year
						</button>
						<div>
							<h3 class="text-lg font-medium mb-4">Import Data</h3>
							<div class="mb-4">
								<label class="block text-sm font-medium text-gray-400 mb-1">Upload CSV File</label>
								<div class="relative">
									<input type="file" id="csvFile" accept=".csv" class="sr-only">
									<label for="csvFile" class="flex items-center justify-center w-full px-4 py-2 bg-dark-1 border border-dashed border-dark-4 rounded-md text-gray-400 cursor-pointer hover:bg-dark-3 transition-colors">
										<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
											<path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
										</svg>
										<span>Click to upload CSV file</span>
									</label>
								</div>
							</div>
							<button id="importBtn" class="w-full px-4 py-2 rounded-md bg-dark-3 text-light hover:bg-dark-4" disabled>
								Import Records
							</button>
						</div>
					</div>

					<div class="bg-dark-2 rounded-lg p-6 flex flex-col">
						<h3 class="text-lg font-medium mb-4">Pending Entries</h3>
						<div class="bg-dark-1 rounded-lg p-4 flex-grow mb-4 overflow-auto max-h-96">
							<div id="pendingEntries" class="text-gray-500">No pending entries</div>
						</div>
						<div class="flex space-x-3">
							<button id="clearBtn" class="flex-1 px-4 py-2 rounded-md bg-dark-3 text-light hover:bg-dark-4" disabled>
								Clear All
							</button>
							<button id="submitAllBtn" class="flex-1 px-4 py-2 rounded-md bg-teal-900 text-teal-light hover:bg-teal-800" disabled>
								Submit All
							</button>
						</div>
					</div>
				</div>
			</div>

			<div class="mt-8">
				<div class="flex items-center mb-4">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2 text-teal-light">
						<path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
					</svg>
					<h3 class="text-lg font-medium">Recent Activity</h3>
				</div>
				<div class="bg-dark-2 rounded-lg overflow-hidden">
					<div class="grid grid-cols-7 gap-4 p-4 border-b border-dark-3 text-gray-400 text-sm font-medium">
						<div>Student ID</div>
						<div>Name</div>
						<div>Year</div>
						<div>Event</div>
						<div>Status</div>
						<div>Time</div>
						<div>Type</div>
					</div>
					<div>
						<?php foreach ($recentEntries as $e): ?>
							<?php $timeIn = date('g:i A', strtotime($e['Sign_In_Time'])); ?>
							<div class="grid grid-cols-7 gap-4 p-4">
								<div><?= htmlspecialchars($e['Student_ID']) ?></div>
								<div><?= htmlspecialchars($e['Name']) ?></div>
								<div><?= htmlspecialchars($e['Year']) ?></div>
								<div><?= htmlspecialchars($e['Event_Name'] ?? 'N/A') ?></div>
								<div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">Sign In</span></div>
								<div><?= $timeIn ?></div>
								<div>
									<?php if (!empty($e['is_auto_sign'])): ?>
										<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900 text-blue-300">Auto</span>
									<?php else: ?>
										<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">Manual</span>
									<?php endif; ?>
								</div>
							</div>
							<?php if (!empty($e['Sign_Out_Time'])): ?>
								<?php $timeOut = date('g:i A', strtotime($e['Sign_Out_Time'])); ?>
								<div class="grid grid-cols-7 gap-4 p-4">
									<div><?= htmlspecialchars($e['Student_ID']) ?></div>
									<div><?= htmlspecialchars($e['Name']) ?></div>
									<div><?= htmlspecialchars($e['Year']) ?></div>
									<div><?= htmlspecialchars($e['Event_Name'] ?? 'N/A') ?></div>
									<div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-900 text-red-300">Sign Out</span></div>
									<div><?= $timeOut ?></div>
									<div>
										<?php if (!empty($e['is_auto_sign'])): ?>
											<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-900 text-blue-300">Auto</span>
										<?php else: ?>
											<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-700 text-gray-300">Manual</span>
										<?php endif; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- Load common utilities, then scanner logic -->
	<script src="../js/common.js" defer></script>
	<script src="../js/scanner.js" defer></script>
	<script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
	<script>
		// Dedicated sound playback for this page
		(function() {
			const successAudio = document.getElementById('success-audio');
			let audioUnlocked = false;

			function unlockAudio() {
				if (audioUnlocked) return;
				try {
					// Try to play very briefly to unlock audio on iOS/Safari
					const p = successAudio.play();
					if (p && typeof p.then === 'function') {
						p.then(() => {
							successAudio.pause();
							successAudio.currentTime = 0;
							audioUnlocked = true;
						}).catch(() => {
							// ignore; will try again on next interaction
						});
					}
				} catch (e) {}
			}

			// Expose a global to let other scripts trigger the success sound
			window.playSuccessAudio = function() {
				try {
					successAudio.currentTime = 0;
					const p = successAudio.play();
					if (p && typeof p.then === 'function') {
						p.catch(() => {});
					}
				} catch (e) {}
			};

			// Unlock on first user interaction
			document.addEventListener('click', unlockAudio, {
				once: true,
				passive: true
			});
			document.addEventListener('touchstart', unlockAudio, {
				once: true,
				passive: true
			});
		})();

		// Render latest person card in Manual Entry from sessionStorage
		(function renderLatestFromSession() {
			const container = document.getElementById('latestPersonCard');
			if (!container) return;
			const id = sessionStorage.getItem('lastPersonId');
			const status = sessionStorage.getItem('lastPersonStatus');
			if (!id || !status) return;

			fetch(`../includes/api/fetch_student_details.php?id=${encodeURIComponent(id)}`)
				.then(r => r.json())
				.then(student => {
					if (!student || student.error) return;
					const statusClass = status === 'Sign Out' ? 'bg-red-900 text-red-300' : 'bg-green-900 text-green-300';
					const parts = (student.Name || '').split(' ');
					const initials = (parts[0]?.charAt(0) || '') + (parts.length > 1 ? parts[parts.length - 1].charAt(0) : '');
					const timeStr = new Date().toLocaleTimeString([], {
						hour: '2-digit',
						minute: '2-digit'
					});
					container.classList.remove('hidden');
					container.innerHTML = `
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-start">
                                <div class=\"h-12 w-12 rounded-full bg-teal-900/30 flex items-center justify-center text-teal-light font-semibold\">${initials}</div>
                                <div class=\"ml-4\">
                                    <div class=\"text-lg font-medium text-light\">${student.Name || 'Unknown'}</div>
                                    <div class=\"text-gray-400 text-sm\">${student.Student_ID || id}</div>
                                </div>
                            </div>
                            <span class=\"inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${statusClass}\">${status}</span>
                        </div>
                        <div class=\"grid grid-cols-2 gap-4 text-sm\">
                            <div>
                                <div class=\"text-gray-400\">Course</div>
                                <div class=\"text-light\">${student.College || 'CCS'}</div>
                            </div>
                            <div>
                                <div class=\"text-gray-400\">Year</div>
                                <div class=\"text-light\">${student.Year ? student.Year + ' Year' : 'N/A'}</div>
                            </div>
                            <div>
                                <div class=\"text-gray-400\">Gender</div>
                                <div class=\"text-light\">${student.Gender === 'M' ? 'Male' : student.Gender === 'F' ? 'Female' : 'N/A'}</div>
                            </div>
                            <div>
                                <div class=\"text-gray-400\">Time</div>
                                <div class=\"text-light\">${timeStr}</div>
                            </div>
                        </div>
                    `;
				})
				.catch(() => {});
		})();
	</script>
</body>

</html>