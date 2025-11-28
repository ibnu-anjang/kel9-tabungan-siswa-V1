<?php
require_once '../koneksi.php';

// Simulate POST request for backup testing
$_POST['action'] = 'backup';
$_POST['backup_type'] = 'full';

echo "<h2>🧪 Testing Backup Creation</h2>";

try {
    $backupType = $_POST['backup_type'] ?? 'full';
    $backupDir = 'backups';

    // Create backups directory if it doesn't exist
    if (!is_dir($backupDir)) {
        mkdir($backupDir, 0755, true);
        echo "<p>✅ Created backups directory</p>";
    }

    $timestamp = date('Y-m-d_H-i-s');
    $filename = '';

    if ($backupType === 'full') {
        // Full database backup using PHP
        $filename = "full_backup_{$timestamp}.sql";
        $filepath = "{$backupDir}/{$filename}";

        // Create SQL backup
        $sql = "-- Database Backup: {$filename}\n";
        $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Database: " . DB_NAME . "\n\n";

        // Get all tables
        $tables = mysqli_query($conn, "SHOW TABLES");
        if ($tables) {
            while ($table = mysqli_fetch_row($tables)) {
                $tableName = $table[0];
                $sql .= "-- Table: {$tableName}\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";

                // Get create table statement
                $createTable = mysqli_query($conn, "SHOW CREATE TABLE `{$tableName}`");
                if ($createTable) {
                    $tableDef = mysqli_fetch_assoc($createTable);
                    $sql .= $tableDef['Create Table'] . ";\n\n";

                    // Get table data
                    $data = mysqli_query($conn, "SELECT * FROM `{$tableName}`");
                    if ($data && mysqli_num_rows($data) > 0) {
                        $sql .= "-- Data for table: {$tableName}\n";
                        while ($row = mysqli_fetch_assoc($data)) {
                            $values = array_map(function($value) use ($conn) {
                                return $value === null ? 'NULL' : "'" . mysqli_real_escape_string($conn, $value) . "'";
                            }, $row);
                            $sql .= "INSERT INTO `{$tableName}` VALUES (" . implode(', ', $values) . ");\n";
                        }
                        $sql .= "\n";
                    }
                }
            }
        }

        if (file_put_contents($filepath, $sql)) {
            echo "<p style='color: green;'>✅ Full SQL backup created: {$filename}</p>";
            echo "<p>📁 Location: {$filepath}</p>";
            echo "<p>📏 Size: " . number_format(filesize($filepath)) . " bytes</p>";

            // Also create CSV export
            $csvFile = "transaksi_export_{$timestamp}.csv";
            $csvPath = "{$backupDir}/{$csvFile}";

            $csv = "ID,Nama Siswa,Tanggal,Jenis Transaksi,Nominal,Keterangan\n";
            $data = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id");
            if ($data) {
                while ($row = mysqli_fetch_assoc($data)) {
                    $csv .= implode(',', [
                        $row['id'],
                        '"' . str_replace('"', '""', $row['nama_siswa']) . '"',
                        $row['tanggal'],
                        $row['jenis_transaksi'],
                        $row['nominal'],
                        '"' . str_replace('"', '""', $row['keterangan']) . '"'
                    ]) . "\n";
                }
            }

            if (file_put_contents($csvPath, $csv)) {
                echo "<p style='color: green;'>✅ CSV export also created: {$csvFile}</p>";
                echo "<p>📁 Location: {$csvPath}</p>";
                echo "<p>📏 Size: " . number_format(filesize($csvPath)) . " bytes</p>";
            }
        } else {
            throw new Exception("Failed to write backup file");
        }

    } elseif ($backupType === 'data') {
        // Data-only backup (CSV format)
        $filename = "data_backup_{$timestamp}.csv";
        $filepath = "{$backupDir}/{$filename}";

        $csv = "ID,Nama Siswa,Tanggal,Jenis Transaksi,Nominal,Keterangan\n";
        $data = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id");
        if ($data) {
            while ($row = mysqli_fetch_assoc($data)) {
                $csv .= implode(',', [
                    $row['id'],
                    '"' . str_replace('"', '""', $row['nama_siswa']) . '"',
                    $row['tanggal'],
                    $row['jenis_transaksi'],
                    $row['nominal'],
                    '"' . str_replace('"', '""', $row['keterangan']) . '"'
                ]) . "\n";
            }
        }

        if (file_put_contents($filepath, $csv)) {
            echo "<p style='color: green;'>✅ Data backup created: {$filename}</p>";
            echo "<p>📁 Location: {$filepath}</p>";
            echo "<p>📏 Size: " . number_format(filesize($filepath)) . " bytes</p>";
        } else {
            throw new Exception("Failed to write CSV backup");
        }
    }

    // List all backup files
    echo "<h3>📋 All Backup Files:</h3>";
    $backupFiles = glob('backups/*');
    if (!empty($backupFiles)) {
        echo "<ul>";
        foreach ($backupFiles as $file) {
            $size = filesize($file);
            $modified = date('Y-m-d H:i:s', filemtime($file));
            echo "<li><strong>" . basename($file) . "</strong> (" . number_format($size) . " bytes, modified: $modified)</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>No backup files found</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Backup failed: " . $e->getMessage() . "</p>";
    error_log("Backup Error: " . $e->getMessage());
}

echo "<hr>";
echo "<p><a href='index.php'>← Back to Main Application</a></p>";
echo "<p><a href='backup_restore.php'>💾 Backup & Restore System</a></p>";
echo "<p><a href='smart_search_test.php'>🔍 Test Smart Search</a></p>";
?>