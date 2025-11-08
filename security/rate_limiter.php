<?php
function rate_limit($key, $limit = 10, $seconds = 60) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $file = __DIR__ . '/../logs/rate_' . md5($ip . $key) . '.json';
    $now = time();
    $data = ['count'=>0,'timestamp'=>$now];

    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true);
        if ($now - $data['timestamp'] > $seconds) {
            $data = ['count'=>0,'timestamp'=>$now];
        }
    }

    $data['count']++;
    file_put_contents($file, json_encode($data));

    if ($data['count'] > $limit) {
        http_response_code(429);
        die("Too many requests. Try again later.");
    }
}
