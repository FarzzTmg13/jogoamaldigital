<?php
session_start();

// Load semua class yang dibutuhkan
require_once 'Model.php';
require_once 'Controller.php';
require_once 'Transaction.php';
require_once 'PaymentMethodManager.php';
require_once 'DashboardController.php';
include 'db_config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Inisialisasi controller
$isAdmin = $_SESSION['role'] === 'admin';
$controller = new DashboardController($conn);

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['simpan_donasi'])) {
        $response = $controller->handleAddDonation($_POST);
        if (isset($response['success'])) {
            $_SESSION['success'] = $response['success'];
        } else {
            $_SESSION['error'] = $response['error'];
            $_SESSION['form_data'] = $_POST;
        }

        unset($_SESSION['form_data']);
        header("Location: dashboard.php");
        exit;
    } 
    
    if (isset($_POST['simpan_transaksi'])) {
        $response = $controller->handleAddTransaction($_POST);
        if (isset($response['success'])) {
            $_SESSION['success'] = $response['success'];
        } else {
            $_SESSION['error'] = $response['error'];
            $_SESSION['form_data'] = $_POST;
        }
        header("Location: dashboard.php");
        exit;
    }
    
    if (isset($_POST['simpan_edit'])) {
        $response = $controller->handleEditTransaction($_POST);
        if (isset($response['success'])) {
            $_SESSION['success'] = $response['success'];
        } else {
            $_SESSION['error'] = $response['error'];
        }
        header("Location: dashboard.php");
        exit;
    }
    
    if (isset($_POST['delete_id'])) {
        $response = $controller->handleDeleteTransaction($_POST['delete_id']);
        if (isset($response['success'])) {
            $_SESSION['success'] = $response['success'];
        } else {
            $_SESSION['error'] = $response['error'];
        }
        header("Location: dashboard.php");
        exit;
    }
}

// Get edit data jika ada
$edit_data = null;
if ($isAdmin && (isset($_GET['edit_id']) || isset($_POST['edit_id']))) {
    $edit_id = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : (int)$_POST['edit_id'];
    try {
        $edit_data = $controller->getTransactionById($edit_id);
    } catch (Exception $e) {
        $_SESSION['error'] = "Gagal memuat data transaksi: " . $e->getMessage();
    }
}

// Ambil filter dari GET
$periode = $_GET['periode'] ?? '';
$jenis_filter = in_array($_GET['jenis'] ?? '', ['pemasukan', 'pengeluaran']) ? $_GET['jenis'] : '';
$program = $_GET['program'] ?? '';

