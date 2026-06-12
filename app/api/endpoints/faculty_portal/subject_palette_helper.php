<?php
/**
 * subject_palette_helper.php
 *
 * Provides TWO public functions used across the app:
 *
 *   paletteForSubject($subject_id, $fallback_name, $conn)
 *     → Called by save_faculty_class.php at INSERT time.
 *       Looks up the subject name from tblsubject, then delegates
 *       to getPaletteForSubject(). Falls back to $fallback_name if
 *       no subject_id is given or the subject is not found.
 *       Uses the global $conn if no $conn argument is passed.
 *
 *   getPaletteForSubject($conn, $subjectName)
 *     → Core function. Takes a raw subject name string and returns
 *       the persistently assigned palette class (e.g. "b-forest").
 *       Creates tblsubjectpalette on first use.
 *
 *   getAllPaletteMappings($conn)
 *     → Returns the full subject_key => palette array.
 *       Used by get_subject_palette.php (frontend map endpoint).
 *
 * Palette table: tblsubjectpalette (auto-created)
 *   id          INT AUTO_INCREMENT PK
 *   subject_key VARCHAR(255) UNIQUE   — normalised name (lowercase, trimmed)
 *   palette     VARCHAR(32)           — e.g. "b-forest"
 *   created_at  TIMESTAMP
 */

define('PALETTE_LIST', [
    'b-forest',   // green
    'b-ocean',    // blue
    'b-sunset',   // orange-red
    'b-plum',     // purple
    'b-teal',     // teal
    'b-rose',     // dark red/pink
    'b-slate',    // grey-blue
    'b-indigo',   // indigo
]);

/* ──────────────────────────────────────────────
   paletteForSubject()
   Called by save_faculty_class.php:
     paletteForSubject($subject_id)
     paletteForSubject('', $class_name)
   ────────────────────────────────────────────── */
function paletteForSubject(string $subject_id, string $fallback_name = '', mysqli $conn = null): string {
    // Use global $conn if none passed
    if ($conn === null) {
        global $conn;
    }

    $resolvedName = '';

    if ($subject_id !== '' && $conn) {
        // Resolve subject name from tblsubject
        $stmt = $conn->prepare(
            "SELECT subject_name, subject_code FROM tblsubject WHERE id = ? AND is_deleted = 0 LIMIT 1"
        );
        if ($stmt) {
            $stmt->bind_param('s', $subject_id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            // Prefer subject_name; fall back to subject_code
            $resolvedName = trim($row['subject_name'] ?? $row['subject_code'] ?? '');
        }
    }

    // If we couldn't resolve from DB, use the fallback name
    if ($resolvedName === '') {
        $resolvedName = $fallback_name;
    }

    // If still nothing, return a safe default
    if (trim($resolvedName) === '') {
        return PALETTE_LIST[0];
    }

    if ($conn) {
        return getPaletteForSubject($conn, $resolvedName);
    }

    // Last resort: deterministic hash (no DB available)
    return _hashPalette($resolvedName);
}

/* ──────────────────────────────────────────────
   getPaletteForSubject()
   Core persistent assignment function.
   ────────────────────────────────────────────── */
function getPaletteForSubject(mysqli $conn, string $subjectName): string {
    _ensurePaletteTable($conn);

    $key = _normalisePaletteKey($subjectName);
    if ($key === '') return PALETTE_LIST[0];

    $safeKey = mysqli_real_escape_string($conn, $key);

    // Return existing assignment
    $res = $conn->query(
        "SELECT palette FROM tblsubjectpalette WHERE subject_key = '$safeKey' LIMIT 1"
    );
    if ($res && $res->num_rows > 0) {
        return $res->fetch_assoc()['palette'];
    }

    // Assign a new palette
    $palette     = _nextAvailablePalette($conn);
    $safePalette = mysqli_real_escape_string($conn, $palette);

    $conn->query(
        "INSERT IGNORE INTO tblsubjectpalette (subject_key, palette)
         VALUES ('$safeKey', '$safePalette')"
    );

    // Re-read in case of a race-condition insert
    $res2 = $conn->query(
        "SELECT palette FROM tblsubjectpalette WHERE subject_key = '$safeKey' LIMIT 1"
    );
    if ($res2 && $res2->num_rows > 0) {
        return $res2->fetch_assoc()['palette'];
    }

    return $palette;
}

/* ──────────────────────────────────────────────
   getAllPaletteMappings()
   Returns full map for the frontend API.
   ────────────────────────────────────────────── */
function getAllPaletteMappings(mysqli $conn): array {
    _ensurePaletteTable($conn);
    $map = [];
    $res = $conn->query(
        "SELECT subject_key, palette FROM tblsubjectpalette ORDER BY id ASC"
    );
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $map[$row['subject_key']] = $row['palette'];
        }
    }
    return $map;
}

/* ======================================================
   PRIVATE HELPERS  (underscore-prefixed)
====================================================== */

function _ensurePaletteTable(mysqli $conn): void {
    $conn->query("
        CREATE TABLE IF NOT EXISTS tblsubjectpalette (
            id          INT          NOT NULL AUTO_INCREMENT,
            subject_key VARCHAR(255) NOT NULL,
            palette     VARCHAR(32)  NOT NULL,
            created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_subject_key (subject_key)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}

/**
 * Normalise a subject name into a stable lowercase key.
 * Strips leading year-section prefixes like "3-1 " so
 * "3-1 APPDEVs" and "3-2 APPDEVs" share the same key.
 */
function _normalisePaletteKey(string $name): string {
    $name = trim($name);
    // Strip "3-1 " / "1-1-7 " style prefixes
    $name = preg_replace('/^\d+[-\x{2013}]\d+(?:[-\x{2013}]\d+)?\s+/u', '', $name);
    return strtolower(trim($name));
}

/**
 * Pick the next palette not yet assigned, or the least-used one
 * if all 8 have been assigned at least once.
 */
function _nextAvailablePalette(mysqli $conn): string {
    $palettes = PALETTE_LIST;
    $used = [];

    $res = $conn->query(
        "SELECT palette FROM tblsubjectpalette ORDER BY id ASC"
    );
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $used[] = $row['palette'];
        }
    }

    // First unused palette
    foreach ($palettes as $p) {
        if (!in_array($p, $used, true)) return $p;
    }

    // All 8 used — pick the least frequent
    $counts = array_count_values($used);
    $min  = PHP_INT_MAX;
    $best = $palettes[0];
    foreach ($palettes as $p) {
        $c = $counts[$p] ?? 0;
        if ($c < $min) { $min = $c; $best = $p; }
    }
    return $best;
}

/**
 * Pure deterministic hash fallback (no DB).
 * Only used if $conn is unavailable.
 */
function _hashPalette(string $name): string {
    $palettes = PALETTE_LIST;
    $key = _normalisePaletteKey($name);
    $h = 0;
    for ($i = 0; $i < strlen($key); $i++) {
        $h = (($h << 5) - $h) + ord($key[$i]);
        $h &= 0x7fffffff;
    }
    return $palettes[$h % count($palettes)];
}