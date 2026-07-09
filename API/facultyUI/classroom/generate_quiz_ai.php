<?php
ob_start();
session_start();

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../../core/config_ai.php';

function send_json($data, $code = 200) {
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function fail_json($message, $code = 400) {
    send_json([
        'status' => 'error',
        'message' => $message
    ], $code);
}

set_error_handler(function ($severity, $message, $file, $line) {
    // Ignore warnings/notices explicitly suppressed with @.
    if (!(error_reporting() & $severity)) {
        return false;
    }
    fail_json("PHP error: $message", 500);
});

set_exception_handler(function ($e) {
    fail_json("Server error: " . $e->getMessage(), 500);
});

$geminiApiKey = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
$openaiApiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail_json('Invalid request method.', 405);
}

if (!isset($_FILES['source_file']) || $_FILES['source_file']['error'] !== UPLOAD_ERR_OK) {
    fail_json('Please upload a PDF, PPTX, DOCX, or TXT file.');
}

$file = $_FILES['source_file'];
$tmpPath = $file['tmp_name'];
$originalName = $file['name'] ?? 'uploaded_file';
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

$allowed = ['pdf', 'pptx', 'docx', 'txt'];
if (!in_array($ext, $allowed, true)) {
    fail_json('Unsupported file type. Use PDF, PPTX, DOCX, or TXT.');
}

if (($file['size'] ?? 0) > 15 * 1024 * 1024) {
    fail_json('File is too large. Maximum allowed size is 15MB.');
}

$mcqCount = max(0, (int)($_POST['mcq_count'] ?? 10));
$pointsPerQuestion = max(0, (float)($_POST['points_per_question'] ?? 0));
$totalPoints = max(0, (float)($_POST['total_points'] ?? 0));
$assessmentKind = strtolower(trim((string)($_POST['assessment_kind'] ?? 'quiz')));
$assessmentKind = $assessmentKind === 'exam' ? 'exam' : 'quiz';
$assessmentLabel = $assessmentKind === 'exam' ? 'exam' : 'quiz';
$assessmentTitle = $assessmentKind === 'exam' ? 'examination' : 'quiz';
$cognitiveList = "remembering, understanding, applying, analyzing, evaluating, creating";
$defaultModel = trim($openaiApiKey) !== '' ? 'openai:gpt-4.1-mini' : 'gemini:gemini-2.5-flash-lite';
$aiModelRaw = trim((string)($_POST['ai_model'] ?? $defaultModel));
$aiProvider = 'gemini';
$aiModel = 'gemini-2.5-flash-lite';
if (strpos($aiModelRaw, ':') !== false) {
    [$aiProvider, $aiModel] = explode(':', $aiModelRaw, 2);
} else {
    $aiModel = $aiModelRaw;
}
$aiProvider = strtolower(trim($aiProvider));
$aiModel = trim($aiModel);

// Keep selected provider. For PDFs we will try to extract text so OpenAI can be used too.

$totalQuestions = $mcqCount;

$easyCount    = (int) round($totalQuestions * 0.20);
$hardCount    = (int) round($totalQuestions * 0.20);
$averageCount = $totalQuestions - $easyCount - $hardCount;

if ($totalQuestions < 10) {
    fail_json('Please request at least 10 questions.');
}

if ($totalQuestions > 50) {
    fail_json('Maximum allowed questions is 50.');
}

function extract_text_from_txt($path) {
    $text = file_get_contents($path);
    return is_string($text) ? $text : '';
}

function extract_text_from_pptx($path) {
    if (!class_exists('ZipArchive')) {
        return '';
    }

    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        return '';
    }

    $texts = [];

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);

        if (preg_match('#^ppt/slides/slide[0-9]+\.xml$#', $name)) {
            $xml = $zip->getFromIndex($i);
            if ($xml) {
                $xml = preg_replace('/<a:t[^>]*>/', ' ', $xml);
                $xml = preg_replace('/<\/a:t>/', ' ', $xml);
                $plain = strip_tags($xml);
                $plain = html_entity_decode($plain, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $plain = preg_replace('/\s+/', ' ', $plain);
                $texts[] = trim($plain);
            }
        }
    }

    $zip->close();

    return trim(implode("\n\n", $texts));
}

