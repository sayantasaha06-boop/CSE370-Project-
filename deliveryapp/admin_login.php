<?php
require __DIR__ . "/config.php";
require __DIR__ . "/helpers.php";

$in = read_json_body();
$email = trim($in["email"] ?? "");
$password = (string)($in["password"] ?? "");

$stmt = $pdo->prepare("SELECT A_id, email, password FROM admin WHERE email = ?");
$stmt->execute([$email]);
$admin = $stmt->fetch();

if (!$admin || $admin["password"] !== $password) {
  respond_json(["message" => "Invalid email/password"], 401);
}

respond_json(["A_id" => (int)$admin["A_id"], "email" => $admin["email"]]);
