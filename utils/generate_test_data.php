<?php
include '../koneksi.php';

echo "=== GENERATE DUMMY DATA >100 RECORDS ===" . PHP_EOL;

// First, check current data
$result = mysqli_query($conn, 'SELECT COUNT(*) as total FROM transaksi');
$current_total = mysqli_fetch_assoc($result)['total'];
echo "Current data: $current_total records" . PHP_EOL;

// Clean up any existing test data
echo "Cleaning up existing test data..." . PHP_EOL;
mysqli_query($conn, "DELETE FROM transaksi WHERE nama_siswa LIKE 'Test_%'");

// Generate realistic test data
$names = [
    'Andi Pratama', 'Siti Nurhaliza', 'Budi Santoso', 'Dewi Lestari', 'Eko Prabowo',
    'Fitri Handayani', 'Gunawan Wijaya', 'Hana Kartika', 'Ibrahim Fikri', 'Jasmine Putri',
    'Kevin Wijaya', 'Lisa Permata', 'Muhammad Rizki', 'Nina Amelia', 'Omar Bakhti'
];

$keterangan_masuk = [
    'Tabungan rutin', 'Uang jajan', 'Hadiah ulang tahun', 'Tugas rumah', 'Prestasi',
    'Bantuan orang tua', 'Kerja sampingan', 'Hadiah sekolah', 'Tabungan liburan', 'Uang saku'
];

$keterangan_keluar = [
    'Beli buku', 'Bayar SPP', 'Jajan sekolah', 'Beli alat tulis', 'Transport',
    'Kegiatan sekolah', 'Belanja kebutuhan', 'Bayar les', 'Biaya lapangan', 'Uang tak terduga'
];

echo PHP_EOL . "Generating 250 dummy transactions..." . PHP_EOL;

$records_created = 0;
$transactions_per_day = 2;

// Generate data for the last 125 days (2 transactions per day)
for ($day = 0; $day < 125; $day++) {
    $tanggal = date('Y-m-d', strtotime("-$day days"));

    for ($trans = 0; $trans < $transactions_per_day; $trans++) {
        // Select random student
        $name = $names[array_rand($names)];
        $student_name = "Test_" . str_replace(' ', '_', $name) . "_" . ($day + 1) . "_" . ($trans + 1);

        // 70% chance masuk, 30% chance keluar
        $jenis = (rand(1, 10) <= 7) ? 'masuk' : 'keluar';

        // Generate nominal based on type
        if ($jenis == 'masuk') {
            // Masuk: 10,000 - 200,000
            $nominal = rand(10000, 200000);
            $keterangan = $keterangan_masuk[array_rand($keterangan_masuk)];
        } else {
            // Keluar: 5,000 - 150,000
            $nominal = rand(5000, 150000);
            $keterangan = $keterangan_keluar[array_rand($keterangan_keluar)];
        }

        // Insert transaction
        $insert_query = "INSERT INTO transaksi (nama_siswa, tanggal, jenis_transaksi, nominal, keterangan) VALUES (?, ?, ?, ?, ?)";

        try {
            $stmt = executeQuery($conn, $insert_query, [
                $student_name,
                $tanggal,
                $jenis,
                $nominal,
                $keterangan
            ], 'sssds');

            if ($stmt) {
                $records_created++;
                echo ".";
                mysqli_stmt_close($stmt);
            }
        } catch (Exception $e) {
            echo "Error creating record: " . $e->getMessage() . PHP_EOL;
        }
    }
}

echo PHP_EOL . PHP_EOL . "✅ Successfully created $records_created dummy transactions!" . PHP_EOL;

// Verify the data
$result = mysqli_query($conn, 'SELECT COUNT(*) as total FROM transaksi');
$new_total = mysqli_fetch_assoc($result)['total'];
echo "Total records in database: $new_total" . PHP_EOL;

// Get some statistics
$stats_query = "SELECT
    jenis_transaksi,
    COUNT(*) as count,
    SUM(nominal) as total_nominal,
    AVG(nominal) as avg_nominal,
    MIN(nominal) as min_nominal,
    MAX(nominal) as max_nominal
FROM transaksi
WHERE nama_siswa LIKE 'Test_%'
GROUP BY jenis_transaksi";

$stats_result = mysqli_query($conn, $stats_query);

echo PHP_EOL . "=== STATISTICS ===" . PHP_EOL;
while ($stats = mysqli_fetch_assoc($stats_result)) {
    $type = ucfirst($stats['jenis_transaksi']);
    $count = number_format($stats['count'], 0, ',', '.');
    $total = number_format($stats['total_nominal'], 0, ',', '.');
    $avg = number_format($stats['avg_nominal'], 0, ',', '.');
    $min = number_format($stats['min_nominal'], 0, ',', '.');
    $max = number_format($stats['max_nominal'], 0, ',', '.');

    echo "$type Transactions:" . PHP_EOL;
    echo "  Count: $count" . PHP_EOL;
    echo "  Total: Rp $total" . PHP_EOL;
    echo "  Average: Rp $avg" . PHP_EOL;
    echo "  Range: Rp $min - Rp $max" . PHP_EOL;
    echo PHP_EOL;
}

