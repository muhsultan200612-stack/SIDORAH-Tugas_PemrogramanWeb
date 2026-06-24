<?php
/**
 * SIDORAH - config_email.php
 * Konfigurasi Gmail SMTP menggunakan PHPMailer via CDN
 */

define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_EMAIL',    'sidorah.april@gmail.com');
define('SMTP_PASSWORD', 'xcyl uxzj tbmu zqeh');
define('SMTP_NAME',     'RS SIDORAH');

function kirim_email($to, $to_name, $subject, $body_html) {
    // Gunakan PHPMailer dari folder lokal
    $phpmailer_path = __DIR__ . '/phpmailer/PHPMailer.php';

    if (!file_exists($phpmailer_path)) {
        // Fallback: download PHPMailer otomatis
        return kirim_email_curl($to, $to_name, $subject, $body_html);
    }

    require_once __DIR__ . '/phpmailer/Exception.php';
    require_once __DIR__ . '/phpmailer/PHPMailer.php';
    require_once __DIR__ . '/phpmailer/SMTP.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_EMAIL;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_EMAIL, SMTP_NAME);
        $mail->addAddress($to, $to_name);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body_html;

        $mail->send();
        return ['success' => true];
    } catch (Exception $e) {
        return ['success' => false, 'error' => $mail->ErrorInfo];
    }
}

function kirim_email_curl($to, $to_name, $subject, $body_html) {
    // Coba via socket SSL langsung
    $from     = SMTP_EMAIL;
    $password = str_replace(' ', '', SMTP_PASSWORD);

    $socket = @fsockopen('ssl://smtp.gmail.com', 465, $errno, $errstr, 15);
    if (!$socket) {
        return ['success' => false, 'error' => "Koneksi SSL gagal: $errstr"];
    }

    $read = fgets($socket, 515);
    if (substr(trim($read), 0, 3) != '220') {
        fclose($socket);
        return ['success' => false, 'error' => "Server: $read"];
    }

    $steps = [
        ["EHLO localhost\r\n", '250'],
        ["AUTH LOGIN\r\n", '334'],
        [base64_encode($from)."\r\n", '334'],
        [base64_encode($password)."\r\n", '235'],
        ["MAIL FROM:<$from>\r\n", '250'],
        ["RCPT TO:<$to>\r\n", '250'],
        ["DATA\r\n", '354'],
    ];

    foreach ($steps as [$cmd, $exp]) {
        fputs($socket, $cmd);
        $r = fgets($socket, 515);
        if (substr(trim($r), 0, 3) != $exp) {
            fclose($socket);
            return ['success' => false, 'error' => "Step gagal ($exp): $r"];
        }
    }

    $date    = date('r');
    $subj    = '=?UTF-8?B?'.base64_encode($subject).'?=';
    $msg  = "Date: $date\r\n";
    $msg .= "From: ".SMTP_NAME." <$from>\r\n";
    $msg .= "To: $to_name <$to>\r\n";
    $msg .= "Subject: $subj\r\n";
    $msg .= "MIME-Version: 1.0\r\n";
    $msg .= "Content-Type: text/html; charset=UTF-8\r\n";
    $msg .= "\r\n".$body_html."\r\n.\r\n";

    fputs($socket, $msg);
    $r = fgets($socket, 515);
    fputs($socket, "QUIT\r\n");
    fclose($socket);

    if (substr(trim($r), 0, 3) == '250') {
        return ['success' => true];
    }
    return ['success' => false, 'error' => "Kirim gagal: $r"];
}

function template_email_reset($nama, $link_reset, $nama_rs) {
    return "
<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'></head>
<body style='font-family:Arial,sans-serif;background:#f8f9fa;margin:0;padding:20px'>
<div style='max-width:520px;margin:0 auto;background:white;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08)'>
    <div style='background:linear-gradient(135deg,#7b0d1e,#c0392b);padding:28px;text-align:center;color:white'>
        <div style='font-size:2.5rem;margin-bottom:8px'>🩸</div>
        <h2 style='margin:0;font-size:1.3rem'>$nama_rs</h2>
        <p style='margin:4px 0 0;opacity:0.8;font-size:0.85rem'>Reset Password Akun</p>
    </div>
    <div style='padding:28px'>
        <p style='color:#374151'>Halo, <strong>$nama</strong>!</p>
        <p style='color:#6b7280;line-height:1.6'>
            Kami menerima permintaan reset password untuk akun SIDORAH kamu.
            Klik tombol di bawah untuk membuat password baru.
        </p>
        <div style='text-align:center;margin:24px 0'>
            <a href='$link_reset'
               style='background:#dc3545;color:white;text-decoration:none;
                      padding:12px 28px;border-radius:10px;font-weight:bold;
                      font-size:1rem;display:inline-block'>
                🔑 Reset Password Sekarang
            </a>
        </div>
        <div style='background:#fef2f2;border-radius:10px;padding:12px;margin-bottom:16px'>
            <p style='color:#991b1b;font-size:0.82rem;margin:0'>
                ⚠️ Link berlaku <strong>1 jam</strong>. Jika tidak meminta reset, abaikan email ini.
            </p>
        </div>
        <p style='color:#9ca3af;font-size:0.78rem'>
            Copy link:<br>
            <span style='color:#dc3545;word-break:break-all'>$link_reset</span>
        </p>
    </div>
    <div style='background:#f9fafb;padding:14px;text-align:center;border-top:1px solid #f3f4f6'>
        <p style='color:#9ca3af;font-size:0.78rem;margin:0'>
            © ".date('Y')." $nama_rs — Email otomatis, jangan dibalas
        </p>
    </div>
</div>
</body>
</html>";
}