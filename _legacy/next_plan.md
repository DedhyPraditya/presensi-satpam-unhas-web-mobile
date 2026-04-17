# Roadmap Kematangan Aplikasi Satpam UNHAS (Next Plan)

Berdasarkan tinjauan Senior Software Engineer, berikut adalah rencana pengembangan aplikasi Satpam UNHAS untuk mencapai standar *Production-Ready*.

---

## 1. Tambahan Fitur (Value & Reliability)

### Fase 1: Keamanan & Integritas (Mendesak)
- **Anti-GPS Spoofing**: Validasi sisi server yang lebih ketat atau penggunaan library pendeteksi "Fake GPS" untuk mencegah manipulasi lokasi.
- **Audit Logs**: Pencatatan setiap perubahan data (siapa yang mengubah jadwal, menghapus absensi, dll) untuk keperluan investigasi.
- **Security Hardening**: Mengganti sisa query manual dengan *Prepared Statements* dan memastikan proteksi XSS di seluruh output.

### Fase 2: Resiliensi (Kehandalan Lapangan)
- **Offline Sync (PWA)**: Implementasi *Offline Queue* agar personil tetap bisa absen di area minim sinyal, data sinkron otomatis saat online.
- **Face Verification**: Integrasi `face-api.js` untuk mencocokkan wajah saat pengambilan foto dokumentasi dengan profil tersimpan.

### Fase 3: Monitoring Pro-aktif
- **Real-time Incident Alert**: Notifikasi instan (Telegram/WhatsApp Bot) ke koordinator saat ada laporan kejadian mendesak.
- **Automated Analytics**: Dashboard insight untuk memantau tren keterlambatan dan titik POS rawan insiden.

---

## 2. Pembersihan Sistem (Clean-up)

- **Pengerampingan Skrip Maintenance**: Memindahkan file seperti `perbaikan_db.php`, `backfill_status.php`, dan `migration_laporan.php` dari root ke folder terproteksi (misal: `admin/tools/`).
- **DRY (Don't Repeat Yourself)**: Sentralisasi logika bisnis (Shift, Status, Jarak) ke dalam `includes/helpers.php` agar tidak ada duplikasi kode.
- **Proteksi Berkas Upload**: Mengamankan folder `uploads/` agar foto tidak bisa diakses/di-brute force secara bebas dari luar.

---

## 3. Pertanyaan Terbuka untuk Diskusi
1. **Hosting**: Apakah struktur folder perlu disesuaikan dengan spek aaPanel (permissions/SSL)?
2. **Notifikasi**: Apakah ingin menggunakan WhatsApp Gateway (seperti Fonnte) untuk notifikasi real-time?
3. **Manual Override**: Perlukah fitur bagi Admin untuk mengoreksi absensi jika ada kendala teknis pada HP personil?

---
*Dokumen ini disusun sebagai panduan pengembangan tahap selanjutnya.*
