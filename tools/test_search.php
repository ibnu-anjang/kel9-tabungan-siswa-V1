<?php
require_once '../koneksi.php';

// Test search functionality specifically for "eko prabowo"
echo "<h2>🔍 Testing Search: 'eko prabowo'</h2>";

// Simulate the search parameters from index.php
$search = "eko prabowo";
$filter = "all";
$limit = 50;

echo "<h3>🎯 Search Parameters:</h3>";
echo "<p><strong>Search Term:</strong> '$search'</p>";
echo "<p><strong>Filter:</strong> '$filter'</p>";
echo "<p><strong>Limit:</strong> $limit</p>";

try {
    // Initialize variables like in index.php
    $conditions = [];
    $params = [];
    $types = '';

    // Handle Smart Search condition (NEW IMPLEMENTATION)
    if (!empty($search)) {
        // Generate multiple search patterns for better matching
        $search_patterns = [
            $search, // Original input
            str_replace('_', ' ', $search), // Underscore to space
            str_replace(' ', '_', $search), // Space to underscore
            str_replace([' ', '_'], '', $search), // Remove both
            str_replace(' ', '%', $search) // Space to wildcard
        ];

        // Remove duplicates while preserving order
        $search_patterns = array_values(array_unique($search_patterns));

        // Build WHERE conditions for each pattern
        $search_conditions = [];
        foreach ($search_patterns as $pattern) {
            $search_conditions[] = "(nama_siswa LIKE ? OR keterangan LIKE ?)";
            $params[] = "%{$pattern}%";
            $params[] = "%{$pattern}%";
            $types .= 'ss';
        }

        // Combine all search conditions with OR
        $conditions[] = "(" . implode(' OR ', $search_conditions) . ")";

        echo "<h3>🔧 Smart Search Patterns Generated:</h3>";
        foreach ($search_patterns as $i => $pattern) {
            echo "<p><strong>Pattern " . ($i+1) . ":</strong> '$pattern' → '%$pattern%'</p>";
        }
        echo "<p><strong>Total Search Conditions:</strong> " . count($search_conditions) . "</p>";
        echo "<p><strong>Total Parameters:</strong> " . count($params) . "</p>";
        echo "<p><strong>Parameter Types:</strong> '$types'</p>";
    }

    // Handle jenis filter
    if ($filter !== 'all') {
        $conditions[] = "jenis_transaksi = ?";
        $params[] = $filter;
        $types .= 's';
    }

    // Build WHERE clause
    $where_clause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';

    // Build LIMIT clause
    $limit_clause = "LIMIT $limit";

    echo "<h3>📋 Generated SQL Query:</h3>";
    $query = "SELECT * FROM transaksi $where_clause ORDER BY tanggal DESC, id DESC $limit_clause";
    echo "<code style='background: #f8f9fa; padding: 10px; display: block; border-radius: 5px;'>";
    echo htmlspecialchars($query);
    echo "</code>";

    echo "<h3>📊 Query Parameters:</h3>";
    echo "<pre>";
    print_r($params);
    echo "</pre>";

    // Execute the query
    echo "<h3>🚀 Executing Query...</h3>";
    $stmt = executeQuery($conn, $query, $params, $types);

    if ($stmt) {
        $result = mysqli_stmt_get_result($stmt);
        $count = mysqli_num_rows($result);

        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h3>✅ Query Results:</h3>";
        echo "<p><strong>Records Found:</strong> $count</p>";
        echo "</div>";

        if ($count > 0) {
            echo "<h4>📋 First 5 Results:</h4>";
            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f8f9fa;'>";
            echo "<th>ID</th><th>Nama Siswa</th><th>Tanggal</th><th>Jenis</th><th>Nominal</th><th>Keterangan</th>";
            echo "</tr>";

            $displayCount = min(5, $count);
            for ($i = 0; $i < $displayCount; $i++) {
                $row = mysqli_fetch_assoc($result);
                $jenisClass = $row['jenis_transaksi'] === 'masuk' ? 'color: green;' : 'color: red;';
                echo "<tr>";
                echo "<td>" . $row['id'] . "</td>";
                echo "<td>" . htmlspecialchars($row['nama_siswa']) . "</td>";
                echo "<td>" . $row['tanggal'] . "</td>";
                echo "<td style='$jenisClass'><strong>" . strtoupper($row['jenis_transaksi']) . "</strong></td>";
                echo "<td>Rp " . number_format($row['nominal'], 2) . "</td>";
                echo "<td>" . htmlspecialchars($row['keterangan']) . "</td>";
                echo "</tr>";
            }

            if ($count > 5) {
                echo "<tr><td colspan='6' style='text-align: italic; color: #666;'>... and " . ($count - 5) . " more records</td></tr>";
            }

            echo "</table>";
        } else {
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>❌ No Records Found</h4>";
            echo "<p>This suggests either:</p>";
            echo "<ul>";
            echo "<li>No data matches the search patterns</li>";
            echo "<li>Data format is different than expected</li>";
            echo "<li>Database connection issues</li>";
            echo "</ul>";
            echo "</div>";
        }

        mysqli_stmt_close($stmt);

        // Test direct database queries to verify data exists
        echo "<h3>🔎 Verification - Direct Database Queries:</h3>";

        // Check if any EKO records exist
        $directQuery = "SELECT COUNT(*) as count FROM transaksi WHERE nama_siswa LIKE '%Eko%' OR keterangan LIKE '%Eko%'";
        $directResult = mysqli_query($conn, $directQuery);
        $directCount = mysqli_fetch_assoc($directResult)['count'];

        echo "<p><strong>Direct '%Eko%' Search:</strong> $directCount records found</p>";

        // Check if any PRABOWO records exist
        $directQuery2 = "SELECT COUNT(*) as count FROM transaksi WHERE nama_siswa LIKE '%Prabowo%' OR keterangan LIKE '%Prabowo%'";
        $directResult2 = mysqli_query($conn, $directQuery2);
        $directCount2 = mysqli_fetch_assoc($directResult2)['count'];

        echo "<p><strong>Direct '%Prabowo%' Search:</strong> $directCount2 records found</p>";

        // Check exact match
        $directQuery3 = "SELECT COUNT(*) as count FROM transaksi WHERE nama_siswa LIKE '%Eko%' AND nama_siswa LIKE '%Prabowo%'";
        $directResult3 = mysqli_query($conn, $directQuery3);
        $directCount3 = mysqli_fetch_assoc($directResult3)['count'];

        echo "<p><strong>Combined 'Eko' + 'Prabowo' Search:</strong> $directCount3 records found</p>";

        // Show some sample records that contain "eko"
        if ($directCount > 0) {
            echo "<h4>📋 Sample Records with 'Eko':</h4>";
            $sampleQuery = "SELECT * FROM transaksi WHERE nama_siswa LIKE '%Eko%' OR keterangan LIKE '%Eko%' LIMIT 3";
            $sampleResult = mysqli_query($conn, $sampleQuery);

            echo "<table border='1' cellpadding='5' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr style='background: #f8f9fa;'>";
            echo "<th>Nama Siswa</th><th>Keterangan</th>";
            echo "</tr>";

            while ($sampleRow = mysqli_fetch_assoc($sampleResult)) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($sampleRow['nama_siswa']) . "</td>";
                echo "<td>" . htmlspecialchars($sampleRow['keterangan']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }

    } else {
        throw new Exception("Failed to prepare or execute query");
    }

} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "<h3>❌ Error:</h3>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
    echo "</div>";
    error_log("Search Test Error: " . $e->getMessage());
}

echo "<hr>";
echo "<p><a href='index.php?search=" . urlencode($search) . "'>🔍 Test in Main Application</a></p>";
echo "<p><a href='index.php'>← Back to Main Application</a></p>";
?>