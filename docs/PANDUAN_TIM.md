# 📖 PANDUAN DEVELOPMENT TIM 9
## Sistem Tabungan Siswa - Panduan Praktis untuk Tim Development

**Versi:** 1.0
**Deadline:** 7 Hari
**Methodology:** Modified Waterfall

---

## 🎯 MISI TIM

> **Membangun sistem CRUD Data Transaksi yang fungsional dengan fokus pada pembedaan jenis transaksi (MASUK/KELUAR) dan tampilan yang rapi.**

---

## 👥 PEMBAGIAN TUGAS & FOKUS AREA

### 🏗️ **Ibnu A. A. M (Ketua Tim) - Backend Lead**
**📍 Fokus Utama: CREATE & UPDATE**

#### Tugas Prioritas:
1. **CREATE (`create.php`)**
   ```php
   // Focus area:
   - Form validation di server side
   - Sanitasi input dengan mysqli_real_escape_string
   - Query INSERT yang aman
   - Error handling yang user-friendly
   ```

2. **UPDATE (`edit.php`)**
   ```php
   // Focus area:
   - Pre-fill form dari database
   - Query UPDATE berdasarkan ID
   - Validasi ID yang valid
   - Maintain data integrity
   ```

3. **Logika Saldo & Koneksi**
   ```php
   // Di koneksi.php & helper functions:
   - Hitung total saldo (SUM masuk - keluar)
   - Koneksi database terpusat
   - Fungsi-fungsi bantu backend
   ```

**🎯 Target Output:**
- Form CREATE yang valid dan aman
- Form UPDATE yang pre-fill dengan data lama
- Koneksi database yang konsisten
- Perhitungan saldo yang akurat

---

### 🎨 **Muhammad Vizar B. - Frontend Logic**
**📍 Fokus Utama: READ & Display Logic**

#### Tugas Prioritas:
1. **READ (`index.php`)**
   ```php
   // Focus area:
   - Query SELECT untuk ambil semua transaksi
   - Loop data ke dalam tabel HTML
   - Format tanggal ke format Indonesia (dd/mm/yyyy)
   - Number format untuk nominal (Rp X.XXX)
   ```

2. **Logika Warna**
   ```php
   // Implementasi:
   - Transaksi MASUK = Warna Hijau (text-success)
   - Transaksi KELUAR = Warna Merah (text-danger)
   - Badge atau icon untuk visual cue
   ```

3. **JavaScript Logic**
   ```javascript
   // Di assets/js/script.js:
   - formatRupiah() function
   - confirmDelete() function
   - Auto-refresh setelah CRUD
   - Form validation di client side
   ```

**🎯 Target Output:**
- Tampilan tabel yang rapi dan terformat
- Perbedaan visual antara MASUK/KELUAR
- Format Rupiah yang konsisten
- Interaktivitas JavaScript yang smooth

---

### 🎭 **Aradhana M. H. - UI/UX Design**
**📍 Fokus Utama: DELETE & Styling**

#### Tugas Prioritas:
1. **DELETE (`delete.php`)**
   ```php
   // Focus area:
   - Konfirmasi sebelum hapus (JavaScript)
   - Query DELETE berdasarkan ID
   - Redirect yang proper
   - Pesan success/error yang jelas
   ```

2. **Bootstrap Integration**
   ```html
   // Components yang harus dikuasai:
   - Card layouts
   - Button styling (success, danger, warning)
   - Table styling (striped, hover, responsive)
   - Alert messages
   - Form components
   ```

3. **Responsive Design**
   ```css
   // Di assets/css/style.css:
   - Mobile-first approach
   - Breakpoints untuk tablet/phone
   - Custom colors untuk branding
   - Animasi subtle untuk UX
   ```

**🎯 Target Output:**
- UI yang responsif di semua device
- Konfirmasi delete yang user-friendly
- Warna dan styling yang konsisten
- Animasi dan interaksi yang smooth

---

## 🛠️ WORKFLOW DEVELOPMENT

### Phase 1: Setup (Day 1)
1. **Semua tim:** Clone/pull project dari shared folder
2. **Ibnu:** Setup database dengan `setup_database.php`
3. **Vizar:** Test koneksi database
4. **Aradhana:** Setup Bootstrap dan basic styling

### Phase 2: Core CRUD (Day 2-3)
1. **Ibnu:** Implement CREATE (`create.php`)
2. **Vizar:** Implement READ (`index.php`)
3. **Aradhana:** Implement DELETE (`delete.php`)
4. **Ibnu:** Implement UPDATE (`edit.php`)

### Phase 3: Polish & Enhancement (Day 4-5)
1. **Vizar:** Tambahkan format Rupiah & logika warna
2. **Aradhana:** Responsive design & animations
3. **Ibnu:** Validasi input & error handling
4. **All:** Testing & bug fixing

### Phase 4: Final Touch (Day 6-7)
1. **Semua tim:** Integration testing
2. **Aradhana:** Final UI polish
3. **Vizar:** Performance optimization
4. **Ibnu:** Documentation & deployment