// Data untuk tampilan
$transactions = $controller->getFilteredTransactions($periode, $jenis_filter, $program) ?: [];
$totals = $controller->getTotals($periode, $jenis_filter, $program);
$total_saldo = $controller->getTotalBalance();
$paymentMethods = $controller->getPaymentMethods();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Keuangan Masjid</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js "></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css ">
    <link href="https://fonts.googleapis.com/css2?family=Poppins :wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com "></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* CSS styling tetap sama seperti versi Anda */
        :root {
            --primary: #2ca6a3;
            --secondary: #248583;
            --danger: #ef4444;
            --warning: #f59e0b;
            --light-bg: #f8fafc;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f1f5f9;
        }
        .card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            transform: translateY(-2px);
        }
        .btn-primary {
            background-color: var(--primary);
            color: white;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #248583;
            transform: translateY(-1px);
        }
        .btn-success {
            background-color: var(--secondary);
            color: white;
        }
        .btn-success:hover {
            background-color: #248583;
        }
        .btn-danger {
            background-color: var(--danger);
            color: white;
        }
        .btn-danger:hover {
            background-color: #dc2626;
        }
        .income {
            color: #2ca6a3;
            font-weight: 500;
        }
        .expense {
            color: var(--danger);
            font-weight: 500;
        }
        .table-responsive {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        th {
            background-color: var(--primary);
            color: white;
            position: sticky;
            top: 0;
        }
        tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .saldo-card {
            background: linear-gradient(135deg, #2ca6a3 0%, #34d399 100%);
            color: white;
            border-radius: 12px;
        }
        /* Form Controls */
.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #D1D5DB;
    border-radius: 0.375rem;
    background-color: #F9FAFB;
    font-size: 0.875rem;
    transition: all 0.15s ease;
}

.form-control:focus {
    outline: none;
    border-color: #2ca6a3;
    box-shadow: 0 0 0 3px rgba(33, 145, 80, 0.2);
    background-color: #FFFFFF;
}
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background-color: #e6f2eb;
            color: #248583;
        }
        .badge-danger {
            background-color: #fee2e2;
            color: #991b1b;
        }        
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 50;
            padding: 1rem;
            overflow-y: auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex !important;
        }

        .modal-content {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 0.5rem;
            width: 100%;
            max-width: 32rem;
            margin: 2rem auto;
            position: relative;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        /* Button Styles */
        .btn-primary {
            background-color: #2ca6a3;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background-color: #248583;
        }

        /* Show/Hide Classes */
        .show,
        .show-block {
            display: block !important;
        }

        .hidden {
            display: none !important;
        }
        
        .payment-method-badge {
            background-color: #e0f2fe;
            color: #0369a1;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            display: inline-block;
        }
        .payment-reference {
            font-size: 11px;
            color: #1e40af;
            display: block;
            margin-top: 2px;
        }        #paymentReferenceField {
            max-height: 0;
            overflow: hidden;
            opacity: 0;
            transition: all 0.3s ease;
            margin-top: 0;
        }
        #paymentReferenceField.show {
            max-height: 500px;
            opacity: 1;
            margin-top: 1rem;
        }
        .error-message {
            color: #dc2626;
            background-color: #fee2e2;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
        }
        .error-message i {
            margin-right: 0.5rem;
        }
        .income-card {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
        }
        .expense-card {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
        }
        #adminPaymentMethod,
        #adminPaymentReference {
            display: none;
        }
        #adminPaymentMethod.show,
        #adminPaymentReference.show {
            display: block;
        }
        #adminPaymentMethod,
        #adminPaymentReference {
            transition: all 0.3s ease;
            overflow: hidden;
        }
        #adminPaymentMethod.hidden,
        #adminPaymentReference.hidden {
            display: none;
        }
        /* Edit Form Styles */
        .edit-form label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .edit-form input,
        .edit-form select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #D1D5DB;
            border-radius: 0.375rem;
            background-color: #F9FAFB;
            font-size: 0.875rem;
            transition: all 0.15s ease;
        }

        .edit-form input:focus,
        .edit-form select:focus {
            outline: none;
            border-color: #2ca6a3;
            box-shadow: 0 0 0 3px rgba(33, 145, 80, 0.2);
            background-color: #FFFFFF;
        }

        .edit-form button[type="submit"] {
            width: 100%;
            padding: 0.75rem;
            background-color: #2ca6a3;
            color: white;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.15s ease;
        }

        .edit-form button[type="submit"]:hover {
            background-color: #248583;
        }

        .edit-form .form-group {
            margin-bottom: 1rem;
        }
    </style>
</head>
<body class="bg-gray-50">
<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Header -->
<header class="flex flex-col md:flex-row justify-between items-center mb-8">
    <div class="mb-4 md:mb-0">
        <a href="index.php" class="flex items-center">
            <h1 class="text-3xl font-bold text-gray-800">
                <i class="fas fa-mosque text-[#2ca6a3] mr-2"></i> JogoAmal Digital
            </h1>
        </a>
        <p class="text-gray-600">Manajemen keuangan masjid yang transparan</p>
    </div>
    <div class="flex items-center space-x-4">
        <div class="text-right">
            <p class="text-sm text-gray-500">Halo,</p>
            <p class="font-medium text-blue-600"><?= $_SESSION['username'] ?></p>
        </div>
        <a href="logout.php" class="flex items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
            <i class="fas fa-sign-out-alt mr-2"></i> Logout
        </a>
    </div>
