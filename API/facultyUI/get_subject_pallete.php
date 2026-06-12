<?php
/**
 * subject_palette_helper.php
 * Shared palette logic for get_subject_palette.php
 */

const PALETTE_LIST = ['b-forest','b-ocean','b-sunset','b-plum','b-teal','b-rose','b-slate','b-indigo'];

function normalisePaletteKey(string $str): string {
    $str = preg_replace('/^\d+[-–]\d+(?:[-–]\d+)?\s+/', '', $str);
    return strtolower(trim($str));
}

function hashPaletteFor(string $key): string {
    $h = 0;
    for ($i = 0; $i < strlen($key); $i++) {
        $h = (($h << 5) - $h) + ord($key[$i]);
        $h &= 0x7FFFFFFF;
    }
    return PALETTE_LIST[$h % count(PALETTE_LIST)];
}

function getPaletteForSubject(mysqli $conn, string $subjectName): string {
    $key = normalisePaletteKey($subjectName);
    if ($key === '') return 'b-forest';

    // Check if a palette table exists; if not, just hash it
    $tableCheck = $conn->query("SHOW TABLES LIKE 'tblsubjectpalette'");
    if (!$tableCheck || $tableCheck->num_rows === 0) {
        return hashPaletteFor($key);
    }

    // Try to find existing mapping
    $stmt = $conn->prepare("SELECT palette FROM tblsubjectpalette WHERE subject_key = ? LIMIT 1");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) return $row['palette'];

    // Assign a new palette
    $palette = hashPaletteFor($key);
    $ins = $conn->prepare("INSERT IGNORE INTO tblsubjectpalette (subject_key, palette) VALUES (?, ?)");
    $ins->bind_param('ss', $key, $palette);
    $ins->execute();
    $ins->close();

    return $palette;
}

function getAllPaletteMappings(mysqli $conn): array {
    $tableCheck = $conn->query("SHOW TABLES LIKE 'tblsubjectpalette'");
    if (!$tableCheck || $tableCheck->num_rows === 0) return [];

    $result = $conn->query("SELECT subject_key, palette FROM tblsubjectpalette");
    if (!$result) return [];

    $map = [];
    while ($row = $result->fetch_assoc()) {
        $map[$row['subject_key']] = $row['palette'];
    }
    return $map;
}