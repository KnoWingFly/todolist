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

		$reset_link = "http://localhost:8000/user/reset_password.php?token=" . $token;

		$mail = new PHPMailer(true);

		try {
			$mail->isSMTP();
			$mail->Host = "smtp.gmail.com";
			$mail->SMTPAuth = true;
			$mail->Username = "unknownowl26@gmail.com";
			$mail->Password = "dbst pvbk dasu xmmb";
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			$mail->Port = 587;

			//Recipients
			$mail->setFrom("your-email@gmail.com", "Event Website");
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
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link href="https://cdn.jsdelivr.net/npm/daisyui@2.51.5/dist/full.css" rel="stylesheet" type="text/css" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-base-300 min-h-screen flex items-center justify-center">

<div class="card w-96 bg-base-100 shadow-xl">
    <div class="card-body">
        <h2 class="card-title justify-center mb-4">Forgot Password</h2>
        <form action="" method="post">
            <div class="form-control">
                <label class="label">
                    <span class="label-text">Email Address</span>
                </label>
                <input type="email" name="email" required autocomplete="off" class="input input-bordered">
            </div>
            <div class="form-control mt-6">
                <button type="submit" class="btn btn-primary">Send Reset Link</button>
            </div>
        </form>

        <?php if ($error): ?>
            <div class="alert alert-error shadow-lg mt-4">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span><?= $error ?></span>
                </div>
            </div>
        <?php elseif ($success): ?>
            <div class="alert alert-success shadow-lg mt-4">
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current flex-shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    <span><?= $success ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
