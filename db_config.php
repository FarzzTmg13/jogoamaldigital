<?php
$host = "sql303.infinityfree.com";
$user = "if0_39076434";
$pass = "FARIZ1987FARIZ";
$dbname = "if0_39076434_jogoamal";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
?>