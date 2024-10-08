<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"])) {
	header("Location: ../views/login.php");
	exit();
}

if (isset($_GET["list_id"])) {
	$list_id = $_GET["list_id"];

	$stmt = $pdo->prepare(
		"DELETE FROM todo_lists WHERE id = :id AND user_id = :user_id"
	);
	$stmt->execute(["id" => $list_id, "user_id" => $_SESSION["user_id"]]);

	header("Location: ../index.php");
}
