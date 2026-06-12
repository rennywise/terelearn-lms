<?php
/**
 * API/send_otp.php
 * Sends OTP via Gmail SMTP — pure PHP, no PHPMailer needed.
 *
 * Changes from original:
 *  - OTP_EXPIRY_MINUTES    : 10 → 2  (expires in 2 minutes)
 *  - RESEND_COOLDOWN_SECONDS: 60     (1 minute cooldown, unchanged)
 *  - is_initial flag bypasses cooldown on first send after login
 *  - SMTP errors are now returned in the response for easier debugging
 */

header('Content-Type: application/json');
ob_start();

// ── CONFIG ────────────────────────────────────────────────────────
define('SMTP_HOST',               'smtp.gmail.com');
define('SMTP_PORT',               587);
define('SMTP_USER',               'lucerorenwel524@gmail.com');
define('SMTP_PASS',               'htjo hjjf lxxi fvbv');  // Gmail App Password (16 chars, no spaces)
define('SMTP_FROM',               'lucerorenwel524@gmail.com');
define('SMTP_NAME',               'TereLearn');
define('OTP_EXPIRY_MINUTES',      2);   // ← 2 minutes
define('RESEND_COOLDOWN_SECONDS', 60);  // ← 1 minute cooldown
// ─────────────────────────────────────────────────────────────────

require_once __DIR__ . '/../../core/db_connect.php';

$body       = json_decode(file_get_contents('php://input'), true);
$user_id    = $body['user_id']    ?? null;
$channel    = $body['channel']    ?? 'email';
$lookup     = $body['lookup']     ?? null;
$is_initial = !empty($body['is_initial']); // true = first send after login, skip cooldown

