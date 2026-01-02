<?php
require __DIR__ . "/config.php";
require __DIR__ . "/helpers.php";

$in = read_json_body();
$A_id = (int)($in["A_id"] ?? 0);
$d_id = (int)($in["d_id"] ?? 0);
$dm_id = (int)($in["dm_id"] ?? 0);

if ($A_id <= 0 || $d_id <= 0 || $dm_id <= 0) {
  respond_json(["message" => "Missing/invalid A_id, d_id, dm_id"], 400);
}

// Check dm availability
$q = $pdo->prepare("SELECT dm_id, name, available FROM delivery_man WHERE dm_id = ?");
$q->execute([$dm_id]);
$dm = $q->fetch();
if (!$dm) respond_json(["message" => "Delivery man not found"], 404);
if ((int)$dm["available"] !== 1) respond_json(["message" => "Delivery man is not available"], 400);

// Ensure delivery exists
$qd = $pdo->prepare("SELECT d_id FROM delivery WHERE d_id = ?");
$qd->execute([$d_id]);
if (!$qd->fetch()) respond_json(["message" => "Delivery not found"], 404);

try {
  $pdo->beginTransaction();

  // admin manages
  $pdo->prepare("INSERT IGNORE INTO manages (A_id, d_id) VALUES (?, ?)")
      ->execute([$A_id, $d_id]);

  // dm makes
  $pdo->prepare("INSERT IGNORE INTO makes (d_id, dm_id) VALUES (?, ?)")
      ->execute([$d_id, $dm_id]);

  // update delivery table for display + mark scheduled
  $pdo->prepare("UPDATE delivery SET dm_name = ?, schedule = 1 WHERE d_id = ?")
      ->execute([$dm["name"], $d_id]);

  $pdo->commit();
} catch (Exception $e) {
  $pdo->rollBack();
  respond_json(["message" => "Assign failed", "error" => $e->getMessage()], 500);
}

respond_json(["message" => "Assigned successfully", "d_id" => $d_id, "dm_id" => $dm_id]);
