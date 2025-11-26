<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if child is logged in
if (!isset($_SESSION['child_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$childName = $_SESSION['child_name'] ?? 'Child';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KiddoCredits - Child Dashboard</title>

    <!-- Child CSS -->
    <link rel="stylesheet" href="../css/child.css">
</head>

<body class="dashboard-body">

<!-- ==================== CHILD HEADER ==================== -->
<header class="parent-header">
    <div class="header-left">
        <!-- For quick local preview you can use the uploaded image path below.
             In your project deployment use "../assets/logo.png" instead. -->
       <img src="../assets/logo.png" class="header-logo" alt="KiddoCredits Logo">

        <h2 class="header-title">KiddoCredits</h2>
    </div>

    <div class="header-right">
        <span class="welcome-text">Welcome, <?php echo htmlspecialchars($childName); ?> 👋</span>
    </div>
</header>

<!-- ==================== CHILD NAVBAR ==================== -->
<nav class="parent-navbar">
    <ul class="navbar-links child-navbar">

        <li><a href="../child/dashboard.php" 
               class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            Home
        </a></li>

        <li><a href="../child/tasks.php"
               class="<?php echo basename($_SERVER['PHP_SELF']) == 'tasks.php' ? 'active' : ''; ?>">
            Tasks
        </a></li>

        <li><a href="../child/reward_catalogue.php"
               class="<?php echo basename($_SERVER['PHP_SELF']) == 'reward_catalogue.php' ? 'active' : ''; ?>">
            Reward Store
        </a></li>

        <li><a href="../child/wallet.php"
               class="<?php echo basename($_SERVER['PHP_SELF']) == 'wallet.php' ? 'active' : ''; ?>">
            Credits Log
        </a></li>

        <!-- RIGHT SIDE LOGOUT -->
        <li style="margin-left: auto;">
            <a href="../auth/logout.php" onclick="return confirmLogout()">
            Logout
            </a>
        </li>

    </ul>
</nav>

<!-- ==================== LOGOUT CONFIRM SCRIPT ==================== -->
<script>
function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}
</script>

<!-- ==================== MAIN CONTENT WRAPPER ==================== -->
<div class="parent-content">
