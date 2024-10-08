<?php
require "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$username = htmlspecialchars($_POST["username"]);
	$email = htmlspecialchars($_POST["email"]);
	$password = password_hash($_POST["password"], PASSWORD_BCRYPT);

	// Insert user into the users table
	$stmt = $pdo->prepare(
		"INSERT INTO users (username, email, password) VALUES (:username, :email, :password)"
	);
	$stmt->execute([
		"username" => $username,
		"email" => $email,
		"password" => $password,
	]);

	header("Location: ../views/login.php");
}
