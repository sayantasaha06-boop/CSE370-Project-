<?php
// Turn off display_errors for production and log errors instead
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Function to send JSON response and exit
function sendResponse($data, $httpCode = 200) {
    http_response_code($httpCode);
    echo json_encode($data);
    exit;
}

try {
    // Check if db_connect.php exists
    $dbFile = __DIR__ . '/../db_connect.php';
    if (!file_exists($dbFile)) {
        throw new Exception('Database connection file not found');
    }
    
    require_once $dbFile;
    
    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed: ' . ($conn->connect_error ?? 'Connection object not found'));
    }
    
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'list') {
            // Get packages with filters
            $sql = "SELECT * FROM meal_packages WHERE is_active = 1";
            $params = [];
            $types = "";
            
            // Apply audience filter
            if (!empty($_GET['audience']) && $_GET['audience'] !== 'All') {
                $sql .= " AND target_audience = ?";
                $params[] = $_GET['audience'];
                $types .= "s";
            }
            
            // Apply search filter
            if (!empty($_GET['q'])) {
                $sql .= " AND (name LIKE CONCAT('%', ?, '%') OR description LIKE CONCAT('%', ?, '%'))";
                $params[] = $_GET['q'];
                $params[] = $_GET['q'];
                $types .= "ss";
            }
            
            // Apply sorting
            $sort = $_GET['sort'] ?? '';
            if ($sort === 'asc') {
                $sql .= " ORDER BY daily_price ASC";
            } elseif ($sort === 'desc') {
                $sql .= " ORDER BY daily_price DESC";
            } else {
                $sql .= " ORDER BY target_audience, daily_price ASC";
            }
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception('Database prepare failed: ' . $conn->error);
            }
            
            if ($params) {
                $stmt->bind_param($types, ...$params);
            }
            
            if (!$stmt->execute()) {
                throw new Exception('Database execute failed: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            $packages = [];
            while ($row = $result->fetch_assoc()) {
                // Decode JSON fields
                $row['features'] = json_decode($row['features'], true) ?: [];
                $row['meals_included'] = json_decode($row['meals_included'], true) ?: [];
                $row['daily_price'] = (float)$row['daily_price'];
                $packages[] = $row;
            }
            
            sendResponse(['ok' => true, 'packages' => $packages]);
        }
        
        if ($action === 'get' && !empty($_GET['id'])) {
            // Get single package
            $id = (int)$_GET['id'];
            
            if ($id <= 0) {
                throw new Exception('Invalid package ID');
            }
            
            $stmt = $conn->prepare("SELECT * FROM meal_packages WHERE id = ? AND is_active = 1");
            if (!$stmt) {
                throw new Exception('Database prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param('i', $id);
            if (!$stmt->execute()) {
                throw new Exception('Database execute failed: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $row['features'] = json_decode($row['features'], true) ?: [];
                $row['meals_included'] = json_decode($row['meals_included'], true) ?: [];
                $row['daily_price'] = (float)$row['daily_price'];
                sendResponse(['ok' => true, 'package' => $row]);
            } else {
                sendResponse(['ok' => false, 'error' => 'Package not found'], 404);
            }
        }
        
        if ($action === 'calculate') {
            // Calculate total price
            $package_id = (int)($_GET['package_id'] ?? 0);
            $duration = (int)($_GET['duration'] ?? 0);
            
            if ($package_id <= 0 || $duration <= 0) {
                sendResponse(['ok' => false, 'error' => 'Invalid parameters'], 400);
            }
            
            $stmt = $conn->prepare("SELECT daily_price FROM meal_packages WHERE id = ? AND is_active = 1");
            if (!$stmt) {
                throw new Exception('Database prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param('i', $package_id);
            if (!$stmt->execute()) {
                throw new Exception('Database execute failed: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $daily_price = (float)$row['daily_price'];
                $total = $daily_price * $duration;
                
                sendResponse([
                    'ok' => true,
                    'daily_price' => $daily_price,
                    'duration' => $duration,
                    'total_amount' => $total
                ]);
            } else {
                sendResponse(['ok' => false, 'error' => 'Package not found'], 404);
            }
        }
        
        // If no action matches
        sendResponse(['ok' => false, 'error' => 'Invalid action'], 400);
    }
    
    if ($method === 'POST') {
        // Log the incoming request for debugging
        $input = file_get_contents('php://input');
        error_log('POST request received: ' . $input);
        
        if (empty($input)) {
            throw new Exception('No data received in POST request');
        }
        
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }
        
        error_log('Decoded POST data: ' . print_r($data, true));
        
        // Validate required fields
        $required = ['package_id', 'customer_name', 'phone', 'email', 'delivery_address', 'start_date', 'duration_days'];
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            throw new Exception('Missing required fields: ' . implode(', ', $missing));
        }
        
        $package_id = (int)$data['package_id'];
        $duration_days = (int)$data['duration_days'];
        
        if ($package_id <= 0) {
            throw new Exception('Invalid package ID');
        }
        
        if ($duration_days <= 0) {
            throw new Exception('Invalid duration');
        }
        
        // Validate package exists and get price
        $stmt = $conn->prepare("SELECT daily_price, meals_included FROM meal_packages WHERE id = ? AND is_active = 1");
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param('i', $package_id);
        if (!$stmt->execute()) {
            throw new Exception('Database execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        if (!$package_row = $result->fetch_assoc()) {
            throw new Exception('Package not found or inactive');
        }
        
        $daily_price = (float)$package_row['daily_price'];
        $meals_included = json_decode($package_row['meals_included'], true) ?: [];
        $meals_per_day = count($meals_included);
        $total_amount = $daily_price * $duration_days;
        
        // Validate start date
        $start_date = $data['start_date'];
        if (strtotime($start_date) <= time()) {
            throw new Exception('Start date must be in the future');
        }
        
        // Insert order
        $sql = "INSERT INTO package_orders 
                (package_id, customer_name, phone, email, delivery_address, start_date, 
                 duration_days, meals_per_day, total_amount, special_instructions, 
                 payment_status, order_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'Active')";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database prepare failed for insert: ' . $conn->error);
        }
        
        $special_instructions = trim($data['special_instructions'] ?? '');
        
        $stmt->bind_param('isssssiids', 
            $package_id,
            trim($data['customer_name']),
            trim($data['phone']),
            trim($data['email']),
            trim($data['delivery_address']),
            $start_date,
            $duration_days,
            $meals_per_day,
            $total_amount,
            $special_instructions
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create order: ' . $stmt->error);
        }
        
        $order_id = $stmt->insert_id;
        
        sendResponse([
            'ok' => true,
            'order_id' => $order_id,
            'total_amount' => $total_amount,
            'message' => 'Order placed successfully'
        ]);
    }
    
    if ($method === 'PUT') {
        // Update package (admin function)
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception('Invalid package ID');
        }
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON data: ' . json_last_error_msg());
        }
        
        $sql = "UPDATE meal_packages SET 
                name = ?, target_audience = ?, daily_price = ?, description = ?,
                features = ?, meals_included = ?, image_url = ?, is_active = ?
                WHERE id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param('ssdssssii',
            trim($data['name']),
            $data['target_audience'],
            (float)$data['daily_price'],
            trim($data['description']),
            json_encode($data['features']),
            json_encode($data['meals_included']),
            trim($data['image_url'] ?? ''),
            (int)($data['is_active'] ?? 1),
            $id
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update package: ' . $stmt->error);
        }
        
        sendResponse(['ok' => true, 'message' => 'Package updated successfully']);
    }
    
    if ($method === 'DELETE') {
        // Soft delete package (admin function)
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            throw new Exception('Invalid package ID');
        }
        
        $stmt = $conn->prepare("UPDATE meal_packages SET is_active = 0 WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param('i', $id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete package: ' . $stmt->error);
        }
        
        sendResponse(['ok' => true, 'message' => 'Package deleted successfully']);
    }
    
    sendResponse(['ok' => false, 'error' => 'Method not allowed'], 405);
    
} catch (Exception $e) {
    error_log('API Error: ' . $e->getMessage());
    sendResponse(['ok' => false, 'error' => $e->getMessage()], 400);
} catch (Error $e) {
    error_log('PHP Error: ' . $e->getMessage());
    sendResponse(['ok' => false, 'error' => 'Internal server error'], 500);
}
?>