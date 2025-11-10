<?php
if (session_status() == PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>BotStore - Marketplace Bot WA & Telegram</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <header class="site-header">
        <div class="wrap">
            <a class="brand" href="index.php">BotStore</a>
            <nav>
                <a href="index.php">Toko</a>
                <a href="checkout.php">Keranjang</a>
                <a href="admin/index.php"></a>
            </nav>
        </div>
    </header>
    <main class="wrap">