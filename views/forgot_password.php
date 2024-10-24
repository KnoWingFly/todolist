<?php
require "../config/db.php";
require "../vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $token = bin2hex(random_bytes(50));
        $stmt = $pdo->prepare(
            "UPDATE users SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE email = ?"
        );
        $stmt->execute([$token, $email]);

        $reset_link = "http://kelompok3hl.aur-auran.my.id/views/reset_password.php?token=" . $token;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "unknownowl26@gmail.com";
            $mail->Password = "dbst pvbk dasu xmmb"; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom("your-email@gmail.com", "Todo List Website");
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = "Password Reset Request";
            $mail->Body = "Click the following link to reset your password: <a href='$reset_link'>$reset_link</a>";

            $mail->send();
            $success = "A password reset link has been sent to your email.";
        } catch (Exception $e) {
            $error = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Email not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="./node_modules/bootstrap/dist/css/bootstrap.min.css">
    <script src="../node_modules/jquery/dist/jquery.min.js"></script>
    <script src="../node_modules/jquery-ui-dist/jquery-ui.min.js"></script>
    <script src="../node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 0.5rem;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
        .form-label {
            font-weight: 600;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card" style="width: 24rem;">
        <div class="card-body">
            <h5 class="card-title text-center">Forgot Password</h5>
            <p class="text-center text-muted">Enter your email address to reset your password.</p>
            <form action="" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" required autocomplete="off" class="form-control" placeholder="you@example.com">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
            </form>

            <?php if ($error): ?>
                <div class="alert alert-danger mt-3">
                    <strong>Error!</strong> <?= $error ?>
                </div>
            <?php elseif ($success): ?>
                <div class="alert alert-success mt-3">
                    <strong>Success!</strong> <?= $success ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
