<?php
// Include database connection
require_once __DIR__ . '/../../core/db_connect.php';
$search = trim($_GET['search'] ?? '');

// Base query
$sql = "SELECT id, faculty_number, first_name, middle_name, last_name, email, phone, birthdate, username, is_active 
        FROM tblfaculty 
        WHERE is_deleted = 0";

// Add search filter if provided
if (!empty($search)) {
    $sql .= " AND (
        faculty_number LIKE ? OR 
        first_name LIKE ? OR 
        middle_name LIKE ? OR 
        last_name LIKE ? OR 
        CONCAT(first_name, ' ', COALESCE(middle_name, ''), ' ', last_name) LIKE ? OR
        email LIKE ? OR
        phone LIKE ? OR
        birthdate LIKE ? OR
        username LIKE ?
    )";
}

$sql .= " ORDER BY id ASC";

$stmt = $conn->prepare($sql);

// Bind parameters if search is applied
if (!empty($search)) {
    $search_param = '%' . $search . '%';
    $stmt->bind_param(
        "sssssssss",
        $search_param,
        $search_param,
        $search_param,
        $search_param,
        $search_param,
        $search_param,
        $search_param,
        $search_param,
        $search_param
    );
}

$stmt->execute();
$result = $stmt->get_result();

// Check if there are any records
if ($result->num_rows > 0) {
    $row_count = 1;

    // Loop through the results and output table rows
    while($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $faculty_id = $row['faculty_number'];
        $first_name = $row['first_name'];
        $middle_name = $row['middle_name'] ? $row['middle_name'] : '';
        $last_name = $row['last_name'];
        
        // Construct full name
        $full_name = trim($first_name . ' ' . $middle_name . ' ' . $last_name);
        
        $email = $row['email'];
        $phone = $row['phone'];
        $birthdate = $row['birthdate'];
        $username = $row['username'];
        $is_active = $row['is_active'];

        $status_badge = $is_active 
            ? '<span class="badge badge-success">Active</span>' 
            : '<span class="badge badge-danger">Inactive</span>';
        
        $toggle_button_class = $is_active ? 'btn-danger' : 'btn-success';
        $toggle_button_icon = $is_active ? 'fa-ban' : 'fa-check-circle';
        $toggle_button_title = $is_active ? 'Deactivate' : 'Activate';

        echo "<tr>";
        echo "<td>" . $row_count++ . "</td>";
        echo "<td>" . htmlspecialchars($faculty_id) . "</td>";
        echo "<td>" . htmlspecialchars($full_name) . "</td>";
        echo "<td>" . htmlspecialchars($email) . "</td>";
        echo "<td>" . htmlspecialchars($phone) . "</td>";
        echo "<td>" . date('m/d/Y', strtotime($birthdate)) . "</td>";
        echo "<td>" . $status_badge . "</td>";

        echo "<td>";
        echo "<div class='d-flex gap-2'>";
       
        // Toggle Status button
        echo "<button class='btn $toggle_button_class btn-sm' onclick='toggleFacultyStatus(\"" . htmlspecialchars($faculty_id) . "\")' title='$toggle_button_title'>";
        echo "<i class='fas $toggle_button_icon'></i>";
        echo "</button>";
       
        // Edit button
        echo "<button class='btn btn-warning btn-sm mx-1' onclick='editFaculty(\"" . htmlspecialchars($faculty_id) . "\")' title='Edit'>";
        echo "<i class='fas fa-pen'></i>";
        echo "</button>";
       
        // Delete button
        echo "<button class='btn btn-dark btn-sm' onclick='deleteFaculty(\"" . htmlspecialchars($faculty_id) . "\")' title='Delete'>";
        echo "<i class='fas fa-trash-alt'></i>";
        echo "</button>";
       
        echo "</div>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='8' class='text-center'>No faculty members found.</td></tr>";
}

$stmt->close();
$conn->close();
?>
