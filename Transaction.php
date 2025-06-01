<?php

class Transaction extends Model{
    private $id;
    private $jenis;
    private $deskripsi;
    private $jumlah;
    private $tanggal;
    private $payment_method;
    private $payment_reference;
    private $nama;

    public function __construct($data = []) {
        $this->id = !empty($data['id']) ? intval($data['id']) : null;
        $this->jenis = $this->sanitizeString($data['jenis'] ?? '', 50);
        $this->deskripsi = $this->sanitizeString($data['deskripsi'] ?? '', 255);
        $this->jumlah = is_numeric($data['jumlah'] ?? 0) ? floatval($data['jumlah']) : 0.0;
        $this->tanggal = !empty($data['tanggal']) ? date('Y-m-d H:i:s', strtotime($data['tanggal'])) : date('Y-m-d H:i:s');
        $this->payment_method = $this->sanitizeString($data['payment_method'] ?? '', 100);
        $this->payment_reference = $this->sanitizeString($data['payment_reference'] ?? '', 100);
        $this->nama = $this->sanitizeString($data['nama'] ?? '', 100);
    }

    // Helper untuk sanitasi string
    private function sanitizeString($value, $maxLength = 255): string {
        $value = trim(strip_tags($value));
        if (strlen($value) > $maxLength) {
            $value = substr($value, 0, $maxLength);
        }
        return $value;
    }

    // Getter dan Setter
    public function getId() { return $this->id; }
    public function getJenis() { return $this->jenis; }
    public function setJenis($jenis) { $this->jenis = $this->sanitizeString($jenis, 50); }

    public function getDeskripsi() { return $this->deskripsi; }
    public function setDeskripsi($deskripsi) { $this->deskripsi = $this->sanitizeString($deskripsi, 255); }

    public function getJumlah() { return $this->jumlah; }
    public function setJumlah($jumlah) { $this->jumlah = is_numeric($jumlah) ? floatval($jumlah) : 0.0; }

    public function getTanggal() { return $this->tanggal; }

    public function getPaymentMethod() { return $this->payment_method; }
    public function setPaymentMethod($method) { $this->payment_method = $this->sanitizeString($method, 100); }

    public function getPaymentReference() { return $this->payment_reference; }
    public function setPaymentReference($reference) { $this->payment_reference = $this->sanitizeString($reference, 100); }

    public function getNama() { return $this->nama; }
    public function setNama($nama) { $this->nama = $this->sanitizeString($nama, 100); }

    // Simpan transaksi baru
    public function save($conn) {
        try {
            if (empty($this->deskripsi)) {
                throw new Exception("Deskripsi transaksi harus diisi");
            }

            if ($this->jumlah <= 0) {
                throw new Exception("Jumlah harus lebih besar dari nol");
            }

            if ($this->jenis === 'pengeluaran' && empty($this->payment_method)) {
                throw new Exception("Metode pembayaran harus dipilih untuk pengeluaran");
            }

            if ($this->payment_method !== 'Tunai' && empty($this->payment_reference)) {
                throw new Exception("Nomor referensi harus diisi untuk pembayaran non-tunai");
            }

            $stmt = $conn->prepare("
                INSERT INTO transactions 
                (jenis, deskripsi, jumlah, payment_method, payment_reference, nama) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "ssdsss",
                $this->jenis,
                $this->deskripsi,
                $this->jumlah,
                $this->payment_method,
                $this->payment_reference,
                $this->nama
            );

            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (\Exception $e) {
            throw new Exception("Gagal menyimpan transaksi: " . $e->getMessage());
        }
    }

    // Update transaksi
    public function update($conn) {
        try {
            if (empty($this->deskripsi)) {
                throw new Exception("Deskripsi tidak boleh kosong");
            }

            if ($this->jumlah <= 0) {
                throw new Exception("Jumlah harus lebih besar dari nol");
            }

            if ($this->jenis === 'pengeluaran' && empty($this->payment_method)) {
                throw new Exception("Metode pembayaran harus dipilih untuk pengeluaran");
            }

            if ($this->payment_method !== 'Tunai' && empty($this->payment_reference)) {
                throw new Exception("Nomor referensi harus diisi untuk pembayaran non-tunai");
            }

            $stmt = $conn->prepare("
                UPDATE transactions 
                SET jenis=?, deskripsi=?, jumlah=?, payment_method=?, payment_reference=?, nama=? 
                WHERE id=?
            ");
            $stmt->bind_param(
                "ssdsssi",
                $this->jenis,
                $this->deskripsi,
                $this->jumlah,
                $this->payment_method,
                $this->payment_reference,
                $this->nama,
                $this->id
            );

            $result = $stmt->execute();
            $stmt->close();

            return $result;
        } catch (\Exception $e) {
            throw new Exception("Gagal mengupdate transaksi: " . $e->getMessage());
        }
    }

    // Hapus transaksi
    public static function delete($conn, $id) {
        try {
            $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
            $stmt->bind_param("i", $id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        } catch (\Exception $e) {
            throw new Exception("Gagal menghapus transaksi: " . $e->getMessage());
        }
    }

    // Ambil semua transaksi
    public static function getAll($conn, $periode = '', $jenis = '', $program = '') {
        $sql = "SELECT * FROM transactions WHERE 1=1";
        $params = [];
        $types = '';

        if (!empty($periode)) {
            $sql .= " AND DATE(tanggal) = ?";
            $params[] = $periode;
            $types .= 's';
        }

        if (!empty($jenis)) {
            $sql .= " AND jenis = ?";
            $params[] = $jenis;
            $types .= 's';
        }

        if (!empty($program)) {
            $sql .= " AND deskripsi LIKE ?";
            $params[] = "%$program%";
            $types .= 's';
        }

        $sql .= " ORDER BY tanggal DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $transactions = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $transactions;
    }

    // Hitung total pemasukan dan pengeluaran
    public static function getTotalIncomeExpense($conn, $periode = '', $jenis = '', $program = '') {
        $sql = "SELECT jenis, SUM(jumlah) as total FROM transactions WHERE 1=1";
        $params = [];
        $types = '';

        if (!empty($periode)) {
            $sql .= " AND DATE(tanggal) = ?";
            $params[] = $periode;
            $types .= 's';
        }

        if (!empty($jenis)) {
            $sql .= " AND jenis = ?";
            $params[] = $jenis;
            $types .= 's';
        }

        if (!empty($program)) {
            $sql .= " AND deskripsi LIKE ?";
            $params[] = "%$program%";
            $types .= 's';
        }

        $sql .= " GROUP BY jenis";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $totals = ['income' => 0, 'expense' => 0];
        while ($row = $result->fetch_assoc()) {
            if ($row['jenis'] === 'pemasukan') {
                $totals['income'] += (float)$row['total'];
            } else {
                $totals['expense'] += (float)$row['total'];
            }
        }

        $stmt->close();

        return $totals;
    }

    // Saldo keseluruhan
    public static function getTotalSaldo($conn) {
        $sql = "SELECT SUM(CASE WHEN jenis='pemasukan' THEN jumlah ELSE -jumlah END) AS saldo FROM transactions";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        return (float)($row['saldo'] ?? 0);
    }

    // Ambil transaksi berdasarkan ID
    public static function getById($conn, $id) {
        $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            throw new Exception("Data tidak ditemukan");
        }

        $data = $result->fetch_assoc();
        $stmt->close();

        return new Transaction($data);
    }
}