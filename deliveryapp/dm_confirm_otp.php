<?php
require __DIR__ . "/config.php";
require __DIR__ . "/helpers.php";

$in = read_json_body();
$dm_id = (int)($in["dm_id"] ?? 0);
$d_id = (int)($in["d_id"] ?? 0);
$otp = trim($in["otp_code"] ?? "");

if ($dm_id <= 0 || $d_id <= 0 || $otp === "") {
  respond_json(["message" => "Missing dm_id, d_id, otp_code"], 400);
}

// ownership
$chk = $pdo->prepare("SELECT 1 FROM makes WHERE d_id = ? AND dm_id = ?");
$chk->execute([$d_id, $dm_id]);
if (!$chk->fetch()) respond_json(["message" => "This delivery is not assigned to you"], 403);

$stmt = $pdo->prepare("SELECT otp_code FROM delivery WHERE d_id = ?");
$stmt->execute([$d_id]);
$row = $stmt->fetch();
if (!$row) respond_json(["message" => "Delivery not found"], 404);

if (($row["otp_code"] ?? "") !== $otp) {
  respond_json(["message" => "Invalid OTP"], 400);
}

// mark delivered
$pdo->prepare("UPDATE delivery SET delivered = 1, out_of_delivery = 0 WHERE d_id = ?")
    ->execute([$d_id]);

respond_json(["message" => "Delivered successfully (OTP verified)", "d_id" => $d_id]);
