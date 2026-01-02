<?php
require __DIR__ . "/config.php";
require __DIR__ . "/helpers.php";

$in = read_json_body();
$customer_id = (int)($in["customer_id"] ?? 0);
$vehicle_no = trim($in["vehicle_no"] ?? "");
$delivery_time = trim($in["delivery_time"] ?? ""); // optional

if ($customer_id <= 0 || $vehicle_no === "") {
  respond_json(["message" => "Missing fields (customer_id, vehicle_no)"], 400);
}

$qc = $pdo->prepare("SELECT customer_id FROM customer WHERE customer_id = ?");
$qc->execute([$customer_id]);
if (!$qc->fetch()) respond_json(["message" => "Customer not found"], 404);

$otp = otp_code(6);

$stmt = $pdo->prepare(
  "INSERT INTO delivery (vehicle_no, delivery_time, delivered, schedule, out_of_delivery, otp_code)
   VALUES (?, NULLIF(?,''), 0, 0, 0, ?)"
);
$stmt->execute([$vehicle_no, $delivery_time, $otp]);
$d_id = (int)$pdo->lastInsertId();

$pdo->prepare("INSERT INTO delivers (customer_id, d_id) VALUES (?, ?)")
    ->execute([$customer_id, $d_id]);

respond_json([
  "message" => "Delivery created",
  "d_id" => $d_id,
  "otp_code" => $otp
]);
