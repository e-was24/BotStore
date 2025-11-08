<?php
if (session_status() == PHP_SESSION_NONE) session_start();

/**
 * Simple "database" of products.
 * In real app: ambil dari DB (MySQL).
 */
function get_products()
{
    return [
        [
            'id' => 'bot-wa-001',
            'title' => 'Auto Reply WA - Starter',
            'price' => 199000,
            'desc' => 'Script auto-reply sederhana untuk WhatsApp (PHP + Webhook).',
            'file' => 'sample-bot.zip'
        ],
        [
            'id' => 'bot-tg-001',
            'title' => 'Telegram Bot - Notifier',
            'price' => 249000,
            'desc' => 'Bot Telegram notifikasi & scheduler (Python).',
            'file' => 'sample-bot-tg.zip'
        ]
    ];
}

/**
 * Format rupiah
 */
function rupiah($n)
{
    return 'Rp ' . number_format($n, 0, ',', '.');
}

/**
 * Create a single-use token after "payment".
 * tokens.json used to store tokens: { "token": { "file": "...", "expires": timestamp } }
 */
function create_download_token($filename, $ttl_seconds = 3600)
{
    $token = bin2hex(random_bytes(16));
    $data = [];
    $path = __DIR__ . '/../storage/tokens.json';
    if (file_exists($path)) $data = json_decode(file_get_contents($path), true) ?? [];
    $data[$token] = [
        'file' => $filename,
        'expires' => time() + $ttl_seconds
    ];
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    return $token;
}

/**
 * Validate token and optionally consume it (single-use).
 */
function validate_and_consume_token($token)
{
    $path = __DIR__ . '/../storage/tokens.json';
    if (!file_exists($path)) return false;
    $data = json_decode(file_get_contents($path), true) ?? [];
    if (!isset($data[$token])) return false;
    $record = $data[$token];
    if ($record['expires'] < time()) {
        unset($data[$token]);
        file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
        return false;
    }
    // Consume token (single-use)
    unset($data[$token]);
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    return $record['file'];
}
