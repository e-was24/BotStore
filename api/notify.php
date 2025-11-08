<?php
/**
 * BotStore Notify System
 * ----------------------
 * Mengirimkan notifikasi ke admin & user
 * setelah pembelian berhasil atau ada alert keamanan.
 */

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../assets/vendor/phpmailer/PHPMailer.php';
require_once __DIR__ . '/../assets/vendor/phpmailer/SMTP.php';
require_once __DIR__ . '/../assets/vendor/phpmailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// === Konfigurasi Dasar ===
$admin_email = "elan@botstore.local"; // bisa diganti pakai ENV
$log_path = __DIR__ . '/../storage/logs/system.log';

// === Ambil Data dari POST ===
$data = json_decode(file_get_contents("php://input"), true);

$type     = $data['type'] ?? 'info';       // success | alert | info
$message  = $data['message'] ?? 'No message provided.';
$userMail = $data['user_email'] ?? null;
$product  = $data['product'] ?? 'Unknown Product';

// === Logging ===
function log_notify($msg, $path) {
    $time = date("Y-m-d H:i:s");
    file_put_contents($path, "[$time] $msg\n", FILE_APPEND);
}

// === Fungsi Kirim Email ===
function send_mail($to, $subject, $body) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // ganti sesuai server kamu
        $mail->SMTPAuth = true;
        $mail->Username = 'your_email@gmail.com'; // Ganti
        $mail->Password = 'your_app_password';    // Ganti
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('no-reply@botstore.local', 'BotStore System');
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->send();

        return true;
    } catch (Exception $e) {
        log_notify("❌ Mail Error: " . $e->getMessage(), __DIR__ . '/../storage/logs/system.log');
        return false;
    }
}

// === Kirim ke Admin ===
$subject = "📢 [BotStore Notification] $type";
$body = "
<h3>📬 BotStore Notification</h3>
<p><b>Type:</b> $type</p>
<p><b>Message:</b> $message</p>
<p><b>Product:</b> $product</p>
<hr>
<p>Sent automatically by BotStore notify.php</p>
";

send_mail($admin_email, $subject, $body);
log_notify("📨 Notify sent to admin: $message", $log_path);

// === Jika Pembelian Sukses Kirim ke User ===
if ($type === "success" && $userMail) {
    $user_body = "
    <h3>🎉 Terima kasih sudah membeli di BotStore!</h3>
    <p>Produk Anda: <b>$product</b></p>
    <p>Status: <b>Pembayaran Berhasil</b></p>
    <p>Anda akan segera menerima link download di halaman profil.</p>
    <hr>
    <p>BotStore Team</p>
    ";
    send_mail($userMail, "✅ Pembelian $product Berhasil", $user_body);
    log_notify("✅ Notify sent to user: $userMail for $product", $log_path);
}

// === Kirim ke Telegram (opsional, kalau telegram.php aktif) ===
if (file_exists(__DIR__ . '/telegram.php')) {
    include_once __DIR__ . '/telegram.php';
    if (function_exists('sendTelegramMessage')) {
        sendTelegramMessage("📢 BotStore $type:\n$message\n🛒 $product");
    }
}

// === Kirim ke WhatsApp (opsional, kalau whatsapp.php aktif) ===
if (file_exists(__DIR__ . '/whatsapp.php')) {
    include_once __DIR__ . '/whatsapp.php';
    if (function_exists('sendWhatsappMessage')) {
        sendWhatsappMessage("📢 BotStore $type:\n$message\n🛒 $product");
    }
}

echo json_encode(["status" => "ok", "message" => "Notification processed."]);
?>