function extract_text_from_docx($path) {
    if (!class_exists('ZipArchive')) {
        return '';
    }

    $zip = new ZipArchive();
    if ($zip->open($path) !== true) {
        return '';
    }

    $xml = $zip->getFromName('word/document.xml');
    $zip->close();

    if (!$xml) {
        return '';
    }

    $xml = preg_replace('/<w:t[^>]*>/', ' ', $xml);
    $xml = preg_replace('/<\/w:t>/', ' ', $xml);
    $plain = strip_tags($xml);
    $plain = html_entity_decode($plain, ENT_QUOTES | ENT_XML1, 'UTF-8');
    $plain = preg_replace('/\s+/', ' ', $plain);

    return trim($plain);
}

function extract_text_from_pdf_basic($path) {
    // 1) Best effort via pdftotext if available
    $tmpTxt = tempnam(sys_get_temp_dir(), 'pdf_txt_');
    if (is_string($tmpTxt) && $tmpTxt !== '') {
        @unlink($tmpTxt); // pdftotext will create it
        $cmd = 'pdftotext -layout ' . escapeshellarg($path) . ' ' . escapeshellarg($tmpTxt) . ' 2>&1';
        @exec($cmd, $out, $code);
        if ($code === 0 && file_exists($tmpTxt)) {
            $txt = @file_get_contents($tmpTxt);
            @unlink($tmpTxt);
            if (is_string($txt) && trim($txt) !== '') {
                return trim(preg_replace('/\s+/', ' ', $txt));
            }
        } else {
            @unlink($tmpTxt);
        }
    }

    // 2) Fallback: naive extraction from PDF text operators (works on many text PDFs)
    $raw = @file_get_contents($path);
    if (!is_string($raw) || $raw === '') return '';

    $chunks = [];
    if (preg_match_all('/\((.*?)\)\s*Tj/s', $raw, $m1)) {
        $chunks = array_merge($chunks, $m1[1]);
    }
    if (preg_match_all('/\[(.*?)\]\s*TJ/s', $raw, $m2)) {
        foreach ($m2[1] as $arr) {
            if (preg_match_all('/\((.*?)\)/s', $arr, $m3)) {
                $chunks = array_merge($chunks, $m3[1]);
            }
        }
    }

    if (empty($chunks)) return '';

    $text = implode(' ', $chunks);
    $text = preg_replace('/\\\\[0-7]{1,3}/', ' ', $text); // octal escapes
    $text = str_replace(['\\n', '\\r', '\\t', '\\(', '\\)', '\\\\'], [' ', ' ', ' ', '(', ')', '\\'], $text);
    $text = @iconv('UTF-8', 'UTF-8//IGNORE', $text) ?: $text;
    $text = preg_replace('/\s+/', ' ', $text);
    return trim($text);
}

function safe_unlink($path) {
    if (!is_string($path) || $path === '') return;
    if (file_exists($path)) {
        @unlink($path);
    }
}

function find_libreoffice_binary() {
    $candidates = [
        'C:\\Program Files\\LibreOffice\\program\\soffice.exe',
        'C:\\Program Files (x86)\\LibreOffice\\program\\soffice.exe',
    ];

    foreach ($candidates as $p) {
        if (file_exists($p)) {
            return $p;
        }
    }

    // Windows PATH lookup fallback
    $where = @shell_exec('where soffice 2>NUL');
    if (is_string($where) && trim($where) !== '') {
        $lines = preg_split('/\r\n|\r|\n/', trim($where));
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line !== '') {
                return $line;
            }
        }
    }

    // Non-Windows/portable fallback
    return 'soffice';
}

