<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$rows = [];
$res  = $conn->query("
    SELECT
        st.id, st.student_number,
        st.first_name, st.middle_name, st.last_name,
        st.email, st.username,
        st.year_level, st.section,
        st.is_active, st.birthdate, st.course_id,
        c.course_code, c.course_name,
        d.dept_code, d.dept_name,
        u.id                       AS user_id,
        COALESCE(u.otp_enabled, 0) AS otp_enabled,
        COALESCE(u.first_login, 1) AS first_login
    FROM   tblstudent st
    JOIN   tblcourse  c  ON c.id  = st.course_id
    LEFT JOIN tbldepartment d ON d.id = c.department_id
    LEFT JOIN tbluser u
           ON (u.email = st.email OR u.username = st.username)
          AND u.is_deleted = 0
    WHERE  st.is_deleted = 0
    ORDER  BY c.course_code, st.last_name, st.first_name
");

if ($res) while ($r = $res->fetch_assoc()) $rows[] = $r;
echo json_encode(['status' => 'success', 'data' => $rows, 'total' => count($rows)]);
$conn->close();
