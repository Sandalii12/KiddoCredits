<?php
// Start session if not started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If no role is set → user is not logged in
if (!isset($_SESSION["role"])) {
    echo "<script>
            alert('Please login to continue.');
            window.location.href = '../auth/login.php';
          </script>";
    exit;
}

// ROLE-BASED PAGE PROTECTION
$current_path = $_SERVER['PHP_SELF']; // ex: /parent/dashboard.php

// If user is PARENT
if ($_SESSION["role"] === "parent") {
    // Parent accessing CHILD pages → BLOCK
    if (strpos($current_path, "/child/") !== false) {
        echo "<script>
                alert('Access denied! Child pages cannot be accessed by parent.');
                window.location.href = '../auth/login.php';
              </script>";
        exit;
    }
}

// If user is CHILD
if ($_SESSION["role"] === "child") {
    // Child accessing PARENT pages → BLOCK
    if (strpos($current_path, "/parent/") !== false) {
        echo "<script>
                alert('Access denied! Parent pages cannot be accessed by child.');
                window.location.href = '../auth/login.php';
              </script>";
        exit;
    }
}
?>