function try_convert_office_to_pdf($libreOffice, $tmpPath, $ext) {
    $tmpDir     = sys_get_temp_dir();
    $uniqueName = 'quiz_' . uniqid('', true) . '.' . $ext;
    $srcCopy    = $tmpDir . DIRECTORY_SEPARATOR . $uniqueName;

    if (!@copy($tmpPath, $srcCopy)) {
        return [false, null, 'Could not prepare temporary file for conversion.'];
    }

    $pdfPath = $tmpDir . DIRECTORY_SEPARATOR . pathinfo($uniqueName, PATHINFO_FILENAME) . '.pdf';
    $loPath  = str_contains($libreOffice, ' ') ? '"' . $libreOffice . '"' : $libreOffice;
    $tmpDirQ = escapeshellarg($tmpDir);
    $srcQ    = escapeshellarg($srcCopy);

    $attemptLogs = [];
    $ok = false;

    for ($attempt = 1; $attempt <= 3; $attempt++) {
        $output = [];
        $returnCode = 1;
        $cmd = "$loPath --headless --nologo --nodefault --nofirststartwizard --convert-to pdf --outdir $tmpDirQ $srcQ 2>&1";
        exec($cmd, $output, $returnCode);

        // Give the filesystem a brief moment on slow/first-run installs.
        clearstatcache(true, $pdfPath);
        if (!file_exists($pdfPath)) {
            usleep(350000);
            clearstatcache(true, $pdfPath);
        }

        if (file_exists($pdfPath) && filesize($pdfPath) > 0) {
            $ok = true;
            break;
        }

        $attemptLogs[] = "Attempt $attempt (code $returnCode): " . trim(implode("\n", $output));
        usleep(300000);
    }

    safe_unlink($srcCopy);

    if (!$ok) {
        safe_unlink($pdfPath);
        return [false, null, trim(implode("\n\n", $attemptLogs))];
    }

    $pdfData = @file_get_contents($pdfPath);
    safe_unlink($pdfPath);
    if ($pdfData === false || $pdfData === '') {
        return [false, null, 'Converted PDF was empty or unreadable.'];
    }

    return [true, $pdfData, null];
}

function clean_json_text($text) {
    $text = trim($text);
    $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);

    if (preg_match('/```json\s*(.*?)```/is', $text, $m)) {
        return trim($m[1]);
    }

    if (preg_match('/```\s*(.*?)```/is', $text, $m)) {
        return trim($m[1]);
    }

    $firstBrace = strpos($text, '{');
    $firstBracket = strpos($text, '[');
    $lastBrace = strrpos($text, '}');
    $lastBracket = strrpos($text, ']');

    if ($firstBrace !== false && $lastBrace !== false && $lastBrace > $firstBrace) {
        $jsonObject = substr($text, $firstBrace, $lastBrace - $firstBrace + 1);
        if ($firstBracket === false || $firstBrace < $firstBracket) {
            return $jsonObject;
        }
    }

    if ($firstBracket !== false && $lastBracket !== false && $lastBracket > $firstBracket) {
        return substr($text, $firstBracket, $lastBracket - $firstBracket + 1);
    }

    return $text;
}

function decode_questions_json($text) {
    $jsonText = clean_json_text($text);
    if ($jsonText === '') {
        return [null, ''];
    }

    $candidates = [];
    $candidates[] = $jsonText;
    $candidates[] = preg_replace('/,\s*([\]}])/m', '$1', $jsonText);
    $candidates[] = trim($jsonText, " \t\n\r\0\x0B\xEF\xBB\xBF");

    foreach ($candidates as $candidate) {
        if (!is_string($candidate) || trim($candidate) === '') {
            continue;
        }

        $decoded = json_decode($candidate, true, 512, JSON_INVALID_UTF8_SUBSTITUTE);
        if (json_last_error() !== JSON_ERROR_NONE) {
            continue;
        }

        if (is_array($decoded) && array_is_list($decoded)) {
            return [$decoded, $candidate];
        }

        if (is_array($decoded)) {
            foreach (['questions', 'items', 'data', 'quiz_questions'] as $key) {
                if (isset($decoded[$key]) && is_array($decoded[$key])) {
                    return [$decoded[$key], $candidate];
                }
            }
        }
    }

    return [null, $jsonText];
}

