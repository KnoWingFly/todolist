<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"])) {
	header("Location: ../views/login.php");
	exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = htmlspecialchars($_POST["username"]);
	$email = htmlspecialchars($_POST["email"]);
	$password = $_POST["password"];

	$user_id = $_SESSION["user_id"];

	if (!empty($password)) {
		$password_hash = password_hash($password, PASSWORD_BCRYPT);
		$stmt = $pdo->prepare(
			"UPDATE users SET username = :username, email = :email, password = :password WHERE id = :id"
		);
		$stmt->execute([
			"username" => $username,
			"email" => $email,
			"password" => $password_hash,
			"id" => $user_id,
		]);
	} else {
		$stmt = $pdo->prepare(
			"UPDATE users SET username = :username, email = :email WHERE id = :id"
		);
		$stmt->execute([
			"username" => $username,
			"email" => $email,
			"id" => $user_id,
		]);
	}

	header("Location: ../index.php");
}
