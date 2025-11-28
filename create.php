<?php
include 'koneksi.php';

// Enhanced Backend Validation by Ibnu A. A. M
// Secure CREATE operation with comprehensive validation

// Fix timezone untuk Indonesia
date_default_timezone_set('Asia/Jakarta');

$jenis = $_GET['jenis'] ?? 'masuk';

// Validate jenis transaksi
if (!in_array($jenis, ['masuk', 'keluar'])) {
    setFlashMessage('error', 'Jenis transaksi tidak valid!');
    header("Location: index.php");
    exit();
}

if (isset($_POST['submit'])) {

    // Comprehensive Backend Validation
    $errors = [];

    // Validate nama siswa
    if (empty(trim($_POST['nama']))) {
        $errors[] = "Nama siswa wajib diisi!";
    } elseif (strlen(trim($_POST['nama'])) < 3) {
        $errors[] = "Nama siswa minimal 3 karakter!";
    } elseif (strlen(trim($_POST['nama'])) > 100) {
        $errors[] = "Nama siswa maksimal 100 karakter!";
    }

    // Validate tanggal
    if (empty($_POST['tanggal'])) {
        $errors[] = "Tanggal wajib diisi!";
    } else {
        $tanggal = $_POST['tanggal'];
        $date_obj = DateTime::createFromFormat('Y-m-d', $tanggal);

        // Format validation
        if (!$date_obj || $date_obj->format('Y-m-d') !== $tanggal) {
            $errors[] = "Format tanggal tidak valid!";
        } else {
            // Get today's date correctly with timezone
            $today = new DateTime('today', new DateTimeZone('Asia/Jakarta'));
            $today_str = $today->format('Y-m-d');

            // Future date validation - allow today but not future
            if ($tanggal > $today_str) {
                $errors[] = "Tanggal tidak boleh di masa depan!";
            }
            // Past date validation
            elseif (strtotime($tanggal) < strtotime('2020-01-01')) {
                $errors[] = "Tanggal tidak boleh terlalu lama!";
            }
        }
    }

    // Validate nominal
    if (empty($_POST['nominal']) || !is_numeric($_POST['nominal'])) {
        $errors[] = "Nominal wajib diisi dengan angka!";
    } elseif (floatval($_POST['nominal']) <= 0) {
        $errors[] = "Nominal harus lebih besar dari 0!";
    } elseif (floatval($_POST['nominal']) > 999999999.99) {
        $errors[] = "Nominal terlalu besar!";
    }

    // Validate keterangan (optional)
    $keterangan = trim($_POST['keterangan'] ?? '');
    if (strlen($keterangan) > 500) {
        $errors[] = "Keterangan maksimal 500 karakter!";
    }

    // If validation fails
    if (!empty($errors)) {
        setFlashMessage('error', implode('<br>', $errors));
        setFlashMessage('old_input', $_POST);
        header("Location: create.php?jenis=$jenis");
        exit();
    }

    // Sanitize and prepare data for database
    try {
        $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));
        $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
        $nominal = floatval($_POST['nominal']);
        $keterangan = mysqli_real_escape_string($conn, $keterangan);

        // Use prepared statement for security
        $query = "INSERT INTO transaksi (nama_siswa, tanggal, jenis_transaksi, nominal, keterangan)
                  VALUES (?, ?, ?, ?, ?)";

        $stmt = executeQuery($conn, $query,
            [$nama, $tanggal, $jenis, $nominal, $keterangan],
            "sssds"
        );

        if ($stmt) {
            // Log successful transaction
            error_log("Transaction created successfully: $nama - $jenis - Rp$nominal");

            setFlashMessage('success', "Transaksi berhasil ditambahkan!");
            header("Location: index.php");
            exit();
        } else {
            throw new Exception("Failed to execute transaction query");
        }

    } catch (Exception $e) {
        error_log("Database Error in CREATE: " . $e->getMessage());
        setFlashMessage('error', "Terjadi kesalahan sistem. Silakan coba lagi.");
        header("Location: create.php?jenis=$jenis");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            background: #f5f5f5;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        
        .form-container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            max-width: 450px;
            width: 100%;
        }
        
        h1 {
            color: #0052cc;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .form-subtitle {
            color: #7c4dff;
            font-size: 14px;
            margin-bottom: 28px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #666;
            font-size: 12px;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        input[type="text"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            width: 100%;
            padding: 12px 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            font-family: inherit;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus,
        input[type="date"]:focus,
        input[type="number"]:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: #0052cc;
            box-shadow: 0 0 0 3px rgba(0, 82, 204, 0.1);
        }
        
        input[type="text"]::placeholder,
        input[type="number"]::placeholder,
        textarea::placeholder {
            color: #999;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }
        
        button[type="submit"],
        .btn-cancel {
            flex: 1;
            padding: 12px 16px;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        button[type="submit"] {
            background: #0052cc;
            color: white;
        }
        
        button[type="submit"]:hover {
            background: #0043a3;
            box-shadow: 0 4px 12px rgba(0, 82, 204, 0.3);
        }
        
        button[type="submit"]:active {
            transform: translateY(1px);
        }
        
        .btn-cancel {
            background: #e8e8e8;
            color: #333;
        }
        
        .btn-cancel:hover {
            background: #d8d8d8;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h1>Tambah Transaksi - <?= ucfirst($jenis) ?></h1>
    <p class="form-subtitle">Masukkan data</p>

    <!-- Flash Messages by Backend Lead Ibnu A. A. M -->
    <?php if (hasFlashMessage('error')): ?>
        <div class="alert alert-danger" style="margin-bottom: 20px;">
            <?= getFlashMessage('error') ?>
        </div>
    <?php endif; ?>

    <?php if (hasFlashMessage('success')): ?>
        <div class="alert alert-success" style="margin-bottom: 20px;">
            <?= getFlashMessage('success') ?>
        </div>
    <?php endif; ?>
    
    <form method="POST">

        <!-- Nama Siswa -->
        <div class="form-group">
            <label>Nama Siswa</label>
            <input type="text" name="nama" required>
        </div>

        <!-- Tanggal -->
        <div class="form-group">
            <label>Tanggal</label>
            <input type="date" name="tanggal" required max="<?= date('Y-m-d') ?>">
            <small style="color: #666; font-size: 11px; margin-top: 4px; display: block;">
                Maksimal: <?= date('d F Y') ?>
            </small>
        </div>

        <!-- Nominal -->
        <div class="form-group">
            <label>Nominal (Rp)</label>
            <input type="number" name="nominal" required>
        </div>

        <!-- Keterangan -->
        <div class="form-group">
            <label>Keterangan</label>
            <textarea name="keterangan"></textarea>
        </div>

        <!-- Tombol -->
        <div class="button-group">
            <button type="submit" name="submit">Simpan</button>
            <a href="index.php" class="btn-cancel">Batal</a>
        </div>

    </form>
</div>

</body>
</html>
