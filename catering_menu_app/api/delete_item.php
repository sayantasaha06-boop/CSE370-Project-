<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../db_connect.php';

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
if (!$data) $data = $_POST;

$id = isset($data['id']) ? (int)$data['id'] : 0;
if ($id <= 0){
  echo json_encode(['ok'=>false, 'error'=>'Invalid id']);
  exit;
}

$stmt = $conn->prepare("DELETE FROM menu_items WHERE id=?");
$stmt->bind_param('i', $id);
if (!$stmt->execute()){
  echo json_encode(['ok'=>false, 'error'=>$stmt->error]);
  exit;
}
echo json_encode(['ok'=>true]);
