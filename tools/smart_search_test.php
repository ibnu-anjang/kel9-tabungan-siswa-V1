<?php
require_once '../koneksi.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Test Smart Search functionality
echo "<!DOCTYPE html>";
echo "<html lang='id'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Smart Search Test</title>";
echo "<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>";
echo "<link rel='stylesheet' href='assets/css/style.css'>";
echo "<style>";
echo ".test-container { max-width: 1000px; margin: 0 auto; padding: 20px; }";
echo ".test-section { background: white; padding: 25px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }";
echo ".test-result { padding: 15px; margin: 10px 0; border-radius: 5px; }";
echo ".test-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }";
echo ".test-error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }";
echo ".test-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }";
echo ".search-pattern { background: #f8f9fa; padding: 10px; margin: 5px 0; border-left: 4px solid #007bff; font-family: monospace; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='test-container'>";
echo "<div class='header'>";
echo "<h1><i class='fas fa-search'></i> Smart Search Test & Verification</h1>";
echo "<div class='breadcrumb'>";
echo "<a href='index.php'><i class='fas fa-home'></i> Beranda</a>";
echo "<span class='breadcrumb-separator'>></span>";
echo "<span class='breadcrumb-current'>Smart Search Test</span>";
echo "</div>";
echo "</div>";

// Test cases for Smart Search
$testCases = [
    'eko prabowo',
    'eko_prabowo',
    'ekoprabowo',
    'andi',
    '2025-11-27',
    'masuk',
    'test',
    'siswa',
    'tabungan'
];

echo "<div class='test-section'>";
echo "<h2><i class='fas fa-vial'></i> Running Smart Search Tests</h2>";

$passedTests = 0;
$totalTests = count($testCases);

