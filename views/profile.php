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
	<style>
        body {
            background-color: #1a1a1a;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            max-width: 400px;
            width: 100%;
        }
        .btn-primary {
            background-color: #333;
            border-color: #333;
        }
        .btn-primary:hover {
            background-color: #444;
            border-color: #444;
        }
        .form-control:focus, .form-select:focus {
            border-color: #333;
            box-shadow: 0 0 0 0.25rem rgba(51, 51, 51, 0.25);
        }
		@media (max-width: 576px) {
            .card {
                margin: 10px;
            }
            h2 {
                font-size: 1.5rem;
            }
        }

		
    </style>
</head>
<body class="bg-light">

  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-lg p-4" style="max-width: 500px; width: 100%;">
      <h2 class="text-center mb-4">Your Profile</h2>
      <form>
        <div class="mb-3">
          <label for="username" class="form-label">Username</label>
          <input type="text" class="form-control" id="username" placeholder="Enter your username">
        </div>

        <div class="mb-3">
          <label for="email" class="form-label">Email</label>
          <input type="email" class="form-control" id="email" placeholder="Enter your email">
        </div>

        <div class="mb-3">
          <label for="password" class="form-label">New Password <small>(Leave empty to keep current password)</small></label>
          <input type="password" class="form-control" id="password" placeholder="Leave empty to keep current password">
        </div>

        <button type="submit" class="btn btn-primary w-100">Save Changes</button>

        <a href="../index.php" class="btn btn-secondary w-100 mt-3">Back to Dashboard</a>
      </form>
    </div>
  </div>
</body>
</html>