function normalize_questions($questions, $mcqCount, $totalPoints, $pointsPerQuestion = 0) {
    $normalized = [];
    $letters = ['A', 'B', 'C', 'D'];

    foreach ($questions as $q) {
        if (!is_array($q)) {
            continue;
        }

        $questionText = trim((string)($q['question'] ?? ''));
        if ($questionText === '') {
            continue;
        }

        $choices = [];

        if (isset($q['choices']) && is_array($q['choices'])) {
            foreach ($q['choices'] as $choice) {
                if (!is_array($choice)) {
                    continue;
                }

                $choiceText = trim((string)($choice['text'] ?? $choice['choice_text'] ?? ''));
                if ($choiceText === '') {
                    continue;
                }

                $choices[] = [
                    'text' => $choiceText,
                    'is_correct' => !empty($choice['is_correct'])
                ];
            }
        }

        if (count($choices) > 0) {
            $hasCorrect = false;
            foreach ($choices as $c) {
                if (!empty($c['is_correct'])) {
                    $hasCorrect = true;
                    break;
                }
            }

            if (!$hasCorrect && isset($choices[0])) {
                $choices[0]['is_correct'] = true;
            }

            $choices = array_slice($choices, 0, 6);
        }

        $normalized[] = [
    'question'        => $questionText,
    'answer'          => trim((string)($q['answer'] ?? $q['answer_key'] ?? '')),
    'points'          => max(0.5, (float)($q['points'] ?? 1)),
    'choices'         => $choices,
    'cognitive_level' => trim((string)($q['cognitive_level'] ?? 'remembering'))
        ];
    }

    $needed = $mcqCount;
    $normalized = array_slice($normalized, 0, $needed);

    if ($pointsPerQuestion > 0 && count($normalized) > 0) {
        foreach ($normalized as &$q) {
            $q['points'] = max(0.5, (float)$pointsPerQuestion);
        }
        unset($q);
    } elseif ($totalPoints > 0 && count($normalized) > 0) {
        $count = count($normalized);
        $base = floor(($totalPoints / $count) * 100) / 100;
        $used = 0;

        foreach ($normalized as $i => &$q) {
            if ($i === $count - 1) {
                $q['points'] = round($totalPoints - $used, 2);
            } else {
                $q['points'] = $base;
                $used += $base;
            }

            if ($q['points'] <= 0) {
                $q['points'] = 1;
            }
        }
        unset($q);
    }

    return $normalized;
}

$extractedText = '';

if ($ext === 'txt') {
    $extractedText = extract_text_from_txt($tmpPath);
} elseif ($ext === 'pptx') {
    $extractedText = extract_text_from_pptx($tmpPath);
} elseif ($ext === 'docx') {
    $extractedText = extract_text_from_docx($tmpPath);
}

function is_gemini_over_quota_error($message) {
    $m = strtolower((string)$message);
    if ($m === '') return false;
    return (
        str_contains($m, 'quota exceeded') ||
        str_contains($m, 'rate limit') ||
        str_contains($m, 'resource_exhausted') ||
        str_contains($m, 'too many requests') ||
        str_contains($m, '429') ||
        str_contains($m, 'high demand') ||
        str_contains($m, 'spikes in demand') ||
        str_contains($m, 'temporarily unavailable') ||
        str_contains($m, 'model is overloaded') ||
        str_contains($m, 'service unavailable')
    );
}

