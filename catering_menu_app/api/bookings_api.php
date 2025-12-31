<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Simple file-based storage for bookings
$BOOKINGS_FILE = __DIR__ . '/data/bookings.json';

// Ensure data directory exists
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0777, true);
}

function read_all() {
    global $BOOKINGS_FILE;
    if (!file_exists($BOOKINGS_FILE)) {
        return [];
    }
    $data = file_get_contents($BOOKINGS_FILE);
    return json_decode($data, true) ?: [];
}

function save_all($items) {
    global $BOOKINGS_FILE;
    file_put_contents($BOOKINGS_FILE, json_encode($items, JSON_PRETTY_PRINT));
}

function conflict_exists($items, $date, $start, $end, $exclude_id = null) {
    $new_start = strtotime("$date $start");
    $new_end = strtotime("$date $end");
    
    foreach ($items as $booking) {
        if ($exclude_id && $booking['id'] === $exclude_id) continue;
        if ($booking['status'] === 'Cancelled') continue;
        
        if ($booking['event_date'] === $date) {
            $existing_start = strtotime($booking['event_date'] . ' ' . $booking['start_time']);
            $existing_end = strtotime($booking['event_date'] . ' ' . $booking['end_time']);
            
            // Check for overlap
            if (($new_start < $existing_end) && ($new_end > $existing_start)) {
                return true;
            }
        }
    }
    return false;
}

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $action = $_GET['action'] ?? 'list';
        
        if ($action === 'check_conflict') {
            $date = $_GET['date'] ?? '';
            $start = $_GET['start'] ?? '';
            $end = $_GET['end'] ?? '';
            $exclude = $_GET['exclude'] ?? null;
            
            if (!$date || !$start || !$end) {
                echo json_encode(['ok' => true]);
                exit;
            }
            
            $items = read_all();
            if (conflict_exists($items, $date, $start, $end, $exclude)) {
                http_response_code(409);
                echo json_encode(['ok' => false, 'error' => 'Time conflict with existing booking']);
            } else {
                echo json_encode(['ok' => true]);
            }
            exit;
        }
        
        // List bookings with filters
        $items = read_all();
        
        // Apply filters
        if (!empty($_GET['status']) && $_GET['status'] !== 'All') {
            $items = array_filter($items, fn($b) => $b['status'] === $_GET['status']);
        }
        
        if (!empty($_GET['q'])) {
            $search = strtolower($_GET['q']);
            $items = array_filter($items, function($b) use ($search) {
                return strpos(strtolower($b['customer_name']), $search) !== false ||
                       strpos(strtolower($b['event_type']), $search) !== false ||
                       strpos(strtolower($b['location']), $search) !== false;
            });
        }
        
        if (!empty($_GET['date_filter'])) {
            $filter = $_GET['date_filter'];
            $today = date('Y-m-d');
            
            $items = array_filter($items, function($b) use ($filter, $today) {
                $event_date = $b['event_date'];
                switch ($filter) {
                    case 'today':
                        return $event_date === $today;
                    case 'week':
                        $week_end = date('Y-m-d', strtotime('+7 days'));
                        return $event_date >= $today && $event_date <= $week_end;
                    case 'month':
                        $month_end = date('Y-m-d', strtotime('+1 month'));
                        return $event_date >= $today && $event_date <= $month_end;
                    case 'upcoming':
                        return $event_date >= $today;
                    default:
                        return true;
                }
            });
        }
        
        // Sort by date
        usort($items, fn($a, $b) => strcmp($a['event_date'], $b['event_date']));
        
        echo json_encode(['ok' => true, 'bookings' => array_values($items)]);
        exit;
    }
    
    if ($method === 'POST') {
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $required = ['customer_name', 'phone', 'email', 'event_type', 'event_date', 'start_time', 'end_time', 'guests', 'location'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }
        
        // Validate date/time
        $event_date = $data['event_date'];
        $start_time = $data['start_time'];
        $end_time = $data['end_time'];
        
        if (strtotime("$event_date $start_time") >= strtotime("$event_date $end_time")) {
            throw new Exception('End time must be after start time');
        }
        
        if (strtotime("$event_date $start_time") <= time()) {
            throw new Exception('Event cannot be scheduled in the past');
        }
        
        // Check for conflicts
        $items = read_all();
        if (conflict_exists($items, $event_date, $start_time, $end_time)) {
            http_response_code(409);
            echo json_encode(['ok' => false, 'error' => 'Time conflict with existing booking']);
            exit;
        }
        
        // Create booking
        $id = bin2hex(random_bytes(8));
        $booking = [
            'id' => $id,
            'customer_name' => trim($data['customer_name']),
            'phone' => trim($data['phone']),
            'email' => trim($data['email']),
            'event_type' => $data['event_type'],
            'event_date' => $event_date,
            'start_time' => $start_time,
            'end_time' => $end_time,
            'guests' => (int)$data['guests'],
            'location' => trim($data['location']),
            'menu_prefs' => trim($data['menu_prefs'] ?? ''),
            'status' => 'Pending',
            'created_at' => date('c')
        ];
        
        $items[] = $booking;
        save_all($items);
        
        echo json_encode(['ok' => true, 'id' => $id]);
        exit;
    }
    
    if ($method === 'PUT') {
        $id = $_GET['id'] ?? '';
        if (!$id) throw new Exception('Missing booking ID');
        
        $input = file_get_contents('php://input');
        $data = json_decode($input, true);
        
        $items = read_all();
        $found = false;
        
        foreach ($items as &$booking) {
            if ($booking['id'] === $id) {
                $booking['customer_name'] = trim($data['customer_name']);
                $booking['phone'] = trim($data['phone']);
                $booking['email'] = trim($data['email']);
                $booking['event_type'] = $data['event_type'];
                $booking['guests'] = (int)$data['guests'];
                $booking['location'] = trim($data['location']);
                $booking['menu_prefs'] = trim($data['menu_prefs'] ?? '');
                $booking['status'] = $data['status'];
                $booking['updated_at'] = date('c');
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Booking not found']);
            exit;
        }
        
        save_all($items);
        echo json_encode(['ok' => true]);
        exit;
    }
    
    if ($method === 'DELETE') {
        $id = $_GET['id'] ?? '';
        if (!$id) throw new Exception('Missing booking ID');
        
        $items = read_all();
        $before_count = count($items);
        $items = array_values(array_filter($items, fn($b) => $b['id'] !== $id));
        
        if (count($items) === $before_count) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'Booking not found']);
            exit;
        }
        
        save_all($items);
        echo json_encode(['ok' => true]);
        exit;
    }
    
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
?>