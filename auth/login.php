<?php
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    include "../includes/db_connection.php";

    $username = $_POST["username"];
    $password = $_POST["password"];
    $role = $_POST["role"] ?? "";

    // Check if role is selected
    if ($role == "") {
        echo "<script>alert('Please select Parent or Child to continue.');</script>";
    } else {

        /* ====================== PARENT LOGIN ====================== */
        if ($role == "parent") {

            $query = "SELECT parent_id, parent_name, parent_password 
                      FROM Parent WHERE parent_username = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                $stmt->bind_result($parent_id, $parent_name, $hashed_pass);
                $stmt->fetch();

                if (password_verify($password, $hashed_pass)) {

                    $_SESSION["parent_id"] = $parent_id;
                    $_SESSION["parent_name"] = $parent_name;
                    $_SESSION["role"] = "parent";

                    echo "<script>
                            alert('Login successful!');
                            window.location.href = '../parent/dashboard.php';
                          </script>";
                    exit;
                } else {
                    echo "<script>alert('Incorrect password. Try again.');</script>";
                }
            } else {
                echo "<script>alert('Parent username not found.');</script>";
            }

            $stmt->close();
        }

        /* ====================== CHILD LOGIN ====================== */
        else if ($role == "child") {

            $query = "SELECT child_id, child_name, child_username, child_password 
                      FROM Child WHERE child_username = ?";
            
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows == 1) {
                
                $stmt->bind_result($child_id, $child_name, $child_username, $hashed_pass);
                $stmt->fetch();

                if (password_verify($password, $hashed_pass)) {

                    $_SESSION["child_id"] = $child_id;
                    $_SESSION["child_name"] = $child_name;
                    $_SESSION["child_username"] = $child_username;
                    $_SESSION["role"] = "child";

                    echo "<script>
                            alert('Login successful!');
                            window.location.href = '../child/dashboard.php';
                          </script>";
                    exit;
                } 
                else {
                    echo "<script>alert('Incorrect password. Try again.');</script>";
                }

            } else {
                echo "<script>alert('Child username not found.');</script>";
            }

            $stmt->close();
        }

    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - KiddoCredits</title>

    <!-- Login CSS -->
    <link rel="stylesheet" href="../css/login.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body>

    <div class="container">

        <img src="../assets/logo.png" class="logo" alt="KiddoCredits Logo">

        <!-- LOGIN TITLE -->
        <h2 class="login_heading">Login</h2>

        <form action="#" method="POST">

            <label>Username</label>
            <input type="text" name="username" placeholder="Enter username" required>

            <label>Password</label>
            <div class="password_box">
                <input type="password" id="password" name="password" placeholder="Enter password" required>
                <span class="toggle_password" onclick="togglePassword()">
                    👁️
                </span>
            </div>

            <label class="role_label">Login as:</label>
            <div class="radio_group">
                <label class="radio_option">
                    <input type="radio" name="role" value="parent"> Parent
                </label>

                <label class="radio_option">
                    <input type="radio" name="role" value="child"> Child
                </label>
            </div>

            <button type="submit" class="btn">Login</button>

        </form>

        <div class="signup_text">
            Don't have a parent account?<br>
            <a href="signup_parent.php">Sign Up</a>
        </div>

    </div>

<script>
function togglePassword() {
    let pass = document.getElementById("password");
    pass.type = (pass.type === "password") ? "text" : "password";
}
</script>

</body>
</html>

