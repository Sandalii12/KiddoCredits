<?php
// -------------------------------------
// BACKEND LOGIC FOR SIGNUP (POST REQUEST)
// -------------------------------------

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Include database connection
    include "../includes/db_connection.php";

    // Getting form values
    $parent_name = $_POST["parent_name"];
    $parent_username = $_POST["parent_username"];
    $parent_password = $_POST["parent_password"];

    // Check if username already exists
    $checkQuery = "SELECT parent_id FROM Parent WHERE parent_username = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $parent_username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('Username already exists! Please choose a different one.');</script>";
    } else {

        // Password Hashing
        $hashedPassword = password_hash($parent_password, PASSWORD_DEFAULT);

        // Insert query
        $insertQuery = "INSERT INTO Parent (parent_name, parent_username, parent_password)
                        VALUES (?, ?, ?)";

        $stmt_insert = $conn->prepare($insertQuery);
        $stmt_insert->bind_param("sss", $parent_name, $parent_username, $hashedPassword);

        if ($stmt_insert->execute()) {
            echo "<script>
                    alert('Signup successful! Please log in.');
                    window.location.href = 'login.php';
                  </script>";
            exit;
        } else {
            echo "<script>alert('Error: Unable to create account. Please try again.');</script>";
        }
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Signup - KiddoCredits</title>

    <!-- CSS for signup -->
    <link rel="stylesheet" href="../css/parent.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>

    <div class="container">

        <img src="../assets/logo.png" class="logo" alt="KiddoCredits Logo">

        <!-- BIG SIGNUP TITLE -->
        <h2 class="signup_heading">Sign Up</h2>

        <form action="../auth/signup_parent.php" method="POST">

            <label>Full Name</label>
            <input type="text" name="parent_name" placeholder="Enter full name" required>

            <label>Username</label>
            <input type="text" name="parent_username" placeholder="Choose username" required>

            <label>Password</label>
            <input type="password" name="parent_password" placeholder="Choose password" required>

            <button type="submit" class="btn">Create Parent Account</button>

        </form>

    </div>

</body>
</html>