try {
    // ── find user ─────────────────────────────────────────────────
    if ($channel === 'recovery' && $lookup) {
        $stmt = $conn->prepare("
            SELECT u.id, u.email, u.username,
                   COALESCE(f.phone,'') AS phone
            FROM tbluser u
            LEFT JOIN tblfaculty f ON f.username = u.username
            WHERE (u.username = ? OR u.email = ?)
              AND u.is_deleted = 0 AND u.is_active = 1
            LIMIT 1
        ");
        $stmt->bind_param('ss', $lookup, $lookup);
    } else {
        $stmt = $conn->prepare("
            SELECT u.id, u.email, u.username,
                   COALESCE(f.phone,'') AS phone
            FROM tbluser u
            LEFT JOIN tblfaculty f ON f.username = u.username
            WHERE u.id = ?
              AND u.is_deleted = 0 AND u.is_active = 1
            LIMIT 1
        ");
        $stmt->bind_param('s', $user_id);
    }
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'Account not found.']);
        exit;
    }

    // ── enforce cooldown (skipped for initial send after login) ───
    if (!$is_initial) {
        $chk = $conn->prepare("SELECT otp_sent_at FROM tbluser WHERE id = ?");
        $chk->bind_param('s', $user['id']);
        $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        $chk->close();

        if (!empty($row['otp_sent_at'])) {
            $elapsed = time() - strtotime($row['otp_sent_at']);
            if ($elapsed < RESEND_COOLDOWN_SECONDS) {
                $wait = RESEND_COOLDOWN_SECONDS - $elapsed;
                ob_end_clean();
                echo json_encode([
                    'success' => false,
                    'message' => "Please wait {$wait} second(s) before requesting a new code."
                ]);
                exit;
            }
        }
    }

    // ── check email is on file ────────────────────────────────────
    if (empty($user['email'])) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => 'No email address on file for this account.']);
        exit;
    }

    // ── generate + store OTP ──────────────────────────────────────
    $otp    = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $hashed = password_hash($otp, PASSWORD_BCRYPT);
    $expiry = date('Y-m-d H:i:s', time() + OTP_EXPIRY_MINUTES * 60);

    $upd = $conn->prepare("
        UPDATE tbluser
        SET otp_hash = ?, otp_expiry = ?, otp_sent_at = NOW()
        WHERE id = ?
    ");
    $upd->bind_param('sss', $hashed, $expiry, $user['id']);
    $upd->execute();
    $upd->close();

    // ── send email via SMTP ───────────────────────────────────────
    $sent = smtpSend($user['email'], $user['username'], $otp);

    ob_end_clean();

    if ($sent === true) {
        echo json_encode([
            'success'     => true,
            'destination' => maskEmail($user['email'])
        ]);
    } else {
        // Return the exact SMTP error so you can debug it
        echo json_encode([
            'success' => false,
            'message' => 'Email delivery failed: ' . $sent
        ]);
    }

} catch (Exception $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

if (isset($conn)) $conn->close();


/* ══════════════════════════════════════════════════════════════════
   SMTP SEND — pure PHP, STARTTLS on port 587
   
   COMMON REASONS EMAIL DOESN'T SEND:
   1. App Password has spaces — store it WITHOUT spaces:
      'htjohjjflxxifvbv'  not  'htjo hjjf lxxi fvbv'
   2. "Less secure app access" or 2FA not configured properly in Gmail
   3. Your hosting server blocks outbound port 587 (ask host to open it)
   4. OpenSSL extension is not loaded in PHP (check phpinfo())
   5. fsockopen() is disabled by your host
══════════════════════════════════════════════════════════════════ */
function smtpSend(string $toEmail, string $toName, string $otp): string|bool {
    $host    = SMTP_HOST;
    $port    = SMTP_PORT;
    $user    = SMTP_USER;
    // Remove spaces from App Password — Gmail ignores them but PHP doesn't
    $pass    = str_replace(' ', '', SMTP_PASS);
    $from    = SMTP_FROM;
    $name    = SMTP_NAME;
    $subject = 'Your TereLearn Login Code';
    $body    = emailHtml($toName, $otp);

    try {
        $sock = @fsockopen('tcp://' . $host, $port, $errno, $errstr, 15);
        if (!$sock) {
            return "Cannot connect to SMTP ({$host}:{$port}): {$errstr} (errno {$errno}). "
                 . "Your server may be blocking outbound port 587.";
        }

        stream_set_timeout($sock, 15);

        $read = function () use ($sock): string {
            $out = '';
            while ($line = fgets($sock, 515)) {
                $out .= $line;
                if (isset($line[3]) && $line[3] === ' ') break;
            }
            return $out;
        };

        $cmd = function (string $c) use ($sock, $read): string {
            fwrite($sock, $c . "\r\n");
            return $read();
        };

        // Greeting
        $r = $read();
        if (substr($r, 0, 3) !== '220') { fclose($sock); return "Bad SMTP greeting: {$r}"; }

        // EHLO
        $r = $cmd("EHLO localhost");
        if (substr($r, 0, 3) !== '250') { fclose($sock); return "EHLO failed: {$r}"; }

        // STARTTLS
        $r = $cmd("STARTTLS");
        if (substr($r, 0, 3) !== '220') { fclose($sock); return "STARTTLS failed: {$r}"; }

        // Upgrade to TLS
        if (!stream_socket_enable_crypto($sock, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($sock);
            return "TLS upgrade failed. Check that OpenSSL is enabled in your PHP (phpinfo()).";
        }

        // EHLO again after TLS
        $r = $cmd("EHLO localhost");
        if (substr($r, 0, 3) !== '250') { fclose($sock); return "EHLO (post-TLS) failed: {$r}"; }

        // AUTH LOGIN
        $cmd("AUTH LOGIN");
        $cmd(base64_encode($user));
        $r = $cmd(base64_encode($pass));
        if (substr($r, 0, 3) !== '235') {
            fclose($sock);
            return "Gmail auth failed (535 = wrong App Password): {$r}. "
                 . "Make sure you generated a Gmail App Password at myaccount.google.com/apppasswords "
                 . "and 2-Step Verification is ON.";
        }

        // Envelope
        $r = $cmd("MAIL FROM:<{$from}>");
        if (substr($r, 0, 3) !== '250') { fclose($sock); return "MAIL FROM rejected: {$r}"; }

        $r = $cmd("RCPT TO:<{$toEmail}>");
        if (substr($r, 0, 3) !== '250') { fclose($sock); return "RCPT TO rejected for {$toEmail}: {$r}"; }

        // Message body
        $cmd("DATA");

        $msg  = "From: =?UTF-8?B?" . base64_encode($name) . "?= <{$from}>\r\n";
        $msg .= "To: =?UTF-8?B?" . base64_encode($toName) . "?= <{$toEmail}>\r\n";
        $msg .= "Subject: =?UTF-8?B?" . base64_encode($subject) . "?=\r\n";
        $msg .= "MIME-Version: 1.0\r\n";
        $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
        $msg .= "Content-Transfer-Encoding: base64\r\n";
        $msg .= "\r\n";
        $msg .= chunk_split(base64_encode($body));
        $msg .= "\r\n.";

        $r = $cmd($msg);
        if (substr($r, 0, 3) !== '250') { fclose($sock); return "DATA rejected: {$r}"; }

        $cmd("QUIT");
        fclose($sock);
        return true;

    } catch (Exception $e) {
        return $e->getMessage();
    }
}


/* ══════════════════════════════════════════════════════════════════
   EMAIL HTML TEMPLATE
══════════════════════════════════════════════════════════════════ */
function emailHtml(string $name, string $otp): string {
    $exp = OTP_EXPIRY_MINUTES;
    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8">
<style>
  body{font-family:'Segoe UI',Arial,sans-serif;background:#f5f7fa;margin:0;padding:30px;}
  .wrap{max-width:460px;margin:0 auto;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);}
  .top{background:linear-gradient(135deg,#4caf50,#2e7d32);padding:28px;text-align:center;color:#fff;}
  .top h1{margin:0;font-size:1.4rem;font-weight:700;}
  .top p{margin:4px 0 0;opacity:.85;font-size:.85rem;}
  .body{padding:28px;color:#4a5568;font-size:.93rem;line-height:1.65;}
  .box{background:#f0fdf4;border:2px dashed #4caf50;border-radius:10px;padding:22px;text-align:center;margin:20px 0;}
  .code{font-size:2.6rem;font-weight:800;letter-spacing:.4em;color:#2e7d32;font-family:monospace;}
  .exp{font-size:.78rem;color:#718096;margin-top:8px;}
  .warn{background:#fef2f2;border-left:4px solid #ef4444;padding:10px 14px;border-radius:4px;font-size:.82rem;color:#b91c1c;margin-top:16px;}
  .foot{background:#f9fafb;padding:14px 28px;text-align:center;font-size:.75rem;color:#a0aec0;border-top:1px solid #e2e8f0;}
</style>
</head>
<body>
<div class="wrap">
  <div class="top">
    <h1>TereLearn</h1>
    <p>Login Verification</p>
  </div>
  <div class="body">
    <p>Hi <strong>{$name}</strong>,</p>
    <p>Use the code below to complete your login. Do <strong>not</strong> share this with anyone.</p>
    <div class="box">
      <div class="code">{$otp}</div>
      <div class="exp">⏱ Expires in {$exp} minutes</div>
    </div>
    <div class="warn">If you did not attempt to log in, contact your administrator immediately.</div>
  </div>
  <div class="foot">TereLearn · Education that transcends</div>
</div>
</body>
</html>
HTML;
}


/* ══════════════════════════════════════════════════════════════════
   MASK EMAIL  e.g. renwel@gmail.com → r****l@gmail.com
══════════════════════════════════════════════════════════════════ */
function maskEmail(string $email): string {
    [$local, $domain] = explode('@', $email, 2);
    $first = substr($local, 0, 1);
    $last  = strlen($local) > 1 ? substr($local, -1) : '';
    $stars = str_repeat('*', max(1, strlen($local) - 2));
    return "{$first}{$stars}{$last}@{$domain}";
}
