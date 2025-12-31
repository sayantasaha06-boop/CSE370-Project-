<?php

$host = "localhost";
$user = "root";
$pass = "";
$db   = "catering";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    http_response_code(500);
    die("❌ DB connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");


$createTableSql = "CREATE TABLE IF NOT EXISTS menu_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  category ENUM('Veg','Non-Veg','Drinks','Desserts') NOT NULL,
  price DECIMAL(10,2) NOT NULL,
  delivery_time_minutes INT NOT NULL,
  description TEXT,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (!$conn->query($createTableSql)) {
    http_response_code(500);
    die("❌ Failed to ensure menu_items table: " . $conn->error);
}
?>
