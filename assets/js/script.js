// JavaScript untuk Sistem Tabungan Siswa

// Fungsi untuk format angka ke Rupiah
function formatRupiah(amount) {
    return 'Rp ' + parseFloat(amount).toLocaleString('id-ID', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Enhanced Delete Confirmation by Backend Lead Ibnu A. A. M
function confirmDelete(id, nama) {
    const confirmDialog = confirm(`⚠️ KONFIRMASI HAPUS\n\nApakah Anda yakin ingin menghapus transaksi:\n\n👤 Nama: ${nama}\n🆔 ID: ${id}\n\n⚠️ Tindakan ini tidak dapat dibatalkan!`);

    if (confirmDialog) {
        // Tambah loading state
        const button = event.target;
        const originalText = button.innerHTML;
        button.innerHTML = '🔄 Menghapus...';
        button.disabled = true;

        // Redirect ke delete
        window.location.href = `delete.php?id=${id}`;
    } else {
        // User cancelled - log for analytics
        console.log(`Delete cancelled for ID: ${id}, Name: ${nama}`);
    }
}

// Enhanced form validation with real-time feedback
function validateFormRealtime() {
    const form = document.getElementById('transaction-form');
    if (!form) return;

    const namaInput = document.getElementById('nama_siswa');
    const nominalInput = document.getElementById('nominal');
    const tanggalInput = document.getElementById('tanggal');

    // Real-time validation feedback
    if (namaInput) {
        namaInput.addEventListener('input', function() {
            const value = this.value.trim();
            if (value.length > 0 && value.length < 3) {
                this.classList.add('is-invalid');
                showError('Nama minimal 3 karakter!');
            } else {
                this.classList.remove('is-invalid');
                hideError();
            }
        });
    }

    if (nominalInput) {
        nominalInput.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value < 0) {
                this.classList.add('is-invalid');
                showError('Nominal tidak boleh negatif!');
            } else {
                this.classList.remove('is-invalid');
                hideError();
            }
        });
    }

    if (tanggalInput) {
        tanggalInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const today = new Date();

            if (selectedDate > today) {
                this.classList.add('is-invalid');
                showError('Tanggal tidak boleh di masa depan!');
            } else {
                this.classList.remove('is-invalid');
                hideError();
            }
        });
    }
}