function run_openai_generation($openaiApiKey, $aiModel, $prompt, $extractedText) {
    if (trim($openaiApiKey) === '') {
        return [false, '', 'OpenAI API key is missing. Set OPENAI_API_KEY in API/config_ai.php'];
    }

    $openAiMessages = [
        ['role' => 'system', 'content' => 'Return valid JSON only.'],
        ['role' => 'user', 'content' => $prompt . "\n\n" . (trim($extractedText) !== '' ? ("Learning material text:\n\n" . $extractedText) : '')]
    ];
    $openAiPayload = [
        'model' => $aiModel ?: 'gpt-4.1-mini',
        'messages' => $openAiMessages,
        'temperature' => 0.25
    ];

    $lastError = '';
    for ($attempt = 1; $attempt <= 3; $attempt++) {
        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $openaiApiKey
            ],
            CURLOPT_POSTFIELDS => json_encode($openAiPayload),
            CURLOPT_TIMEOUT => 90
        ]);
        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            $lastError = 'OpenAI request failed: ' . $curlError;
            usleep(400000 * $attempt);
            continue;
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            $text = (string)($decoded['choices'][0]['message']['content'] ?? '');
            if (trim($text) !== '') {
                return [true, $text, ''];
            }
            $lastError = 'OpenAI returned an empty response.';
            usleep(300000 * $attempt);
            continue;
        }

        $lastError = 'OpenAI API error: ' . (($decoded['error']['message'] ?? $response));
        usleep(500000 * $attempt);
    }

    return [false, '', ($lastError ?: 'OpenAI API is busy right now. Please try again.')];
}

function extract_gemini_text($decoded) {
    if (!is_array($decoded)) {
        return '';
    }

    $parts = $decoded['candidates'][0]['content']['parts'] ?? null;
    if (!is_array($parts)) {
        return '';
    }

    $texts = [];
    foreach ($parts as $part) {
        $partText = trim((string)($part['text'] ?? ''));
        if ($partText !== '') {
            $texts[] = $partText;
        }
    }

    return trim(implode("\n", $texts));
}

function run_gemini_generation($geminiApiKey, $model, $payload) {
    $lastError = '';
    $decoded = null;

    for ($attempt = 1; $attempt <= 3; $attempt++) {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . urlencode($geminiApiKey);
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => 90
        ]);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false) {
            $lastError = 'Gemini request failed: ' . $curlError;
            usleep(400000 * $attempt);
            continue;
        }

        $decoded = json_decode($response, true);
        if ($httpCode >= 200 && $httpCode < 300) {
            return [true, $decoded, ''];
        }

        $apiMessage = $decoded['error']['message'] ?? $response;
        $lastError = 'Gemini API error: ' . $apiMessage;
        usleep(500000 * $attempt);
    }

    return [false, $decoded, $lastError ?: 'Gemini API is busy right now. Please try again in a moment.'];
}

// If OpenAI is selected but we don't have extracted text, auto-fallback to
// Gemini instead of showing a blocking error.
if ($aiProvider === 'openai' && trim($extractedText) === '') {
    $aiProvider = 'gemini';
    if ($aiModel === '' || str_starts_with($aiModel, 'gpt-')) {
        $aiModel = 'gemini-2.5-flash';
    }
}

$prompt = "
You are helping a faculty member create a {$assessmentTitle} for an LMS.

Generate {$assessmentLabel} questions from the uploaded learning material.

Requirements:
- Create exactly {$mcqCount} multiple-choice questions.
- Total questions: {$totalQuestions}.
- Use clear classroom-friendly wording suitable for a formal {$assessmentTitle}.
- Avoid questions that are too vague.
- Provide exactly 4 choices per question.
- Only one choice must be correct.
- Return JSON only. No markdown. No explanation.
- When this is an exam, treat the questions as TEST I. MULTIPLE CHOICE from the examination template.

