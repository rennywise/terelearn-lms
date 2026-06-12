<?php

if (!function_exists('sqb_uuid4')) {
    function sqb_uuid4(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }
}

function sqb_ensure_tables(mysqli $conn): void
{
    $conn->query("
        CREATE TABLE IF NOT EXISTS tblsavedquestion (
            id VARCHAR(36) NOT NULL PRIMARY KEY,
            class_id VARCHAR(36) NOT NULL,
            author_id VARCHAR(36) NOT NULL,
            source_post_id VARCHAR(36) NOT NULL,
            source_post_title VARCHAR(255) DEFAULT NULL,
            source_post_type VARCHAR(100) DEFAULT NULL,
            source_topic VARCHAR(255) DEFAULT NULL,
            question_type VARCHAR(32) NOT NULL DEFAULT 'multiple_choice',
            question TEXT NOT NULL,
            answer_key TEXT DEFAULT NULL,
            points DECIMAL(10,2) NOT NULL DEFAULT 1.00,
            cognitive_level VARCHAR(32) DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_savedquestion_class_author (class_id, author_id),
            INDEX idx_savedquestion_source_post (source_post_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    $conn->query("
        CREATE TABLE IF NOT EXISTS tblsavedquestionchoice (
            id VARCHAR(36) NOT NULL PRIMARY KEY,
            saved_question_id VARCHAR(36) NOT NULL,
            choice_text TEXT NOT NULL,
            is_correct TINYINT(1) NOT NULL DEFAULT 0,
            order_num INT NOT NULL DEFAULT 0,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_savedquestionchoice_question (saved_question_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function sqb_delete_source_post_questions(mysqli $conn, string $sourcePostId): void
{
    if ($sourcePostId === '') {
        return;
    }

    $ids = [];
    $sel = $conn->prepare("SELECT id FROM tblsavedquestion WHERE source_post_id=?");
    $sel->bind_param("s", $sourcePostId);
    $sel->execute();
    $res = $sel->get_result();
    while ($row = $res->fetch_assoc()) {
        $ids[] = $row['id'];
    }
    $sel->close();

    if (!$ids) {
        return;
    }

    $delChoice = $conn->prepare("DELETE FROM tblsavedquestionchoice WHERE saved_question_id=?");
    foreach ($ids as $savedQuestionId) {
        $delChoice->bind_param("s", $savedQuestionId);
        $delChoice->execute();
    }
    $delChoice->close();

    $delQuestion = $conn->prepare("DELETE FROM tblsavedquestion WHERE source_post_id=?");
    $delQuestion->bind_param("s", $sourcePostId);
    $delQuestion->execute();
    $delQuestion->close();
}

function sqb_store_post_questions(
    mysqli $conn,
    string $classId,
    string $authorId,
    string $sourcePostId,
    string $sourcePostTitle,
    string $sourcePostType,
    string $sourceTopic,
    array $questions
): void {
    sqb_ensure_tables($conn);
    sqb_delete_source_post_questions($conn, $sourcePostId);

    $allowedLevels = ['remembering', 'understanding', 'applying', 'analyzing', 'evaluating', 'creating'];
    $insQuestion = $conn->prepare("
        INSERT INTO tblsavedquestion
            (id, class_id, author_id, source_post_id, source_post_title, source_post_type, source_topic, question_type, question, answer_key, points, cognitive_level)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
    ");
    $insChoice = $conn->prepare("
        INSERT INTO tblsavedquestionchoice
            (id, saved_question_id, choice_text, is_correct, order_num)
        VALUES (?,?,?,?,?)
    ");

    foreach ($questions as $question) {
        $questionText = trim((string)($question['question'] ?? ''));
        if ($questionText === '') {
            continue;
        }

        $choices = is_array($question['choices'] ?? null) ? $question['choices'] : [];
        $questionType = $choices ? 'multiple_choice' : 'identification';
        $answerKey = trim((string)($question['answer'] ?? $question['answer_key'] ?? ''));
        $points = (float)($question['points'] ?? 1);
        if ($points <= 0) {
            $points = 1;
        }
        $level = strtolower(trim((string)($question['cognitive_level'] ?? '')));
        if (!in_array($level, $allowedLevels, true)) {
            $level = null;
        }

        $savedQuestionId = sqb_uuid4();
        $insQuestion->bind_param(
            "sssssssssdss",
            $savedQuestionId,
            $classId,
            $authorId,
            $sourcePostId,
            $sourcePostTitle,
            $sourcePostType,
            $sourceTopic,
            $questionType,
            $questionText,
            $answerKey,
            $points,
            $level
        );
        $insQuestion->execute();

        foreach ($choices as $choiceIndex => $choice) {
            $choiceText = trim((string)($choice['text'] ?? $choice['choice_text'] ?? ''));
            if ($choiceText === '') {
                continue;
            }
            $choiceId = sqb_uuid4();
            $isCorrect = !empty($choice['is_correct']) ? 1 : 0;
            $orderNum = (int)$choiceIndex;
            $insChoice->bind_param("sssii", $choiceId, $savedQuestionId, $choiceText, $isCorrect, $orderNum);
            $insChoice->execute();
        }
    }

    $insQuestion->close();
    $insChoice->close();
}

function sqb_fetch_sources(mysqli $conn, string $classId, string $authorId): array
{
    sqb_ensure_tables($conn);
    $sql = "
        SELECT
            source_post_id,
            COALESCE(NULLIF(source_post_title, ''), 'Untitled Quiz') AS source_post_title,
            COALESCE(NULLIF(source_post_type, ''), 'quiz') AS source_post_type,
            COALESCE(source_topic, '') AS source_topic,
            COUNT(*) AS question_count,
            MAX(created_at) AS latest_created_at
        FROM tblsavedquestion
        WHERE class_id=? AND author_id=?
        GROUP BY source_post_id, source_post_title, source_post_type, source_topic
        ORDER BY latest_created_at DESC, source_post_title ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $classId, $authorId);
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = [];
    while ($row = $res->fetch_assoc()) {
        $row['question_count'] = (int)$row['question_count'];
        $rows[] = $row;
    }
    $stmt->close();
    return $rows;
}

function sqb_fetch_questions(
    mysqli $conn,
    string $classId,
    string $authorId,
    array $sourcePostIds = []
): array {
    sqb_ensure_tables($conn);

    $sql = "
        SELECT
            q.id,
            q.source_post_id,
            q.source_post_title,
            q.source_post_type,
            q.source_topic,
            q.question_type,
            q.question,
            q.answer_key,
            q.points,
            q.cognitive_level
        FROM tblsavedquestion q
        WHERE q.class_id=? AND q.author_id=?
    ";
    $params = [$classId, $authorId];
    $types = "ss";

    if ($sourcePostIds) {
        $placeholders = implode(',', array_fill(0, count($sourcePostIds), '?'));
        $sql .= " AND q.source_post_id IN ($placeholders)";
        foreach ($sourcePostIds as $id) {
            $types .= "s";
            $params[] = $id;
        }
    }

    $sql .= " ORDER BY q.created_at DESC, q.source_post_title ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $questions = [];
    while ($row = $res->fetch_assoc()) {
        $row['points'] = (float)$row['points'];
        $row['choices'] = [];
        $questions[$row['id']] = $row;
    }
    $stmt->close();

    if (!$questions) {
        return [];
    }

    $questionIds = array_keys($questions);
    $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
    $types = str_repeat('s', count($questionIds));
    $choiceSql = "
        SELECT saved_question_id, choice_text, is_correct, order_num
        FROM tblsavedquestionchoice
        WHERE saved_question_id IN ($placeholders)
        ORDER BY order_num ASC, created_at ASC
    ";
    $choiceStmt = $conn->prepare($choiceSql);
    $choiceStmt->bind_param($types, ...$questionIds);
    $choiceStmt->execute();
    $choiceRes = $choiceStmt->get_result();
    while ($choice = $choiceRes->fetch_assoc()) {
        $qid = $choice['saved_question_id'];
        if (!isset($questions[$qid])) {
            continue;
        }
        $questions[$qid]['choices'][] = [
            'text' => $choice['choice_text'],
            'is_correct' => (int)$choice['is_correct'] === 1,
        ];
    }
    $choiceStmt->close();

    return array_values($questions);
}

function sqb_shuffle_question_choices(array $question): array
{
    if (!empty($question['choices']) && is_array($question['choices'])) {
        shuffle($question['choices']);
    }
    return $question;
}

function sqb_load_post_questions_by_post_id(mysqli $conn, string $postId): array
{
    $stmt = $conn->prepare("
        SELECT id, question, answer_key, points, cognitive_level
        FROM tblpostquestion
        WHERE post_id=?
        ORDER BY order_num ASC, id ASC
    ");
    $stmt->bind_param("s", $postId);
    $stmt->execute();
    $res = $stmt->get_result();
    $questions = [];
    while ($row = $res->fetch_assoc()) {
        $row['choices'] = [];
        $questions[$row['id']] = [
            'question' => $row['question'],
            'answer' => $row['answer_key'],
            'points' => (float)$row['points'],
            'cognitive_level' => $row['cognitive_level'],
            'choices' => [],
        ];
    }
    $stmt->close();

    if (!$questions) {
        return [];
    }

    $questionIds = array_keys($questions);
    $placeholders = implode(',', array_fill(0, count($questionIds), '?'));
    $types = str_repeat('s', count($questionIds));
    $choiceSql = "
        SELECT question_id, choice_text, is_correct
        FROM tblpostchoice
        WHERE question_id IN ($placeholders)
        ORDER BY order_num ASC, id ASC
    ";
    $choiceStmt = $conn->prepare($choiceSql);
    $choiceStmt->bind_param($types, ...$questionIds);
    $choiceStmt->execute();
    $choiceRes = $choiceStmt->get_result();
    while ($choice = $choiceRes->fetch_assoc()) {
        $qid = $choice['question_id'];
        if (!isset($questions[$qid])) {
            continue;
        }
        $questions[$qid]['choices'][] = [
            'text' => $choice['choice_text'],
            'is_correct' => (int)$choice['is_correct'] === 1,
        ];
    }
    $choiceStmt->close();

    return array_values($questions);
}

function sqb_sync_existing_posts(mysqli $conn, string $classId, string $authorId): void
{
    sqb_ensure_tables($conn);

    $stmt = $conn->prepare("
        SELECT p.id, p.title, p.post_type, COALESCE(p.topic, '') AS topic, COUNT(q.id) AS post_question_count
        FROM tblpost p
        INNER JOIN tblpostquestion q ON q.post_id = p.id
        WHERE p.class_id=? AND p.author_id=? AND p.is_deleted=0
        GROUP BY p.id, p.title, p.post_type, p.topic
    ");
    $stmt->bind_param("ss", $classId, $authorId);
    $stmt->execute();
    $res = $stmt->get_result();
    $posts = [];
    while ($row = $res->fetch_assoc()) {
        $posts[] = $row;
    }
    $stmt->close();

    $countStmt = $conn->prepare("SELECT COUNT(*) AS bank_count FROM tblsavedquestion WHERE source_post_id=?");
    foreach ($posts as $post) {
        $postId = (string)$post['id'];
        $countStmt->bind_param("s", $postId);
        $countStmt->execute();
        $countRes = $countStmt->get_result()->fetch_assoc();
        $bankCount = (int)($countRes['bank_count'] ?? 0);
        $postCount = (int)($post['post_question_count'] ?? 0);
        if ($bankCount === $postCount && $bankCount > 0) {
            continue;
        }
        $questions = sqb_load_post_questions_by_post_id($conn, $postId);
        if (!$questions) {
            continue;
        }
        sqb_store_post_questions(
            $conn,
            $classId,
            $authorId,
            $postId,
            (string)($post['title'] ?? ''),
            (string)($post['post_type'] ?? ''),
            (string)($post['topic'] ?? ''),
            $questions
        );
    }
    $countStmt->close();
}
