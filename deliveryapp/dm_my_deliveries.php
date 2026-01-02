<?php
require __DIR__ . "/config.php";
require __DIR__ . "/helpers.php";

$dm_id = (int)($_GET["dm_id"] ?? 0);
if ($dm_id <= 0) respond_json(["message" => "Provide dm_id"], 400);

$sql = "
SELECT d.d_id, d.vehicle_no, d.delivery_time, d.delivered, d.schedule, d.out_of_delivery, d.otp_code,
       c.name AS customer_name, c.address, c.phone_no
FROM makes mk
JOIN delivery d ON d.d_id = mk.d_id
LEFT JOIN delivers de ON de.d_id = d.d_id
LEFT JOIN customer c ON c.customer_id = de.customer_id
WHERE mk.dm_id = ?
ORDER BY d.d_id DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$dm_id]);
respond_json($stmt->fetchAll());
