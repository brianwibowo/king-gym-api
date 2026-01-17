# 📋 King Gym App - Dokumentasi Fitur Lengkap

Dokumen ini berisi rangkuman fitur yang telah tersedia dalam aplikasi King Gym Management System. Aplikasi ini dirancang untuk memudahkan operasional gym mulai dari kasir, keanggotaan, hingga manajemen karyawan.

---

## 📱 1. Login & Keamanan
Halaman pertama yang diakses untuk menjaga keamanan data.
*   **Secure Login**: Masuk menggunakan Email dan Password yang terdaftar.
*   **Forgot Password**: Fitur "Lupa Password" mandiri. User bisa me-reset password langsung dari aplikasi tanpa harus menghubungi developer.
*   **Role-Based Access**: Sistem mengenali apakah user adalah **Superadmin (Owner)** atau **Admin (Staff)**, yang akan membedakan akses fitur tertentu di dalam aplikasi.

---

## 🏠 2. Dashboard (Beranda)
Pusat informasi utama untuk melihat performa gym secara *real-time*.
*   **Income Summary**: Ringkasan keuangan komprehensif yang menampilkan:
    *   Total Omzet (Pemasukan) berdasarkan rentang waktu yang dipilih.
    *   Persentase kontribusi dari Membership vs Penjualan Produk.
    *   Filter Tanggal Fleksibel (Hari Ini, 7 Hari Terakhir, Bulan Ini, atau Custom).
*   **Products Insights**: Analisis mendalam performa produk (Pie Chart & List):
    *   **Top 3 Best Selling**: Produk paling laris terjual.
    *   **Least Selling**: Produk yang kurang diminati.
    *   Filter periode wawasan (Harian, Mingguan, Bulanan).
*   **Recent Transactions**: Daftar transaksi terbaru yang masuk hari ini, memudahkan pemantauan arus kas secara real-time.

---

## 🛒 3. Point of Sale (POS / Kasir)
Mesin kasir digital untuk memproses pembayaran member.
*   **Katalog Terintegrasi**:
    *   **Membership**: Pilihan paket member (Harian, Bulanan, Tahunan).
    *   **Produk**: Penjualan barang retail (Air Mineral, Suplemen, Merchandise, dll).
*   **Keranjang Belanja**: Bisa memasukkan beberapa item sekaligus dalam satu transaksi (misal: Daftar Member + Beli Minum).
*   **Manajemen Qty**: Bisa menambah/mengurangi jumlah barang dengan mudah.
*   **Checkout Fleksibel**:
    *   Otomatis menghitung Total Harga.
    *   Mendukung pilihan metode pembayaran (Tunai, Transfer, QRIS).
    *   Pencatatan nama customer/member.

---

## 👥 4. Membership (Manajemen Anggota)
Database lengkap untuk mengelola pelanggan setia gym.
*   **Daftar Member**: List semua member dengan indikator status warna:
    *   🟢 **Active**: Member aktif.
    *   🔴 **Expired**: Masa aktif habis.
*   **Pencarian Cepat**: Cari member berdasarkan Nama atau Email.
*   **Tambah Member Baru**: Form pendaftaran member baru yang praktis.
*   **Edit & Hapus**: Update data member atau hapus jika diperlukan.
*   **Perpanjangan (Renew)**: Fitur mudah untuk memperpanjang masa aktif member yang sudah expired.

---

## 📅 5. Attendance (Absensi Karyawan)
Sistem absensi canggih untuk memantau kedisiplinan staff.
*   **Berbasis Lokasi & Foto**:
    *   **GPS Geo-Tagging**: Absen hanya bisa dilakukan di lokasi gym (koordinat dikunci).
    *   **Selfie Evidence**: Wajib foto selfie saat Masuk (Clock In) dan Pulang (Clock Out).
*   **Flexible Shift**: Staff bisa Clock In/Out berkali-kali dalam sehari (cocok untuk jam istirahat atau lembur).
*   **History Absensi**: Staff bisa melihat riwayat kehadiran mereka selama 30 hari terakhir.
*   **👑 Fitur Khusus Superadmin**:
    *   **Lihat Semua Absensi**: Superadmin bisa melihat daftar absensi seluruh karyawan.
    *   **Filter Tanggal**: Bisa memilih tanggal tertentu untuk dicek (ada fitur swipe tanggal).
    *   **Detail View**: Klik salah satu kartu untuk melihat Foto Full & Peta Lokasi.

---

## 📊 6. Rekap (Laporan Penjualan)
Laporan keuangan harian yang transparan dan detail.
*   **Navigasi Tanggal**: Geser (Swipe) tanggal untuk melihat laporan hari-hari sebelumnya.
*   **Ringkasan Keuangan**: Menampilkan Total Pemasukan dan Jumlah Transaksi pada tanggal tersebut.
*   **Detail Transaksi**: Klik pada transaksi untuk melihat rincian barang apa saja yang dibeli.
*   **Hapus Transaksi (Void)**: Jika ada kesalahan input kasir, admin bisa menghapus transaksi tersebut.
*   **Export Excel**: Download laporan penjualan harian ke format Excel (.xlsx) untuk pembukuan lebih lanjut.

---

## ⚙️ 7. Profile (Pengaturan Akun)
Pengaturan personal dan sistem.
*   **Edit Profil**: Ganti Nama dan Foto Profil akun.
*   **Ganti Password**: Update password akun demi keamanan.
*   **Tampilan (Appearance)**: Pilihan mode Gelap (Dark Mode) atau Terang (Light Mode) sesuai selera visual.
*   **👑 Fitur Khusus Superadmin**:
    *   **Add Admin Account**: Fitur untuk membuatkan akun baru bagi staff/karyawan admin.

---
*Dokumen ini dibuat otomatis oleh Sistem Assistant King Gym.*
