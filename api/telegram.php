<?php
/**
 * Telegram API Integration
 * ------------------------
 * Digunakan untuk mengirim pesan otomatis ke admin
 * melalui bot Telegram.
 */

function sendTelegramMessage($message)
{
    $config_path = __DIR__ . '/../storage/config.json';
    if (!file_exists($config_path)) {
        return false;
    }

    $config = json_decode(file_get_contents($config_path), true);
    $bot_token = $config['telegram']['bot_token'] ?? null;
    $chat_id   = $config['telegram']['chat_id'] ?? null;

    if (!$bot_token || !$chat_id) {
        return false;
    }

    $url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];

    $options = [
        'http' => [
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data)
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result ? json_decode($result, true) : false;
}
?>
