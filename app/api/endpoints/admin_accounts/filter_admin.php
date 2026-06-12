<?php
// Include database connection
require_once __DIR__ . '/../../core/db_connect.php';

// Check if fetching single admin by ID (for edit modal)
$user_id = trim($_GET['id'] ?? '');

if (!empty($user_id)) {
    // Return JSON response for single admin
    header('Content-Type: application/json');
    $sql = "SELECT id, email, username, user_level_id, is_active 
            FROM tbluser 
            WHERE id = ? AND user_level_id IN (1, 4) AND is_deleted = 0";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        $conn->close();
        exit;
    }
    
    $stmt->bind_param("s", $user_id);
    if (!$stmt->execute()) {
        echo json_encode(['status' => 'error', 'message' => 'Query execution failed: ' . $stmt->error]);
        $stmt->close();
        $conn->close();
        exit;
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'admin' => $admin]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Admin not found']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Otherwise, return HTML table rows for display
// Check if search parameter is provided
$search = trim($_GET['search'] ?? '');

if (!empty($search)) {
    // Search query with LIKE pattern
    $search_pattern = '%' . $search . '%';
    $sql = "SELECT id, email, username, user_level_id, is_active 
            FROM tbluser 
            WHERE user_level_id IN (1, 4) AND is_deleted = 0 
            AND (email LIKE ? OR username LIKE ?)
            ORDER BY user_level_id ASC, id ASC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo '<tr><td colspan="6" class="text-center text-danger">Database error</td></tr>';
        $conn->close();
        exit;
    }
    
    $stmt->bind_param("ss", $search_pattern, $search_pattern);
} else {
    // Fetch all admins and sub-admins
    $sql = "SELECT id, email, username, user_level_id, is_active 
            FROM tbluser 
            WHERE user_level_id IN (1, 4) AND is_deleted = 0 
            ORDER BY user_level_id ASC, id ASC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo '<tr><td colspan="6" class="text-center text-danger">Database error</td></tr>';
        $conn->close();
        exit;
    }
}

if (!$stmt->execute()) {
    echo '<tr><td colspan="6" class="text-center text-danger">Query execution failed</td></tr>';
    $stmt->close();
    $conn->close();
    exit;
}

$result = $stmt->get_result();

// Check if there are any records
if ($result->num_rows > 0) {
    $row_count = 1;

    // Loop through the results and output table rows
    while($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $email = $row['email'];
        $username = $row['username'];
        $user_level_id = $row['user_level_id'];
        $is_active = $row['is_active'];

        // Determine user level label
        $user_level = ($user_level_id == 1) ? 'Admin' : 'Sub-Admin';

        $status_badge = $is_active 
            ? '<span class="badge badge-success">Active</span>' 
            : '<span class="badge badge-danger">Inactive</span>';
        
        $toggle_button_class = $is_active ? 'btn-danger' : 'btn-success';
        $toggle_button_icon = $is_active ? 'fa-ban' : 'fa-check-circle';
        $toggle_button_title = $is_active ? 'Deactivate' : 'Activate';

        echo "<tr>";
        echo "<td>" . $row_count++ . "</td>";
        echo "<td>" . htmlspecialchars($email) . "</td>";
        echo "<td>" . htmlspecialchars($username) . "</td>";
        echo "<td>" . htmlspecialchars($user_level) . "</td>";
        echo "<td>" . $status_badge . "</td>";

        echo "<td>";
        echo "<div class='d-flex gap-2'>";
       
        // Toggle Status button
        echo "<button class='btn $toggle_button_class btn-sm' onclick='toggleSubAdminStatus(\"" . htmlspecialchars($id) . "\", " . ($is_active ? 0 : 1) . ")' title='$toggle_button_title'>";
        echo "<i class='fas $toggle_button_icon'></i>";
        echo "</button>";
       
        // Edit button
        echo "<button class='btn btn-warning btn-sm mx-1' onclick='editAdmin(\"" . htmlspecialchars($id) . "\")' title='Edit'>";
        echo "<i class='fas fa-pen'></i>";
        echo "</button>";
       
        // Delete button
        echo "<button class='btn btn-dark btn-sm' onclick='deleteSubAdmin(\"" . htmlspecialchars($id) . "\")' title='Delete'>";
        echo "<i class='fas fa-trash-alt'></i>";
        echo "</button>";
       
        echo "</div>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No admins or sub-admins found.</td></tr>";
}

$stmt->close();
$conn->close();
?>
