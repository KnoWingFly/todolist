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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css">
    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa; /* Light background color */
        }
        .container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            border-radius: 1rem; /* Rounded corners */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1); /* Subtle shadow */
        }
        .btn-primary {
            background-color: #007bff; /* Bootstrap primary color */
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3; /* Darker shade on hover */
            border-color: #0056b3;
        }
        .password-requirements {
            display: none; /* Hide by default */
            position: absolute;
            background-color: rgba(255, 255, 255, 0.9);
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            width: 90%; /* Adjust to match the input width */
            left: 5%; /* Center below the input */
            margin-top: 5px; /* Space between input and requirements */
        }

        .password-requirements.active {
            display: block; /* Show when active */
        }

        .requirement-met {
            color: green;
        }

        .requirement-not-met {
            color: red;
        }

        .match-password {
            display: none; /* Hide by default */
            color: green;
            margin-top: 5px;
        }

        .not-match-password {
            display: none; /* Hide by default */
            color: red;
            margin-top: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card w-50">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Reset Password</h2>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <strong>Error!</strong> <?= $error ?>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success">
                    <strong>Success!</strong> <?= $success ?>
                </div>
            <?php else: ?>
                <form action="" method="post">
                    <div class="mb-3">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" id="password" required autocomplete="off" class="form-control">
                        <div id="password-popup" class="password-requirements bg-info">
                            <ul id="password-requirements-list" class="list-disc list-inside">
                                <li id="min-length" class="requirement-not-met">At least 12 characters</li>
                                <li id="uppercase" class="requirement-not-met">At least one uppercase letter</li>
                                <li id="lowercase" class="requirement-not-met">At least one lowercase letter</li>
                                <li id="number" class="requirement-not-met">At least one number</li>
                                <li id="special-char" class="requirement-not-met">At least one special character</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" required autocomplete="off" class="form-control">
                        <div id="confirm-password-message" class="match-password">Passwords match!</div>
                        <div id="confirm-password-message-not" class="not-match-password">Passwords do not match.</div>
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Show password requirements on focus
        $('#password').on('focus', function() {
            $('#password-popup').addClass('active');
        }).on('blur', function() {
            $('#password-popup').removeClass('active');
        }).on('input', function() {
            validatePassword($(this).val());
        });

        $('#confirm_password').on('input', function() {
            checkConfirmPassword($(this).val(), $('#password').val());
        });

        function validatePassword(password) {
            const minLength = password.length >= 12;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecialChar = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            $('#min-length').toggleClass('requirement-met', minLength).toggleClass('requirement-not-met', !minLength);
            $('#uppercase').toggleClass('requirement-met', hasUppercase).toggleClass('requirement-not-met', !hasUppercase);
            $('#lowercase').toggleClass('requirement-met', hasLowercase).toggleClass('requirement-not-met', !hasLowercase);
            $('#number').toggleClass('requirement-met', hasNumber).toggleClass('requirement-not-met', !hasNumber);
            $('#special-char').toggleClass('requirement-met', hasSpecialChar).toggleClass('requirement-not-met', !hasSpecialChar);

            // Enable the submit button if all requirements are met
            const allRequirementsMet = minLength && hasUppercase && hasLowercase && hasNumber && hasSpecialChar;
            $('button[type="submit"]').prop('disabled', !allRequirementsMet);
        }

        function checkConfirmPassword(confirmPassword, newPassword) {
            if (confirmPassword === newPassword && confirmPassword !== '') {
                $('#confirm-password-message').show();
                $('#confirm-password-message-not').hide();
            } else {
                $('#confirm-password-message').hide();
                $('#confirm-password-message-not').show();
            }
        }
    });
</script>
</body>
</html>
