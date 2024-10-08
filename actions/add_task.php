<?php
session_start();
require "../config/db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
	$title = $_POST["taskTitle"];
	$dueDate = $_POST["dueDate"];
	$status = $_POST["status"];
	$cardColor = $_POST["card_color"]; 
	$label = $_POST["taskLabel"];
	$user_id = $_SESSION["user_id"];

	$stmt = $pdo->prepare(
		"SELECT id FROM todo_lists WHERE user_id = :user_id LIMIT 1"
	);
	$stmt->execute(["user_id" => $user_id]);
	$list = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$list) {
		echo "No to-do list found for this user.";
		exit();
	}

	$list_id = $list["id"];

	$stmt = $pdo->prepare(
		"INSERT INTO tasks (list_id, title, due_date, status, card_color, label) VALUES (:list_id, :title, :due_date, :status, :card_color, :label)"
	);
	$stmt->execute([
		"list_id" => $list_id,
		"title" => $title,
		"due_date" => $dueDate,
		"status" => $status,
		"card_color" => $cardColor,
		"label" => $label,
	]);

	header("Location: ../index.php");
	exit();
}
