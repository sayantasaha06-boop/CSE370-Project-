<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_connect.php';

$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT id, name, category, price, delivery_time_minutes, description, is_available
        FROM menu_items";
$where = [];
$params = [];
$types  = "";

if ($category && $category !== "All") {
  $where[] = "category = ?";
  $params[] = $category;
  $types   .= "s";
}
if ($q !== "") {
  $where[] = "(name LIKE CONCAT('%', ?, '%') OR description LIKE CONCAT('%', ?, '%'))";
  $params[] = $q; $params[] = $q;
  $types   .= "ss";
}
if ($where) {
  $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY name ASC";

$stmt = $conn->prepare($sql);
if ($params) {
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
  $row['price'] = (float)$row['price'];
  $row['delivery_time_minutes'] = (int)$row['delivery_time_minutes'];
  $row['is_available'] = (int)$row['is_available'];
  $items[] = $row;
}
echo json_encode(['ok'=>true, 'items'=>$items]);
