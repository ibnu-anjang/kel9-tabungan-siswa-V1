<?php
/**
 * Script Setup Database untuk Sistem Tabungan Siswa
 * Tim 9 - Kelas 9
 *
 * Jalankan script ini sekali saja untuk setup awal database
 */

require_once '../koneksi.php';

// Fungsi untuk membuat database
function createDatabase($connection) {
    $dbName = "db_team9_tabungan";

    // Cek database sudah ada atau belum
    $query = "CREATE DATABASE IF NOT EXISTS $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";

    if (mysqli_query($connection, $query)) {
        echo "<div class='alert alert-success'>✅ Database '$dbName' berhasil dibuat/sudah ada</div>";
        return true;
    } else {
        echo "<div class='alert alert-danger'>❌ Gagal membuat database: " . mysqli_error($connection) . "</div>";
        return false;
    }
}

// Fungsi untuk membuat tabel transaksi
function createTransaksiTable($connection) {
    // Switch ke database yang benar
    mysqli_select_db($connection, "db_team9_tabungan");

    $query = "CREATE TABLE IF NOT EXISTS transaksi (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nama_siswa VARCHAR(100) NOT NULL,
        tanggal DATE NOT NULL,
        jenis_transaksi ENUM('masuk', 'keluar') NOT NULL,
        nominal DECIMAL(15,2) NOT NULL,
        keterangan TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    if (mysqli_query($connection, $query)) {
        echo "<div class='alert alert-success'>✅ Tabel 'transaksi' berhasil dibuat</div>";
        return true;
    } else {
        echo "<div class='alert alert-danger'>❌ Gagal membuat tabel: " . mysqli_error($connection) . "</div>";
        return false;
    }
}

// Fungsi untuk insert data sample
function insertSampleData($connection) {
    $sampleData = [
        ['Ahmad Rizki', '2024-01-15', 'masuk', 50000.00, 'Uang jajan januari'],
        ['Siti Nurhaliza', '2024-01-16', 'masuk', 75000.00, 'Tabungan bulanan'],
        ['Budi Santoso', '2024-01-17', 'keluar', 25000.00, 'Beli alat tulis kelas'],
        ['Dewi Lestari', '2024-01-18', 'masuk', 100000.00, 'Uang raport'],
        ['Rudi Hermawan', '2024-01-19', 'keluar', 15000.00, 'Beli sapu pel']
    ];

    $successCount = 0;

    foreach ($sampleData as $data) {
        $nama_siswa = mysqli_real_escape_string($connection, $data[0]);
        $tanggal = mysqli_real_escape_string($connection, $data[1]);
        $jenis_transaksi = mysqli_real_escape_string($connection, $data[2]);
        $nominal = mysqli_real_escape_string($connection, $data[3]);
        $keterangan = mysqli_real_escape_string($connection, $data[4]);

        $query = "INSERT IGNORE INTO transaksi (nama_siswa, tanggal, jenis_transaksi, nominal, keterangan)
                  VALUES ('$nama_siswa', '$tanggal', '$jenis_transaksi', '$nominal', '$keterangan')";

        if (mysqli_query($connection, $query)) {
            if (mysqli_affected_rows($connection) > 0) {
                $successCount++;
            }
        }
    }

    if ($successCount > 0) {
        echo "<div class='alert alert-info'>📊 Berhasil insert $successCount data sample</div>";
        return true;
    } else {
        echo "<div class='alert alert-warning'>⚠️ Data sample sudah ada atau gagal diinsert</div>";
        return false;
    }
}