// Date range
$date_range_query = "SELECT MIN(tanggal) as earliest, MAX(tanggal) as latest FROM transaksi WHERE nama_siswa LIKE 'Test_%'";
$range_result = mysqli_query($conn, $date_range_query);
$range = mysqli_fetch_assoc($range_result);

echo "=== DATE RANGE ===" . PHP_EOL;
echo "Earliest transaction: {$range['earliest']}" . PHP_EOL;
echo "Latest transaction: {$range['latest']}" . PHP_EOL;
echo "Span: " . (strtotime($range['latest']) - strtotime($range['earliest'])) / (60 * 60 * 24) . " days" . PHP_EOL;
echo PHP_EOL;

// Test different limits to show the quick fix in action
echo "=== TESTING QUICK FIX WITH REAL DATA ===" . PHP_EOL;

$test_limits = [10, 25, 50, 100, 500, 1000];

foreach ($test_limits as $limit) {
    echo PHP_EOL . "Testing with limit = $limit:" . PHP_EOL;

    $start_time = microtime(true);
    $result = mysqli_query($conn, "SELECT * FROM transaksi WHERE nama_siswa LIKE 'Test_%' ORDER BY tanggal DESC, id DESC LIMIT $limit");
    $end_time = microtime(true);

    $displayed = mysqli_num_rows($result);
    $total_test_query = "SELECT COUNT(*) as total FROM transaksi WHERE nama_siswa LIKE 'Test_%'";
    $total_result = mysqli_query($conn, $total_test_query);
    $total_test_data = mysqli_fetch_assoc($total_result)['total'];

    $hidden = max(0, $total_test_data - $displayed);
    $execution_time = number_format(($end_time - $start_time) * 1000, 2);

    echo "  ✓ Displayed: $displayed records" . PHP_EOL;
    echo "  ✓ Hidden: $hidden records" . PHP_EOL;
    echo "  ✓ Total test data: $total_test_data records" . PHP_EOL;
    echo "  ✓ Execution time: {$execution_time}ms" . PHP_EOL;

    if ($hidden > 0) {
        echo "  ⚠️  WARNING: Data hidden due to limit!" . PHP_EOL;
        echo "     → Quick Fix allows up to 1000 records" . PHP_EOL;
    } else {
        echo "  ✅ All test data visible" . PHP_EOL;
    }
}

echo PHP_EOL . "=== TESTING WARNING SYSTEM ===" . PHP_EOL;

// Simulate the index.php logic
$limit = 100; // Default limit
$total_query = "SELECT COUNT(*) as total_records FROM transaksi";
$result_total = mysqli_query($conn, $total_query);
$total_records_data = mysqli_fetch_assoc($result_total);
$total_records = $total_records_data['total_records'];

echo "Simulating default behavior (limit = 100):" . PHP_EOL;
echo "Total database records: " . number_format($total_records, 0, ',', '.') . PHP_EOL;
echo "Default limit: 100" . PHP_EOL;
echo "Hidden records: " . number_format($total_records - 100, 0, ',', '.') . PHP_EOL;

if ($total_records > $limit) {
    echo PHP_EOL . "🚨 WARNING MESSAGE WOULD APPEAR:" . PHP_EOL;
    echo "⚠️ Perhatian!" . PHP_EOL;
    echo "Menampilkan 100 dari total " . number_format($total_records, 0, ',', '.') . " transaksi." . PHP_EOL;
    echo "Data lebih lama tidak ditampilkan karena melebihi batas (100)." . PHP_EOL;
    echo "Untuk melihat semua data, pilih 'Jumlah Data: 1000' atau gunakan filter pencarian." . PHP_EOL;
}

echo PHP_EOL . "=== QUICK FIX DEMONSTRATION ===" . PHP_EOL;
echo "With Quick Fix:" . PHP_EOL;
echo "✅ Limit increased to 1000 records" . PHP_EOL;
echo "✅ Dropdown has more options (500, 1000)" . PHP_EOL;
echo "✅ Warning system implemented" . PHP_EOL;
echo "✅ Users can see up to 10x more data" . PHP_EOL;

echo PHP_EOL . "🎉 TEST DATA GENERATION COMPLETED!" . PHP_EOL;
echo "Visit index.php to see the quick fix in action!" . PHP_EOL;
echo "📁 Test data can be cleaned up later with: DELETE FROM transaksi WHERE nama_siswa LIKE 'Test_%'" . PHP_EOL;
?>