<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>KiddoCredits - Parent Panel</title>

    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Parent CSS -->
    <link rel="stylesheet" href="../css/parent.css">

</head>
<body>

<!-- ========================= HEADER ========================= -->
<header class="parent-header">
    <div class="header-left">
        <img src="../assets/logo.png" alt="KiddoCredits Logo" class="header-logo">
        <h1 class="header-title">KiddoCredits</h1>
    </div>
    <div class="header-right">
        <span class="welcome-text">
            Welcome, <?php echo $_SESSION['parent_name'] ?? 'Parent'; ?>
        </span>
    </div>
</header>

<!-- ========================= NAVBAR ========================= -->
<nav class="parent-navbar">
    <ul class="navbar-links">
        <li><a href="../parent/dashboard.php">Home</a></li>
        <li><a href="../parent/children.php">My Children</a></li>
        <li><a href="../parent/tasks.php">Task List</a></li>
        <li><a href="../parent/reward_add.php">Add Reward</a></li>
        <li><a href="../parent/reward_list.php">Reward List</a></li>

        <!-- Logout with confirmation popup -->
        <li><a href="../auth/logout.php" onclick="return confirmLogout()">Logout</a></li>
    </ul>
</nav>

<!-- ========================= MAIN CONTENT WRAPPER ========================= -->
<div class="parent-content">

<!-- ==================== LOGOUT CONFIRM SCRIPT ==================== -->
<script>
function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}
</script>
