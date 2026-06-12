<?php
/**
 * API/Class/fetch_dropdown_data_class.php
 *
 * Returns courses, subjects, and faculty for the Dean class modal.
 *
 * Subjects are filtered by joining tblsubjectpreset on the active
 * school_year + semester. Accepts optional GET ?year_level=N to narrow
 * subjects to a specific year level (used when Dean picks Year Level
 * before choosing a subject).
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../core/db_connect.php';

$response = ['status' => 'error', 'message' => 'Unknown error', 'data' => []];

try {
    /* ── 0. Active semester ── */
    $semRes = $conn->query(
        "SELECT school_year, semester
         FROM   tblsemestersetting
         WHERE  is_active = 1 AND is_deleted = 0
         ORDER  BY id DESC LIMIT 1"
    );
    if (!$semRes || $semRes->num_rows === 0) {
        throw new Exception('No active semester set.');
    }
    $semRow           = $semRes->fetch_assoc();
    $activeSchoolYear = $semRow['school_year'];
    $activeSemester   = $semRow['semester'];

    /* ── 1. Optional year_level filter ── */
    $filterYear = isset($_GET['year_level']) ? (int)$_GET['year_level'] : 0;

    /* ── 2. Courses ── */
    $rCourses = $conn->query(
        "SELECT id, course_code, course_name
         FROM   tblcourse
         WHERE  is_Deleted = 0
         ORDER  BY course_code"
    );
    if (!$rCourses) throw new Exception('Courses: ' . $conn->error);

    $courses = [];
    while ($row = $rCourses->fetch_assoc()) {
        $row['id'] = (string)$row['id'];
        $courses[] = $row;
    }

    /* ── 3. Subjects via preset join ── */
    $yearCond = '';
    $params   = [$activeSchoolYear, $activeSemester];
    $types    = 'ss';

    if ($filterYear > 0) {
        $yearCond = "AND (p.year_level = ? OR p.year_level IS NULL)";
        $params[] = $filterYear;
        $types   .= 'i';
    }

    $sql = "SELECT DISTINCT
                   s.id,
                   s.subject_code,
                   s.subject_name,
                   target.program_id AS course_id,
                   p.year_level,
                   p.semester
            FROM   tblsubject s
            INNER  JOIN tblsubjectpreset p
                   ON  p.subject_id  = s.id
                   AND p.school_year = ?
                   AND p.semester    = ?
                   $yearCond
            INNER JOIN (
                SELECT sp0.id AS preset_id, COALESCE(sp0.owner_course_id, sp0.course_id) AS program_id
                FROM tblsubjectpreset sp0
                UNION ALL
                SELECT spp.preset_id, spp.program_id
                FROM tblsubjectpreset_programs spp
                UNION ALL
                SELECT sp1.id AS preset_id, tc.id AS program_id
                FROM tblsubjectpreset sp1
                JOIN tblcourse tc ON tc.is_Deleted = 0
                WHERE sp1.share_scope = 'all_programs'
            ) target
                   ON target.preset_id = p.id
            WHERE  s.is_deleted = 0
              AND  s.is_active  = 1
            ORDER  BY p.year_level ASC, s.subject_code ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare subjects: ' . $conn->error);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $rSubjects = $stmt->get_result();

    $subjects = [];
    while ($row = $rSubjects->fetch_assoc()) {
        $row['id']         = (string)$row['id'];
        $row['course_id']  = (string)$row['course_id'];
        $row['year_level'] = $row['year_level'] !== null ? (int)$row['year_level'] : null;
        $subjects[]        = $row;
    }

    /* ── 4. Faculty ── */
    $rFaculty = $conn->query(
        "SELECT id, first_name, last_name, faculty_number
         FROM   tblfaculty
         WHERE  is_deleted = 0 AND is_active = 1
         ORDER  BY last_name, first_name"
    );
    if (!$rFaculty) throw new Exception('Faculty: ' . $conn->error);

    $faculty = [];
    while ($row = $rFaculty->fetch_assoc()) {
        $row['id']        = (string)$row['id'];
        $row['full_name'] = trim($row['first_name'] . ' ' . $row['last_name']);
        $faculty[]        = $row;
    }

    $response = [
        'status'  => 'success',
        'message' => 'Dropdown data fetched successfully.',
        'data'    => [
            'courses'            => $courses,
            'subjects'           => $subjects,
            'faculty'            => $faculty,
            'active_semester'    => $activeSemester,
            'active_school_year' => $activeSchoolYear,
        ],
    ];

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

$conn->close();
echo json_encode($response);
