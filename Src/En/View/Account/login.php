<?php
session_start();

$host = "sql5.freesqldatabase.com"; 
$user = "sql5767007";           
$pass = "jLRCnZ3l23";            
$dbname = "sql5767007";            

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'] ?? null;
    $password = $_POST['password'] ?? null;
    $errors = [];

    if (!$username || !$password) {
        $errors[] = "Please enter both username and password.";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $user_id;
                $_SESSION['username'] = $username;
                header("Location: user_panel.html"); 
                exit;
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "Username not found.";
        }

        $stmt->close();
    }

    if (!empty($errors)) {
        echo implode("<br>", $errors);
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sharenzy - Login</title>
    <link rel="stylesheet" href="../../../Engine/Ui/Main/main.css">
    <link rel="stylesheet" href="../../../Engine/Ui/Account/login.css"> 
    <script src="../../../Engine/Scripts/main_sidebar.js" defer></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Login</h1>
        </header>
        
        <nav class="main-nav-sidebar" id="sidebar">
            <div class="menu-icon" id="menu-icon">
                <img src="../../../Engine/Images/Svg/Menu.svg" alt="Menu Icon" width="24" height="24">
            </div>
        
            <a href="View/Account/login.php" class="account-icon">
                <img src="../../../Engine/Images/Svg/Account.svg" alt="Account Icon" width="40" height="40">
            </a>
        
            <ul>
                <li><a href="../../index.html">Home</a></li>
                <li><a href="#profile">Profile</a></li>
                <li><a href="#messages">Messages</a></li>
                <li><a href="#explore">Explore</a></li>
                <li><a href="#settings">Settings</a></li>
                <li><a href="View/Terms/terms_of_service.html">Terms Of Service</a></li>
            </ul>
        </nav>

        <div class="login-card">
            <form class="login-form" method="POST">
                <div class="input-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>

                <div class="input-group">
    <label for="password" class="link-btn">Password</label>
    <input type="password" id="password" name="password" required>
</div>

<div class="action-group">
    <button type="submit" class="login-btn">Login</button>
    <div class="create-account">
        <p><a href="create.php" class="link-btn">Forgot Password?</a></p>
        <p>Don't Have An Account? <a href="create.php" class="link-btn">Create An Account Here</a></p>
    </div>
</div>

           

    </div>

    <footer>
            <p>© 2025 Sharenzy. All Rights Reserved.</p>
        </footer>
</body>
</html>
