<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"])) {
	header("Location: ../views/login.php");
	exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
	$title = htmlspecialchars($_POST["title"]);
	$user_id = $_SESSION["user_id"];

	$stmt = $pdo->prepare(
		"INSERT INTO todo_lists (user_id, title) VALUES (:user_id, :title)"
	);
	$stmt->execute(["user_id" => $user_id, "title" => $title]);

	header("Location: ../index.php");
}
