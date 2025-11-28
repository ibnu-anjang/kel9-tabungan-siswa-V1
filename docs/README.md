# 📚 Sistem Tabungan Siswa - Tim 9

**Sistem Manajemen Tabungan Siswa / Kas Kelas** yang dibangun dengan PHP Native dan Bootstrap 5 untuk mengelola transaksi keuangan kelas.

## 🎯 Tujuan Proyek

- Membangun sistem CRUD Data Transaksi yang fungsional
- Memecahkan tantangan utama: **Pembedaan Jenis Transaksi (Masuk vs Keluar)**
- Menyajikan data dengan tampilan yang rapi dan responsif
- Deadline: 7 hari dengan metode Modified Waterfall

## 👥 Tim Development

| Nama | Peran | Tugas Utama | Fokus Teknis |
|------|-------|-------------|--------------|
| **Ibnu A. A. M** (Ketua) | Core Backend | CREATE & UPDATE | Koneksi DB, Validasi, Logika Saldo |
| **Muhammad Vizar B.** | Frontend Logic | READ | Display Data, Logika Warna, Format Rupiah |
| **Aradhana M. H.** | UI/UX Design | DELETE | UI Design, Styling, Simple Delete Logic |

## 🛠️ Tech Stack

| Kategori | Teknologi | Justifikasi |
|----------|-----------|-------------|
| **Backend** | PHP Native | Sederhana, mudah integrasi dengan HTML & MySQL |
| **Database** | MySQL/MariaDB | Stabil, teruji, bundle dengan XAMPP |
| **Frontend** | HTML5 + CSS3 | Standar web modern |
| **CSS Framework** | Bootstrap 5 | Responsif, cepat, tampilan rapi |
| **Client Script** | JavaScript | Validasi form, konfirmasi, interaksi sederhana |

## 📁 Struktur Folder

```
kel9-tabungan-siswa/
├── 📄 index.php                 # Halaman utama (READ data)
├── 📄 create.php               # Form tambah transaksi (CREATE)
├── 📄 edit.php                 # Form edit transaksi (UPDATE)
├── 📄 delete.php               # Proses hapus data (DELETE)
├── 📄 koneksi.php              # Koneksi database (terpusat)
├── 📄 setup_database.php       # Script setup database
├── 📄 laporan.php              # Halaman laporan/statistik
├── 📁 assets/                  # Assets statis
│   ├── 📁 css/
│   │   └── 📄 style.css        # Custom styles
│   ├── 📁 js/
│   │   └── 📄 script.js        # JavaScript functions
│   └── 📁 images/              # Gambar dan icon
├── 📁 api/                     # API endpoints (opsional)
│   ├── 📄 get_transactions.php
│   └── 📄 get_total_saldo.php
├── 📄 blueprint.md             # Dokumen teknis
├── 📄 README.md                # Dokumentasi ini
└── 📄 .env                     # Konfigurasi environment (jika perlu)
```

## 🚀 Quick Start

### 1. Persyaratan
- XAMPP (Apache + MySQL)
- Web browser (Chrome, Firefox, dll)
- Text editor (VS Code, Sublime, dll)

### 2. Instalasi
1. Clone/download folder ini ke `htdocs/` XAMPP
2. Aktifkan Apache dan MySQL di XAMPP Control Panel
3. Akses `http://localhost/phpmyadmin`
4. Buat database baru: `db_team9_tabungan`
5. Import atau jalankan `setup_database.php`
6. Akses aplikasi: `http://localhost/kel9-tabungan-siswa`

### 3. Konfigurasi Database
Edit file `koneksi.php` jika perlu:
```php
$db_host = "localhost";
$db_user = "root";      // default XAMPP
$db_pass = "";          // default XAMPP
$db_name = "db_team9_tabungan";
```

## 📋 Fitur Utama (CRUD)

