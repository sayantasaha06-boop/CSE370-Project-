<?php
require __DIR__ . "/config.php";
require __DIR__ . "/helpers.php";

$in = read_json_body();
$A_id = (int)($in["A_id"] ?? 0);
$name = trim($in["name"] ?? "");
$contact_no = trim($in["contact_no"] ?? "");

if ($A_id <= 0 || $name === "" || $contact_no === "") {
  respond_json(["message" => "Missing fields (A_id, name, contact_no)"], 400);
}

$stmt = $pdo->prepare(
  "INSERT INTO delivery_man (name, contact_no, available, not_available, A_id)
   VALUES (?, ?, 1, 0, ?)"
);
$stmt->execute([$name, $contact_no, $A_id]);

respond_json(["message" => "Delivery man added", "dm_id" => (int)$pdo->lastInsertId()]);
