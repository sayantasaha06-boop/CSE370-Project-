<?php
require __DIR__ . "/config.php";
require __DIR__ . "/helpers.php";

$in = read_json_body();
$dm_id = (int)($in["dm_id"] ?? 0);
$d_id = (int)($in["d_id"] ?? 0);
$delivered = (int)($in["delivered"] ?? 0);
$schedule = (int)($in["schedule"] ?? 0);
$out_of_delivery = (int)($in["out_of_delivery"] ?? 0);

if ($dm_id <= 0 || $d_id <= 0) respond_json(["message" => "Missing dm_id or d_id"], 400);

// ownership check
$chk = $pdo->prepare("SELECT 1 FROM makes WHERE d_id = ? AND dm_id = ?");
$chk->execute([$d_id, $dm_id]);
if (!$chk->fetch()) respond_json(["message" => "This delivery is not assigned to you"], 403);

// update fields
$u = $pdo->prepare(
  "UPDATE delivery SET delivered = ?, schedule = ?, out_of_delivery = ? WHERE d_id = ?"
);
$u->execute([$delivered, $schedule, $out_of_delivery, $d_id]);

respond_json(["message" => "Updated", "d_id" => $d_id]);
