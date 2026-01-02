<?php
require __DIR__ . "/config.php";
require __DIR__ . "/helpers.php";

$d_id = (int)($_GET["d_id"] ?? 0);
if ($d_id <= 0) respond_json(["message" => "Provide d_id"], 400);

$sql = "
SELECT d.*,
       c.customer_id, c.name AS customer_name, c.phone_no, c.address,
       dm.dm_id, dm.name AS deliveryman_name
FROM delivery d
LEFT JOIN delivers de ON de.d_id = d.d_id
LEFT JOIN customer c ON c.customer_id = de.customer_id
LEFT JOIN makes mk ON mk.d_id = d.d_id
LEFT JOIN delivery_man dm ON dm.dm_id = mk.dm_id
WHERE d.d_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$d_id]);
$row = $stmt->fetch();

if (!$row) respond_json(["message" => "Not found"], 404);
respond_json($row);
