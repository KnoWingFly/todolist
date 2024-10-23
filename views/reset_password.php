<?php
require "../config/db.php";
require '../vendor/autoload.php';

$error = "";
$success = "";

// Check if the token is in the URL
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Verify the token and check if it has expired
    $stmt = $pdo->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
        // If the form is submitted
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            $new_password = $_POST["password"];
            $confirm_password = $_POST["confirm_password"];

            // Check if the passwords match
            if ($new_password === $confirm_password) {
                // Hash the new password
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update the password and clear the reset token
                $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
                $stmt->execute([$hashed_password, $token]);

                $success = "Your password has been successfully reset!";
            } else {
                $error = "Passwords do not match. Please try again.";
            }
        }
    } else {
        $error = "Invalid or expired token. Please request a new password reset.";
    }
} else {
    $error = "No token provided.";
}
?>

<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-base-300 min-h-screen flex items-center justify-center">

<div class="card w-96 bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title justify-center mb-4">Reset Password</h2>

        <?php if ($error): ?>
            <div class="alert alert-error shadow-lg mb-4">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><?= $error ?></span>
                </div>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success shadow-lg mb-4">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span><?= $success ?></span>
                </div>
            </div>
        <?php else: ?>
            <form action="" method="post">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text">New Password</span>
                    </label>
                    <input type="password" name="password" required autocomplete="off" class="input input-bordered">
                </div>
                <div class="form-control mt-4">
                    <label class="label">
                        <span class="label-text">Confirm New Password</span>
                    </label>
                    <input type="password" name="confirm_password" required autocomplete="off" class="input input-bordered">
                </div>
                <div class="form-control mt-6">
                    <button type="submit" class="btn btn-primary">Reset Password</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
