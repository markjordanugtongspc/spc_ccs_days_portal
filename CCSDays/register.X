<?php
session_start();
require_once 'includes/config.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirmPassword = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $role = 'admin'; // Fixed role as admin
    
    $errors = [];
    
    // Validate inputs
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM staff WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists";
        }
        $stmt->close();
    }
    
    // Insert new admin if no errors
    if (empty($errors)) {
        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Verify the hash works before storing it
        if (password_verify($password, $hashedPassword)) {
            $stmt = $conn->prepare("INSERT INTO staff (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);
            
            if ($stmt->execute()) {
                // Log the successful registration and the hash used
                error_log("Admin registered successfully: " . $email);
                $success = "Admin registered successfully! <a href='index.php'>Login here</a>";
            } else {
                $errors[] = "Error registering admin: " . $conn->error;
            }
            $stmt->close();
        } else {
            $errors[] = "Error creating secure password hash. Please try again.";
        }
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <title>CCS Days - Admin Registration</title>
    <link rel="icon" href="includes/images/spc-ccs-logo.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet"
    />
    <link rel="stylesheet" href="styles.css">
</head>
<body class="bg-dark-1 text-light">
    <!-- Navigation Header -->
    <nav
        class="text-light shadow-md p-4 fixed top-0 left-0 w-full z-40 bg-dark-2 bg-opacity-90 backdrop-blur-sm"
    >
        <div class="flex items-center justify-between">
            <!-- Logo Icon -->
            <div class="flex items-center">
                <a href="index.php">
                    <img src="includes/images/spc-ccs-logo.png" alt="CCS Logo" class="h-10 w-10">
                </a>
            </div>
            <!-- Navigation Links -->
            <div class="flex items-center space-x-6">
                <a
                    href="index.php"
                    class="hover-teal transition-all text-teal-light text-lg font-medium"
                >
                    Login
                </a>
                <a
                    href="register.php"
                    class="hover-teal transition-all text-teal-light border-b-2 border-teal text-lg font-medium"
                >
                    Register
                </a>
                <button id="themeToggle" class="text-teal-light hover-teal transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <!-- Registration Form Section -->
    <div class="flex flex-col md:flex-row h-screen">
        <!-- Left Side - Registration Form -->
        <div class="md:w-1/2 flex items-center justify-center p-8 bg-dark-1">
            <div class="bg-dark-2 p-8 rounded-xl shadow-teal w-full max-w-md border border-teal border-opacity-30 backdrop-blur-sm">
                <h2 class="text-4xl font-extrabold mb-8 text-center text-teal-light tracking-tight">
                    Admin Registration
                </h2>
                
                <?php if (isset($errors) && !empty($errors)): ?>
                    <div class="bg-red-900 bg-opacity-50 border border-red-500 text-light p-4 mb-6 rounded-lg">
                        <ul class="list-disc list-inside">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($success)): ?>
                    <div class="bg-teal bg-opacity-20 border border-teal-light text-light p-4 mb-6 rounded-lg">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="register.php" class="text-left">
                    <!-- Name Input -->
                    <div class="mb-6">
                        <label
                            for="name"
                            class="block text-xl text-teal-light mb-2 font-medium tracking-wide"
                        >
                            Name
                        </label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"
                            class="w-full px-4 py-3 rounded-lg bg-dark-1 border border-teal border-opacity-50 text-light focus:outline-none focus:border-teal-light focus:ring-2 focus:ring-teal focus:ring-opacity-20 shadow-inner-teal focus-animation text-base font-medium"
                            placeholder="Enter your full name"
                        />
                    </div>

                    <!-- Email Input -->
                    <div class="mb-6">
                        <label
                            for="email"
                            class="block text-xl text-teal-light mb-2 font-medium tracking-wide"
                        >
                            Email
                        </label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"
                            class="w-full px-4 py-3 rounded-lg bg-dark-1 border border-teal border-opacity-50 text-light focus:outline-none focus:border-teal-light focus:ring-2 focus:ring-teal focus:ring-opacity-20 shadow-inner-teal focus-animation text-base font-medium"
                            placeholder="Enter your email"
                        />
                    </div>

                    <!-- Password Input -->
                    <div class="mb-6">
                        <label
                            for="password"
                            class="block text-xl text-teal-light mb-2 font-medium tracking-wide"
                        >
                            Password
                        </label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="w-full px-4 py-3 rounded-lg bg-dark-1 border border-teal border-opacity-50 text-light focus:outline-none focus:border-teal-light focus:ring-2 focus:ring-teal focus:ring-opacity-20 shadow-inner-teal focus-animation text-base font-medium"
                            placeholder="Enter your password"
                        />
                    </div>
                    
                    <!-- Confirm Password Input -->
                    <div class="mb-8">
                        <label
                            for="confirm_password"
                            class="block text-xl text-teal-light mb-2 font-medium tracking-wide"
                        >
                            Confirm Password
                        </label>
                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="w-full px-4 py-3 rounded-lg bg-dark-1 border border-teal border-opacity-50 text-light focus:outline-none focus:border-teal-light focus:ring-2 focus:ring-teal focus:ring-opacity-20 shadow-inner-teal focus-animation text-base font-medium"
                            placeholder="Confirm your password"
                        />
                    </div>

                    <!-- Register Button -->
                    <button
                        type="submit"
                        class="w-full bg-gradient-button hover:opacity-90 text-light py-3 px-6 rounded-lg font-bold text-lg tracking-wide transition-all shadow-teal btn-click-animation"
                    >
                        Register Admin
                    </button>
                    
                    <!-- Login link -->
                    <div class="text-center mt-6">
                        <p class="text-light text-lg">
                            Already have an account? 
                            <a href="index.php" class="text-teal-light hover-teal transition-all font-medium">Login</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side - Welcome Section -->
        <div class="md:w-1/2 bg-dark-2 flex items-center justify-center relative overflow-hidden h-screen">
            <div class="absolute inset-0">
                <div class="absolute inset-0 ripple-bg ripple-animation"></div>
                <div class="absolute inset-0 shimmer"></div>
                <div class="absolute inset-0 grid grid-cols-4 gap-8 p-8">
                    <!-- Decorative circles -->
                    <div class="h-16 w-16 rounded-full bg-teal opacity-5"></div>
                    <div class="h-24 w-24 rounded-full bg-teal-light opacity-3"></div>
                    <div class="h-20 w-20 rounded-full bg-teal opacity-5"></div>
                    <div class="h-12 w-12 rounded-full bg-teal-light opacity-3"></div>
                    <div class="h-16 w-16 rounded-full bg-teal-light opacity-3"></div>
                    <div class="h-24 w-24 rounded-full bg-teal opacity-5"></div>
                    <div class="h-20 w-20 rounded-full bg-teal-light opacity-3"></div>
                    <div class="h-12 w-12 rounded-full bg-teal opacity-5"></div>
                </div>
            </div>

            <div class="z-10 text-center max-w-lg p-8">
                <div class="float-animation mb-8 inline-block">
                    <img
                        src="includes/images/spc-ccs-logo.png"
                        alt="SPC CCS Logo"
                        class="w-32 h-32 mx-auto"
                    />
                </div>
                <h1 class="text-5xl font-extrabold mb-4 text-light tracking-tight leading-tight">
                    Admin
                    <span class="text-teal-light">Registration</span>
                </h1>
                <p class="text-xl text-light opacity-90 mb-8 font-normal leading-relaxed">
                    Register a new administrator account to manage CCS events and attendance.
                    This registration is only intended for authorized personnel.
                </p>
                <div class="flex justify-center space-x-2">
                    <span class="h-3 w-3 rounded-full bg-teal"></span>
                    <span class="h-3 w-3 rounded-full bg-teal-light"></span>
                    <span class="h-3 w-3 rounded-full bg-teal"></span>
                </div>
            </div>
        </div>
    </div>

    <script src="js/script.js"></script>
</body>
</html> 