Difficulty Distribution (STRICTLY FOLLOW THESE EXACT COUNTS):
- {$easyCount} questions must have difficulty: \"easy\" (basic recall, definitions, direct facts).
- {$averageCount} questions must have difficulty: \"average\" (understanding or simple application).
- {$hardCount} questions must have difficulty: \"difficult\" (analysis, evaluation, or complex application).
- Add a \"difficulty\" field to every question: \"easy\", \"average\", or \"difficult\".

Cognitive Level Distribution:
- Distribute questions across ALL six Bloom's Taxonomy levels: {$cognitiveList}.
- Aim for roughly equal coverage across all six levels.
- Include a \"cognitive_level\" field in each question set to one of: \"remembering\", \"understanding\", \"applying\", \"analyzing\", \"evaluating\", \"creating\".
- Do NOT cluster questions in just one or two cognitive levels.

Required JSON format:
[
  {
    \"question\": \"Question text here\",
    \"cognitive_level\": \"remembering\",
    \"difficulty\": \"easy\",
    \"points\": 1,
    \"choices\": [
      {\"text\": \"Choice A\", \"is_correct\": false},
      {\"text\": \"Choice B\", \"is_correct\": true},
      {\"text\": \"Choice C\", \"is_correct\": false},
      {\"text\": \"Choice D\", \"is_correct\": false}
    ]
  }
]
";

$parts = [
    [
        'text' => $prompt
    ]
];

if ($ext === 'pptx' || $ext === 'docx') {
    $libreOffice = find_libreoffice_binary();
    [$ok, $pdfData, $convertLog] = try_convert_office_to_pdf($libreOffice, $tmpPath, $ext);

    if ($ok && $pdfData) {
        $parts[] = [
            'inline_data' => [
                'mime_type' => 'application/pdf',
                'data'      => base64_encode($pdfData)
            ]
        ];
    } else {
        if (trim($extractedText) === '') {
            fail_json('Could not convert file to PDF for AI reading. Please retry in a few seconds, or upload a PDF. ' . ($convertLog ? "Details: $convertLog" : ''));
        }
        if (mb_strlen($extractedText) > 50000) {
            $extractedText = mb_substr($extractedText, 0, 50000);
        }
        $parts[] = ['text' => "Learning material text:\n\n" . $extractedText];
    }

} elseif ($ext === 'pdf') {
    // Extract text too so we can fallback to OpenAI if Gemini is rate-limited.
    $extractedText = extract_text_from_pdf_basic($tmpPath);
    $parts[] = [
        'inline_data' => [
            'mime_type' => 'application/pdf',
            'data'      => base64_encode(file_get_contents($tmpPath))
        ]
    ];
} else {
    if (trim($extractedText) === '') {
        fail_json('Could not extract text from this file. Try another file or convert it to PDF/TXT.');
    }
    if (mb_strlen($extractedText) > 50000) {
        $extractedText = mb_substr($extractedText, 0, 50000);
    }
    $parts[] = ['text' => "Learning material text:\n\n" . $extractedText];
}

$payload = [
    'contents' => [
        [
            'role' => 'user',
            'parts' => $parts
        ]
    ],
    'generationConfig' => [
        'temperature' => 0.25,
        'responseMimeType' => 'application/json',
        'responseSchema' => [
            'type' => 'ARRAY',
            'items' => [
                'type' => 'OBJECT',
                'required' => ['question', 'cognitive_level', 'difficulty', 'points', 'choices'],
                'properties' => [
                    'question' => ['type' => 'STRING'],
                    'cognitive_level' => ['type' => 'STRING'],
                    'difficulty' => ['type' => 'STRING'],
                    'points' => ['type' => 'NUMBER'],
                    'choices' => [
                        'type' => 'ARRAY',
                        'items' => [
                            'type' => 'OBJECT',
                            'required' => ['text', 'is_correct'],
                            'properties' => [
                                'text' => ['type' => 'STRING'],
                                'is_correct' => ['type' => 'BOOLEAN']
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]
];

$text = '';

if ($aiProvider === 'openai') {
    [$ok, $openaiText, $openaiErr] = run_openai_generation($openaiApiKey, $aiModel, $prompt, $extractedText);
    if (!$ok) {
        fail_json($openaiErr, 500);
    }
    $text = $openaiText;
} else {
    if (trim($geminiApiKey) === '') {
        fail_json('Gemini API key is missing. Check API/config_ai.php', 500);
    }
    $models = $aiModel ? [$aiModel] : ['gemini-2.5-flash-lite', 'gemini-2.0-flash-lite-001', 'gemini-2.5-flash'];
    $decoded = null;
    $lastError = '';
    $succeeded = false;

    foreach ($models as $model) {
        [$ok, $modelDecoded, $modelError] = run_gemini_generation($geminiApiKey, $model, $payload);
        if ($ok) {
            $decoded = $modelDecoded;
            $succeeded = true;
            break;
        }
        $lastError = $modelError;
    }

    if (!$succeeded) {
        // Auto-fallback: when Gemini is rate-limited/quota-limited, use OpenAI if available
        // and we have extracted text (OpenAI path in this endpoint is text-based).
        $canFallbackToOpenAI = (trim($openaiApiKey) !== '' && trim($extractedText) !== '');
        if ($canFallbackToOpenAI && is_gemini_over_quota_error($lastError)) {
            $fallbackModel = 'gpt-4.1-mini';
            [$ok, $openaiText, $openaiErr] = run_openai_generation($openaiApiKey, $fallbackModel, $prompt, $extractedText);
            if ($ok) {
                $text = $openaiText;
                $succeeded = true;
            } else {
                fail_json('Gemini quota reached, and OpenAI fallback failed: ' . $openaiErr, 500);
            }
        } else {
            fail_json($lastError ?: 'Gemini API is busy right now. Please try again in a moment.', 500);
        }
    }
    if (trim($text) === '') {
        $text = extract_gemini_text($decoded);
    }
}

if (trim($text) === '') {
    fail_json('Gemini returned an empty response.', 500);
}

$questions = null;
[$questions, $jsonText] = decode_questions_json($text);

if (!is_array($questions)) {
    if ($aiProvider !== 'openai' && trim($geminiApiKey) !== '' && !empty($decoded)) {
        $retryPrompt = $prompt . "\n\nIMPORTANT: Return ONLY a valid JSON array. Do not wrap it in an object. Do not include markdown, comments, or trailing commas.";
        $retryPayload = $payload;
        $retryPayload['contents'][0]['parts'][0]['text'] = $retryPrompt;
        [$retryOk, $retryDecoded, $retryError] = run_gemini_generation($geminiApiKey, $aiModel ?: 'gemini-2.5-flash-lite', $retryPayload);
        if ($retryOk) {
            $retryText = extract_gemini_text($retryDecoded);
            if (trim($retryText) !== '') {
                [$questions, $jsonText] = decode_questions_json($retryText);
            }
        } else {
            $lastError = $retryError;
        }
    }

    $canFallbackToOpenAI = (trim($openaiApiKey) !== '' && trim($extractedText) !== '');
    if ($aiProvider !== 'openai' && $canFallbackToOpenAI) {
        [$ok, $openaiText, $openaiErr] = run_openai_generation($openaiApiKey, 'gpt-4.1-mini', $prompt, $extractedText);
        if ($ok) {
            [$questions, $jsonText] = decode_questions_json($openaiText);
        } else {
            fail_json('Gemini returned invalid JSON, and fallback generation failed: ' . $openaiErr, 500);
        }
    }
}

if (!is_array($questions)) {
    fail_json('Gemini returned invalid JSON. Please try again.', 500);
}

$questions = normalize_questions($questions, $mcqCount, $totalPoints, $pointsPerQuestion);

if (count($questions) === 0) {
    fail_json('No valid questions were generated. Try a clearer document.', 500);
}

send_json([
    'status' => 'success',
    'message' => 'Quiz generated successfully.',
    'questions' => $questions
]);
