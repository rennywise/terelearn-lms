    <?php
    header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

    function generateJoinCode($conn) {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $code = strtoupper(substr(bin2hex(random_bytes(4)), 0, 7));
            $chk = $conn->prepare("SELECT id FROM tblclass WHERE join_code = ? LIMIT 1");
            $chk->bind_param("s", $code);
            $chk->execute();
            $exists = $chk->get_result()->fetch_assoc();
            $chk->close();
            if (!$exists) return $code;
        }
        return strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 7));
    }

    try {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $src = is_array($json) ? $json : $_POST;

        $class_id      = trim($src['class_id'] ?? '');
        $course_id     = trim($src['course_id'] ?? '');
        $subject_id    = trim($src['subject_id'] ?? '');
        $faculty_id    = trim($src['faculty_id'] ?? '');
        $section       = trim($src['section'] ?? '');
        $semester      = trim($src['class_semester'] ?? '');
        $year_level    = trim($src['year_level'] ?? '');
        $schedule      = trim($src['schedule'] ?? '');
        $break_time    = trim($src['break_time'] ?? '');
        $class_days    = trim($src['class_days_formatted'] ?? '');
        $w_recitation  = (int)($src['w_recitation'] ?? 10);
        $w_quiz        = (int)($src['w_quiz'] ?? 20);
        $w_activities  = (int)($src['w_activities'] ?? 30);
        $w_exam        = (int)($src['w_exam'] ?? 40);
        $cnt_recitation= (int)($src['cnt_recitation'] ?? 0);
        $cnt_quiz      = (int)($src['cnt_quiz'] ?? 0);
        $cnt_activities= (int)($src['cnt_activities'] ?? 0);
        $cnt_exam      = (int)($src['cnt_exam'] ?? 0);

        if (($w_recitation + $w_quiz + $w_activities + $w_exam) !== 100) {
            echo json_encode(['status' => 'error', 'message' => 'Grading weights must total 100%.']);
            exit;
        }

        if (!$course_id || !$subject_id || !$faculty_id || !$section || !$schedule) {
            echo json_encode(['status' => 'error', 'message' => 'Required fields missing']);
            exit;
        }

        // Get subject code for class_code
        $sub_stmt = $conn->prepare("SELECT subject_code FROM tblsubject WHERE id=? AND is_deleted=0");
        if (!$sub_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $sub_stmt->bind_param("s", $subject_id);
        $sub_stmt->execute();
        $sub_result = $sub_stmt->get_result();

        if ($sub_result->num_rows === 0) {
            throw new Exception('Subject not found');
        }

        $sub_row = $sub_result->fetch_assoc();
        $class_code = $sub_row['subject_code'] . '-' . $section;
        $sub_stmt->close();

        $is_update = !empty($class_id);

        if ($is_update) {
            // UPDATE
            $sql = "UPDATE tblclass SET
                    subject_id=?, 
                    course_id=?, 
                    faculty_id=?, 
                    section=?,
                    class_semester=?, 
                    year_level=?, 
                    schedule=?,
                    break_time=?,
                    class_days=?,
                    class_code=?,
                    updated_at=NOW()
                    WHERE id=? AND is_deleted=0";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("sssssssssss", 
                $subject_id, $course_id, $faculty_id, $section,
                $semester, $year_level, $schedule, $break_time,
                $class_days, $class_code, $class_id);
                
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            if ($stmt->affected_rows > 0) {
                ensureGradingTable($conn);
                saveGradingScheme($conn, $class_id, $w_recitation, $w_quiz, $w_activities, $w_exam, $cnt_recitation, $cnt_quiz, $cnt_activities, $cnt_exam);
                echo json_encode(['status' => 'success', 'message' => 'Class updated successfully!']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Class not found or no changes made']);
            }
            $stmt->close();
        } else {
            // INSERT
            $class_id = bin2hex(random_bytes(16));
            // Always generate a fresh class code on create
            $subject_prefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', (string)$sub_row['subject_code']));
            if ($subject_prefix === '') $subject_prefix = 'CLS';
            $insert_class_code = '';
            for ($attempt = 0; $attempt < 20; $attempt++) {
                $suffix = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
                $candidate = $subject_prefix . '-' . $suffix;
                $chkCode = $conn->prepare("SELECT id FROM tblclass WHERE class_code = ? LIMIT 1");
                $chkCode->bind_param("s", $candidate);
                $chkCode->execute();
                $existsCode = $chkCode->get_result()->fetch_assoc();
                $chkCode->close();
                if (!$existsCode) { $insert_class_code = $candidate; break; }
            }
            if ($insert_class_code === '') {
                $insert_class_code = $subject_prefix . '-' . strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 5));
            }
            $join_code = generateJoinCode($conn);
            $join_link_token = bin2hex(random_bytes(24));
            
            $sql = "INSERT INTO tblclass
                    (id, class_code, subject_id, course_id, faculty_id, section,
                    class_semester, year_level, schedule, break_time, class_days,
                    join_code, join_link_token, is_active, is_deleted, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 0, NOW(), NOW())";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("sssssssssssss", 
                $class_id, $insert_class_code, $subject_id, $course_id, $faculty_id, $section,
                $semester, $year_level, $schedule, $break_time, $class_days, $join_code, $join_link_token);
                
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }

            ensureGradingTable($conn);
            saveGradingScheme($conn, $class_id, $w_recitation, $w_quiz, $w_activities, $w_exam, $cnt_recitation, $cnt_quiz, $cnt_activities, $cnt_exam);
            echo json_encode(['status' => 'success', 'message' => 'Class added successfully!']);
            $stmt->close();
        }

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }

    $conn->close();

    function ensureGradingTable($conn) {
        $sql = "CREATE TABLE IF NOT EXISTS tblclassgradingscheme (
            id VARCHAR(36) NOT NULL,
            class_id VARCHAR(36) NOT NULL,
            weight_recitation INT NOT NULL DEFAULT 10,
            weight_quiz INT NOT NULL DEFAULT 20,
            weight_activities INT NOT NULL DEFAULT 30,
            weight_exam INT NOT NULL DEFAULT 40,
            count_recitation INT NOT NULL DEFAULT 0,
            count_quiz INT NOT NULL DEFAULT 0,
            count_activities INT NOT NULL DEFAULT 0,
            count_exam INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_class_id (class_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        $conn->query($sql);
    }

    function saveGradingScheme($conn, $class_id, $wr, $wq, $wa, $we, $cr, $cq, $ca, $ce) {
        $id = bin2hex(random_bytes(16));
        $stmt = $conn->prepare("
            INSERT INTO tblclassgradingscheme
            (id, class_id, weight_recitation, weight_quiz, weight_activities, weight_exam, count_recitation, count_quiz, count_activities, count_exam)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                weight_recitation = VALUES(weight_recitation),
                weight_quiz = VALUES(weight_quiz),
                weight_activities = VALUES(weight_activities),
                weight_exam = VALUES(weight_exam),
                count_recitation = VALUES(count_recitation),
                count_quiz = VALUES(count_quiz),
                count_activities = VALUES(count_activities),
                count_exam = VALUES(count_exam),
                updated_at = CURRENT_TIMESTAMP
        ");
        $stmt->bind_param("ssiiiiiiii", $id, $class_id, $wr, $wq, $wa, $we, $cr, $cq, $ca, $ce);
        $stmt->execute();
        $stmt->close();
    }
    ?>