// Utility functions for validation feedback
function showError(message) {
    let errorDiv = document.getElementById('validation-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.id = 'validation-error';
        errorDiv.className = 'alert alert-danger mt-2';
        errorDiv.style.display = 'none';
        const form = document.querySelector('form');
        if (form) form.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function hideError() {
    const errorDiv = document.getElementById('validation-error');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

// Fungsi untuk validasi form tambah/edit
function validateForm() {
    const nama = document.getElementById('nama_siswa').value.trim();
    const tanggal = document.getElementById('tanggal').value;
    const jenis = document.getElementById('jenis_transaksi').value;
    const nominal = document.getElementById('nominal').value;

    if (nama === '') {
        alert('Nama siswa harus diisi!');
        return false;
    }

    if (tanggal === '') {
        alert('Tanggal harus diisi!');
        return false;
    }

    if (jenis === '') {
        alert('Jenis transaksi harus dipilih!');
        return false;
    }

    if (nominal === '' || nominal <= 0) {
        alert('Nominal harus diisi dengan angka positif!');
        return false;
    }

    return true;
}

// Fungsi untuk menghitung dan menampilkan total saldo
function updateTotalSaldo() {
    fetch('api/get_total_saldo.php')
        .then(response => response.json())
        .then(data => {
            const saldoElement = document.getElementById('total-saldo');
            if (data.success) {
                saldoElement.textContent = formatRupiah(data.total_saldo);

                // Add color based on balance
                if (data.total_saldo < 0) {
                    saldoElement.className = 'display-4 text-danger';
                } else {
                    saldoElement.className = 'display-4';
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Fungsi untuk load data transaksi via AJAX (opsional)
function loadTransactions() {
    fetch('api/get_transactions.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector('table tbody');
            tbody.innerHTML = '';

            data.forEach((trans, index) => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${index + 1}</td>
                    <td>${trans.nama_siswa}</td>
                    <td>${trans.tanggal}</td>
                    <td><span class="badge badge-${trans.jenis_transaksi}">${trans.jenis_transaksi.toUpperCase()}</span></td>
                    <td class="nominal ${trans.jenis_transaksi}">${formatRupiah(trans.nominal)}</td>
                    <td>${trans.keterangan || '-'}</td>
                    <td>
                        <a href="edit.php?id=${trans.id}" class="btn btn-sm btn-warning">✏️</a>
                        <button onclick="confirmDelete(${trans.id}, '${trans.nama_siswa}')" class="btn btn-sm btn-danger">🗑️</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Mobile menu toggle function
function toggleMobileMenu() {
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        sidebar.classList.toggle('mobile-open');

        // Close mobile menu when clicking outside
        if (sidebar.classList.contains('mobile-open')) {
            setTimeout(function() {
                document.addEventListener('click', closeMobileMenuOutside);
            }, 100);
        } else {
            document.removeEventListener('click', closeMobileMenuOutside);
        }
    }
}

function closeMobileMenuOutside(event) {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.querySelector('.mobile-menu-toggle');

    if (sidebar && menuToggle &&
        !sidebar.contains(event.target) &&
        !menuToggle.contains(event.target)) {
        sidebar.classList.remove('mobile-open');
        document.removeEventListener('click', closeMobileMenuOutside);
    }
}

// Enhanced delete confirmation with mobile support
function confirmDelete(id, nama) {
    // Use mobile-friendly confirmation
    const isMobile = window.innerWidth <= 768;
    let confirmMessage;

    if (isMobile) {
        confirmMessage = `Hapus transaksi?\n\n👤 ${nama}\nID: ${id}\n\n⚠️ Tidak bisa dibatalkan!`;
    } else {
        confirmMessage = `⚠️ KONFIRMASI HAPUS\n\nApakah Anda yakin ingin menghapus transaksi:\n\n👤 Nama: ${nama}\n🆔 ID: ${id}\n\n⚠️ Tindakan ini tidak dapat dibatalkan!`;
    }

    const confirmDialog = confirm(confirmMessage);

    if (confirmDialog) {
        // Find and disable the button
        const button = event ? event.target : null;
        if (button && button.tagName === 'BUTTON') {
            button.innerHTML = '🔄 Menghapus...';
            button.disabled = true;
            button.style.opacity = '0.7';
        }

        // Redirect to delete with small delay for UX
        setTimeout(function() {
            window.location.href = `delete.php?id=${id}`;
        }, 500);
    } else {
        // User cancelled - log for analytics
        console.log(`Delete cancelled for ID: ${id}, Name: ${nama}`);
    }
}

// Enhanced form validation with mobile support
function validateForm() {
    const nama = document.getElementById('nama_siswa');
    const tanggal = document.getElementById('tanggal');
    const nominal = document.getElementById('nominal');

    let isValid = true;

    // Validate nama
    if (nama && nama.value.trim() === '') {
        showFieldError(nama, 'Nama siswa harus diisi!');
        isValid = false;
    } else if (nama && nama.value.trim().length < 3) {
        showFieldError(nama, 'Nama minimal 3 karakter!');
        isValid = false;
    } else if (nama) {
        clearFieldError(nama);
    }

    // Validate tanggal
    if (tanggal && tanggal.value === '') {
        showFieldError(tanggal, 'Tanggal harus diisi!');
        isValid = false;
    } else if (tanggal && new Date(tanggal.value) > new Date()) {
        showFieldError(tanggal, 'Tanggal tidak boleh di masa depan!');
        isValid = false;
    } else if (tanggal) {
        clearFieldError(tanggal);
    }

    // Validate nominal
    if (nominal && (nominal.value === '' || parseFloat(nominal.value) <= 0)) {
        showFieldError(nominal, 'Nominal harus lebih besar dari 0!');
        isValid = false;
    } else if (nominal && parseFloat(nominal.value) > 999999999.99) {
        showFieldError(nominal, 'Nominal terlalu besar!');
        isValid = false;
    } else if (nominal) {
        clearFieldError(nominal);
    }

    return isValid;
}

// Show field error message
function showFieldError(field, message) {
    if (field) {
        field.classList.add('is-invalid');

        // Create or update error message
        let errorDiv = field.parentNode.querySelector('.field-error');
        if (!errorDiv) {
            errorDiv = document.createElement('div');
            errorDiv.className = 'field-error text-danger small mt-1';
            field.parentNode.appendChild(errorDiv);
        }
        errorDiv.textContent = message;

        // Scroll to error on mobile
        if (window.innerWidth <= 768) {
            setTimeout(function() {
                field.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 100);
        }
    }
}

// Clear field error message
function clearFieldError(field) {
    if (field) {
        field.classList.remove('is-invalid');
        const errorDiv = field.parentNode.querySelector('.field-error');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Update total saldo saat halaman dimuat
    updateTotalSaldo();

    // Load transaksi jika menggunakan AJAX
    // loadTransactions();

    // Add event listener untuk form validation
    const form = document.getElementById('transaction-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateForm()) {
                e.preventDefault();
            }
        });
    }

    // Auto-format nominal input
    const nominalInput = document.getElementById('nominal');
    if (nominalInput) {
        nominalInput.addEventListener('input', function() {
            // Remove non-numeric characters except decimal point
            this.value = this.value.replace(/[^0-9.]/g, '');

            // Real-time validation
            if (this.value && parseFloat(this.value) <= 0) {
                showFieldError(this, 'Nominal harus lebih besar dari 0!');
            } else if (this.value) {
                clearFieldError(this);
            }
        });
    }

    // Handle window resize for mobile menu
    window.addEventListener('resize', function() {
        const sidebar = document.getElementById('sidebar');
        if (window.innerWidth > 768 && sidebar) {
            sidebar.classList.remove('mobile-open');
            document.removeEventListener('click', closeMobileMenuOutside);
        }
    });

    // Close mobile menu on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const sidebar = document.getElementById('sidebar');
            if (sidebar && sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
                document.removeEventListener('click', closeMobileMenuOutside);
            }
        }
    });
});

// Utility functions
function showNotification(message, type = 'success') {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

    document.querySelector('.container').insertBefore(alertDiv, document.querySelector('.container').firstChild);

    // Auto remove after 3 seconds
    setTimeout(function() {
        alertDiv.remove();
    }, 3000);
}