<?php
require_once __DIR__ . '/../../core/db_connect.php';
header('Content-Type: application/json');

$res = $conn->query("
    SELECT
      a.id,
      a.admin_number,
      a.first_name,
      a.middle_name,
      a.last_name,
      a.suffix,
      a.email,
      a.phone,
      a.birthdate,
      a.username,
      a.is_active,
      a.is_superadmin,
      a.created_at,
      (
        SELECT u1.id
        FROM tbluser u1
        WHERE u1.is_deleted = 0
          AND (u1.email = a.email OR u1.username = a.username)
        ORDER BY (u1.user_level_id = 1) DESC, u1.updated_at DESC
        LIMIT 1
      ) AS user_id,
      IFNULL((
        SELECT u2.otp_enabled
        FROM tbluser u2
        WHERE u2.is_deleted = 0
          AND (u2.email = a.email OR u2.username = a.username)
        ORDER BY (u2.user_level_id = 1) DESC, u2.updated_at DESC
        LIMIT 1
      ), 1) AS otp_enabled,
      IFNULL((
        SELECT u3.first_login
        FROM tbluser u3
        WHERE u3.is_deleted = 0
          AND (u3.email = a.email OR u3.username = a.username)
        ORDER BY (u3.user_level_id = 1) DESC, u3.updated_at DESC
        LIMIT 1
      ), 1) AS first_login
    FROM tbladmin a
    WHERE a.is_deleted = 0
    ORDER BY a.last_name, a.first_name
");

$data = [];
while ($r = $res->fetch_assoc()) $data[] = $r;

echo json_encode(['status' => 'success', 'data' => $data]);
