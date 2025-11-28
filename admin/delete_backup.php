<?php
require_once '../koneksi.phpp';

// Initialize session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security check
if (!isset($_GET['file']) || empty($_GET['file'])) {
    setFlashMessage('error', 'No file specified for deletion');
    header('Location: backup_restore.php');
    exit;
}

$filename = basename($_GET['file']); // Prevent directory traversal
$filepath = 'backups/' . $filename;

// Validate file path
if (strpos($filename, '..') !== false || !file_exists($filepath)) {
    setFlashMessage('error', 'Invalid file or file not found');
    header('Location: backup_restore.php');
    exit;
}

// Allowed file extensions
$allowedExtensions = ['sql', 'csv'];
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions)) {
    setFlashMessage('error', 'File type not allowed for deletion');
    header('Location: backup_restore.php');
    exit;
}

// Delete the file
if (unlink($filepath)) {
    setFlashMessage('success', "✅ Backup file '$filename' deleted successfully");
} else {
    setFlashMessage('error', "❌ Failed to delete backup file '$filename'");
}

header('Location: backup_restore.php');
exit;
?>