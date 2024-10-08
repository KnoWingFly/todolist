<?php
session_start();
require "../config/db.php";

if (!isset($_SESSION["user_id"])) {
	header("Location: ../views/login.php");
	exit();
}

$query = htmlspecialchars($_GET["query"]);
$list_id = $_GET["list_id"];

$stmt = $pdo->prepare(
	"SELECT * FROM tasks WHERE list_id = :list_id AND task_description LIKE :query"
);
$stmt->execute(["list_id" => $list_id, "query" => "%" . $query . "%"]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

include "../views/tasks.php";
