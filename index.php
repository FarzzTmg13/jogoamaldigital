<?php
// file dashboard.php
session_start();
include 'db_config.php'; // Pastikan untuk menyertakan file config database Anda
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hero Section</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Base Styling */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f1f5f9;
}

/* Hero Section Styles */
.hero {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 50px 20px;
    background-color: white;
    color: #333;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.hero-content {
    position: relative;
    z-index: 2;
}

.hero h1 {
    font-size: 2.5rem;
    margin-bottom: 20px;
    font-weight: 700;
    line-height: 1.2;
    color: #2ca6a3;
}

.hero p {
    font-size: 1.2rem;
    margin-bottom: 30px;
    opacity: 0.9;
    line-height: 1.6;
    color: #555;
}

.cta-button {
    background-color: #2ca6a3;
    color: white;
    padding: 15px 30px;
    font-size: 1rem;
    border: none;
    border-radius: 50px;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    font-weight: 600;
    display: inline-block;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.cta-button:hover {
    background-color: #248583;
    transform: translateY(-3px);
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
}

.hero-image {
    width: 100%;
    max-width: 550px;
    height: auto;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 20px 25px rgba(0, 0, 0, 0.1);
    position: relative;
    margin-top: 30px;
}

.hero-image img {
    width: 100%;
    height: auto;
    display: block;
    transition: transform 0.5s ease;
}

.hero-image:hover img {
    transform: scale(1.05);
}

.hero-image::before,
.hero-image::after,
.hero-image .decorative-circle {
    content: '';
    position: absolute;
    z-index: -1;
}

.hero-image::before {
    top: -20px;
    left: -20px;
    width: 100%;
    height: 100%;
    border: 2px dashed rgba(52, 211, 153, 0.3);
    border-radius: 15px;
}

.hero-image::after {
    bottom: -15px;
    left: -15px;
    width: 80px;
    height: 80px;
    background-color: #2ca6a3;
    opacity: 0.2;
    border-radius: 12px;
}

.hero-image .decorative-circle {
    top: -15px;
    right: -15px;
    width: 60px;
    height: 60px;
    background-color: #248583;
    opacity: 0.2;
    border-radius: 50%;
}

@media (min-width: 768px) {
    .hero {
        flex-direction: row;
        min-height: 500px;
        padding: 0 50px;
    }

    .hero-content {
        text-align: left;
        max-width: 600px;
        margin-right: 40px;
    }
}

@media (max-width: 768px) {
    .hero-image {
        max-width: 400px;
    }

    .hero-image::before,
    .hero-image::after,
    .hero-image .decorative-circle {
        display: none; /* Hide decorative elements on mobile */
    }
}

@media (max-width: 640px) {
    .hero h1 {
        font-size: 1.75rem;
    }

    .hero p {
        font-size: 1rem;
    }

    .cta-button {
        padding: 12px 24px;
        font-size: 0.9rem;
    }

    .section-title {
        font-size: 1.5rem;
    }
}

/* Partners Section */
.partners-section {
    padding: 60px 20px;
    background-color: #f8fafc;
    text-align: center;
}

.section-title {
    font-size: 2rem;
    font-weight: 700;
    color: #2ca6a3;
    margin-bottom: 40px;
}

.partners-container {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
}

.partner-card {
    background-color: white;
    border-radius: 12px;
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
    padding: 25px;
    width: 280px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-align: center;
    position: relative;
}

.partner-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
}

.partner-avatar {
    width: 100px;
    height: 100px;
    margin: 0 auto 15px;
    overflow: hidden;
    border-radius: 50%;
    border: 3px solid #2ca6a3;
}

.partner-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.partner-card:hover .partner-avatar img {
    transform: scale(1.1);
}

.partner-name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 5px;
}

.partner-role {
    font-size: 0.95rem;
    color: #64748b;
    margin-bottom: 10px;
    font-style: italic;
}

