<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"])) {
	header("Location: views/login.php");
	exit();
}

$user_id = $_SESSION["user_id"];

// Fetch user profile information
$stmt = $pdo->prepare("SELECT username, email FROM users WHERE id = :user_id");
$stmt->execute(["user_id" => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$username = $_POST["username"];
	$email = $_POST["email"];
	$password = !empty($_POST["password"])
		? password_hash($_POST["password"], PASSWORD_DEFAULT)
		: null;

	// Update user information
	if ($password) {
		$stmt = $pdo->prepare(
			"UPDATE users SET username = :username, email = :email, password = :password WHERE id = :user_id"
		);
		$stmt->execute([
			"username" => $username,
			"email" => $email,
			"password" => $password,
			"user_id" => $user_id,
		]);
	} else {
		$stmt = $pdo->prepare(
			"UPDATE users SET username = :username, email = :email WHERE id = :user_id"
		);
		$stmt->execute([
			"username" => $username,
			"email" => $email,
			"user_id" => $user_id,
		]);
	}

	header("Location: profile.php");
	exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="../node_modules/bootstrap/dist/css/bootstrap.min.css" />
</head>
<body>
    <div class="container mt-5">
        <h1 class="text-center">Your Profile</h1>

        <!-- Profile Form -->
        <form method="POST" action="profile.php">
            <div class="form-group mb-3">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars(
                	$user["username"]
                ) ?>" required>
            </div>

            <div class="form-group mb-3">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars(
                	$user["email"]
                ) ?>" required>
            </div>

            <div class="form-group mb-3">
                <label for="password">New Password (Leave empty to keep current password)</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>

            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>

        <a href="../index.php" class="btn btn-secondary mt-3">Back to Dashboard</a>
    </div>
</body>
</html>
