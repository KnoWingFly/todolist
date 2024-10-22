<?php
session_start();
require "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$email = htmlspecialchars($_POST["email"]);
	$password = $_POST["password"];

	$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
	$stmt->execute(["email" => $email]);
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	if ($user && password_verify($password, $user["password"])) {
		$_SESSION["user_id"] = $user["id"];
		header("Location: ../index.php");
	} else {
		header("Location: ../views/login.php?error=1");
	}
}
