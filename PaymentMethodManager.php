<?php

class PaymentMethodManager{
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn; // ✅ Simpan koneksi langsung
    }
    /**
     * Ambil semua metode pembayaran aktif dari database
     */
    public static function getAllActive($conn) {
        $sql = "SELECT id, name FROM payment_methods WHERE is_active = TRUE ORDER BY name ASC";
        $result = $conn->query($sql);
        $methods = [];

        while ($row = $result->fetch_assoc()) {
            $methods[] = $row;
        }

        return $methods;
    }

    /**
     * Ambil metode pembayaran berdasarkan ID
     */
    public static function getMethodById($conn, $id) {
        $stmt = $conn->prepare("SELECT name FROM payment_methods WHERE id = ? AND is_active = TRUE");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return null;
        }

        return $result->fetch_assoc();
    }

    /**
     * Tambahkan metode pembayaran baru
     */
    public static function addMethod($conn, $name) {
        // Validasi input
        if (empty(trim($name))) {
            throw new Exception("Nama metode pembayaran tidak boleh kosong");
        }

        $stmt = $conn->prepare("INSERT INTO payment_methods (name, is_active) VALUES (?, TRUE)");
        $stmt->bind_param("s", $name);

        if (!$stmt->execute()) {
            throw new Exception("Gagal menambahkan metode pembayaran");
        }

        return true;
    }

    /**
     * Nonaktifkan metode pembayaran
     */
    public static function deactivateMethod($conn, $id) {
        $stmt = $conn->prepare("UPDATE payment_methods SET is_active = FALSE WHERE id = ?");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("Gagal menonaktifkan metode pembayaran");
        }

        return true;
    }

    /**
     * Aktifkan kembali metode pembayaran
     */
    public static function activateMethod($conn, $id) {
        $stmt = $conn->prepare("UPDATE payment_methods SET is_active = TRUE WHERE id = ?");
        $stmt->bind_param("i", $id);

        if (!$stmt->execute()) {
            throw new Exception("Gagal mengaktifkan metode pembayaran");
        }

        return true;
    }
}