<?php
/**
 * debug_upload.php
 * Place this at: C:\xampp\htdocs\firmmm\debug_upload.php
 * Then open: http://localhost/firmmm/debug_upload.php
 * Upload any file using the form below and check the results.
 * DELETE THIS FILE after debugging is done.
 */

$results = [];

// ── 1. PHP upload settings ────────────────────────────────────────────────────
$results['php_upload_max_filesize'] = ini_get('upload_max_filesize');
$results['php_post_max_size']       = ini_get('post_max_size');
$results['php_file_uploads']        = ini_get('file_uploads');
$results['php_tmp_dir']             = ini_get('upload_tmp_dir') ?: sys_get_temp_dir();

// ── 2. Path resolution ────────────────────────────────────────────────────────
$doc_root   = rtrim($_SERVER['DOCUMENT_ROOT'], '\\/');
$upload_dir = $doc_root . DIRECTORY_SEPARATOR . 'firmmm' . DIRECTORY_SEPARATOR . 'uploads_files' . DIRECTORY_SEPARATOR . 'debug_test' . DIRECTORY_SEPARATOR;

$results['DOCUMENT_ROOT']  = $_SERVER['DOCUMENT_ROOT'];
$results['computed_dir']   = $upload_dir;
$results['dir_exists']     = is_dir($upload_dir) ? 'YES' : 'NO';
$results['dir_writable']   = is_writable(dirname($upload_dir)) ? 'YES' : 'NO (parent not writable)';

// Try to create it
if (!is_dir($upload_dir)) {
    $made = mkdir($upload_dir, 0755, true);
    $results['mkdir_result'] = $made ? 'Created successfully' : 'FAILED to create — check permissions';
} else {
    $results['mkdir_result'] = 'Already exists';
}

// Re-check after mkdir
$results['dir_exists_after'] = is_dir($upload_dir) ? 'YES' : 'NO';
if (is_dir($upload_dir)) {
    $results['dir_writable_after'] = is_writable($upload_dir) ? 'YES' : 'NO — folder exists but not writable';
}

// ── 3. Handle test upload ─────────────────────────────────────────────────────
$upload_result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['testfile'])) {
    $f = $_FILES['testfile'];
    $upload_result = [
        'original_name' => $f['name'],
        'size'          => $f['size'] . ' bytes',
        'tmp_name'      => $f['tmp_name'],
        'error_code'    => $f['error'],
        'tmp_exists'    => file_exists($f['tmp_name']) ? 'YES' : 'NO',
        'tmp_readable'  => is_readable($f['tmp_name']) ? 'YES' : 'NO',
    ];

    if ($f['error'] === UPLOAD_ERR_OK) {
        $dest = $upload_dir . 'test_' . time() . '_' . basename($f['name']);
        $moved = move_uploaded_file($f['tmp_name'], $dest);
        $upload_result['move_result']  = $moved ? 'SUCCESS — file saved to disk' : 'FAILED — move_uploaded_file returned false';
        $upload_result['destination']  = $dest;
        $upload_result['file_on_disk'] = $moved && file_exists($dest) ? 'YES — ' . filesize($dest) . ' bytes' : 'NO';
    } else {
        $codes = [
            1 => 'UPLOAD_ERR_INI_SIZE — file exceeds upload_max_filesize',
            2 => 'UPLOAD_ERR_FORM_SIZE — file exceeds MAX_FILE_SIZE',
            3 => 'UPLOAD_ERR_PARTIAL — only partially uploaded',
            4 => 'UPLOAD_ERR_NO_FILE — no file uploaded',
            6 => 'UPLOAD_ERR_NO_TMP_DIR — missing temp folder',
            7 => 'UPLOAD_ERR_CANT_WRITE — failed to write to disk',
            8 => 'UPLOAD_ERR_EXTENSION — extension stopped upload',
        ];
        $upload_result['error_meaning'] = $codes[$f['error']] ?? 'Unknown error';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Upload Debug</title>
<style>
  body { font-family: monospace; background: #111; color: #ddd; padding: 2rem; }
  h2   { color: #4fc; margin-bottom: .5rem; }
  h3   { color: #fa0; margin: 1.5rem 0 .5rem; }
  table { border-collapse: collapse; width: 100%; max-width: 900px; margin-bottom: 1.5rem; }
  td, th { border: 1px solid #333; padding: .4rem .75rem; text-align: left; }
  th { background: #1a1a1a; color: #aaa; }
  .ok  { color: #4fc; font-weight: bold; }
  .bad { color: #f55; font-weight: bold; }
  .warn { color: #fa0; }
  form { margin-top: 1.5rem; background: #1a1a1a; border: 1px solid #333; padding: 1.25rem; border-radius: 8px; max-width: 480px; }
  input[type=file]   { display: block; margin: .75rem 0; color: #ddd; }
  input[type=submit] { background: #1a9e78; color: #fff; border: none; padding: .5rem 1.25rem; border-radius: 6px; cursor: pointer; font-size: 1rem; }
  input[type=submit]:hover { background: #0d7a5e; }
  .warn-box { background: #2a1500; border: 1px solid #a05000; color: #fa0; padding: .75rem 1rem; border-radius: 6px; margin-bottom: 1rem; }
</style>
</head>
<body>

<h2>🔍 Upload Debug Tool</h2>
<div class="warn-box">⚠️ DELETE this file after debugging: <strong>firmmm/debug_upload.php</strong></div>

<h3>PHP Environment</h3>
<table>
  <tr><th>Setting</th><th>Value</th></tr>
  <?php foreach ($results as $k => $v): ?>
  <tr>
    <td><?= htmlspecialchars($k) ?></td>
    <td class="<?= (strpos((string)$v, 'FAIL') !== false || strpos((string)$v, 'NO') === 0) ? 'bad' : ((strpos((string)$v, 'YES') === 0 || strpos((string)$v, 'success') !== false || strpos((string)$v, 'Created') !== false || strpos((string)$v, 'exists') !== false) ? 'ok' : '') ?>">
      <?= htmlspecialchars((string)$v) ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>

<?php if ($upload_result): ?>
<h3>Upload Test Result</h3>
<table>
  <tr><th>Key</th><th>Value</th></tr>
  <?php foreach ($upload_result as $k => $v): ?>
  <tr>
    <td><?= htmlspecialchars($k) ?></td>
    <td class="<?= (strpos((string)$v, 'FAIL') !== false || strpos((string)$v, 'NO') === 0 || strpos((string)$v, 'ERR') !== false) ? 'bad' : (strpos((string)$v, 'SUCCESS') !== false || strpos((string)$v, 'YES') === 0 ? 'ok' : '') ?>">
      <?= htmlspecialchars((string)$v) ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<h3>Test File Upload</h3>
<form method="POST" enctype="multipart/form-data">
  <label>Select any file (PDF, PPTX, image…)</label>
  <input type="file" name="testfile">
  <input type="submit" value="Upload Test File">
</form>

</body>
</html>