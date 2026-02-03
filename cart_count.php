<?php
if (session_status() == PHP_SESSION_NONE) session_start();

$count = 0;
if (!empty($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $it) {
        $count += (int)($it['qty'] ?? 0);
    }
}

// Release session lock early so concurrent requests aren't blocked
if (session_status() !== PHP_SESSION_NONE) {
    session_write_close();
}

echo $count;
