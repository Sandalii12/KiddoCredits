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
        <h2 class="header-title">KiddoCredits</h2>
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
        <li><a href="../parent/dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Home</a></li>
        <li><a href="../parent/children.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'children.php' ? 'active' : ''; ?>">My Children</a></li>
        <li><a href="../parent/tasks.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'tasks.php' ? 'active' : ''; ?>">Task List</a></li>
        <li><a href="../parent/reward_list.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reward_list.php' ? 'active' : ''; ?>">Reward List</a></li>

        <!-- Logout with confirmation popup (push to right) -->
        <li style="margin-left: auto;"><a href="../auth/logout.php" onclick="return confirmLogout()">Logout</a></li>
    </ul>
</nav>
<!-- UNIVERSAL SIDECARD -->
<!-- Backdrop for sidecard (clicking it will close the sidecard) -->
<div id="sideCardBackdrop" class="sidecard-backdrop hidden" aria-hidden="true"></div>

<!-- UNIVERSAL SIDECARD -->
<div id="sideCard" class="side-card hidden" aria-hidden="true">
  <div class="side-card-content">

    <button class="side-close" id="sideCardClose">&times;</button>

    <h3 id="sideCardTitle">Form</h3>

    <form id="sideCardForm" method="POST">
        <input type="hidden" name="mode" value="">
        <input type="hidden" name="task_id" value="0">
        <input type="hidden" name="entity_id" value="0">
        <div id="sideCardFields"></div>

        <div class="actions" style="display:flex;gap:12px;margin-top:15px;">
            <button type="submit" class="btn">Save</button>
            <button type="button" class="btn outline" id="sideCardCancel">Cancel</button>
        </div>
    </form>

  </div>
</div>



<!-- ========================= MAIN CONTENT WRAPPER ========================= -->
<div class="parent-content">

<!-- ==================== LOGOUT CONFIRM SCRIPT ==================== -->
<script>
function confirmLogout() {
    return confirm("Are you sure you want to logout?");
}
</script>
