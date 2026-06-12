<?php
/**
 * API/notify_suspicious.php
 * Sends a warning email to the account owner after 5 failed login attempts.
 * Standalone — no PHPMailer, no includes from send_otp.php.
 */

header('Content-Type: application/json');
ob_start();
require_once __DIR__ . '/../../core/db_connect.php';

// ── same Gmail config ─────────────────────────────────────────────
define('NS_SMTP_HOST', 'smtp.gmail.com');
define('NS_SMTP_PORT', 587);
define('NS_SMTP_USER', 'lucerorenwel524@gmail.com');
define('NS_SMTP_PASS', 'htjo hjjf lxxi fvbv');
define('NS_SMTP_FROM', 'lucerorenwel524@gmail.com');
define('NS_SMTP_NAME', 'TereLearn');
// ─────────────────────────────────────────────────────────────────

$body       = json_decode(file_get_contents('php://input'), true);
$username   = $body['username']    ?? 'unknown';
$ownerEmail = $body['owner_email'] ?? '';
$ownerPhone = $body['owner_phone'] ?? '';

if (empty($ownerEmail)) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'No owner email.']);
    exit;
}

$ip   = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$time = date('F j, Y \a\t g:i A');

$html = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8">
<style>
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f7fa;margin:0;padding:30px;}
  .wrap{max-width:460px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);}
  .top{background:linear-gradient(135deg,#f59e0b,#b45309);padding:28px;text-align:center;color:#fff;}
  .top h1{margin:0;font-size:1.4rem;font-weight:700;}
  .body{padding:28px;color:#4a5568;font-size:.93rem;line-height:1.65;}
  .box{background:#fffbeb;border-left:4px solid #f59e0b;border-radius:6px;padding:14px;margin:18px 0;}
  .foot{background:#f9fafb;padding:14px 28px;text-align:center;font-size:.75rem;color:#a0aec0;border-top:1px solid #e2e8f0;}
</style>
</head>
<body>
<div class="wrap">
  <div class="top"><h1>⚠️ Suspicious Login Alert</h1></div>
  <div class="body">
    <p>Hi,</p>
    <p>We detected <strong>5 consecutive failed login attempts</strong> on your TereLearn account.</p>
    <div class="box">
      <strong>Account:</strong> {$username}<br>
      <strong>Time:</strong> {$time}<br>
      <strong>IP Address:</strong> {$ip}
    </div>
    <p>If this was you, use the <em>Reset it</em> link on the login page to recover your account.</p>
    <p>If this was <strong>not you</strong>, contact your administrator immediately.</p>
  </div>
  <div class="foot">TereLearn Security · Education that transcends</div>
</div>
</body>
</html>
HTML;

$sent = nsSend($ownerEmail, $username, $html);
ob_end_clean();
echo json_encode(['success' => ($sent === true), 'message' => $sent === true ? 'Notified' : $sent]);

/* ══════════════════════════════════════════
   SMTP send — pure PHP, no library
══════════════════════════════════════════ */
function nsSend(string $toEmail, string $toName, string $htmlBody) {
    $subject = '⚠️ Suspicious Login Alert — TereLearn';
    try {
        $sock = fsockopen('tcp://' . NS_SMTP_HOST, NS_SMTP_PORT, $errno, $errstr, 15);
        if (!$sock) return "Cannot connect: $errstr ($errno)";
        stream_set_timeout($sock, 10);

        $read = function() use ($sock): string {
            $out = '';
            while ($line = fgets($sock, 515)) {
                $out .= $line;
                if ($line[3] === ' ') break;
            }
            return $out;
        };
        $cmd = function(string $c) use ($sock, $read): string {
            fwrite($sock, $c . "\r\n");
            return $read();
        };

        $r = $read();
        if (substr($r, 0, 3) !== '220') return "Bad greeting: $r";
        $cmd("EHLO localhost");
        $r = $cmd("STARTTLS");
        if (substr($r, 0, 3) !== '220') return "STARTTLS failed: $r";
        stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $cmd("EHLO localhost");
        $cmd("AUTH LOGIN");
        $cmd(base64_encode(NS_SMTP_USER));
        $r = $cmd(base64_encode(NS_SMTP_PASS));
        if (substr($r, 0, 3) !== '235') return "Auth failed: $r";
        $cmd("MAIL FROM:<" . NS_SMTP_FROM . ">");
        $cmd("RCPT TO:<$toEmail>");
        $cmd("DATA");

        $msg  = "From: =?UTF-8?B?" . base64_encode(NS_SMTP_NAME) . "?= <" . NS_SMTP_FROM . ">\r\n";
        $msg .= "To: =?UTF-8?B?" . base64_encode($toName) . "?= <$toEmail>\r\n";
        $msg .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $msg .= chunk_split(base64_encode($htmlBody));
        $msg .= "\r\n.";

        $r = $cmd($msg);
        if (substr($r, 0, 3) !== '250') return "DATA failed: $r";
        $cmd("QUIT");
        fclose($sock);
        return true;
    } catch (Exception $e) {
        return $e->getMessage();
    }
}