// Fungsi untuk menampilkan status setup
function showSetupStatus($connection) {
    // Cek database
    $dbCheck = mysqli_query($connection, "SHOW DATABASES LIKE 'db_team9_tabungan'");
    $dbExists = mysqli_num_rows($dbCheck) > 0;

    // Cek tabel
    if ($dbExists) {
        mysqli_select_db($connection, "db_team9_tabungan");
        $tableCheck = mysqli_query($connection, "SHOW TABLES LIKE 'transaksi'");
        $tableExists = mysqli_num_rows($tableCheck) > 0;

        // Cek jumlah data
        if ($tableExists) {
            $dataCount = mysqli_fetch_assoc(mysqli_query($connection, "SELECT COUNT(*) as total FROM transaksi"))['total'];
        } else {
            $dataCount = 0;
        }
    } else {
        $tableExists = false;
        $dataCount = 0;
    }

    echo "<div class='card'>";
    echo "<div class='card-header bg-info text-white'><h5>📊 Status Setup Database</h5></div>";
    echo "<div class='card-body'>";
    echo "<p><strong>Database 'db_team9_tabungan':</strong> " . ($dbExists ? "✅ Ada" : "❌ Belum ada") . "</p>";
    echo "<p><strong>Tabel 'transaksi':</strong> " . ($tableExists ? "✅ Ada" : "❌ Belum ada") . "</p>";
    echo "<p><strong>Data sample:</strong> " . ($dataCount > 0 ? "✅ $dataCount data" : "❌ Belum ada") . "</p>";
    echo "</div>";
    echo "</div>";
}

// Proses utama
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $success = true;

    switch ($_POST['action']) {
        case 'setup_all':
            $success = createDatabase($conn);
            if ($success) {
                $success = createTransaksiTable($conn);
                if ($success) {
                    insertSampleData($conn);
                }
            }
            break;

        case 'create_db':
            $success = createDatabase($conn);
            break;

        case 'create_table':
            $success = createTransaksiTable($conn);
            break;

        case 'insert_sample':
            $success = insertSampleData($conn);
            break;
    }

    if ($success) {
        echo "<div class='alert alert-success'>🎉 Setup berhasil dilakukan!</div>";
    } else {
        echo "<div class='alert alert-danger'>❌ Setup gagal. Periksa error di atas.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - Sistem Tabungan Siswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .step-card {
            border-left: 4px solid #007bff;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <header class="text-center mb-4">
                    <h1>🗄️ Database Setup</h1>
                    <p class="lead">Sistem Tabungan Siswa - Tim 9</p>
                </header>

                <?php showSetupStatus($conn); ?>

                <div class="card mt-4">
                    <div class="card-header bg-primary text-white">
                        <h5>🔧 Setup Instructions</h5>
                    </div>
                    <div class="card-body">
                        <div class="step-card p-3 bg-light">
                            <h6>Langkah 1: Setup Lengkap</h6>
                            <p class="mb-2">Jalankan setup lengkap untuk membuat database, tabel, dan data sample:</p>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="setup_all">
                                <button type="submit" class="btn btn-success">
                                    🚀 Jalankan Setup Lengkap
                                </button>
                            </form>
                        </div>

                        <div class="step-card p-3 bg-light">
                            <h6>Langkah 2: Setup Manual (Optional)</h6>
                            <p class="mb-2">Jika ingin setup bagian per bagian:</p>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="create_db">
                                        <button type="submit" class="btn btn-info btn-sm w-100">
                                            📁 Buat Database
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="create_table">
                                        <button type="submit" class="btn btn-warning btn-sm w-100">
                                            📋 Buat Tabel
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="insert_sample">
                                        <button type="submit" class="btn btn-secondary btn-sm w-100">
                                            📊 Insert Data Sample
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info mt-3">
                            <h6>📝 Database Structure:</h6>
                            <code>
                                CREATE TABLE transaksi (<br>
                                &nbsp;&nbsp;id INT PRIMARY KEY AUTO_INCREMENT,<br>
                                &nbsp;&nbsp;nama_siswa VARCHAR(100) NOT NULL,<br>
                                &nbsp;&nbsp;tanggal DATE NOT NULL,<br>
                                &nbsp;&nbsp;jenis_transaksi ENUM('masuk', 'keluar') NOT NULL,<br>
                                &nbsp;&nbsp;nominal DECIMAL(15,2) NOT NULL,<br>
                                &nbsp;&nbsp;keterangan TEXT<br>
                                );
                            </code>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="index.php" class="btn btn-primary">
                        🏠 Masuk ke Aplikasi
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>