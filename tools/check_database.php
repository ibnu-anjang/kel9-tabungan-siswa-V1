<?php
// Database connection test
require_once '../koneksi.php';

echo "<h2>🔍 Database Connection & Backup Status Check</h2>";

// Test basic connection
echo "<h3>✅ Connection Test</h3>";
if ($conn) {
    echo "<p style='color: green;'>✅ Database connection successful!</p>";

    // Check if database exists
    $db_check = mysqli_query($conn, "SELECT DATABASE() as current_db");
    $db_result = mysqli_fetch_assoc($db_check);
    echo "<p>📁 Current Database: <strong>" . $db_result['current_db'] . "</strong></p>";

    // Check table structure
    echo "<h3>📋 Table Structure</h3>";
    $tables_query = mysqli_query($conn, "SHOW TABLES");
    if ($tables_query && mysqli_num_rows($tables_query) > 0) {
        echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
        echo "<tr><th>Table</th><th>Records</th><th>Size</th></tr>";

        while ($table = mysqli_fetch_row($tables_query)) {
            $table_name = $table[0];
            $count_query = mysqli_query($conn, "SELECT COUNT(*) as count FROM `$table_name`");
            $count_result = mysqli_fetch_assoc($count_query);

            // Get table size
            $size_query = mysqli_query($conn, "SELECT ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'size_mb' FROM information_schema.TABLES WHERE table_schema = DATABASE() AND table_name = '$table_name'");
            $size_result = mysqli_fetch_assoc($size_query);

            echo "<tr>";
            echo "<td>$table_name</td>";
            echo "<td>" . number_format($count_result['count']) . "</td>";
            echo "<td>" . $size_result['size_mb'] . " MB</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: orange;'>⚠️ No tables found in database</p>";
    }

    // Check for recent data
    echo "<h3>📊 Recent Data Analysis</h3>";
    $recent_query = mysqli_query($conn, "SELECT COUNT(*) as total, DATE(MAX(tanggal)) as latest_date FROM transaksi");
    $recent_data = mysqli_fetch_assoc($recent_query);

    echo "<p>📈 Total Records: <strong>" . number_format($recent_data['total']) . "</strong></p>";
    echo "<p>📅 Latest Transaction: <strong>" . ($recent_data['latest_date'] ?: 'No data') . "</strong></p>";

    // Check backup files
    echo "<h3>💾 Available Backup Files</h3>";
    $backup_files = glob('*.backup_*');
    if (!empty($backup_files)) {
        echo "<ul>";
        foreach ($backup_files as $file) {
            $size = filesize($file);
            $modified = date('Y-m-d H:i:s', filemtime($file));
            echo "<li><strong>$file</strong> (" . number_format($size) . " bytes, modified: $modified)</li>";
        }
        echo "</ul>";
    } else {
        echo "<p style='color: orange;'>⚠️ No backup files found</p>";
    }

    // Check for PHP errors in log
    echo "<h3>🔍 Recent PHP Errors</h3>";
    $error_log_path = ini_get('error_log');
    if ($error_log_path && file_exists($error_log_path)) {
        $errors = file_get_contents($error_log_path);
        $recent_errors = array_filter(explode("\n", $errors), function($line) {
            return strpos($line, 'Database Error') !== false ||
                   strpos($line, 'MySQL') !== false ||
                   strpos($line, 'mysqli') !== false;
        });

        if (!empty($recent_errors)) {
            echo "<div style='background: #ffe6e6; padding: 10px; border-radius: 5px;'>";
            echo "<p><strong>Recent Database Errors:</strong></p>";
            echo "<ul>";
            foreach (array_slice($recent_errors, -10) as $error) {
                if (!empty(trim($error))) {
                    echo "<li style='font-size: 12px; color: #d32f2f;'>" . htmlspecialchars(substr($error, 0, 200)) . "...</li>";
                }
            }
            echo "</ul>";
            echo "</div>";
        } else {
            echo "<p style='color: green;'>✅ No recent database errors found</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ Error log not accessible</p>";
    }

} else {
    echo "<p style='color: red;'>❌ Database connection failed!</p>";
    echo "<p>Please check:</p>";
    echo "<ul>";
    echo "<li>MySQL/XAMPP service is running</li>";
    echo "<li>Database 'db_team9_tabungan' exists</li>";
    echo "<li>User 'root' has proper permissions</li>";
    echo "</ul>";
}

echo "<hr>";
echo "<p><a href='index.php'>← Back to Main Application</a></p>";
echo "<p><a href='setup_database.php'>↻ Re-run Database Setup</a></p>";
?>