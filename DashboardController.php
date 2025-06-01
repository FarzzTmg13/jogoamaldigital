<?php

class DashboardController extends Controller {
    protected $conn; // ✅ Diubah dari private ke protected

    public function __construct($conn) {
        parent::__construct($conn);
        $this->conn = $conn;
    }

    /**
     * Handle proses tambah transaksi donasi
     */
    public function handleAddDonation($postData) {
        try {
            if (empty($postData['deskripsi'])) {
                throw new Exception("Deskripsi program harus diisi");
            }
            if (empty($postData['payment_method'])) {
                throw new Exception("Metode pembayaran harus dipilih");
            }

            if (!is_numeric($postData['jumlah']) || $postData['jumlah'] <= 0) {
                throw new Exception("Jumlah donasi harus berupa angka positif");
            }

            $transaction = new Transaction([
                'jenis' => 'pemasukan',
                'deskripsi' => $postData['deskripsi'],
                'jumlah' => $postData['jumlah'],
                'payment_method' => $postData['payment_method'],
                'payment_reference' => $postData['payment_reference'] ?? null,
                'nama' => $postData['nama'] ?? ''
            ]);

            if ($transaction->save($this->conn)) {
                $message = strtolower($transaction->getPaymentMethod()) === 'tunai'
                    ? "Silahkan menuju ke Kantor Takmir Masjid untuk Konfirmasi Pembayaran Donasi"
                    : "Tunggu beberapa saat, Silahkan hubungi Whatsapp kami. Admin akan mengkonfirmasi uang Donasi Anda";
                return ['success' => $message];
            }

            return ['error' => 'Gagal menyimpan transaksi donasi'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Handle proses tambah transaksi umum (oleh admin)
     */
    public function handleAddTransaction($postData) {
        try {
            if (empty($postData['deskripsi'])) {
                throw new Exception("Deskripsi tidak boleh kosong");
            }
            if (!is_numeric($postData['jumlah']) || (float)$postData['jumlah'] <= 0) {
                throw new Exception("Jumlah harus berupa angka positif");
            }

            $transaction = new Transaction([
                'jenis' => $postData['jenis'],
                'deskripsi' => $postData['deskripsi'],
                'jumlah' => $postData['jumlah'],
                'payment_method' => $postData['payment_method'] ?? null,
                'payment_reference' => $postData['payment_reference'] ?? null,
                'nama' => $postData['nama'] ?? ''
            ]);

            // Validasi tambahan untuk pengeluaran
            if ($transaction->getJenis() === 'pengeluaran') {
                if (empty($transaction->getPaymentMethod())) {
                    throw new Exception("Metode pembayaran harus dipilih untuk pengeluaran");
                }
                if ($transaction->getPaymentMethod() !== 'Tunai' && empty($transaction->getPaymentReference())) {
                    throw new Exception("Nomor referensi harus diisi untuk pembayaran non-tunai");
                }
            }

            if ($transaction->save($this->conn)) {
                return ['success' => 'Transaksi berhasil disimpan!'];
            }

            return ['error' => 'Gagal menyimpan transaksi'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Handle proses edit transaksi
     */
    public function handleEditTransaction($postData) {
    try {
        $id = intval($postData['edit_id']);
        if (!$id) {
            throw new Exception("ID transaksi tidak valid");
        }

        $transaction = new Transaction([
            'id' => $id,
            'jenis' => $postData['jenis'],
            'deskripsi' => $postData['deskripsi'],
            'jumlah' => (float)$postData['jumlah'],
            'payment_method' => $postData['payment_method'] ?? null,
            'payment_reference' => $postData['payment_reference'] ?? null,
            'nama' => $postData['nama']
        ]);

        if ($transaction->update($this->conn)) {
            return ['success' => 'Transaksi berhasil diubah!'];
        }

        return ['error' => 'Gagal mengubah transaksi'];
    } catch (\Exception $e) {
        return ['error' => $e->getMessage()];
    }
}

    /**
     * Handle proses hapus transaksi
     */
    public function handleDeleteTransaction($id) {
        if (Transaction::delete($this->conn, $id)) {
            return ['success' => 'Transaksi berhasil dihapus!'];
        }
        return ['error' => 'Gagal menghapus transaksi'];
    }

    /**
     * Ambil semua transaksi dengan filter
     */
    public function getFilteredTransactions($periode = '', $jenis = '', $program = '') {
        return Transaction::getAll($this->conn, $periode, $jenis, $program);
    }

    /**
     * Hitung total pemasukan dan pengeluaran
     */
    public function getTotals($periode = '', $jenis = '', $program = '') {
        return Transaction::getTotalIncomeExpense($this->conn, $periode, $jenis, $program);
    }

    /**
     * Hitung saldo keseluruhan
     */
    public function getTotalBalance() {
        return Transaction::getTotalSaldo($this->conn);
    }

    /**
     * Ambil metode pembayaran aktif
     */
    public function getPaymentMethods() {
        return PaymentMethodManager::getAllActive($this->conn);
    }

    /**
     * Ambil detail transaksi untuk modal edit
     */
    public function getTransactionById($id) {
    return Transaction::getById($this->conn, $id);
}
}