<?php
require_once '../koneksi.php';

// Initialize session for flash messages
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page title
$pageTitle = "Backup & Restore - Tabungan Siswa";

// Handle backup operations
if ($_POST['action'] === 'backup') {
    try {
        $backupType = $_POST['backup_type'] ?? 'full';
        $backupDir = 'backups';

        // Create backups directory if it doesn't exist
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename = '';

        if ($backupType === 'full') {
            // Full database backup using mysqldump
            $filename = "full_backup_{$timestamp}.sql";
            $filepath = "{$backupDir}/{$filename}";

            // Create SQL backup using PHP (since mysqldump might not be available)
            $sql = "-- Database Backup: {$filename}\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- Database: " . DB_NAME . "\n\n";

            // Get all tables
            $tables = mysqli_query($conn, "SHOW TABLES");
            while ($table = mysqli_fetch_row($tables)) {
                $tableName = $table[0];
                $sql .= "-- Table: {$tableName}\n";
                $sql .= "DROP TABLE IF EXISTS `{$tableName}`;\n";

                // Get create table statement
                $createTable = mysqli_query($conn, "SHOW CREATE TABLE `{$tableName}`");
                $tableDef = mysqli_fetch_assoc($createTable);
                $sql .= $tableDef['Create Table'] . ";\n\n";

                // Get table data
                $data = mysqli_query($conn, "SELECT * FROM `{$tableName}`");
                if (mysqli_num_rows($data) > 0) {
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

            if (file_put_contents($filepath, $sql)) {
                setFlashMessage('success', "✅ Full database backup created: {$filename}");

                // Also create CSV export
                $csvFile = "transaksi_export_{$timestamp}.csv";
                $csvPath = "{$backupDir}/{$csvFile}";

                $csv = "ID,Nama Siswa,Tanggal,Jenis Transaksi,Nominal,Keterangan\n";
                $data = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id");
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

                file_put_contents($csvPath, $csv);
                setFlashMessage('success', "✅ CSV export also created: {$csvFile}");
            } else {
                throw new Exception("Failed to write backup file");
            }

        } elseif ($backupType === 'data') {
            // Data-only backup (CSV format)
            $filename = "data_backup_{$timestamp}.csv";
            $filepath = "{$backupDir}/{$filename}";

            $csv = "ID,Nama Siswa,Tanggal,Jenis Transaksi,Nominal,Keterangan\n";
            $data = mysqli_query($conn, "SELECT * FROM transaksi ORDER BY id");
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

            if (file_put_contents($filepath, $csv)) {
                setFlashMessage('success', "✅ Data backup created: {$filename}");
            } else {
                throw new Exception("Failed to write CSV backup");
            }
        }

    } catch (Exception $e) {
        setFlashMessage('error', "❌ Backup failed: " . $e->getMessage());
        error_log("Backup Error: " . $e->getMessage());
    }
}

// Handle restore operations
if ($_POST['action'] === 'restore') {
    try {
        if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['backup_file'];
            $filename = $file['name'];
            $filepath = $file['tmp_name'];

            // Validate file type
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            if ($extension === 'sql') {
                // Restore SQL backup
                $sql = file_get_contents($filepath);

                // Split SQL into individual statements
                $statements = array_filter(array_map('trim', explode(';', $sql)));

                $successCount = 0;
                $errorCount = 0;

                foreach ($statements as $statement) {
                    if (!empty($statement) && !preg_match('/^--/', $statement)) {
                        try {
                            if (mysqli_query($conn, $statement)) {
                                $successCount++;
                            } else {
                                $errorCount++;
                                error_log("Restore SQL Error: " . mysqli_error($conn) . " - Statement: " . substr($statement, 0, 100));
                            }
                        } catch (Exception $e) {
                            $errorCount++;
                            error_log("Restore Exception: " . $e->getMessage());
                        }
                    }
                }

                setFlashMessage('success', "✅ Restore completed. {$successCount} statements executed, {$errorCount} errors");

            } elseif ($extension === 'csv') {
                // Restore CSV backup (data only)
                $handle = fopen($filepath, 'r');
                if ($handle !== FALSE) {
                    // Skip header
                    fgetcsv($handle);

                    $successCount = 0;
                    $errorCount = 0;

                    while (($data = fgetcsv($handle)) !== FALSE) {
                        try {
                            $stmt = mysqli_prepare($conn, "INSERT INTO transaksi (id, nama_siswa, tanggal, jenis_transaksi, nominal, keterangan) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE nama_siswa = VALUES(nama_siswa), tanggal = VALUES(tanggal), jenis_transaksi = VALUES(jenis_transaksi), nominal = VALUES(nominal), keterangan = VALUES(keterangan)");

                            mysqli_stmt_bind_param($stmt, "isssds",
                                $data[0], // id
                                $data[1], // nama_siswa
                                $data[2], // tanggal
                                $data[3], // jenis_transaksi
                                $data[4], // nominal
                                $data[5]  // keterangan
                            );

                            if (mysqli_stmt_execute($stmt)) {
                                $successCount++;
                            } else {
                                $errorCount++;
                            }

                            mysqli_stmt_close($stmt);

                        } catch (Exception $e) {
                            $errorCount++;
                            error_log("CSV Restore Error: " . $e->getMessage());
                        }
                    }

                    fclose($handle);
                    setFlashMessage('success', "✅ CSV restore completed. {$successCount} records imported, {$errorCount} errors");
                } else {
                    throw new Exception("Failed to read CSV file");
                }

            } else {
                throw new Exception("Invalid file type. Only .sql and .csv files are supported.");
            }

        } else {
            throw new Exception("No file uploaded or upload error occurred.");
        }

    } catch (Exception $e) {
        setFlashMessage('error', "❌ Restore failed: " . $e->getMessage());
        error_log("Restore Error: " . $e->getMessage());
    }
}

// Get list of backup files
$backupFiles = [];
$backupDir = 'backups';
if (is_dir($backupDir)) {
    $files = scandir($backupDir);
    foreach ($files as $file) {
        if ($file !== '.' && $file !== '..') {
            $filepath = "{$backupDir}/{$file}";
            $backupFiles[] = [
                'name' => $file,
                'size' => number_format(filesize($filepath)),
                'modified' => date('Y-m-d H:i:s', filemtime($filepath)),
                'type' => pathinfo($file, PATHINFO_EXTENSION)
            ];
        }
    }
}

// Sort by modification date (newest first)
usort($backupFiles, function($a, $b) {
    return strtotime($b['modified']) - strtotime($a['modified']);
});
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .backup-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        .backup-section {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .backup-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }
        .form-group {
            flex: 1;
            min-width: 200px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-warning {
            background: #ffc107;
            color: black;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .backup-files {
            margin-top: 20px;
        }
        .backup-file {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 10px;
            background: #f8f9fa;
        }
        .file-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .file-icon {
            font-size: 24px;
            color: #007bff;
        }
        .file-details {
            display: flex;
            flex-direction: column;
        }
        .file-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        .file-meta {
            font-size: 12px;
            color: #666;
        }
        .file-actions {
            display: flex;
            gap: 10px;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        .upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            margin: 20px 0;
        }
        .upload-area.dragover {
            border-color: #007bff;
            background: #f0f8ff;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="backup-container">
        <div class="header">
            <h1><i class="fas fa-database"></i> Backup & Restore</h1>
            <div class="breadcrumb">
                <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-current">Backup & Restore</span>
            </div>
        </div>

        <?php if (hasFlashMessage('success')): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= getFlashMessage('success') ?>
            </div>
        <?php endif; ?>

        <?php if (hasFlashMessage('error')): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= getFlashMessage('error') ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?= number_format(mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM transaksi"))['count']) ?></div>
                <div class="stat-label">Total Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= count($backupFiles) ?></div>
                <div class="stat-label">Backup Files</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?= date('Y-m-d H:i') ?></div>
                <div class="stat-label">Current Time</div>
            </div>
        </div>

        <!-- Backup Section -->
        <div class="backup-section">
            <h2><i class="fas fa-download"></i> Create Backup</h2>
            <form method="post" class="backup-form">
                <input type="hidden" name="action" value="backup">
                <div class="form-group">
                    <label for="backup_type">Backup Type:</label>
                    <select name="backup_type" id="backup_type" class="form-control" required>
                        <option value="full">Full Database (SQL + CSV)</option>
                        <option value="data">Data Only (CSV)</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-download"></i> Create Backup
                </button>
            </form>
        </div>

        <!-- Restore Section -->
        <div class="backup-section">
            <h2><i class="fas fa-upload"></i> Restore Backup</h2>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="restore">
                <div class="upload-area">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 48px; color: #007bff; margin-bottom: 20px;"></i>
                    <h3>Upload Backup File</h3>
                    <p>Drag and drop your backup file here or click to browse</p>
                    <input type="file" name="backup_file" accept=".sql,.csv" required style="margin: 20px 0;">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Restore Backup
                    </button>
                </div>
            </form>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Warning:</strong> Restoring from SQL will replace the entire database.
                CSV restore will only update data and preserve existing records.
            </div>
        </div>

        <!-- Backup Files List -->
        <?php if (!empty($backupFiles)): ?>
        <div class="backup-section">
            <h2><i class="fas fa-archive"></i> Available Backup Files</h2>
            <div class="backup-files">
                <?php foreach ($backupFiles as $file): ?>
                <div class="backup-file">
                    <div class="file-info">
                        <div class="file-icon">
                            <?php if ($file['type'] === 'sql'): ?>
                                <i class="fas fa-database"></i>
                            <?php else: ?>
                                <i class="fas fa-file-csv"></i>
                            <?php endif; ?>
                        </div>
                        <div class="file-details">
                            <div class="file-name"><?= htmlspecialchars($file['name']) ?></div>
                            <div class="file-meta">
                                Size: <?= $file['size'] ?> bytes |
                                Modified: <?= $file['modified'] ?>
                            </div>
                        </div>
                    </div>
                    <div class="file-actions">
                        <a href="backups/<?= urlencode($file['name']) ?>"
                           class="btn btn-primary btn-sm"
                           download>
                            <i class="fas fa-download"></i> Download
                        </a>
                        <button class="btn btn-danger btn-sm"
                                onclick="deleteBackup('<?= htmlspecialchars($file['name']) ?>')">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Instructions -->
        <div class="backup-section">
            <h2><i class="fas fa-question-circle"></i> How to Use</h2>
            <div class="instructions">
                <h3>🔄 Backup Process</h3>
                <ol>
                    <li><strong>Full Backup:</strong> Creates complete database structure and data in SQL format, plus CSV export</li>
                    <li><strong>Data Backup:</strong> Creates CSV file with all transaction data only</li>
                    <li>Files are saved in the <code>backups/</code> directory</li>
                </ol>

                <h3>↩️ Restore Process</h3>
                <ol>
                    <li>Choose a backup file (.sql for full restore, .csv for data only)</li>
                    <li><strong>SQL Restore:</strong> Will recreate tables and all data</li>
                    <li><strong>CSV Restore:</strong> Will import/update transaction data only</li>
                    <li>Always test restores in development first!</li>
                </ol>

                <h3>⚠️ Important Notes</h3>
                <ul>
                    <li>SQL backups contain complete database structure and data</li>
                    <li>CSV backups contain only the transaksi table data</li>
                    <li>Regular backups are recommended (daily or weekly)</li>
                    <li>Store backup files in a secure location</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Drag and drop functionality
        const uploadArea = document.querySelector('.upload-area');
        const fileInput = document.querySelector('input[name="backup_file"]');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');

            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                const fileName = e.dataTransfer.files[0].name;
                uploadArea.querySelector('p').textContent = `Selected: ${fileName}`;
            }
        });

        // Delete backup function
        function deleteBackup(filename) {
            if (confirm(`Are you sure you want to delete ${filename}?`)) {
                window.location.href = `delete_backup.php?file=${encodeURIComponent(filename)}`;
            }
        }

        // Update file selection display
        fileInput.addEventListener('change', (e) => {
            const fileName = e.target.files[0]?.name || 'Drag and drop your backup file here or click to browse';
            uploadArea.querySelector('p').textContent = `Selected: ${fileName}`;
        });
    </script>
</body>
</html>