</header>

    <!-- Saldo Card -->
    <div class="saldo-card p-6 mb-8 shadow-lg">
        <div class="flex justify-between items-center">
            <div>
                <p class="text-sm font-medium">Total Saldo Tersedia</p>
                <h2 class="text-4xl font-bold">Rp <?= number_format($total_saldo, 2, ',', '.') ?></h2>
            </div>
            <div class="bg-white bg-opacity-20 p-3 rounded-full">
                <i class="fas fa-wallet text-2xl"></i>
            </div>
        </div>
    </div>

    <!-- Income & Expense Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- Pemasukan -->
        <div class="card p-6 bg-green-50 border border-green-100">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm font-medium text-green-600">Total Pemasukan</p>
                    <h2 class="text-2xl font-bold text-green-700">Rp <?= number_format($totals['income'], 2, ',', '.') ?></h2>
                </div>
                <div class="bg-green-100 p-3 rounded-full">
                    <i class="fas fa-arrow-down text-green-600"></i>
                </div>
            </div>
        </div>
        <!-- Pengeluaran -->
        <div class="card p-6 bg-red-50 border border-red-100">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm font-medium text-red-600">Total Pengeluaran</p>
                    <h2 class="text-2xl font-bold text-red-700">Rp <?= number_format($totals['expense'], 2, ',', '.') ?></h2>
                </div>
                <div class="bg-red-100 p-3 rounded-full">
                    <i class="fas fa-arrow-up text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <?php if ($isAdmin): ?>
