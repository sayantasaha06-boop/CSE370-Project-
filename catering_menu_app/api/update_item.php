<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_connect.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

$id    = isset($data['id']) ? (int)$data['id'] : 0;
$name  = trim($data['name'] ?? '');
$category = trim($data['category'] ?? '');
$price = $data['price'] ?? '';
$eta   = $data['delivery_time_minutes'] ?? '';
$desc  = trim($data['description'] ?? '');
$is_available = isset($data['is_available']) ? (int)$data['is_available'] : 1;

$validCats = ['Veg','Non-Veg','Drinks','Desserts'];
if ($id <= 0 || $name === '' || !in_array($category, $validCats) || !is_numeric($price) || !is_numeric($eta)) {
  echo json_encode(['ok'=>false, 'error'=>'Invalid input']);
  exit;
}

$sql = "UPDATE menu_items
        SET name=?, category=?, price=?, delivery_time_minutes=?, description=?, is_available=?
        WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssddsii', $name, $category, $price, $eta, $desc, $is_available, $id);
if (!$stmt->execute()){
  echo json_encode(['ok'=>false, 'error'=>$stmt->error]);
  exit;
}
echo json_encode(['ok'=>true]);
