<?php
require_once __DIR__ . '/../../core/db_connect.php';

$search = trim($_GET['search'] ?? '');

$sql = "SELECT s.id,
               s.subject_code,
               s.subject_name,
               c.course_name,
               s.is_active
        FROM tblsubject s
        LEFT JOIN tblcourse c ON s.course_id = c.id
        WHERE s.is_deleted = 0";

if (!empty($search)) {
    $sql .= " AND (
          s.subject_code LIKE ? OR
          s.subject_name LIKE ? OR
          c.course_name LIKE ?
    )";
}
$sql .= " ORDER BY s.subject_code ASC";

$stmt = $conn->prepare($sql);
if (!empty($search)) {
    $param = "%$search%";
    $stmt->bind_param("sss", $param, $param, $param);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row_count = 1;
    while ($row = $result->fetch_assoc()) {
        $id         = $row['id'];
        $code       = $row['subject_code'];
        $name       = $row['subject_name'];
        $program    = $row['course_name'] ?? 'N/A';
        $is_active  = $row['is_active'];

        $status_badge = $is_active
            ? '<span class="badge badge-success">Active</span>'
            : '<span class="badge badge-danger">Inactive</span>';

        $toggle_class = $is_active ? 'btn-danger' : 'btn-success';
        $toggle_icon  = $is_active ? 'fa-ban' : 'fa-check-circle';
        $toggle_title = $is_active ? 'Deactivate' : 'Activate';

        echo "<tr>";
        echo "<td class='text-center'>" . $row_count++ . "</td>";
        echo "<td>" . htmlspecialchars($code) . "</td>";
        echo "<td>" . htmlspecialchars($name) . "</td>";
        echo "<td>" . htmlspecialchars($program) . "</td>";
        echo "<td class='text-center'>" . $status_badge . "</td>";

        echo "<td>";
        echo "<div class='d-flex gap-2 justify-content-center'>";

        // Toggle
        echo "<button class='btn $toggle_class btn-sm' onclick='toggleSubjectStatus(\"" . htmlspecialchars($id) . "\")' title='$toggle_title'>";
        echo "<i class='fas $toggle_icon'></i>";
        echo "</button>";

        // Edit
        echo "<button class='btn btn-warning btn-sm mx-1' onclick='editSubj(\"" . htmlspecialchars($id) . "\")' title='Edit'>";
        echo "<i class='fas fa-pen'></i>";
        echo "</button>";

        // Delete
        echo "<button class='btn btn-dark btn-sm' onclick='deleteSubj(\"" . htmlspecialchars($id) . "\")' title='Delete'>";
        echo "<i class='fas fa-trash-alt'></i>";
        echo "</button>";

        echo "</div>";
        echo "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='6' class='text-center'>No subjects found.</td></tr>";
}

$stmt->close();
$conn->close();
?>
