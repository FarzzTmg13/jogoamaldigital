<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
session_destroy();

// Arahkan ke halaman utama
header('Location: index.php');
exit();
?>