<?php
include 'koneksi.php';

// Enhanced Backend UPDATE by Ibnu A. A. M
// Secure UPDATE operation with comprehensive validation and error handling

// Fix timezone untuk Indonesia
date_default_timezone_set('Asia/Jakarta');

// Validate ID parameter
if (!isset($_GET['id'])) {
    setFlashMessage('error', 'ID transaksi tidak ditemukan!');
    header("Location: index.php");
    exit();
}

// Validate and sanitize ID
$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id || $id <= 0) {
    setFlashMessage('error', 'ID transaksi tidak valid!');
    header("Location: index.php");
    exit();
}

// Ambil data dengan prepared statement untuk security
try {
    $query = "SELECT * FROM transaksi WHERE id = ?";
    $stmt = executeQuery($conn, $query, [$id], "i");
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        setFlashMessage('error', 'Data transaksi tidak ditemukan!');
        header("Location: index.php");
        exit();
    }

} catch (Exception $e) {
    error_log("Database Error in EDIT (SELECT): " . $e->getMessage());
    setFlashMessage('error', 'Terjadi kesalahan sistem. Silakan coba lagi.');
    header("Location: index.php");
    exit();
}

// Process update request
if (isset($_POST['update'])) {

    // Comprehensive Backend Validation
    $errors = [];

    // Validate nama siswa
    if (empty(trim($_POST['nama_siswa']))) {
        $errors[] = "Nama siswa wajib diisi!";
    } elseif (strlen(trim($_POST['nama_siswa'])) < 3) {
        $errors[] = "Nama siswa minimal 3 karakter!";
    } elseif (strlen(trim($_POST['nama_siswa'])) > 100) {
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

    // Validate jenis transaksi
    $jenis = $_POST['jenis_transaksi'] ?? '';
    if (!in_array($jenis, ['masuk', 'keluar'])) {
        $errors[] = "Jenis transaksi tidak valid!";
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
        header("Location: edit.php?id=$id");
        exit();
    }

    // Sanitize and prepare data for database
    try {
        $nama = mysqli_real_escape_string($conn, trim($_POST['nama_siswa']));
        $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal']);
        $nominal = floatval($_POST['nominal']);
        $keterangan = mysqli_real_escape_string($conn, $keterangan);

        // Log sebelum update
        error_log("Updating transaction ID $id: $nama - $jenis - Rp$nominal");

        // Use prepared statement for security
        $query = "UPDATE transaksi SET
                    nama_siswa = ?,
                    tanggal = ?,
                    jenis_transaksi = ?,
                    nominal = ?,
                    keterangan = ?
                  WHERE id = ?";

        $stmt = executeQuery($conn, $query,
            [$nama, $tanggal, $jenis, $nominal, $keterangan, $id],
            "sssdsi"
        );

        if ($stmt) {
            // Check if row was actually updated
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                error_log("Transaction ID $id updated successfully");
                setFlashMessage('success', "Transaksi berhasil diupdate!");
            } else {
                setFlashMessage('info', "Tidak ada perubahan pada data.");
            }

            header("Location: index.php");
            exit();
        } else {
            throw new Exception("Failed to execute UPDATE query");
        }

    } catch (Exception $e) {
        error_log("Database Error in UPDATE: " . $e->getMessage());
        setFlashMessage('error', "Terjadi kesalahan sistem. Silakan coba lagi.");
        header("Location: edit.php?id=$id");
        exit();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Transaksi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

<h2>Edit Transaksi</h2>

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

<?php if (hasFlashMessage('info')): ?>
    <div class="alert alert-info" style="margin-bottom: 20px;">
        <?= getFlashMessage('info') ?>
    </div>
<?php endif; ?>

<form method="POST">

    <label class="form-label">Nama Siswa:</label>
    <input type="text" name="nama_siswa" class="form-control" value="<?= $data['nama_siswa'] ?>" required>

    <label class="form-label mt-2">Tanggal:</label>
    <input type="date" name="tanggal" class="form-control" value="<?= $data['tanggal'] ?>" required max="<?= date('Y-m-d') ?>">
    <small style="color: #666; font-size: 11px; margin-top: 4px; display: block;">
        Maksimal: <?= date('d F Y') ?>
    </small>

    <label class="form-label mt-2">Jenis Transaksi:</label>
    <select class="form-control" name="jenis_transaksi" required>
        <option value="masuk" <?= $data['jenis_transaksi'] == 'setoran' ? 'selected' : '' ?>>Setoran</option>
        <option value="keluar" <?= $data['jenis_transaksi'] == 'penarikan' ? 'selected' : '' ?>>Penarikan</option>
    </select>


    <label class="form-label mt-2">Nominal:</label>
    <input type="number" name="nominal" class="form-control" value="<?= $data['nominal'] ?>" required>

    <label class="form-label mt-2">Keterangan:</label>
    <textarea name="keterangan" class="form-control"><?= $data['keterangan'] ?></textarea>

    <button type="submit" name="update" class="btn btn-primary mt-3">Simpan Perubahan</button>
    <a href="index.php" class="btn btn-secondary mt-3">Kembali</a>

</form>

</body>
</html>