.partner-email {
    font-size: 0.9rem;
    color: #2ca6a3;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}

.partner-email a {
    color: #2ca6a3;
    text-decoration: none;
    transition: color 0.3s ease;
}

.partner-email a:hover {
    color: #248583;
}

/* Auth Buttons */
.auth-buttons {
    display: flex;
    gap: 10px;
}

.auth-button {
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.login-btn {
    background-color: #f1f5f9;
    color: #334155;
}

.login-btn:hover {
    background-color: #e2e8f0;
}

.register-btn {
    background-color: #2ca6a3;
    color: white;
}

.register-btn:hover {
    background-color: #248583;
}       


/* Technology Stack Section Styles */
.tech-stack-section {
    padding: 60px 20px;
    text-align: center;
}

.tech-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.tech-card {
    background-color: #f8fafc;
    border-radius: 16px;
    padding: 30px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.tech-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.tech-icon {
    font-size: 3.5rem;
    margin-bottom: 20px;
}

.tech-name {
    font-size: 1.5rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 15px;
}

.tech-description {
    font-size: 0.95rem;
    color: #64748b;
    line-height: 1.6;
}

/* Responsive Design */
@media (max-width: 768px) {
    .tech-container {
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        padding: 10px;
    }

    .tech-card {
        padding: 20px;
    }

    .tech-icon {
        font-size: 2.5rem;
    }

    .tech-name {
        font-size: 1.25rem;
    }

    .tech-description {
        font-size: 0.9rem;
    }
}

@media (max-width: 480px) {
    .tech-container {
        grid-template-columns: 1fr;
    }

    .tech-card {
        padding: 15px;
    }
}


    </style>
</head>
<body>
    <!-- Header Section - Matching the first example -->
    <header class="flex flex-col md:flex-row justify-between items-center mb-8 p-4 bg-white shadow-sm">
        <div class="mb-4 md:mb-0">
            <a href="index.php" class="flex items-center">
                <h1 class="text-3xl font-bold text-gray-800">
                    <i class="fas fa-mosque text-[#2ca6a3] mr-2"></i> JogoAmal Digital
                </h1>
            </a>
            <p class="text-gray-600">Manajemen keuangan masjid yang transparan</p>
        </div>
        
        <div class="flex items-center space-x-4">
            <?php if (isset($_SESSION['username'])): ?>
                <!-- Tampilkan info user jika sudah login -->
                <div class="text-right">
                    <p class="text-sm text-gray-500">Halo,</p>
                    <p class="font-medium text-blue-600"><?= htmlspecialchars($_SESSION['username']) ?></p>
                </div>
                <a href="logout.php" class="flex items-center px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition">
                    <i class="fas fa-sign-out-alt mr-2"></i> <span class="hidden md:inline">Logout</span>
                </a>
            <?php else: ?>
                <!-- Tampilkan tombol login/register jika belum login -->
                <div class="auth-buttons">
                    <a href="login.php" class="auth-button login-btn">
                        <i class="fas fa-sign-in-alt mr-2"></i> Login
                    </a>
                    <a href="signup.php" class="auth-button register-btn">
                        <i class="fas fa-user-plus mr-2"></i> Daftar
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <!-- Content -->
        <div class="hero-content">
            <h1>Berikan Donasi Anda untuk Masa Depan yang Lebih Baik</h1>
            <p>
                Bergabunglah dengan kami dalam usaha membangun dan memajukan Masjid Jogokariyan 
                melalui donasi Anda. Setiap kontribusi akan sangat berarti bagi perkembangan masjid kita.
            </p>
            <a href="dashboard.php" class="cta-button">
                <i class="fas fa-tachometer-alt mr-2"></i> Lihat Dashboard
            </a>
        </div>
        
        <!-- Image Container -->
        <div class="hero-image">
            <img src="jogokariyan.png" alt="Masjid dengan arsitektur modern">
            <div class="decorative-circle"></div>
        </div>
    </section>

    <!-- Our Partner Section -->
<section class="partners-section">
    <h2 class="section-title">Tim Kami</h2>
    <div class="partners-container">
        <!-- Partner 1 -->
        <div class="partner-card">
            <div class="partner-avatar">
                <img src="fairuz.jpg" alt="Gahyaka Ararya Fairuz">
            </div>
            <h3 class="partner-name">Gahyaka Ararya F</h3>
            <p class="partner-role">Perancang Konsep</p>
            <p class="partner-email">
                <i class="fas fa-envelope"></i> 
                <a href="mailto:24106050006@student.uin-suka.ac.id?subject=Kolaborasi Proyek&body=Halo Fairuz, saya ingin berdiskusi tentang...">Email</a>
            </p>
        </div>

        <!-- Partner 2 -->
        <div class="partner-card">
            <div class="partner-avatar">
                <img src="fariz.jpg" alt="Fariz Husain Albar">
            </div>
            <h3 class="partner-name">Fariz Husain Albar</h3>
            <p class="partner-role">Fullstack Developer</p>
            <p class="partner-email">
                <i class="fas fa-envelope"></i> 
                <a href="mailto:24106050011@student.uin-suka.ac.id?subject=Kolaborasi Proyek&body=Halo Fariz, saya ingin berdiskusi tentang...">Email</a>
            </p>
        </div>

        <!-- Partner 3 -->
        <div class="partner-card">
            <div class="partner-avatar">
                <img src="faiz.jpg" alt="Faiz Satria Ahimsa">
            </div>
            <h3 class="partner-name">Faiz Satria Ahimsa</h3>
            <p class="partner-role">Pemikir Kreatif</p>
            <p class="partner-email">
                <i class="fas fa-envelope"></i> 
                <a href="mailto:24106050019@student.uin-suka.ac.id?subject=Kolaborasi Proyek&body=Halo Faiz, saya ingin berdiskusi tentang...">Email</a>
            </p>
        </div>
    </div>
</section>

<!-- Technology Stack Section -->
<section class="tech-stack-section bg-white py-16 px-4">
    <h2 class="section-title">Teknologi Yang Kami Gunakan</h2>
    <div class="tech-container">
        <!-- HTML -->
        <div class="tech-card">
            <div class="tech-icon">
                <i class="fab fa-html5 text-[#e34f26]"></i>
            </div>
            <h3 class="tech-name">HTML5</h3>
            <p class="tech-description">
                Struktur website modern dengan semantic markup untuk aksesibilitas optimal
            </p>
        </div>

        <!-- CSS -->
        <div class="tech-card">
            <div class="tech-icon">
                <i class="fab fa-css3-alt text-[#2965f1]"></i>
            </div>
            <h3 class="tech-name">CSS3</h3>
            <p class="tech-description">
                Styling responsif dengan Tailwind CSS untuk tampilan yang menarik
            </p>
        </div>

        <!-- JavaScript -->
        <div class="tech-card">
            <div class="tech-icon">
                <i class="fab fa-js text-[#f7df1e]"></i>
            </div>
            <h3 class="tech-name">JavaScript</h3>
            <p class="tech-description">
                Interaktivitas dinamis dan manipulasi DOM untuk pengalaman pengguna yang lebih baik
            </p>
        </div>

        <!-- PHP -->
        <div class="tech-card">
            <div class="tech-icon">
                <i class="fab fa-php text-[#777bb4]"></i>
            </div>
            <h3 class="tech-name">PHP</h3>
            <p class="tech-description">
                Backend processing dengan PHP OOP untuk manajemen data yang efisien
            </p>
        </div>
    </div>
</section>


    
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

    <!-- Font Awesome untuk ikon -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>