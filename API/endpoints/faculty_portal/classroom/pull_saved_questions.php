<?php
session_start();
header('Content-Type: application/json');

$level = (int)($_SESSION['user_level_id'] ?? $_SESSION['user_type_id'] ?? 0);
$userId = $_SESSION['user_id'] ?? '';

if (!$userId || $level !== 2) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$classId = trim($_POST['class_id'] ?? '');
$questionCount = (int)($_POST['question_count'] ?? 0);
$pointsPerQuestion = (float)($_POST['points_per_question'] ?? 1);
$shuffleChoices = true;
$sourcePostIdsRaw = $_POST['source_post_ids'] ?? '[]';

if ($classId === '') {
    echo json_encode(['status' => 'error', 'message' => 'class_id is required']);
    exit;
}

if ($questionCount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Please enter how many questions to pull.']);
    exit;
}

if ($pointsPerQuestion <= 0) {
    $pointsPerQuestion = 1;
}

$sourcePostIds = json_decode($sourcePostIdsRaw, true);
if (!is_array($sourcePostIds)) {
    $sourcePostIds = [];
}
$sourcePostIds = array_values(array_filter(array_map('trim', $sourcePostIds)));

require_once __DIR__ . '/../../../core/db_connect.php';
require_once __DIR__ . '/saved_questions_helper.php';

$conn->set_charset('utf8mb4');
sqb_sync_existing_posts($conn, $classId, $userId);
$allQuestions = sqb_fetch_questions($conn, $classId, $userId, $sourcePostIds);
$availableCount = count($allQuestions);

if ($questionCount > $availableCount) {
    $conn->close();
    echo json_encode([
        'status' => 'error',
        'message' => "You only have {$availableCount} saved question" . ($availableCount === 1 ? '' : 's') . " available.",
        'available_count' => $availableCount,
    ]);
    exit;
}

shuffle($allQuestions);
$selected = array_slice($allQuestions, 0, $questionCount);
$questions = [];
foreach ($selected as $index => $question) {
    if ($shuffleChoices) {
        $question = sqb_shuffle_question_choices($question);
    }
    $questions[] = [
        'id' => $question['id'],
        'question' => $question['question'],
        'answer' => $question['answer_key'],
        'answer_key' => $question['answer_key'],
        'points' => $pointsPerQuestion,
        'cognitive_level' => $question['cognitive_level'],
        'question_type' => $question['question_type'],
        'source_post_id' => $question['source_post_id'],
        'source_post_title' => $question['source_post_title'],
        'source_topic' => $question['source_topic'],
        'choices' => $question['choices'],
        'order_num' => $index,
    ];
}

$conn->close();

echo json_encode([
    'status' => 'success',
    'available_count' => $availableCount,
    'pulled_count' => count($questions),
    'questions' => $questions,
]);
