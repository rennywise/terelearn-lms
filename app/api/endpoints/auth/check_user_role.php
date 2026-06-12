<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Include database connection
require_once __DIR__ . '/../../core/db_connect.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$identifier = isset($input['identifier']) ? trim($input['identifier']) : '';

if (empty($identifier)) {
    echo json_encode([
        'success' => false,
        'error' => 'empty_identifier'
    ]);
    exit;
}

try {
    // Query to check if user exists and get their role
    $query = "SELECT 
                u.id, 
                u.username, 
                u.email, 
                u.user_level_id, 
                ul.user_type 
              FROM tbluser u 
              LEFT JOIN tbluserlevel ul ON u.user_level_id = ul.id
              WHERE (u.username = ? OR u.email = ?) 
              AND u.is_deleted = 0 
              AND u.is_active = 1
              LIMIT 1";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }
    
    $stmt->bind_param('ss', $identifier, $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'error' => 'not_found'
        ]);
        $stmt->close();
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Check if user is ONLY a student (user_level_id = 3 means Student)
    // If they are only student, disable dropdown. Otherwise enable it.
    $is_student_only = ($user['user_level_id'] == 3);
    
    echo json_encode([
        'success' => true,
        'is_student_only' => $is_student_only,
        'user_type' => $user['user_type'],
        'username' => $user['username'],
        'user_level_id' => $user['user_level_id']
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'database_error',
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
