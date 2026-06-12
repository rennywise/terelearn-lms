<?php
/**
 * API/facultyUI/classroom/save_post.php
 */
session_start();
header('Content-Type: application/json');

$level   = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$user_id = $_SESSION['user_id'] ?? '';

if (!$user_id || $level !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

$class_id      = trim($_POST['class_id']      ?? '');
$post_type     = trim($_POST['post_type']     ?? 'announcement');
$post_type_id  = trim($_POST['post_type_id']  ?? '');
$sub_label     = trim($_POST['sub_label']     ?? '');
$title         = trim($_POST['title']         ?? '');
$body          = trim($_POST['body']          ?? '');
$due_date      = trim($_POST['due_date']      ?? '');
$points        = trim($_POST['points']        ?? '');
$topic         = trim($_POST['topic']         ?? '');
$lesson_period = strtolower(trim($_POST['lesson_period'] ?? ''));
$links_json    = trim($_POST['links_json']    ?? '[]');
$post_id_input = trim($_POST['post_id']       ?? '');
$quiz_json     = trim($_POST['quiz_json']     ?? '[]');

if (!$class_id) {
    echo json_encode(['status'=>'error','message'=>'class_id is required']); exit;
}

$is_lesson = strtolower($post_type) === 'lesson'
    || stripos($sub_label, 'lesson') !== false;

if ($is_lesson && $topic === '') {
    echo json_encode(['status'=>'error','message'=>'Lesson topic is required.']); exit;
}
if ($is_lesson && !in_array($lesson_period, ['prelim', 'midterm', 'finals'], true)) {
    echo json_encode(['status'=>'error','message'=>'Please select a grading period for this lesson.']); exit;
}
if (!$is_lesson) {
    $lesson_period = '';
}

$links     = json_decode($links_json, true) ?: [];
$questions = json_decode($quiz_json,  true) ?: [];

require_once __DIR__ . '/../../../core/db_connect.php';
require_once __DIR__ . '/saved_questions_helper.php';

function uuid4(){
    return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff),
        mt_rand(0,0x0fff)|0x4000,mt_rand(0,0x3fff)|0x8000,
        mt_rand(0,0xffff),mt_rand(0,0xffff),mt_rand(0,0xffff));
}

// ── Upload directory ──────────────────────────────────────────────────────────
// Build app-relative paths dynamically (works even if project folder name changes).
$doc_root = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '\\/');
$script_name = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
$app_base = preg_replace('#/API/.*$#', '', $script_name);
if ($app_base === '\\' || $app_base === '/') $app_base = '';
$app_base = rtrim(str_replace('\\', '/', $app_base), '/');

$upload_dir = $doc_root
            . ($app_base !== '' ? DIRECTORY_SEPARATOR . ltrim(str_replace('/', DIRECTORY_SEPARATOR, $app_base), DIRECTORY_SEPARATOR) : '')
            . DIRECTORY_SEPARATOR . 'uploads_files'
            . DIRECTORY_SEPARATOR . $class_id
            . DIRECTORY_SEPARATOR;

// Web-accessible path (relative to current app root, e.g. /TERELEARN/uploads_files/...).
$web_base = ($app_base !== '' ? $app_base : '') . '/uploads_files/' . $class_id . '/';

if (!is_dir($upload_dir)) {
    if (!mkdir($upload_dir, 0755, true)) {
        // If directory creation fails, return a useful error
        echo json_encode([
            'status'  => 'error',
            'message' => 'Could not create upload directory: ' . $upload_dir
        ]);
        $conn->close();
        exit;
    }
}

$allowed_ext = ['pdf','doc','docx','ppt','pptx','xls','xlsx',
                'jpg','jpeg','png','gif','webp','txt','mp4','mp3','wav'];