<!-- Admin Form -->
<div class="card p-6">
    <div class="flex items-center mb-4">
        <div class="bg-[#e6f2eb] p-2 rounded-full mr-3">
            <i class="fas fa-plus-circle text-[#2ca6a3]"></i>
        </div>
        <h3 class="text-lg font-semibold">Tambah Transaksi</h3>
    </div>

    <form method="POST" class="space-y-4" id="adminTransactionForm">
        <!-- Nama -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
            <input type="text" name="nama" placeholder="Nama Pengguna" class="form-control w-full" required>
        </div>

        <!-- Jenis Transaksi -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Transaksi</label>
            <select name="jenis" id="jenisTransaksi" class="form-control w-full" required>
                <option value="">Pilih Jenis</option>
                <option value="pemasukan">Pemasukan</option>
                <option value="pengeluaran">Pengeluaran</option>
            </select>
        </div>

        <!-- Deskripsi -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <input type="text" name="deskripsi" placeholder="Contoh: Donasi Jumat / Pembelian Perlengkapan"
                   class="form-control w-full" required>
        </div>

        <!-- Jumlah -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah (Rp)</label>
            <input type="number" name="jumlah" placeholder="500000" class="form-control w-full" required min="1">
        </div>

        <!-- Metode Pembayaran -->
        <div id="adminPaymentMethod" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
            <select name="payment_method" id="adminPaymentMethodSelect" class="form-control w-full">
                <option value="Tunai">Tunai</option>
                <?php foreach ($paymentMethods as $method): ?>
                    <option value="<?= htmlspecialchars($method['name']) ?>">
                        <?= htmlspecialchars($method['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Nomor Referensi -->
        <div id="adminPaymentReference" style="display: none;">
            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening Tujuan</label>
            <input type="text" name="payment_reference" id="adminPaymentReferenceInput"
                   placeholder="No. Rekening / Telephone" class="form-control w-full">
        </div>

        <!-- Submit Button -->
        <button type="submit" name="simpan_transaksi" class="btn-primary w-full py-3 rounded-lg font-medium">
            <i class="fas fa-save mr-2"></i> Simpan Transaksi
        </button>
    </form>
</div>
<?php endif; ?>

        <?php if (!$isAdmin): ?>
        <!-- User Donation Form -->
        <div class="card p-6">
            <div class="flex items-center mb-4">
                <div class="bg-green-100 p-2 rounded-full mr-3">
                    <i class="fas fa-hand-holding-heart text-green-500"></i>
                </div>
                <h3 class="text-lg font-semibold">Form Donasi</h3>
            </div>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="error-message mb-4">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $_SESSION['error'] ?>
                </div>
            <?php unset($_SESSION['error']); endif; ?>
            <?php $form_data = $_SESSION['form_data'] ?? []; ?>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="jenis" value="pemasukan">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Asli / Samaran<span class="text-red-500">*</span></label>
                    <input type="text" name="nama" placeholder="Nama Pengguna" class="form-control w-full" required
                           value="<?= htmlspecialchars($form_data['nama'] ?? '') ?>">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Untuk Program <span class="text-red-500">*</span></label>
                    <input type="text" name="deskripsi" placeholder="Contoh: Pembangunan Masjid" class="form-control w-full" required
                           value="<?= htmlspecialchars($form_data['deskripsi'] ?? '') ?>">
                </div>

                <!-- Jumlah Donasi -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Donasi (Rp) <span class="text-red-500">*</span></label>
                    <input type="number" name="jumlah" min="1" step="any" placeholder="50000" class="form-control w-full" required
                        value="<?= htmlspecialchars($_SESSION['form_data']['jumlah'] ?? '') ?>">
                </div>
                <!-- Metode Pembayaran -->
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran <span class="text-red-500">*</span></label>
    <select name="payment_method" id="userPaymentMethod" class="form-control w-full" required>
        <option value="">Pilih Metode Pembayaran</option>
        <?php foreach ($paymentMethods as $method): ?>
            <option value="<?= htmlspecialchars($method['name']) ?>"
                <?= (isset($form_data['payment_method']) && $form_data['payment_method'] === $method['name']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($method['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>

<!-- Nomor Referensi -->
<div id="paymentReferenceField" class="hidden">
    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening Anda</label>
    <input type="text" name="payment_reference" placeholder="No. Rekening / Telepon" class="form-control w-full"
           value="<?= htmlspecialchars($form_data['payment_reference'] ?? '') ?>">
    <p class="text-xs text-gray-500 mt-1">Silakan ditransfer lewat 023417654980 / 089661832244</p>
</div>
                <button type="submit" name="simpan_donasi" class="btn-success w-full py-3 rounded-lg font-medium flex items-center justify-center">
                    <i class="fas fa-donate mr-2"></i> Donasi Sekarang
                </button>
            </form>
            <?php unset($_SESSION['form_data']); ?>
        </div>
        <?php endif; ?>

        <!-- Filter Section -->
        <div class="card p-6">
            <div class="flex items-center mb-4">
                <div class="bg-purple-100 p-2 rounded-full mr-3">
                    <i class="fas fa-filter text-purple-500"></i>
                </div>
                <h3 class="text-lg font-semibold">Filter Transaksi</h3>
            </div>
            <form method="GET" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Periode</label>
                    <input type="date" id="periode" name="periode" class="form-control w-full">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Transaksi</label>
                    <select name="jenis" id="jenis" class="form-control w-full">
                        <option value="">Semua Jenis</option>
                        <option value="pemasukan">Pemasukan</option>
                        <option value="pengeluaran">Pengeluaran</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program/Kegiatan</label>
                    <input type="text" name="program" placeholder="Cari program..." class="form-control w-full">
                </div>
                <button type="submit" class="bg-purple-500 text-white w-full py-3 rounded-lg font-medium hover:bg-purple-600 transition flex items-center justify-center">
                    <i class="fas fa-search mr-2"></i> Terapkan Filter
                </button>
            </form>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="card p-6 mb-8">
        <div class="flex items-center mb-4">
            <div class="bg-orange-100 p-2 rounded-full mr-3">
                <i class="fas fa-chart-line text-orange-500"></i>
            </div>
            <h3 class="text-lg font-semibold">Grafik Keuangan</h3>
        </div>
        <canvas id="chartKeuangan" height="300"></canvas>
    </div>

    <!-- Transaction History -->
    <div class="card p-6">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center">
            <div class="bg-indigo-100 p-2 rounded-full mr-3">
                <i class="fas fa-history text-indigo-500"></i>
            </div>
            <h3 class="text-lg font-semibold">Riwayat Transaksi</h3>
        </div>
        <div class="text-sm text-gray-500">
            Total: <?= count($transactions) ?> transaksi
        </div>
    </div>
    <div class="table-responsive">
        <table class="w-full">
            <thead>
                <tr>
                    <th class="py-3 px-4 text-left">Tanggal</th>
                    <th class="py-3 px-4 text-left">Nama</th>
                    <th class="py-3 px-4 text-left">Jenis</th>
                    <th class="py-3 px-4 text-left">Keterangan</th>
                    <th class="py-3 px-4 text-left">Metode</th>
                    <th class="py-3 px-4 text-right">Jumlah</th>
                    <?php if ($isAdmin): ?>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="<?= $isAdmin ? 7 : 6 ?>" class="py-4 px-4 text-center text-gray-500">
                            Belum ada transaksi
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): ?>
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="py-3 px-4"><?= date('d/m/Y H:i', strtotime($t['tanggal'])) ?></td>
                            <td class="py-3 px-4"><?= htmlspecialchars($t['nama']) ?></td>
                            <td class="py-3 px-4">
                                <span class="badge <?= $t['jenis'] === 'pemasukan' ? 'badge-success' : 'badge-danger' ?>">
                                    <?= ucfirst($t['jenis']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4"><?= htmlspecialchars($t['deskripsi']) ?></td>
                            <td class="py-3 px-4">
                                <?php if ($t['payment_method']): ?>
                                    <span class="payment-method-badge">
                                        <?= htmlspecialchars($t['payment_method']) ?>
                                    </span>
                                    <?php if ($_SESSION['role'] === 'admin' && $t['payment_reference']): ?>
                                        <span class="payment-reference">
                                            <?= htmlspecialchars($t['payment_reference']) ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-right">
                                Rp <?= number_format($t['jumlah'], 0, ',', '.') ?>
                            </td>
                            <?php if ($isAdmin): ?>
                                <td class="py-3 px-4 text-center">
                                    <form method="POST" class="inline-block" id="editForm<?= $t['id'] ?>">
                                        <input type="hidden" name="edit_id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="text-blue-500 hover:text-blue-700 p-2 rounded-full hover:bg-blue-50">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </form>
                                    <form method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus transaksi ini?')">
                                        <input type="hidden" name="delete_id" value="<?= $t['id'] ?>">
                                        <button type="submit" class="text-red-500 hover:text-red-700 p-2 rounded-full hover:bg-red-50">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>

<!-- Edit Modal -->
<?php if ($isAdmin && $edit_data instanceof Transaction): ?>
    <div class="modal active" id="editModal">
        <div class="modal-content bg-white p-6 rounded-lg shadow-xl w-full max-w-md mx-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">Edit Transaksi</h3>
                <button type="button" onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-xl p-2">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="edit_id" value="<?= $edit_data->getId() ?>">                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($edit_data->getNama()) ?>" class="form-control w-full" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis</label>
                    <select name="jenis" id="editJenisTransaksi" class="form-control w-full" required>
                        <option value="pemasukan" <?= $edit_data->getJenis() === 'pemasukan' ? 'selected' : '' ?>>Pemasukan</option>
                        <option value="pengeluaran" <?= $edit_data->getJenis() === 'pengeluaran' ? 'selected' : '' ?>>Pengeluaran</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                    <input type="text" name="deskripsi" value="<?= htmlspecialchars($edit_data->getDeskripsi()) ?>" class="form-control w-full" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                    <input type="number" name="jumlah" step="0.01" value="<?= htmlspecialchars($edit_data->getJumlah()) ?>" class="form-control w-full" required>
                </div>

                <div id="editPaymentMethod">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Metode Pembayaran</label>
                    <select name="payment_method" id="editPaymentMethodSelect" class="form-control w-full">
                        <option value="Tunai" <?= $edit_data->getPaymentMethod() === 'Tunai' ? 'selected' : '' ?>>Tunai</option>
                        <?php foreach ($paymentMethods as $method): ?>
                            <option value="<?= htmlspecialchars($method['name']) ?>"
                                <?= $edit_data->getPaymentMethod() === $method['name'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($method['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>                </div>

                <div id="editPaymentReference" class="<?= $edit_data->getPaymentMethod() !== 'Tunai' ? 'show' : 'hidden' ?>">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Referensi</label>
                    <input type="text" name="payment_reference" id="editPaymentReferenceInput"
                           value="<?= htmlspecialchars($edit_data->getPaymentReference() ?? '') ?>" 
                           class="form-control w-full">
                </div>                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Batal
                    </button>
                    <button type="submit" name="simpan_edit" class="px-4 py-2 bg-[#2ca6a3] text-white rounded-lg hover:bg-[#248583]">
                        <i class="fas fa-save mr-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>


    <footer class="bg-[#2ca6a3] text-white py-12 px-4 mt-12">
        <div class="max-w-6xl mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Logo dan Deskripsi -->
                <div class="col-span-1">
                    <div class="flex items-center mb-4">
                        <i class="fas fa-mosque text-2xl mr-2"></i>
                        <h2 class="text-xl font-bold">JogoAmal Digital</h2>
                    </div>
                    <p class="text-gray-100 mb-4">Platform manajemen keuangan masjid berbasis digital yang transparan dan akuntabel.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-100 hover:text-white transition">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-100 hover:text-white transition">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-100 hover:text-white transition">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="text-gray-100 hover:text-white transition">
                            <i class="fab fa-youtube"></i>
                        </a>
                    </div>
                </div>

                <!-- Kontak -->
                <div class="col-span-1">
                    <h3 class="text-lg font-semibold mb-4 border-b border-[#ffffff] pb-2">Hubungi Kami</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-3 text-[#ffffff]"></i>
                            <a href="https://maps.google.com/?q=Jl.+Jogokariyan+No.+36 ,+Mantrijeron,+Yogyakarta" 
                       target="_blank" class="text-gray-100 hover:underline">
                        Jl. Jogokariyan No. 36, Mantrijeron, Yogyakarta
                    </a>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-phone-alt mt-1 mr-3 text-[#ffffff]"></i>
                            <a href="https://wa.me/6289661832244 " target="_blank" class="text-gray-100 hover:underline">
                        +6289661832244
                    </a>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-envelope mt-1 mr-3 text-[#ffffff]"></i>
                            <span>info@jogoamaldigital</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-[#ffffff] mt-8 pt-8 text-center text-gray-100">
                <p>&copy; <?php echo date('Y'); ?> JogoAmal Digital. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <?php if (isset($_SESSION['success'])) : ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                html: '<?= addslashes($_SESSION['success']) ?>',
                confirmButtonText: 'OK'
            }).then(() => {
                window.location.href = 'dashboard.php';
            });
        });
    </script>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

</body>
<script>
    // Inisialisasi chart hanya jika elemen tersedia
    const ctx = document.getElementById('chartKeuangan');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_map(fn($t) => date('d M', strtotime($t['tanggal'])), $transactions)) ?>,
                datasets: [{
                    label: 'Transaksi Keuangan',
                    data: <?= json_encode(array_map(fn($t) => $t['jenis'] === 'pemasukan' ? $t['jumlah'] : -$t['jumlah'], $transactions)) ?>,
                    backgroundColor: <?= json_encode(array_map(fn($t) => $t['jenis'] === 'pemasukan' ? '#10b981' : '#ef4444', $transactions)) ?>,
                    borderColor: <?= json_encode(array_map(fn($t) => $t['jenis'] === 'pemasukan' ? '#059669' : '#dc2626', $transactions)) ?>,
                    borderWidth: 1,
                    borderRadius: 4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + Math.abs(context.raw).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + Math.abs(value).toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    }

    document.getElementById('closeModalBtn')?.addEventListener('click', function () {
    if (modal) {
        modal.classList.remove('active');
        window.location.href = 'dashboard.php';
    }
});    document.addEventListener('DOMContentLoaded', function () {
    const paymentMethodSelect = document.getElementById('userPaymentMethod');
    const paymentReferenceField = document.getElementById('paymentReferenceField');

    if (paymentMethodSelect && paymentReferenceField) {
        function handlePaymentMethodChange() {
            const selectedMethod = paymentMethodSelect.value;

            if (selectedMethod && selectedMethod !== 'Tunai') {
                // Tampilkan field nomor referensi
                paymentReferenceField.classList.remove('hidden');
                paymentReferenceField.classList.add('show');

                // Jadikan wajib diisi
                const input = paymentReferenceField.querySelector('input');
                if (input) {
                    input.setAttribute('required', 'required');
                }            } else {
                // Sembunyikan field nomor referensi
                paymentReferenceField.classList.remove('show');
                paymentReferenceField.classList.add('hidden');

                // Hilangkan required dan kosongkan nilai
                const input = paymentReferenceField.querySelector('input');
                if (input) {
                    input.removeAttribute('required');
                    input.value = ''; // Kosongkan nilai
                }
            }
        }

        // Jalankan saat halaman dimuat
        handlePaymentMethodChange();

        // Event listener untuk perubahan metode pembayaran
        paymentMethodSelect.addEventListener('change', handlePaymentMethodChange);
    }
});

    // Fungsi Modal Edit
    document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('editModal');
    const closeBtn = modal?.querySelector('button[onclick="closeModal()"]');
    const editForm = modal?.querySelector('form');

    if (!modal) {
        console.warn("Modal dengan ID 'editModal' tidak ditemukan.");
        return;
    }

    window.closeModal = function () {
    if (modal) {
        modal.classList.remove('active');
        window.location.href = 'dashboard.php'; // Bersihkan POST
    }
}

    // Tutup modal saat klik di luar
    window.onclick = function(event) {
        if (event.target === modal) {
            closeModal();
        }
    };

    // Tutup modal saat tekan Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });

    // Validasi form
    if (editForm) {
        editForm.addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
        });
    }

    // Logika Metode Pembayaran
    const editJenisTransaksi = document.getElementById('editJenisTransaksi');
    const editPaymentMethod = document.getElementById('editPaymentMethod');
    const editPaymentReference = document.getElementById('editPaymentReference');
    const editPaymentMethodSelect = document.getElementById('editPaymentMethodSelect');
    const editPaymentReferenceInput = document.getElementById('editPaymentReferenceInput');    if (editJenisTransaksi && editPaymentMethod && editPaymentReference) {
        function checkPaymentMethod() {
            const jenis = editJenisTransaksi.value;
            const method = editPaymentMethodSelect.value;
            
            // Tampilkan metode pembayaran untuk semua jenis transaksi
            editPaymentMethod.style.display = 'block';
            
            // Atur visibilitas input nomor referensi
            if (method === 'Tunai') {
                editPaymentReference.classList.add('hidden');
                editPaymentReference.classList.remove('show');
                if (editPaymentReferenceInput) {
                    editPaymentReferenceInput.removeAttribute('required');
                    editPaymentReferenceInput.value = ''; // Reset nilai saat metode Tunai
                }
            } else {
                editPaymentReference.classList.remove('hidden');
                editPaymentReference.classList.add('show');
                if (editPaymentReferenceInput) {
                    editPaymentReferenceInput.setAttribute('required', 'required');
                }
            }

            // Jika jenis pengeluaran, metode pembayaran wajib diisi
            if (jenis === 'pengeluaran') {
                editPaymentMethodSelect.setAttribute('required', 'required');
            } else {
                editPaymentMethodSelect.removeAttribute('required');
            }
        }

        // Event listener untuk perubahan jenis transaksi
        editJenisTransaksi.addEventListener('change', checkPaymentMethod);

        // Event listener untuk perubahan metode pembayaran
        editPaymentMethodSelect.addEventListener('change', checkPaymentMethod);

        // Jalankan pengecekan saat halaman dimuat
        checkPaymentMethod();
    }    // Modal handling functions
    function closeModal() {
        const modal = document.getElementById('editModal');
        if (modal) {
            modal.classList.remove('active');
            window.location.href = 'dashboard.php';
        }
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    // Close modal with Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeModal();
        }
    });    // Logika untuk form tambah transaksi admin
    const jenisTransaksi = document.getElementById('jenisTransaksi');
    const adminPaymentMethod = document.getElementById('adminPaymentMethod');
    const adminPaymentReference = document.getElementById('adminPaymentReference');
    const adminPaymentMethodSelect = document.getElementById('adminPaymentMethodSelect');
    const adminPaymentReferenceInput = document.getElementById('adminPaymentReferenceInput');
    const adminForm = document.getElementById('adminTransactionForm');

    if (jenisTransaksi && adminPaymentMethod && adminPaymentReference) {
        function checkAdminPaymentMethod() {
            const jenis = jenisTransaksi.value;
            const method = adminPaymentMethodSelect.value;

            // Tampilkan metode pembayaran untuk semua jenis transaksi
            adminPaymentMethod.style.display = 'block';

            // Atur required berdasarkan jenis transaksi
            if (jenis === 'pengeluaran') {
                adminPaymentMethodSelect.setAttribute('required', 'required');
            } else {
                adminPaymentMethodSelect.removeAttribute('required');
                // Biarkan nilai metode pembayaran jika sudah dipilih
            }

            // Tampilkan nomor referensi jika metode non-tunai dipilih
            if (method && method !== 'Tunai') {
                adminPaymentReference.style.display = 'block';
                // Hanya required untuk pengeluaran
                if (jenis === 'pengeluaran') {
                    adminPaymentReferenceInput.setAttribute('required', 'required');
                } else {
                    adminPaymentReferenceInput.removeAttribute('required');
                }
            } else {
                adminPaymentReference.style.display = 'none';
                adminPaymentReferenceInput.removeAttribute('required');
                adminPaymentReferenceInput.value = '';
            }
        }

        // Event listener untuk form submit
        if (adminForm) {
            adminForm.addEventListener('submit', function(e) {
                const jenis = jenisTransaksi.value;
                const method = adminPaymentMethodSelect.value;

                if (jenis === 'pengeluaran') {
                    if (!method) {
                        e.preventDefault();
                        alert('Mohon pilih metode pembayaran untuk pengeluaran');
                        return;
                    }
                    if (method !== 'Tunai' && !adminPaymentReferenceInput.value) {
                        e.preventDefault();
                        alert('Mohon isi nomor referensi untuk pembayaran pengeluaran non-tunai');
                        return;
                    }
                }
                // Tidak perlu validasi tambahan untuk pemasukan
            });
        }

        // Event listeners untuk perubahan nilai
        jenisTransaksi.addEventListener('change', checkAdminPaymentMethod);
        adminPaymentMethodSelect.addEventListener('change', checkAdminPaymentMethod);

        // Initial check
        checkAdminPaymentMethod();
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const jenisTransaksi = document.getElementById('jenisTransaksi');
    const adminPaymentMethod = document.getElementById('adminPaymentMethod');
    const adminPaymentReference = document.getElementById('adminPaymentReference');
    const adminPaymentMethodSelect = document.getElementById('adminPaymentMethodSelect');
    const adminPaymentReferenceInput = document.getElementById('adminPaymentReferenceInput');    function checkAdminPaymentMethod() {
            const jenis = jenisTransaksi.value;
            const method = adminPaymentMethodSelect.value;

            // Always show payment method for all transaction types
            adminPaymentMethod.style.display = 'block';
            
            if (jenis === 'pemasukan') {
                // Reset ke Tunai dan sembunyikan opsi lain untuk pemasukan
                adminPaymentMethodSelect.value = 'Tunai';
                Array.from(adminPaymentMethodSelect.options).forEach(option => {
                    if (option.value !== 'Tunai') {
                        option.style.display = 'none';
                    }
                });
                adminPaymentMethodSelect.removeAttribute('required');
                adminPaymentReference.style.display = 'none';
                adminPaymentReferenceInput.removeAttribute('required');
                adminPaymentReferenceInput.value = '';
            } else if (jenis === 'pengeluaran') {
                // Tampilkan semua opsi untuk pengeluaran
                Array.from(adminPaymentMethodSelect.options).forEach(option => {
                    option.style.display = 'block';
                });
                adminPaymentMethodSelect.setAttribute('required', 'required');
                
                // Show reference number field for non-cash payments
                if (method && method !== 'Tunai') {
                    adminPaymentReference.style.display = 'block';
                    adminPaymentReferenceInput.setAttribute('required', 'required');
                } else {
                    adminPaymentReference.style.display = 'none';
                    adminPaymentReferenceInput.removeAttribute('required');
                    adminPaymentReferenceInput.value = '';
                }
            } else {
                // Default state (no selection)
                adminPaymentMethodSelect.removeAttribute('required');
                adminPaymentReference.style.display = 'none';
                adminPaymentReferenceInput.removeAttribute('required');
                adminPaymentReferenceInput.value = '';
            }
        }

    // Event listener untuk perubahan jenis transaksi
    jenisTransaksi.addEventListener('change', function () {
        checkAdminPaymentMethod();
    });

    // Event listener untuk perubahan metode pembayaran
    adminPaymentMethodSelect.addEventListener('change', function () {
        checkAdminPaymentMethod();
    });

    // Trigger awal saat halaman dimuat
    checkAdminPaymentMethod();
});
</script>
</html>