<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_connect.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

$name  = trim($data['name'] ?? '');
$category = trim($data['category'] ?? '');
$price = $data['price'] ?? '';
$eta   = $data['delivery_time_minutes'] ?? '';
$desc  = trim($data['description'] ?? '');
$is_available = isset($data['is_available']) ? (int)$data['is_available'] : 1;

$validCats = ['Veg','Non-Veg','Drinks','Desserts'];
if ($name === '' || !in_array($category, $validCats) || !is_numeric($price) || !is_numeric($eta)) {
  echo json_encode(['ok'=>false, 'error'=>'Invalid input']);
  exit;
}

$sql = "INSERT INTO menu_items (name, category, price, delivery_time_minutes, description, is_available)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssddsi', $name, $category, $price, $eta, $desc, $is_available);
if (!$stmt->execute()){
  echo json_encode(['ok'=>false, 'error'=>$stmt->error]);
  exit;
}
echo json_encode(['ok'=>true, 'id'=>$stmt->insert_id]);
