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

// Developer data
$developers = [
    [
        'name' => 'Mark Jordan Ugtong',
        'role' => 'Full Stack Developer & Project Lead',
        'description' => 'Lead developer who created the entire CCS Days attendance system from scratch. Responsible for system architecture, database design, backend API development, and frontend implementation using modern web technologies.',
        'specialties' => ['PHP', 'MySQL', 'JavaScript', 'System Architecture', 'Project Management'],
        'avatar' => 'MU',
        'color' => 'from-blue-500 to-purple-600'
    ],
    [
        'name' => 'Jay Bodiongan',
        'role' => 'Backend Enhancement Specialist',
        'description' => 'Backend developer who enhanced the attendance system logic and functionality. Contributed to improving the core attendance features and added new system capabilities for better performance.',
        'specialties' => ['Backend Logic', 'Attendance Systems', 'Feature Enhancement', 'PHP Development'],
        'avatar' => 'JB',
        'color' => 'from-green-500 to-emerald-600'
    ],
    [
        'name' => 'Jesper Ian Barila',
        'role' => 'Export System Developer',
        'description' => 'Specialized in implementing the comprehensive export system functionality. Developed features to convert attendance logs, sign-in/sign-out records into multiple formats including PDF, Excel, and Word documents.',
        'specialties' => ['Export Systems', 'Data Conversion', 'PDF Generation', 'File Management'],
        'avatar' => 'JB',
        'color' => 'from-indigo-500 to-blue-600'
    ],
    [
        'name' => 'Romarc Bongcaron',
        'role' => 'Frontend & JavaScript Specialist',
        'description' => 'Frontend developer who fixed critical Ajax code and JavaScript functionality. Specialized in resolving sign-in/sign-out sound effects bugs and improving user interface interactions.',
        'specialties' => ['JavaScript', 'Ajax', 'Sound Effects', 'UI Debugging', 'Frontend Optimization'],
        'avatar' => 'RB',
        'color' => 'from-yellow-500 to-orange-600'
    ],
    [
        'name' => 'Christian Maglangit',
        'role' => 'School Student President & Project Inspiration',
        'description' => 'CCS Department Student President who originally conceptualized and initiated the first version of this attendance system. His leadership and vision inspired the creation of this modern, improved implementation.',
        'specialties' => ['Project Vision', 'Leadership', 'System Planning', 'Student Administration'],
        'avatar' => 'CM',
        'color' => 'from-purple-500 to-pink-600'
    ]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>CCS Days - Credits</title>
    <link rel="icon" href="../includes/images/spc-ccs-logo.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css" rel="stylesheet" />
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
            <a href="credits.php" class="sidebar-link active">
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
                <button id="themeToggle" class="text-teal-light hover-teal transition-all cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>
                <a href="../includes/logout.php" class="text-light hover:text-teal-light transition-all cursor-pointer" title="Logout">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                    </svg>
                </a>
            </div>
        </div>

        <div class="dashboard-container">
            <!-- Header Section -->
            <div class="text-center mb-12">
                <h1 class="text-4xl md:text-5xl font-bold bg-gradient-to-r from-teal-400 to-blue-500 bg-clip-text text-transparent mb-4">
                    Development Team Credits
                </h1>
                <p class="text-xl text-gray-400 max-w-3xl mx-auto leading-relaxed">
                    Meet the talented developers who brought the CCS Days Connect system to life. 
                    Each member contributed their unique expertise to create this comprehensive student management platform.
                </p>
            </div>

            <!-- Stats Section -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
                <div class="bg-dark-2 rounded-xl p-6 border border-teal-900/20 hover:border-teal-600/40 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 mb-1">Team Members</p>
                            <p class="text-3xl font-bold text-teal-400"><?php echo count($developers); ?></p>
                        </div>
                        <div class="w-12 h-12 bg-teal-900/30 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-teal-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-2 rounded-xl p-6 border border-purple-900/20 hover:border-purple-600/40 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 mb-1">Development Hours</p>
                            <p class="text-3xl font-bold text-purple-400">1,200+</p>
                        </div>
                        <div class="w-12 h-12 bg-purple-900/30 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-2 rounded-xl p-6 border border-green-900/20 hover:border-green-600/40 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 mb-1">Lines of Code</p>
                            <p class="text-3xl font-bold text-green-400">15,000+</p>
                        </div>
                        <div class="w-12 h-12 bg-green-900/30 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-dark-2 rounded-xl p-6 border border-orange-900/20 hover:border-orange-600/40 transition-all duration-300">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-400 mb-1">Project Duration</p>
                            <p class="text-3xl font-bold text-orange-400">6 Months</p>
                        </div>
                        <div class="w-12 h-12 bg-orange-900/30 rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Carousel -->
            <div class="relative mb-12">
                <div id="team-carousel" class="relative overflow-hidden rounded-2xl">
                    <div class="flex transition-transform duration-500 ease-in-out" id="carousel-inner">
                        <?php foreach ($developers as $index => $dev): ?>
                        <div class="w-full flex-shrink-0 px-4">
                            <div class="bg-dark-2 rounded-2xl overflow-hidden border border-gray-700/50 hover:border-gray-600/50 transition-all duration-300 transform hover:scale-[1.02]">
                                <div class="relative h-32 bg-gradient-to-br <?php echo $dev['color']; ?> overflow-hidden">
                                    <div class="absolute inset-0 bg-black/20"></div>
                                    <div class="absolute top-4 right-4">
                                        <span class="bg-white/20 backdrop-blur-sm px-3 py-1 rounded-full text-white text-sm font-medium">
                                            #<?php echo str_pad($index + 1, 2, '0', STR_PAD_LEFT); ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="p-8">
                                    <div class="flex items-start space-x-6">
                                        <div class="relative">
                                            <div class="w-24 h-24 bg-gradient-to-br <?php echo $dev['color']; ?> rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-xl -mt-16 border-4 border-dark-2 group cursor-pointer avatar-container" data-dev-index="<?php echo $index; ?>">
                                                <span class="avatar-text"><?php echo $dev['avatar']; ?></span>
                                                <div class="absolute inset-0 bg-black/40 rounded-2xl opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-200">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                    </svg>
                                                </div>
                                            </div>
                                            <div class="absolute -bottom-2 -right-2 w-8 h-8 bg-green-500 rounded-full border-4 border-dark-2 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-2xl font-bold text-white mb-2"><?php echo $dev['name']; ?></h3>
                                            <p class="text-lg text-teal-400 font-medium mb-4"><?php echo $dev['role']; ?></p>
                                            <p class="text-gray-300 leading-relaxed mb-6"><?php echo $dev['description']; ?></p>
                                            
                                            <div class="mb-6">
                                                <h4 class="text-sm font-semibold text-gray-400 mb-3 uppercase tracking-wider">Specialties</h4>
                                                <div class="flex flex-wrap gap-2">
                                                    <?php foreach ($dev['specialties'] as $specialty): ?>
                                                    <span class="bg-gradient-to-r <?php echo $dev['color']; ?> bg-opacity-20 text-white px-3 py-1 rounded-full text-sm font-medium border border-white/10">
                                                        <?php echo $specialty; ?>
                                                    </span>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="flex space-x-3">
                                                <button class="flex-1 bg-gradient-to-r <?php echo $dev['color']; ?> hover:opacity-90 text-white font-semibold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-dark-2">
                                                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                    </svg>
                                                    View Profile
                                                </button>
                                                <button class="bg-dark-1 hover:bg-dark-3 text-gray-300 font-semibold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 border border-gray-600/50 hover:border-gray-500/50 focus:outline-none">
                                                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                                    </svg>
                                                    Contact
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <button id="prev-btn" class="absolute left-4 top-1/2 transform -translate-y-1/2 w-12 h-12 bg-dark-2/80 hover:bg-dark-2 border border-gray-600/50 rounded-full flex items-center justify-center text-white transition-all duration-200 backdrop-blur-sm hover:scale-110 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>
                <button id="next-btn" class="absolute right-4 top-1/2 transform -translate-y-1/2 w-12 h-12 bg-dark-2/80 hover:bg-dark-2 border border-gray-600/50 rounded-full flex items-center justify-center text-white transition-all duration-200 backdrop-blur-sm hover:scale-110 focus:outline-none focus:ring-2 focus:ring-teal-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                <!-- Indicators -->
                <div class="flex justify-center space-x-2 mt-6">
                    <?php foreach ($developers as $index => $dev): ?>
                    <button class="carousel-indicator w-3 h-3 rounded-full transition-all duration-200 focus:outline-none <?php echo $index === 0 ? 'bg-teal-500' : 'bg-gray-600 hover:bg-gray-500'; ?>" data-slide="<?php echo $index; ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Technology Stack -->
            <div class="bg-dark-2 rounded-2xl p-8 border border-gray-700/50">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-white mb-4">Technology Stack</h2>
                    <p class="text-gray-400 max-w-2xl mx-auto">
                        The powerful technologies and frameworks that made this project possible
                    </p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    <?php 
                    $technologies = [
                        ['name' => 'PHP', 'color' => 'from-purple-500 to-blue-600'],
                        ['name' => 'MySQL', 'color' => 'from-blue-500 to-cyan-600'],
                        ['name' => 'JavaScript', 'color' => 'from-yellow-400 to-orange-500'],
                        ['name' => 'Tailwind CSS', 'color' => 'from-cyan-400 to-blue-500'],
                        ['name' => 'HTML5', 'color' => 'from-orange-500 to-red-600'],
                        ['name' => 'Flowbite', 'color' => 'from-indigo-500 to-purple-600']
                    ];
                    
                    foreach ($technologies as $tech): ?>
                    <div class="group">
                        <div class="bg-gradient-to-br <?php echo $tech['color']; ?> p-0.5 rounded-xl">
                            <div class="bg-dark-1 rounded-xl p-4 h-full group-hover:bg-dark-2 transition-all duration-200">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-gradient-to-br <?php echo $tech['color']; ?> rounded-lg mx-auto mb-3 flex items-center justify-center">
                                        <span class="text-white font-bold text-lg"><?php echo substr($tech['name'], 0, 1); ?></span>
                                    </div>
                                    <h3 class="font-semibold text-white group-hover:text-gray-200"><?php echo $tech['name']; ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Thank You Section -->
            <div class="text-center mt-12 py-12 border-t border-gray-700/50">
                <div class="max-w-3xl mx-auto">
                    <h2 class="text-3xl font-bold text-white mb-4">Thank You</h2>
                    <p class="text-xl text-gray-400 leading-relaxed mb-8">
                        We extend our heartfelt gratitude to everyone who contributed to making the CCS Days Connect system a reality. 
                        This project represents countless hours of dedication, collaboration, and innovation.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <button class="bg-gradient-to-r from-teal-500 to-blue-600 hover:from-teal-600 hover:to-blue-700 text-white font-semibold py-3 px-8 rounded-xl transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-dark-1 focus:ring-teal-500">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            Show Appreciation
                        </button>
                        <a href="dashboard.php" class="bg-dark-1 hover:bg-dark-3 text-gray-300 font-semibold py-3 px-8 rounded-xl transition-all duration-200 transform hover:scale-105 border border-gray-600/50 hover:border-gray-500/50 focus:outline-none inline-block">
                            <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                            </svg>
                            Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/common.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.js"></script>
    <script>
        // Team Carousel Functionality
        class TeamCarousel {
            constructor() {
                this.currentSlide = 0;
                this.totalSlides = <?php echo count($developers); ?>;
                this.carouselInner = document.getElementById('carousel-inner');
                this.indicators = document.querySelectorAll('.carousel-indicator');
                this.prevBtn = document.getElementById('prev-btn');
                this.nextBtn = document.getElementById('next-btn');
                
                this.init();
            }
            
            init() {
                // Add event listeners
                this.prevBtn.addEventListener('click', () => this.prevSlide());
                this.nextBtn.addEventListener('click', () => this.nextSlide());
                
                // Add indicator event listeners
                this.indicators.forEach((indicator, index) => {
                    indicator.addEventListener('click', () => this.goToSlide(index));
                });
                
                // Auto-play functionality
                this.startAutoPlay();
                
                // Pause auto-play on hover
                const carousel = document.getElementById('team-carousel');
                carousel.addEventListener('mouseenter', () => this.pauseAutoPlay());
                carousel.addEventListener('mouseleave', () => this.startAutoPlay());
            }
            
            goToSlide(slideIndex) {
                this.currentSlide = slideIndex;
                const translateX = -slideIndex * 100;
                this.carouselInner.style.transform = `translateX(${translateX}%)`;
                this.updateIndicators();
            }
            
            nextSlide() {
                this.currentSlide = (this.currentSlide + 1) % this.totalSlides;
                this.goToSlide(this.currentSlide);
            }
            
            prevSlide() {
                this.currentSlide = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
                this.goToSlide(this.currentSlide);
            }
            
            updateIndicators() {
                this.indicators.forEach((indicator, index) => {
                    if (index === this.currentSlide) {
                        indicator.classList.remove('bg-gray-600', 'hover:bg-gray-500');
                        indicator.classList.add('bg-teal-500');
                    } else {
                        indicator.classList.remove('bg-teal-500');
                        indicator.classList.add('bg-gray-600', 'hover:bg-gray-500');
                    }
                });
            }
            
            startAutoPlay() {
                this.autoPlayInterval = setInterval(() => {
                    this.nextSlide();
                }, 5000); // Change slide every 5 seconds
            }
            
            pauseAutoPlay() {
                if (this.autoPlayInterval) {
                    clearInterval(this.autoPlayInterval);
                }
            }
        }
        
        // Initialize carousel when DOM is loaded
        document.addEventListener('DOMContentLoaded', function() {
            new TeamCarousel();
            
            // Add smooth scroll behavior for better UX
            document.documentElement.style.scrollBehavior = 'smooth';
            
            // Add intersection observer for animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-fade-in-up');
                    }
                });
            }, observerOptions);
            
            // Observe elements for animation
            document.querySelectorAll('.bg-dark-2').forEach(el => {
                observer.observe(el);
            });
        });
        
        // Add CSS animation class dynamically
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .animate-fade-in-up {
                animation: fadeInUp 0.6s ease-out forwards;
            }
            
            /* Custom scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
            }
            
            ::-webkit-scrollbar-track {
                background: #1a1a1a;
            }
            
            ::-webkit-scrollbar-thumb {
                background: #4a5568;
                border-radius: 4px;
            }
            
            ::-webkit-scrollbar-thumb:hover {
                background: #2d3748;
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