---

## 📋 CHECKLIST PER FITUR

### ✅ CREATE (`create.php`)
- [ ] Form dengan validasi HTML5
- [ ] Sanitasi input di server side
- [ ] Query INSERT yang aman
- [ ] Error handling yang clear
- [ ] Success message yang informatif
- [ ] Redirect yang proper

### ✅ READ (`index.php`)
- [ ] Query SELECT yang optimal
- [ ] Loop data ke tabel HTML
- [ ] Format tanggal Indonesia
- [ ] Format Rupiah dengan number_format
- [ ] Warna berbeda untuk MASUK/KELUAR
- [ ] Total saldo yang auto-update

### ✅ UPDATE (`edit.php`)
- [ ] Pre-fill form dengan data lama
- [ ] Validasi ID yang valid
- [ ] Query UPDATE yang aman
- [ ] Error handling yang baik
- [ ] Success message yang jelas

### ✅ DELETE (`delete.php`)
- [ ] Konfirmasi JavaScript
- [ ] Query DELETE berdasarkan ID
- [ ] Redirect yang tepat
- [ ] Pesan success/error yang user-friendly

---

## 🔧 STANDAR CODING

### PHP Standards
```php
// 1. Gunakan snake_case untuk variabel
$nama_siswa = "John Doe";
$tanggal_transaksi = "2024-01-15";

// 2. Selalu sanitasi input
$nama_siswa = mysqli_real_escape_string($connection, trim($_POST['nama_siswa']));

// 3. Validasi sebelum query
if (!empty($nama_siswa) && is_numeric($nominal)) {
    // Lakukan query
}

// 4. Error handling yang informatif
if (mysqli_query($connection, $query)) {
    echo "Success!";
} else {
    echo "Error: " . mysqli_error($connection);
}
```

### JavaScript Standards
```javascript
// 1. Gunakan camelCase untuk variabel
function formatRupiah(amount) {
    return 'Rp ' + amount.toLocaleString('id-ID');
}

// 2. Selalu validasi form
function validateForm() {
    const nama = document.getElementById('nama_siswa').value.trim();
    if (nama === '') {
        alert('Nama harus diisi!');
        return false;
    }
    return true;
}
```

### CSS Standards
```css
/* 1. Gunakan kebab-case untuk class */
.btn-custom {
    border-radius: 8px;
}

/* 2. Mobile-first approach */
.table-responsive {
    font-size: 1rem;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.8rem;
    }
}
```

---

## 🚨 POTENTIAL ISSUES & SOLUTIONS

### Common Backend Issues
**Problem:** SQL Injection
**Solution:** Selalu gunakan `mysqli_real_escape_string()`

**Problem:** Data tidak masuk ke database
**Solution:** Check koneksi, nama table, dan kolom

**Problem:** Error timezone
**Solution:** Set timezone di PHP: `date_default_timezone_set('Asia/Jakarta');`

### Common Frontend Issues
**Problem:** Tampilan mobile berantakan
**Solution:** Gunakan Bootstrap responsive classes

**Problem:** Format Rupiah tidak konsisten
**Solution:** Buat function `formatRupiah()` terpusat

**Problem:** Confirm delete tidak berfungsi
**Solution:** Check JavaScript errors di console

---

## 📞 COMMUNICATION PROTOCOL

### Daily Standup (15 menit)
- **What did you do yesterday?**
- **What will you do today?**
- **Any blockers/issues?**

### Progress Updates
- Update progress di README.md checklist
- Screenshot output di group chat
- Report critical issues immediately

### Code Review
- Review code sebelum merge
- Focus pada security and best practices
- Test semua fitur sebelum declare "done"

---

## 🎯 SUCCESS METRICS

### Functional Requirements
- [ ] Semua CRUD operations berfungsi
- [ ] Validasi input berjalan
- [ ] Format data konsisten
- [ ] Responsive design works

### Bonus Points
- [ ] Animasi dan transitions smooth
- [ ] Error handling user-friendly
- [ ] Performance optimized
- [ ] Documentation lengkap

### Grade Target
- **80-89:** ⭐⭐⭐⭐ (Baik sekali)
- **90-100:** ⭐⭐⭐⭐⭐ (Sempurna + Bonus)

---

## 🏁 FINAL DELIVERY

### Package yang harus dikumpulkan:
1. Source code lengkap (folder `kel9-tabungan-siswa/`)
2. Database export (SQL file)
3. Demo video/screenshots
4. README.md yang updated
5. Laporan documentation (jika required)

### Demo Requirements:
1. Show all CRUD operations
2. Show responsive design
3. Show error handling
4. Show data validation

---

**"Build together, test together, succeed together!" - Tim 9**

📞 **Hubungi Ketua Tim** untuk emergency issues
📧 **Technical Support** untuk coding problems
🎨 **UI/UX Help** untuk design issues