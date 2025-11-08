<?php
// ================================================
// 🧩 BotStore Secure Download System
// ================================================

require_once 'includes/functions.php';
require_once 'includes/header.php';
require_once 'assets/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



// Paths
$productsPath = __DIR__ . '/storage/products/';
$logPath      = __DIR__ . '/storage/logs/security.log';
$tmpTokens    = __DIR__ . '/storage/tmp/temp_tokens.json';

// ===== Serve file via AJAX =====
if (isset($_GET['serve']) && $_GET['serve'] == '1') {
    $token = $_GET['token'] ?? null;
    if (!$token) {
        http_response_code(400);
        echo "Invalid token";
        exit;
    }

    $fileData = validate_and_consume_token($token); // ambil nama file & hapus token
    if (!$fileData) {
        http_response_code(403);
        echo "Token invalid or expired";
        exit;
    }

    $filePath = $productsPath . basename($fileData['file']);
    if (!file_exists($filePath)) {
        http_response_code(404);
        echo "File not found";
        exit;
    }

    // Kirim email notifikasi ke admin
    try {
        $mail = new PHPMailer(true);
        $mail->From = "noreply@botstore.id";
        $mail->FromName = "BotStore System";
        $mail->addAddress("admin@gmail.com", "Elan");
        $mail->Subject = "🔔 Bot Downloaded: {$fileData['product_name']}";
        $mail->Body = "
            <b>Product:</b> {$fileData['product_name']}<br>
            <b>User:</b> {$fileData['email']}<br>
            <b>Time:</b> {$fileData['created_at']}<br>
            <b>Status:</b> Download Successful ✅
        ";
        $mail->isHTML(true);
        $mail->send();
    } catch (Exception $e) {
        logDownload($logPath, "📧 Email failed: " . $e->getMessage());
    }

    logDownload($logPath, "✅ {$fileData['email']} downloaded {$fileData['product_name']} ({$fileData['file']})");

    // Serve file
    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filePath));
    readfile($filePath);
    exit;
}

// ===== HTML / UI =====
$token = $_GET['token'] ?? null;
if (!$token) {
    echo "<p>Token tidak ditemukan. Kembali ke <a href='index.php'>Toko</a>.</p>";
    require_once 'includes/footer.php';
    exit;
}
?>

<h2>Download</h2>
<p>Proses pengunduhan akan dimulai setelah kamu klik tombol <strong>Download</strong>.</p>

<div class="download-wrap">
    <button id="btnDownload" class="btn primary">Download ZIP</button>
    <div id="progressContainer" class="progress" style="display:none;">
        <div id="bar" class="bar"></div>
    </div>
    <div id="msg"></div>
</div>

<script>
document.getElementById('btnDownload').addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    const progressContainer = document.getElementById('progressContainer');
    const bar = document.getElementById('bar');
    const msg = document.getElementById('msg');
    progressContainer.style.display = 'block';
    let progress = 0;

    const interval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress >= 90) progress = 90;
        bar.style.width = progress + '%';
    }, 300);

    fetch('download.php?serve=1&token=<?= urlencode($token) ?>')
        .then(resp => {
            if (!resp.ok) throw new Error('Gagal validasi token atau file.');
            return resp.blob();
        })
        .then(blob => {
            clearInterval(interval);
            bar.style.width = '100%';
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'download.zip';
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
            msg.innerText = 'Download dimulai...';
        })
        .catch(err => {
            clearInterval(interval);
            btn.disabled = false;
            msg.innerText = 'Error: ' + err.message;
            bar.style.width = '0%';
        });
});
</script>

<?php require_once 'includes/footer.php'; ?>

<?php
function logDownload($logPath, $message) {
    $time = date('Y-m-d H:i:s');
    $line = "[$time] [DOWNLOAD] $message\n";
    file_put_contents($logPath, $line, FILE_APPEND);
}
?>
