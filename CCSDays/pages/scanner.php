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
    
    // Optional: Clear the session variable after using it once
    // Uncomment if you want to clear after one display
    // unset($_SESSION['last_scanned_qr']);
}



// pull all attendance logs joined to students
require_once __DIR__ . '/../includes/config.php';
$pdo = getDbConnection();
$sql = "
  SELECT
	a.Attendance_ID,
	a.Student_ID,
	a.QR_Code,
	a.Sign_In_Time,
	a.Sign_Out_Time,
	s.Name,
	s.Year
  FROM attendance a
  JOIN students s ON a.Student_ID = s.Student_ID
  ORDER BY a.Attendance_ID DESC
  LIMIT 6
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$recentEntries = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
				<a href="#" class="sidebar-link">
					<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 icon">
						<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076-.124a6.57 6.57 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
						<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
					</svg>
					Settings
				</a>
			</div>
			<div class="user-info">
				<div class="user-avatar">JD</div>
				<div class="user-details">
					<div class="user-name">Mark Jordan Ugtong</div>
					<div class="user-role">System Administrator</div>
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
					<a href="#" class="text-light">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
								<path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
							</svg>
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
								<button id="startScanner" class="flex items-center justify-center px-4 py-2 rounded-md bg-teal-900 text-teal-light hover:bg-teal-800 cursor-pointer transition-colors">
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
						</div>
					</div>
				</div>
				
				<!-- Manual Entry Content -->
				<div class="manual-content hidden">
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
						<div class="grid grid-cols-5 gap-4 p-4 border-b border-dark-3 text-gray-400 text-sm font-medium">
							<div>Student ID</div>
							<div>Name</div>
							<div>Year</div>
							<div>Status</div>
							<div>Time</div>
						</div>
						<div>
							<?php foreach ($recentEntries as $e): ?>
								<?php $timeIn = date('g:i A', strtotime($e['Sign_In_Time'])); ?>
								<div class="grid grid-cols-5 gap-4 p-4">
									<div><?= htmlspecialchars($e['Student_ID']) ?></div>
									<div><?= htmlspecialchars($e['Name']) ?></div>
									<div><?= htmlspecialchars($e['Year']) ?></div>
									<div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-900 text-green-300">Sign In</span></div>
									<div><?= $timeIn ?></div>
								</div>
								<?php if (!empty($e['Sign_Out_Time'])): ?>
									<?php $timeOut = date('g:i A', strtotime($e['Sign_Out_Time'])); ?>
									<div class="grid grid-cols-5 gap-4 p-4">
										<div><?= htmlspecialchars($e['Student_ID']) ?></div>
										<div><?= htmlspecialchars($e['Name']) ?></div>
										<div><?= htmlspecialchars($e['Year']) ?></div>
										<div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-900 text-red-300">Sign Out</span></div>
										<div><?= $timeOut ?></div>
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
	</body>
</html>