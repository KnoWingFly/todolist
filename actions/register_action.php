<?php
require "../config/db.php";

$error = "";

// Backend Password Validation Logic
function isValidPassword($password) {
    return preg_match('/[A-Z]/', $password) &&    // At least one uppercase letter
           preg_match('/[a-z]/', $password) &&    // At least one lowercase letter
           preg_match('/\d/', $password) &&       // At least one number
           preg_match('/[^A-Za-z0-9]/', $password) && // At least one special character
           strlen($password) >= 12;               // Minimum length of 12 characters
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars($_POST["username"]);
    $email = htmlspecialchars($_POST["email"]);
    $password = $_POST["password"];

    // Password validation
    if (!isValidPassword($password)) {
        $error = "Password does not meet the required criteria!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert user into the users table
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)"
        );
        $stmt->execute([
            "username" => $username,
            "email" => $email,
            "password" => $hashed_password,
        ]);

        header("Location: ../views/login.php");
        exit();
    }
}
