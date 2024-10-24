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

        $reset_link = "http://localhost:8000/views/reset_password.php?token=" . $token;

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = "unknownowl26@gmail.com";
            $mail->Password = "dbst pvbk dasu xmmb"; // Consider using environment variables for sensitive data
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;400;500;600;700;800;900&display=swap");

        html, body { 
            min-height: 100vh; 
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Poppins", sans-serif;
            background-color: rgb(15, 23, 42);
        }

        .forgot_password {
            min-height: 100px; 
            align-items: center;
            text-align: center;
            padding: 30px;
            border-radius: 10px;
            background-color: #fff;
            box-shadow: 0px 0px 15px rgba(0,0,0,0.05);
        }

        .group {
            margin: 20px 0px;
        }

        .group input {
            border: 0;
            outline: none;
            font-size: 13px;
            padding: 0 10px;
            border-bottom: 1px solid #aaa;
            transition: 0.5s;
        }

        .group input:hover {
            border-bottom: 1px solid #000;
        }

        .group button {
            width: 100%; 
            height: 45px;
            outline: none;
            border: none;
            background-color: #000;
            color: #fff;
            font-weight: 600;
            font-size: 16px;
            border-radius: 5px;
            transition: 0.5s;
        }

        .group button:hover {
            background-color: #0056b3;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="forgot_password">
    <h5 class="card-title mb-4">Forgot Password</h5>
    <p class="text-muted">Enter your email address to reset your password.</p>
    <form action="" method="post" class="group">
        <div class="mb-3">
            <input type="email" name="email" id="email" required autocomplete="off" placeholder="you@example.com" class="form-control mx-auto" style="max-width: 280px;">
        </div>
        <div class="text-center"> 
            <button type="submit" class="btn btn-dark"><span>Send Reset Link</span></button>
        </div>
    </form>
    
    <?php if ($error): ?>
        <div class="alert alert-danger">
            <strong>Error!</strong> <?= $error ?>
        </div>
    <?php elseif ($success): ?>
        <div class="alert alert-success">
            <strong>Success!</strong> <?= $success ?>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
