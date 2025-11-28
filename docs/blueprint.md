Tentu, Ibnu. Menyusun dokumen *Tech Stack* (Tumpukan Teknologi) adalah praktik profesional yang menunjukkan perencanaan matang. Dokumen ini akan sangat membantu saat kamu presentasi ke guru.

Berikut adalah **Dokumen Blueprint Tumpukan Teknologi** untuk **Sistem Tabungan Siswa Tim 9**.

***

# 📄 BLUEPRINT TEKNIS & TUMPukan TEKNOLOGI
## PROYEK: SISTEM TABUNGAN SISWA / KAS KELAS (TEAM 9)

### 1. PENDAHULUAN & TUJUAN PROYEK

Dokumen ini merinci tumpukan teknologi (Tech Stack), arsitektur, dan alokasi peran yang digunakan dalam pengembangan **Sistem Tabungan Siswa**.

* **Model Pengembangan:** *Modified Waterfall*, disesuaikan untuk *deadline* 7 hari.
* **Target Utama:** Menghasilkan aplikasi yang memenuhi kriteria CRUD **Data Transaksi** dan menyelesaikan tantangan utama: **Pembedaan Jenis Transaksi (Masuk vs Keluar)** dengan tampilan yang rapi (nilai plus).

***

### 2. TUMPUKAN TEKNOLOGI INTI (CORE TECH STACK)

Kami memilih kombinasi teknologi **LAMP Stack (Linux/XAMPP, Apache, MySQL, PHP)** karena sifatnya yang ringan (*lightweight*), stabil, dan cepat diimplementasikan untuk aplikasi berbasis web PHP Native.

| Kategori | Teknologi | Deskripsi dan Justifikasi Pemilihan |
| :--- | :--- | :--- |
| **Backend** | **PHP Native** | Bahasa *server-side* utama. Dipilih karena kesederhanaan dan kemudahan integrasi langsung dengan HTML (Embedded Scripting) dan MySQL. Digunakan untuk logika CRUD dan perhitungan saldo. |
| **Database** | **MySQL / MariaDB** | Sistem Manajemen Basis Data Relasional (RDBMS) yang teruji, andal, dan *bundle* dalam XAMPP. |
| **Frontend (View)** | **HTML5 & CSS3** | Standar markup dan *styling* dasar. |
| **Frontend Framework** | **Bootstrap 5** | Kerangka kerja CSS yang digunakan untuk **mempercepat pengembangan UI** dan memastikan tampilan **responsif** (*mobile-friendly*) dan **rapi** (memenuhi kriteria nilai plus kerapian). |
| **Client Script** | **JavaScript (JS)** | Digunakan untuk fungsi sederhana di sisi klien, seperti validasi form (memastikan input terisi) dan konfirmasi sebelum aksi `DELETE`. |

***

### 3. ARSITEKTUR APLIKASI & LINGKUNGAN

#### 3.1 Struktur Database (`db_team9_tabungan`)
Struktur database dirancang untuk mengatasi tantangan pembedaan transaksi melalui kolom `jenis_transaksi`.

| Kolom | Tipe Data | Atribut Kunci | Peran Khusus |
| :--- | :--- | :--- | :--- |
| `id` | INT | **PRIMARY KEY, A_I** | Pengenal unik untuk setiap baris. |
| `nama_siswa` | VARCHAR(100) | NOT NULL | Data penanggung jawab transaksi. |
| `tanggal` | DATE | NOT NULL | Data temporal untuk *tracking*. |
| `jenis_transaksi` | **ENUM** | NOT NULL | **KUNCI TANTANGAN:** Hanya menerima nilai `'masuk'` atau `'keluar'`. |
| `nominal` | **DECIMAL(15,2)** | NOT NULL | Digunakan untuk presisi data mata uang (menghindari error hitungan *float*). |
| `keterangan` | TEXT | NULL | Catatan tambahan transaksi. |

#### 3.2 Lingkungan Pengembangan (Environment)
* **Environment:** Lokal (*Localhost*)
* **Server:** XAMPP (Apache)
* **URL Akses:** `http://localhost/team9_tabungan/index.php`

***

### 4. ALOKASI SUMBER DAYA & PERAN (JOB ROLE BREAKDOWN)

Pembagian tugas didasarkan pada kekuatan teknis masing-masing anggota untuk memastikan setiap fitur CRUD memiliki penanggung jawab yang jelas.

| Anggota Tim | Peran Teknis | Tugas CRUD Utama | Fokus Logika |
| :--- | :--- | :--- | :--- |
| **Ibnu A. A. M (Ketua)** | Core Backend | **CREATE** (Tambah Data) & **UPDATE** (Edit Data) | Koneksi DB, Validasi Input, Logika Saldo (**Total Saldo**). |
| **Muhammad Vizar B.** | Frontend Logic | **READ** (Tampil Data) | *Looping* data, **Logika Warna** (Hijau/Merah), Format Angka ke Rupiah. |
| **Aradhana M. H.** | UI/UX Design | **DELETE** (Hapus Data) | Desain Tampilan (Bootstrap), *Styling* Umum, Logika `DELETE` yang *simple*. |

***

### 5. STANDAR TEKNIS & KUALITAS

Untuk menjaga kualitas dan integrasi kode, ditetapkan standar berikut:

| Area | Standar Teknis | Manfaat |
| :--- | :--- | :--- |
| **Koneksi** | File `koneksi.php` terpusat. | Memastikan semua anggota menggunakan satu *setting* koneksi yang sama, menghindari konflik. |
| **Penanganan Data** | Penggunaan fungsi `number_format()` | Memastikan `nominal` yang ditarik dari DB tampil sebagai format Rupiah (`Rp X.XXX`) di sisi klien (tugas Vizar). |
| **Keamanan** | Penggunaan `mysqli_real_escape_string` (minimal) | Mengurangi risiko *SQL Injection* pada inputan untuk data string. |
| **Kerapian Kode** | Naming Convention (`snake_case`) | Penggunaan format `snake_case` untuk variabel PHP (`$nama_siswa`) agar konsisten dan mudah dibaca oleh semua anggota. |