// ── Insert or update post ─────────────────────────────────────────────────────
$is_edit    = $post_id_input !== '';
$post_id    = $is_edit ? $post_id_input : uuid4();
$due_val    = $due_date !== '' ? $due_date : null;
$points_val = $points   !== '' ? (string)(float)$points : null;
$topic_val  = $topic    !== '' ? $topic : null;
$lesson_period_val = $lesson_period !== '' ? $lesson_period : null;

if ($is_edit) {
    $chk = $conn->prepare("SELECT id FROM tblpost WHERE id=? AND class_id=? AND author_id=? AND is_deleted=0 LIMIT 1");
    $chk->bind_param("sss", $post_id, $class_id, $user_id);
    $chk->execute();
    $exists = $chk->get_result()->fetch_assoc();
    $chk->close();

    if (!$exists) {
        echo json_encode(['status'=>'error','message'=>'Post not found or not allowed.']);
        $conn->close(); exit;
    }

    $upd = $conn->prepare("
        UPDATE tblpost
        SET post_type=?, post_type_id=?, sub_label=?,
            title=?, body=?, due_date=?, points=?, topic=?, lesson_period=?
        WHERE id=? AND class_id=? AND author_id=?
    ");
    $upd->bind_param("ssssssssssss",
        $post_type, $post_type_id, $sub_label,
        $title, $body, $due_val, $points_val, $topic_val, $lesson_period_val,
        $post_id, $class_id, $user_id
    );
    if (!$upd->execute()) {
        echo json_encode(['status'=>'error','message'=>'Failed to update post: '.$upd->error]);
        $upd->close(); $conn->close(); exit;
    }
    $upd->close();

    // Delete old questions + choices
    $oldQ = $conn->prepare("SELECT id FROM tblpostquestion WHERE post_id=?");
    $oldQ->bind_param("s", $post_id);
    $oldQ->execute();
    $rows = $oldQ->get_result();
    while ($row = $rows->fetch_assoc()) {
        $delC = $conn->prepare("DELETE FROM tblpostchoice WHERE question_id=?");
        $delC->bind_param("s", $row['id']);
        $delC->execute(); $delC->close();
    }
    $oldQ->close();

    $delQ = $conn->prepare("DELETE FROM tblpostquestion WHERE post_id=?");
    $delQ->bind_param("s", $post_id); $delQ->execute(); $delQ->close();

    $delL = $conn->prepare("DELETE FROM tblpostattachment WHERE post_id=? AND attach_type IN ('link','youtube')");
    $delL->bind_param("s", $post_id); $delL->execute(); $delL->close();

} else {
    $ins = $conn->prepare("
        INSERT INTO tblpost
            (id, class_id, author_id, post_type, post_type_id, sub_label,
             title, body, due_date, points, topic, lesson_period, is_deleted, created_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,0,NOW())
    ");
    $ins->bind_param("ssssssssssss",
        $post_id, $class_id, $user_id,
        $post_type, $post_type_id, $sub_label,
        $title, $body, $due_val, $points_val, $topic_val, $lesson_period_val
    );
    if (!$ins->execute()) {
        echo json_encode(['status'=>'error','message'=>'Failed to create post: '.$ins->error]);
        $ins->close(); $conn->close(); exit;
    }
    $ins->close();
}

if (!empty($questions)) {
    sqb_ensure_tables($conn);
    if ($is_edit) {
        sqb_delete_source_post_questions($conn, $post_id);
    }
}

// ── Handle uploaded files ─────────────────────────────────────────────────────
$upload_errors   = [];
$upload_success  = [];

if (!empty($_FILES['files']['name'][0])) {
    $files = $_FILES['files'];
    $count = count($files['name']);

    for ($i = 0; $i < $count; $i++) {
        if (empty($files['name'][$i])) continue;

        // Check PHP upload error code
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $codes = [
                1=>'File exceeds upload_max_filesize in php.ini',
                2=>'File exceeds MAX_FILE_SIZE in form',
                3=>'File only partially uploaded',
                4=>'No file uploaded',
                6=>'Missing temp folder',
                7=>'Failed to write to disk',
                8=>'Extension stopped upload',
            ];
            $upload_errors[] = ($codes[$files['error'][$i]] ?? 'Unknown error code '.$files['error'][$i])
                              . ' — file: '.$files['name'][$i];
            continue;
        }

        $orig_name = basename($files['name'][$i]);
        $ext       = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $upload_errors[] = 'Extension not allowed: '.$ext.' ('.$orig_name.')';
            continue;
        }

        // Sanitize filename and make it unique
        $safe      = preg_replace('/[^a-zA-Z0-9._-]/', '_', $orig_name);
        $filename  = dechex(time()) . '_' . $i . '_' . $safe;
        $dest      = $upload_dir . $filename;

        if (!move_uploaded_file($files['tmp_name'][$i], $dest)) {
            // Provide detailed debug info so you can diagnose the exact failure
            $upload_errors[] = implode(' | ', [
                'move_uploaded_file failed',
                'src: '  . $files['tmp_name'][$i],
                'dest: ' . $dest,
                'dir_exists: '    . (is_dir($upload_dir) ? 'yes' : 'no'),
                'dir_writable: '  . (is_writable($upload_dir) ? 'yes' : 'no'),
                'tmp_exists: '    . (file_exists($files['tmp_name'][$i]) ? 'yes' : 'no'),
                'tmp_readable: '  . (is_readable($files['tmp_name'][$i]) ? 'yes' : 'no'),
            ]);
            continue;
        }

        // Successfully moved to disk
        $upload_success[] = $orig_name;

        $mime_map = [
            'pdf' =>'application/pdf',
            'doc' =>'application/msword',
            'docx'=>'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'ppt' =>'application/vnd.ms-powerpoint',
            'pptx'=>'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'xls' =>'application/vnd.ms-excel',
            'xlsx'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'jpg' =>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png',
            'gif' =>'image/gif','webp'=>'image/webp','txt'=>'text/plain',
            'mp4' =>'video/mp4','mp3'=>'audio/mpeg','wav'=>'audio/wav',
        ];
        $mime      = $mime_map[$ext] ?? 'application/octet-stream';
        $web_path  = $web_base . $filename;
        $attach_id = uuid4();
        $file_size = (int)filesize($dest);

        $stmt = $conn->prepare("
            INSERT INTO tblpostattachment
                (id, post_id, attach_type, file_name, file_path, file_size, mime_type)
            VALUES (?, ?, 'file', ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssssds",
            $attach_id,
            $post_id,
            $orig_name,
            $web_path,
            $file_size,
            $mime
        );
        if (!$stmt->execute()) {
            $upload_errors[] = 'DB insert failed for ['.$orig_name.']: '.$stmt->error;
        }
        $stmt->close();
    }
}

// ── Handle links / YouTube ────────────────────────────────────────────────────
foreach ($links as $lnk) {
    $url  = trim($lnk['url']  ?? '');
    $type = trim($lnk['type'] ?? 'link');
    if (!$url) continue;
    $lid  = uuid4();
    $lins = $conn->prepare("INSERT INTO tblpostattachment (id, post_id, attach_type, url) VALUES (?,?,?,?)");
    $lins->bind_param("ssss", $lid, $post_id, $type, $url);
    $lins->execute(); $lins->close();
}

// ── Handle quiz questions ─────────────────────────────────────────────────────
foreach ($questions as $qi => $q) {
    $qtext      = trim($q['question']  ?? '');
    $answer_key = trim($q['answer']    ?? $q['answer_key'] ?? '');
    $qpts       = (float)($q['points'] ?? 1);
    if (!$qtext) continue;

    $qid   = uuid4();
    $order = (int)$qi;
    $cognitive_level = trim((string)($q['cognitive_level'] ?? ''));
    $cognitive_level = strtolower($cognitive_level);
    $allowed_levels = ['remembering','understanding','applying','analyzing','evaluating','creating'];
    if (!in_array($cognitive_level, $allowed_levels, true)) $cognitive_level = '';
    $q_time_limit = (int)($q['time_limit_seconds'] ?? 0);
    if ($q_time_limit < 0) $q_time_limit = 0;

    static $has_cognitive_level = null;
    if ($has_cognitive_level === null) {
        $col = $conn->query("SHOW COLUMNS FROM tblpostquestion LIKE 'cognitive_level'");
        $has_cognitive_level = ($col && $col->num_rows > 0);
        if (!$has_cognitive_level) {
            // Self-heal older local databases so Bloom's taxonomy can be stored.
            $conn->query("ALTER TABLE tblpostquestion ADD COLUMN cognitive_level VARCHAR(32) NULL AFTER order_num");
            $col = $conn->query("SHOW COLUMNS FROM tblpostquestion LIKE 'cognitive_level'");
            $has_cognitive_level = ($col && $col->num_rows > 0);
        }
    }
    static $has_q_time_limit = null;
    if ($has_q_time_limit === null) {
        $col = $conn->query("SHOW COLUMNS FROM tblpostquestion LIKE 'time_limit_seconds'");
        $has_q_time_limit = ($col && $col->num_rows > 0);
    }

    if ($has_cognitive_level && $has_q_time_limit) {
        $qins = $conn->prepare("
            INSERT INTO tblpostquestion (id, post_id, question, answer_key, points, order_num, cognitive_level, time_limit_seconds)
            VALUES (?,?,?,?,?,?,?,?)
        ");
        $qins->bind_param("ssssdisi", $qid, $post_id, $qtext, $answer_key, $qpts, $order, $cognitive_level, $q_time_limit);
    } elseif ($has_cognitive_level) {
        $qins = $conn->prepare("
            INSERT INTO tblpostquestion (id, post_id, question, answer_key, points, order_num, cognitive_level)
            VALUES (?,?,?,?,?,?,?)
        ");
        $qins->bind_param("ssssdis", $qid, $post_id, $qtext, $answer_key, $qpts, $order, $cognitive_level);
    } elseif ($has_q_time_limit) {
        $qins = $conn->prepare("
            INSERT INTO tblpostquestion (id, post_id, question, answer_key, points, order_num, time_limit_seconds)
            VALUES (?,?,?,?,?,?,?)
        ");
        $qins->bind_param("ssssdii", $qid, $post_id, $qtext, $answer_key, $qpts, $order, $q_time_limit);
    } else {
        $qins = $conn->prepare("
            INSERT INTO tblpostquestion (id, post_id, question, answer_key, points, order_num)
            VALUES (?,?,?,?,?,?)
        ");
        $qins->bind_param("ssssdi", $qid, $post_id, $qtext, $answer_key, $qpts, $order);
    }
    $qins->execute(); $qins->close();

    foreach (($q['choices'] ?? []) as $ci => $ch) {
        $ctext   = trim($ch['text'] ?? $ch['choice_text'] ?? '');
        $correct = (int)($ch['is_correct'] ?? 0);
        if (!$ctext) continue;
        $cid  = uuid4();
        $cord = (int)$ci;
        $cins = $conn->prepare("INSERT INTO tblpostchoice (id, question_id, choice_text, is_correct, order_num) VALUES (?,?,?,?,?)");
        $cins->bind_param("sssii", $cid, $qid, $ctext, $correct, $cord);
        $cins->execute(); $cins->close();
    }
}

// ── Save quiz settings as draft when quiz fields are provided ──────────────────
$qm_in  = trim($_POST['quiz_mode']          ?? '');
$tm_in  = trim($_POST['time_mode']          ?? '');
$tl_in  = trim($_POST['time_limit_seconds'] ?? '');
$oa_in  = trim($_POST['open_at']            ?? '');
$ca_in  = trim($_POST['close_at']           ?? '');
$ma_in  = (int)($_POST['max_attempts']      ?? 1);

if ($qm_in !== '' && in_array($qm_in, ['live','due_date'])) {
    $qm   = $conn->real_escape_string($qm_in);
    // Canonical time mode stored at post level.
    $tm_raw = strtolower($tm_in);
    if (!in_array($tm_raw, ['none','per_quiz','per_question'], true)) $tm_raw = 'none';

    // Fallback: when per-question mode is selected, derive seconds from question payload.
    $tl_num = ($tl_in !== '' && (int)$tl_in > 0) ? (int)$tl_in : 0;
    if ($tm_raw === 'per_question' && $tl_num <= 0) {
        foreach ($questions as $qq) {
            $qsec = (int)($qq['time_limit_seconds'] ?? 0);
            if ($qsec > 0) { $tl_num = $qsec; break; }
        }
    }
    $tl   = ($tl_num > 0) ? $tl_num : 'NULL';
    $ma   = max(0, min(2, $ma_in));
    $oa   = 'NULL';
    $du   = 'NULL';
    if ($qm_in === 'due_date' && $oa_in !== '') {
        $ts = strtotime($oa_in);
        if ($ts !== false) $oa = "'" . $conn->real_escape_string(date('Y-m-d H:i:s', $ts)) . "'";
    }
    if ($qm_in === 'due_date' && $ca_in !== '') {
        $ts = strtotime($ca_in);
        if ($ts !== false) $du = "'" . $conn->real_escape_string(date('Y-m-d H:i:s', $ts)) . "'";
    }
    $pid_esc = $conn->real_escape_string($post_id);
    $chkQ = $conn->query("SELECT id FROM tblquiz WHERE post_id='$pid_esc' LIMIT 1");
    if ($chkQ && $chkQ->num_rows > 0) {
        $qid_esc = $conn->real_escape_string($chkQ->fetch_assoc()['id']);
        $conn->query("UPDATE tblquiz SET is_published=0,quiz_mode='$qm',max_attempts=$ma,time_limit_seconds=$tl,due_date=$du,is_force_closed=0,live_started_at=NULL,live_ended_at=NULL,results_released_at=NULL WHERE id='$qid_esc'");
    } else {
        $nqid = uuid4();
        $conn->query("INSERT INTO tblquiz (id,post_id,is_published,quiz_mode,max_attempts,time_limit_seconds,due_date,is_force_closed,live_started_at,live_ended_at,results_released_at) VALUES ('" . $conn->real_escape_string($nqid) . "','$pid_esc',0,'$qm',$ma,$tl,$du,0,NULL,NULL,NULL)");
    }

    // Keep quiz as draft after save so Publish appears before Manage Quiz.
    $tm_esc = $conn->real_escape_string($tm_raw);
    if ($qm_in === 'due_date') {
        $conn->query("UPDATE tblpost SET is_published=0, is_force_closed=0, is_force_open=0, live_started_at=NULL, live_ended_at=NULL, results_released_at=NULL, time_mode='$tm_esc', open_at=$oa, close_at=$du WHERE id='$pid_esc'");
    } else {
        $conn->query("UPDATE tblpost SET is_published=0, is_force_closed=0, is_force_open=0, live_started_at=NULL, live_ended_at=NULL, results_released_at=NULL, time_mode='$tm_esc', open_at=NULL, close_at=NULL WHERE id='$pid_esc'");
    }
}

if (!empty($questions)) {
    sqb_store_post_questions(
        $conn,
        $class_id,
        $user_id,
        $post_id,
        $title,
        $post_type,
        $topic_val ?? '',
        $questions
    );
}

$conn->close();

echo json_encode([
    'status'         => 'success',
    'message'        => $is_edit ? 'Post updated successfully.' : 'Post created successfully.',
    'post_id'        => $post_id,
    'files_received' => isset($_FILES['files']) ? count(array_filter($_FILES['files']['name'])) : 0,
    'upload_success' => $upload_success,
    'upload_errors'  => $upload_errors,
]);
