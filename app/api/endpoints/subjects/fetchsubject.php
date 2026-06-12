<?php
require_once __DIR__ . '/../../core/db_connect.php';

try {
    $search = isset($_GET['search']) ? trim($_GET['search']) : '';

    $where = "s.is_deleted = 0";
    if ($search !== '') {
        $s = $conn->real_escape_string($search);
        $where .= " AND (
              s.subject_code LIKE '%$s%' OR
              s.subject_name LIKE '%$s%' OR
              c.course_name LIKE '%$s%'
        )";
    }

    $sql = "SELECT s.id,
                   s.subject_code,
                   s.subject_name,
                   c.course_name,
                   s.is_active
            FROM tblsubject s
            LEFT JOIN tblcourse c ON s.course_id = c.id
            WHERE $where
            ORDER BY s.subject_code ASC";

    $result = $conn->query($sql);
    if (!$result) throw new Exception($conn->error);

    $rowNum = 1;
    $output = '';

    while ($row = $result->fetch_assoc()) {
        $id        = $row['id'];
        $code      = $row['subject_code'];
        $name      = $row['subject_name'];
        $program   = $row['course_name'] ?? 'N/A';
        $is_active = $row['is_active'];

        $status_badge = $is_active
            ? '<span class="badge badge-success">Active</span>'
            : '<span class="badge badge-danger">Inactive</span>';

        $toggle_class = $is_active ? 'btn-danger' : 'btn-success';
        $toggle_icon  = $is_active ? 'fa-ban' : 'fa-check-circle';
        $toggle_title = $is_active ? 'Deactivate' : 'Activate';

        $output .= "<tr class='striped'>";
        $output .= "<td class='text-center'>" . $rowNum++ . "</td>";
        $output .= "<td>" . htmlspecialchars($code) . "</td>";
        $output .= "<td>" . htmlspecialchars($name) . "</td>";
        $output .= "<td>" . htmlspecialchars($program) . "</td>";
        $output .= "<td class='text-center'>" . $status_badge . "</td>";

        $output .= "<td class='text-center'>";
        $output .= "<div class='d-flex gap-2 justify-content-center'>";

        // Toggle
        $output .= "<button class='btn $toggle_class btn-sm' onclick='toggleStatus(\"" . htmlspecialchars($id) . "\")' title='$toggle_title'>";
        $output .= "<i class='fas $toggle_icon'></i>";
        $output .= "</button>";

        // Edit
        $output .= "<button class='btn btn-warning btn-sm mx-1' onclick='editSubj(\"" . htmlspecialchars($id) . "\")' title='Edit'>";
        $output .= "<i class='fas fa-pen'></i>";
        $output .= "</button>";

        // Delete
        $output .= "<button class='btn btn-dark btn-sm' onclick='deleteSubj(\"" . htmlspecialchars($id) . "\")' title='Delete'>";
        $output .= "<i class='fas fa-trash-alt'></i>";
        $output .= "</button>";

        $output .= "</div>";
        $output .= "</td>";
        $output .= "</tr>";
    }

    if ($rowNum === 1) $output .= '<tr><td colspan="6" class="text-center text-muted">No subjects found</td></tr>';
    echo $output;
    $result->free();

} catch (Exception $e) {
    echo '<tr><td colspan="6" class="text-center text-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</td></tr>';
}
$conn->close();
?>
