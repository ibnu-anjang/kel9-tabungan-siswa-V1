<?php
include '../koneksi.php';

// Check if user is authenticated (simple session check)
if (session_status() === PHP_SESSION_NONE) {
    if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
}
if (!isset($_SESSION['admin_logged_in'])) {
    // Simple password protection for demo
    if ($_POST['password'] === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
    } elseif ($_POST) {
        $error = "Password salah!";
    }

    if (!$_SESSION['admin_logged_in']) {
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Admin Login - Tabungan Siswa</title>
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
            <link rel="stylesheet" href="../assets/css/style.css">
        </head>
        <body>
            <div style="max-width: 400px; margin: 100px auto; padding: 40px; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                <h2><i class="fas fa-shield-alt"></i> Admin Login</h2>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="post">
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>

                <div class="alert alert-info mt-3">
                    <small><i class="fas fa-info-circle"></i> Default password: <strong>admin123</strong></small>
                </div>

                <p class="mt-3 text-center">
                    <a href="../index.php"><i class="fas fa-arrow-left"></i> Kembali ke Beranda</a>
                </p>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Get database statistics
$total_records = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM transaksi"))['count'];
$total_masuk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as total FROM transaksi WHERE jenis_transaksi='masuk'"))['total'] ?? 0;
$total_keluar = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(nominal) as total FROM transaksi WHERE jenis_transaksi='keluar'"))['total'] ?? 0;
$total_saldo = $total_masuk - $total_keluar;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Tabungan Siswa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .admin-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .admin-card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s;
        }
        .admin-card:hover {
            transform: translateY(-5px);
        }
        .admin-card .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .admin-card h3 {
            margin: 10px 0;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        .logout-btn {
            background: #dc3545;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 0;
        }
        .logout-btn:hover {
            background: #c82333;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="header">
            <h1><i class="fas fa-cogs"></i> Admin Panel</h1>
            <div class="breadcrumb">
                <a href="../index.php"><i class="fas fa-home"></i> Beranda</a>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-current">Admin Panel</span>
            </div>
            <a href="?logout=1" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Statistics Overview -->
        <h2><i class="fas fa-chart-line"></i> Statistik Database</h2>
        <div class="admin-grid">
            <div class="admin-card stats-card">
                <div class="icon"><i class="fas fa-database"></i></div>
                <h3><?= number_format($total_records) ?></h3>
                <p>Total Transaksi</p>
            </div>
            <div class="admin-card stats-card">
                <div class="icon"><i class="fas fa-arrow-down" style="color: #28a745;"></i></div>
                <h3>Rp <?= number_format($total_masuk, 2) ?></h3>
                <p>Total Masuk</p>
            </div>
            <div class="admin-card stats-card">
                <div class="icon"><i class="fas fa-arrow-up" style="color: #dc3545;"></i></div>
                <h3>Rp <?= number_format($total_keluar, 2) ?></h3>
                <p>Total Keluar</p>
            </div>
            <div class="admin-card stats-card">
                <div class="icon"><i class="fas fa-wallet"></i></div>
                <h3>Rp <?= number_format($total_saldo, 2) ?></h3>
                <p>Saldo Akhir</p>
            </div>
        </div>

        <!-- Admin Tools -->
        <h2><i class="fas fa-tools"></i> Alat Administrasi</h2>
        <div class="admin-grid">
            <div class="admin-card">
                <div class="icon" style="color: #28a745;"><i class="fas fa-play"></i></div>
                <h3>Database Setup</h3>
                <p>Initialize or reset database structure</p>
                <a href="../utils/setup_database.php" class="btn btn-success">
                    <i class="fas fa-cog"></i> Buka
                </a>
            </div>

            <div class="admin-card">
                <div class="icon" style="color: #6f42c1;"><i class="fas fa-plus"></i></div>
                <h3>Generate Test Data</h3>
                <p>Create sample transaction data for testing</p>
                <a href="../utils/generate_test_data.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Buka
                </a>
            </div>

            <div class="admin-card">
                <div class="icon" style="color: #e83e8c;"><i class="fas fa-trash"></i></div>
                <h3>Cleanup Test Data</h3>
                <p>Remove sample data while preserving real data</p>
                <a href="../utils/cleanup_test_data.php" class="btn btn-danger">
                    <i class="fas fa-broom"></i> Buka
                </a>
            </div>
        </div>

        <!-- Quick Access -->
        <h2><i class="fas fa-rocket"></i> Akses Cepat</h2>
        <div class="admin-grid">
            <div class="admin-card">
                <div class="icon" style="color: #fd7e14;"><i class="fas fa-home"></i></div>
                <h3>Halaman Utama</h3>
                <p>Kembali ke aplikasi tabungan siswa</p>
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Buka
                </a>
            </div>
        </div>

        <div style="text-align: center; margin: 40px 0; color: #666;">
            <p><i class="fas fa-info-circle"></i> Admin Panel - Sistem Tabungan Siswa Team 9</p>
            <p><small>Default password: <strong>admin123</strong> (ubah untuk production)</small></p>
        </div>
    </div>

    <?php
    if (isset($_GET['logout'])) {
        unset($_SESSION['admin_logged_in']);
        header('Location: index.php');
        exit;
    }
    ?>
</body>
</html>