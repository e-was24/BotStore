<?php
/**
 * WhatsApp API Integration (via Fonnte)
 * -------------------------------------
 * Kirim notifikasi transaksi ke WhatsApp user/admin.
 */

function sendWhatsappMessage($message, $to = null)
{
    $config_path = __DIR__ . '/../storage/config.json';
    if (!file_exists($config_path)) {
        return false;
    }

    $config = json_decode(file_get_contents($config_path), true);
    $token = $config['whatsapp']['token'] ?? null;
    $default_number = $config['whatsapp']['admin_number'] ?? null;

    if (!$token || (!$to && !$default_number)) {
        return false;
    }

    $target = $to ?: $default_number;
    $data = [
        'target' => $target,
        'message' => $message
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => ["Authorization: $token"]
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
?>