foreach ($testCases as $search) {
    echo "<div class='test-result test-info'>";
    echo "<h4>🔍 Testing Search: '$search'</h4>";

    try {
        // Smart Search implementation (copied from index.php)
        $search_conditions = [];
        $params = [];
        $types = "";

        if (!empty($search)) {
            // Generate search patterns
            $search_patterns = [
                $search, // Original input
                str_replace('_', ' ', $search), // Underscore to space
                str_replace(' ', '_', $search), // Space to underscore
                str_replace([' ', '_'], '', $search), // Remove both
                str_replace(' ', '%', $search) // Space to wildcard
            ];

            // Remove duplicates while preserving order
            $search_patterns = array_values(array_unique($search_patterns));

            // Build WHERE conditions
            foreach ($search_patterns as $i => $pattern) {
                $search_conditions[] = "(nama_siswa LIKE ? OR keterangan LIKE ?)";
                $params[] = "%{$pattern}%";
                $params[] = "%{$pattern}%";
                $types .= "ss";
            }

            // Also try exact date match
            if (strtotime($search)) {
                $search_conditions[] = "tanggal = ?";
                $params[] = $search;
                $types .= "s";
            }

            // Try exact transaction type match
            if (in_array(strtolower($search), ['masuk', 'keluar'])) {
                $search_conditions[] = "jenis_transaksi = ?";
                $params[] = strtolower($search);
                $types .= "s";
            }
        }

        // Build final query
        $where_clause = "";
        if (!empty($search_conditions)) {
            $where_clause = "WHERE (" . implode(" OR ", $search_conditions) . ")";
        }

        // Test the query
        $query = "SELECT * FROM transaksi {$where_clause} ORDER BY tanggal DESC, id DESC LIMIT 100";
        $stmt = executeQuery($conn, $query, $params, $types);

        if ($stmt) {
            $result = mysqli_stmt_get_result($stmt);
            $count = mysqli_num_rows($result);

            if ($count > 0) {
                echo "<p style='color: green;'>✅ <strong>Success:</strong> Found $count records</p>";
                echo "<div class='search-patterns'>";
                echo "<strong>Search Patterns Generated:</strong><br>";

                for ($i = 0; $i < count($search_patterns); $i++) {
                    echo "<div class='search-pattern'>Pattern " . ($i+1) . ": '" . $search_patterns[$i] . "'</div>";
                }

                echo "</div>";

                // Show first few results
                echo "<strong>Sample Results:</strong><br>";
                $sampleCount = min(3, $count);
                for ($j = 0; $j < $sampleCount; $j++) {
                    $row = mysqli_fetch_assoc($result);
                    echo "<small>• {$row['nama_siswa']} - {$row['tanggal']} - {$row['jenis_transaksi']} - Rp{$row['nominal']}</small><br>";
                }

                $passedTests++;
            } else {
                echo "<p style='color: orange;'>⚠️ <strong>No Results:</strong> Search executed but found 0 records</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ <strong>Query Failed:</strong> " . mysqli_error($conn) . "</p>";
        }

    } catch (Exception $e) {
        echo "<p style='color: red;'>❌ <strong>Exception:</strong> " . $e->getMessage() . "</p>";
    }

    echo "</div>";
}

echo "<div class='test-result test-success'>";
echo "<h3>📊 Test Summary</h3>";
echo "<p><strong>Tests Passed:</strong> $passedTests / $totalTests</p>";
echo "<p><strong>Success Rate:</strong> " . round(($passedTests / $totalTests) * 100, 2) . "%</p>";

if ($passedTests === $totalTests) {
    echo "<p style='color: green; font-weight: bold;'>🎉 All Smart Search tests PASSED! System is working correctly.</p>";
} else {
    echo "<p style='color: orange; font-weight: bold;'>⚠️ Some tests failed. Check the results above.</p>";
}
echo "</div>";

echo "</div>";

// Performance Test
echo "<div class='test-section'>";
echo "<h2><i class='fas fa-tachometer-alt'></i> Performance Test</h2>";

$performanceTests = [
    'Search with 1 result' => 'eko prabowo',
    'Search with multiple results' => 'siswa',
    'Search with no results' => 'nonexistent12345',
    'Empty search' => ''
];

foreach ($performanceTests as $testName => $searchTerm) {
    $startTime = microtime(true);

    // Execute search
    $search_conditions = [];
    $params = [];
    $types = "";

    if (!empty($searchTerm)) {
        $search_patterns = [
            $searchTerm,
            str_replace('_', ' ', $searchTerm),
            str_replace(' ', '_', $searchTerm),
            str_replace([' ', '_'], '', $searchTerm),
            str_replace(' ', '%', $searchTerm)
        ];
        $search_patterns = array_values(array_unique($search_patterns));

        foreach ($search_patterns as $pattern) {
            $search_conditions[] = "(nama_siswa LIKE ? OR keterangan LIKE ?)";
            $params[] = "%{$pattern}%";
            $params[] = "%{$pattern}%";
            $types .= "ss";
        }
    }

    $where_clause = !empty($search_conditions) ? "WHERE (" . implode(" OR ", $search_conditions) . ")" : "";
    $query = "SELECT * FROM transaksi {$where_clause} ORDER BY tanggal DESC, id DESC LIMIT 100";
    $stmt = executeQuery($conn, $query, $params, $types);

    if ($stmt) {
        $result = mysqli_stmt_get_result($stmt);
        $count = mysqli_num_rows($result);
    }

    $endTime = microtime(true);
    $executionTime = round(($endTime - $startTime) * 1000, 2); // Convert to milliseconds

    echo "<p><strong>{$testName}:</strong> {$executionTime}ms ({$count} results)</p>";
}

echo "</div>";

// Database Info
echo "<div class='test-section'>";
echo "<h2><i class='fas fa-info-circle'></i> System Information</h2>";

// Get database stats
$totalRecords = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM transaksi"));
$dateRange = mysqli_fetch_assoc(mysqli_query($conn, "SELECT MIN(tanggal) as min_date, MAX(tanggal) as max_date FROM transaksi"));
$transactionTypes = mysqli_fetch_assoc(mysqli_query($conn, "SELECT
    SUM(CASE WHEN jenis_transaksi = 'masuk' THEN 1 ELSE 0 END) as masuk_count,
    SUM(CASE WHEN jenis_transaksi = 'keluar' THEN 1 ELSE 0 END) as keluar_count
    FROM transaksi"));

echo "<div style='display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;'>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<h4>📊 Database Stats</h4>";
echo "<p>Total Records: <strong>" . number_format($totalRecords['count']) . "</strong></p>";
echo "<p>Date Range: <strong>" . $dateRange['min_date'] . " to " . $dateRange['max_date'] . "</strong></p>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<h4>💰 Transaction Types</h4>";
echo "<p>Money In: <strong>" . number_format($transactionTypes['masuk_count']) . "</strong></p>";
echo "<p>Money Out: <strong>" . number_format($transactionTypes['keluar_count']) . "</strong></p>";
echo "</div>";

echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>";
echo "<h4>🔧 System Status</h4>";
echo "<p>Database: <strong style='color: green;'>Connected</strong></p>";
echo "<p>Smart Search: <strong style='color: green;'>Active</strong></p>";
echo "<p>PHP Version: <strong>" . PHP_VERSION . "</strong></p>";
echo "</div>";
echo "</div>";

echo "</div>";

echo "<div class='test-section'>";
echo "<p><a href='index.php' class='btn btn-primary'><i class='fas fa-arrow-left'></i> Back to Main Application</a></p>";
echo "<p><a href='backup_restore.php' class='btn btn-success'><i class='fas fa-database'></i> Backup & Restore</a></p>";
echo "<p><a href='check_database.php' class='btn btn-info'><i class='fas fa-check'></i> Database Check</a></p>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?>