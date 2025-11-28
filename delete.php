<?php
include 'koneksi.php';

// Enhanced Backend DELETE by Ibnu A. A. M
// Secure DELETE operation with validation and logging

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

try {
    // Get transaction data for logging before deletion
    $query = "SELECT * FROM transaksi WHERE id = ?";
    $stmt = executeQuery($conn, $query, [$id], "i");
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    if (!$data) {
        setFlashMessage('error', 'Data transaksi tidak ditemukan!');
        header("Location: index.php");
        exit();
    }

    // Log sebelum hapus
    error_log("Deleting transaction ID $id: {$data['nama_siswa']} - {$data['jenis_transaksi']} - Rp{$data['nominal']}");

    // Use prepared statement for secure DELETE
    $delete_query = "DELETE FROM transaksi WHERE id = ?";
    $delete_stmt = executeQuery($conn, $delete_query, [$id], "i");

    if ($delete_stmt) {
        // Check if row was actually deleted
        if (mysqli_stmt_affected_rows($delete_stmt) > 0) {
            error_log("Transaction ID $id deleted successfully");
            setFlashMessage('success', "Transaksi berhasil dihapus!");
        } else {
            setFlashMessage('warning', "Data tidak dapat dihapus atau sudah tidak ada.");
        }

        header("Location: index.php");
        exit();
    } else {
        throw new Exception("Failed to execute DELETE query");
    }

} catch (Exception $e) {
    error_log("Database Error in DELETE: " . $e->getMessage());
    setFlashMessage('error', "Terjadi kesalahan sistem. Data tidak dapat dihapus.");
    header("Location: index.php");
    exit();
}
?>
