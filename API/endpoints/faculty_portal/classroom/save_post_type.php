<?php
/**
 * API/facultyUI/classroom/save_post_type.php
 * Create or delete a faculty-custom post type
 */
session_start();
header('Content-Type: application/json');

$level = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
if (!isset($_SESSION['user_id']) || $level !== 2) {
    echo json_encode(['status'=>'error','message'=>'Unauthorized']); exit;
}

require_once __DIR__ . '/../../../core/db_connect.php';
$faculty_id = trim((string)($_SESSION['faculty_id'] ?? ''));
if ($faculty_id === '') {
    $user_id = (string)$_SESSION['user_id'];
    $stmt = $conn->prepare("
        SELECT f.id
        FROM tblfaculty f
        INNER JOIN tbluser u ON u.id = ?
        WHERE f.is_deleted = 0
          AND u.is_deleted = 0
          AND (f.username = u.username OR f.email = u.email)
        LIMIT 1
    ");
    $stmt->bind_param('s', $user_id);
    $stmt->execute();
    $faculty = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $faculty_id = (string)($faculty['id'] ?? $user_id);
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = trim($input['action'] ?? 'create'); // create | delete

if ($action === 'delete') {
    $id = trim($input['id'] ?? '');
    if (!$id) { echo json_encode(['status'=>'error','message'=>'id required']); exit; }
    $stmt = $conn->prepare("UPDATE tblposttype SET is_deleted=1 WHERE id=? AND faculty_id=?");
    $stmt->bind_param('ss', $id, $faculty_id);
    $stmt->execute();
    $affected = $stmt->affected_rows; $stmt->close(); $conn->close();
    echo json_encode($affected
        ? ['status'=>'success','message'=>'Type deleted.']
        : ['status'=>'error','message'=>'Not found or not yours.']);
    exit;
}

// Create
$label      = trim($input['type_label']  ?? '');
$key        = trim($input['type_key']    ?? '');
$icon       = trim($input['icon']        ?? 'fa-file-alt');
$color_bg   = trim($input['color_bg']   ?? '#f3f4f6');
$color_text = trim($input['color_text'] ?? '#374151');
$is_grad    = (int)($input['is_gradable'] ?? 0);
$has_quiz   = (int)($input['has_quiz']   ?? 0);
$has_file   = (int)($input['has_file']   ?? 0);

if (!$label) { echo json_encode(['status'=>'error','message'=>'Label is required.']); exit; }
if ($has_quiz) $is_grad = 1;

// Auto-generate key from label if not provided
if (!$key) {
    $key = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $label));
    $key = trim($key, '_');
}

$stmt = $conn->prepare("
    INSERT INTO tblposttype
      (faculty_id, type_key, type_label, icon, color_bg, color_text, is_gradable, has_quiz, has_file, sort_order)
    VALUES (?,?,?,?,?,?,?,?,?,50)
");
$stmt->bind_param('ssssssiii', $faculty_id, $key, $label, $icon, $color_bg, $color_text, $is_grad, $has_quiz, $has_file);
$stmt->execute();
$newId = $conn->insert_id;
$stmt->close(); $conn->close();

echo json_encode(['status'=>'success','message'=>"Type '{$label}' created.",'id'=>$newId,'type_key'=>$key]);
