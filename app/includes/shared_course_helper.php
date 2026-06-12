<?php

function sharedCourseNormalizeScope(string $scope): string {
    $scope = trim($scope);
    $valid = ['department_only', 'selected_programs', 'all_programs'];
    return in_array($scope, $valid, true) ? $scope : 'department_only';
}

function sharedCourseParseProgramIds($raw): array {
    if (is_array($raw)) {
        $list = $raw;
    } else {
        $text = trim((string)$raw);
        $list = $text === '' ? [] : preg_split('/\s*,\s*/', $text);
    }

    $ids = [];
    foreach ($list as $id) {
        $id = trim((string)$id);
        if ($id !== '') $ids[$id] = true;
    }
    return array_keys($ids);
}

function sharedCourseAllProgramIds(mysqli $conn): array {
    $ids = [];
    $res = $conn->query("SELECT id FROM tblcourse WHERE is_Deleted = 0");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $ids[] = (string)$row['id'];
        }
    }
    return $ids;
}

function sharedCourseTargetPrograms(mysqli $conn, string $ownerCourseId, string $scope, array $selectedIds): array {
    $targets = [$ownerCourseId];
    if ($scope === 'selected_programs') {
        $targets = array_merge($targets, $selectedIds);
    } elseif ($scope === 'all_programs') {
        $targets = sharedCourseAllProgramIds($conn);
    }

    $clean = [];
    foreach ($targets as $id) {
        $id = trim((string)$id);
        if ($id !== '') $clean[$id] = true;
    }
    return array_keys($clean);
}

function sharedCourseQuotedList(mysqli $conn, array $ids): string {
    if (!$ids) return "''";
    $safe = array_map(static fn($id) => "'" . mysqli_real_escape_string($conn, (string)$id) . "'", $ids);
    return implode(',', $safe);
}

function sharedCourseFindDuplicatePreset(
    mysqli $conn,
    string $subjectCode,
    string $schoolYear,
    string $semester,
    int $yearLevel,
    array $targetProgramIds,
    ?int $excludePresetId = null
): ?array {
    $safeCode = mysqli_real_escape_string($conn, trim($subjectCode));
    $safeSy   = mysqli_real_escape_string($conn, $schoolYear);
    $safeSem  = mysqli_real_escape_string($conn, $semester);
    $safeYl   = (int)$yearLevel;
    $targets  = sharedCourseQuotedList($conn, $targetProgramIds);
    $exclude  = $excludePresetId ? "AND sp.id <> " . (int)$excludePresetId : '';

    $sql = "
        SELECT sp.id, s.subject_code, s.subject_name
        FROM tblsubjectpreset sp
        INNER JOIN tblsubject s
                ON s.id = sp.subject_id
               AND s.is_deleted = 0
        LEFT JOIN tblsubjectpreset_programs spp
               ON spp.preset_id = sp.id
        WHERE UPPER(TRIM(s.subject_code)) = UPPER(TRIM('$safeCode'))
          AND sp.school_year = '$safeSy'
          AND sp.semester = '$safeSem'
          AND COALESCE(sp.year_level, 0) = $safeYl
          $exclude
          AND (
                sp.course_id IN ($targets)
                OR sp.owner_course_id IN ($targets)
                OR spp.program_id IN ($targets)
                OR sp.share_scope = 'all_programs'
              )
        LIMIT 1";

    $res = $conn->query($sql);
    if ($res && $res->num_rows) {
        return $res->fetch_assoc();
    }
    return null;
}

function sharedCourseSyncPrograms(
    mysqli $conn,
    int $presetId,
    string $ownerCourseId,
    string $scope,
    array $selectedIds,
    ?string $addedBy = null
): void {
    $scope = sharedCourseNormalizeScope($scope);
    $programIds = [$ownerCourseId];
    if ($scope === 'selected_programs') {
        $programIds = array_merge($programIds, $selectedIds);
    }

    $dedup = [];
    foreach ($programIds as $id) {
        $id = trim((string)$id);
        if ($id !== '') $dedup[$id] = true;
    }
    $programIds = array_keys($dedup);

    $del = $conn->prepare("DELETE FROM tblsubjectpreset_programs WHERE preset_id = ?");
    $del->bind_param('i', $presetId);
    $del->execute();
    $del->close();

    if (!$programIds) return;

    $ins = $conn->prepare("
        INSERT INTO tblsubjectpreset_programs (preset_id, program_id, added_by)
        VALUES (?, ?, ?)
    ");
    foreach ($programIds as $programId) {
        $ins->bind_param('iss', $presetId, $programId, $addedBy);
        $ins->execute();
    }
    $ins->close();
}