### ✅ CREATE (index.php/create.php)
- Form tambah transaksi baru
- Validasi input (nama, tanggal, jenis, nominal)
- Pilihan jenis: **MASUK** (pemasukan) / **KELUAR** (pengeluaran)
- Auto-format nominal

### ✅ READ (index.php)
- Tampilan semua data transaksi
- Total saldo otomatis
- Warna berbeda untuk masuk (hijau) dan keluar (merah)
- Format Rupiah pada nominal
- Responsive design

### ✅ UPDATE (index.php/edit.php)
- Form edit transaksi yang sudah ada
- Pre-fill form dengan data lama
- Validasi input sama seperti CREATE

### ✅ DELETE (index.php/delete.php)
- Konfirmasi sebelum hapus
- Hapus data berdasarkan ID
- Auto-refresh setelah hapus

## 🎨 UI/UX Guidelines

### 🎯 Standar Visual
- **Hijau** untuk transaksi MASUK (+)
- **Merah** untuk transaksi KELUAR (-)
- Bootstrap 5 untuk komponen UI
- Responsive design (mobile-friendly)

### 📝 Format Data
- **Tanggal**: YYYY-MM-DD (HTML5 date input)
- **Nominal**: DECIMAL(15,2) di DB, format Rupiah di UI
- **Jenis**: ENUM('masuk', 'keluar')

## 🔒 Keamanan

- `mysqli_real_escape_string()` untuk prevent SQL Injection
- Validasi input di client dan server side
- Konfirmasi untuk aksi berbahaya (DELETE)

## 📊 Database Schema

```sql
CREATE TABLE transaksi (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nama_siswa VARCHAR(100) NOT NULL,
    tanggal DATE NOT NULL,
    jenis_transaksi ENUM('masuk', 'keluar') NOT NULL,
    nominal DECIMAL(15,2) NOT NULL,
    keterangan TEXT
);
```

## 🔧 Development Guidelines

### 📏 Coding Standards
- **PHP**: Snake case untuk variabel (`$nama_siswa`)
- **JavaScript**: Camel case untuk variabel (`formatRupiah`)
- **CSS**: Kebab case untuk class (`btn-success`)

### 🎯 Focus Area per Anggota

#### Ibnu (Backend Lead)
```php
// Fokus pada: CREATE & UPDATE Logic
- Koneksi database (koneksi.php)
- Validasi input
- Logika perhitungan saldo
- SQL queries untuk INSERT/UPDATE
```

#### Vizar (Frontend Logic)
```php
// Fokus pada: READ Display
- Looping data dari DB ke tabel
- Format angka ke Rupiah
- Logika warna (masuk/keluar)
- JavaScript interaktif
```

#### Aradhana (UI/UX)
```php
// Fokus pada: DELETE & Styling
- Bootstrap integration
- Desain responsif
- Konfirmasi DELETE
- Overall visual aesthetics
```

## 🚨 Important Notes

1. **Satu Koneksi**: Semua file WAJIB menggunakan `koneksi.php`
2. **Validasi**: Jangan percaya user input, selalu validasi!
3. **Naming**: Konsisten dengan snake_case untuk PHP
4. **Testing**: Test fitur CRUD satu per satu
5. **Backup**: Backup database sebelum demo!

## 📞 Hubungi Tim

- **Ketua Tim**: Ibnu A. A. M
- **Technical Support**: Muhammad Vizar B.
- **UI/UX Issues**: Aradhana M. H.

## 📈 Progress Tracking

- [x] Blueprint & Planning
- [x] Template Structure
- [ ] Database Setup
- [ ] CREATE Implementation
- [ ] READ Implementation
- [ ] UPDATE Implementation
- [ ] DELETE Implementation
- [ ] UI/UX Polish
- [ ] Testing & Bug Fix
- [ ] Final Demo

---

**Deadline**: 7 Hari 🏁
**Method**: Modified Waterfall
**Target**: ⭐⭐⭐⭐⭐ (Bonus untuk kerapian & fitur lengkap)

*"Build together, succeed together!"*