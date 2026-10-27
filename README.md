# CarbonTrace

Aplikasi web pelacakan jejak karbon personal (Tugas Besar PPL, mendukung SDG 13).
Dibangun dengan Laravel 12, Blade, Tailwind CSS, Alpine.js, dan Laravel Breeze.

## Modul Gamifikasi — Man Rezki Setiawan (102022430015)

| PBI | Fitur | Pengguna | Administrator |
| --- | --- | --- | --- |
| PBI-13 (KF-14) | Manajemen Lencana | Galeri lencana di `/lencana`: status terkunci/terbuka beserta progres menuju syarat | CRUD di `/admin/lencana`: nama, deskripsi, jenis syarat, nilai syarat, gambar, status aktif. Lencana yang sudah diraih tidak bisa dihapus, hanya dinonaktifkan |
| PBI-14 (KF-15) | Manajemen Tantangan | Daftar tantangan yang sedang dan akan berjalan di `/tantangan`, beserta halaman detail | CRUD di `/admin/tantangan`: judul, kategori, periode, target penurunan (kg CO₂e), hadiah poin, dan daftar peserta. Tantangan yang sudah diikuti peserta aktif tidak bisa dihapus |
| PBI-15 (KF-16) | Keikutsertaan Tantangan | `/tantangan-saya`: **ikuti** (Create), **pantau progres & saring status** (Read), **perbarui progres** (Update: berjalan → selesai/gagal), **mundur** (Delete: status jadi batal, lalu bisa ikut lagi) | Pemantauan peserta di halaman detail tantangan |

### Cara menghitung progres tantangan

1. **Garis dasar**: rata-rata emisi harian pengguna pada kategori tantangan selama 30 hari sebelum ia mulai,
   dibagi jumlah hari yang memiliki catatan aktivitas.
2. **Penghematan**: setiap hari selama tantangan yang memiliki catatan aktivitas menyumbang
   `garis dasar − emisi kategori pada hari itu`.
3. **Progres** = total penghematan ÷ `reduction_target` × 100%.
   - Mencapai 100% → status **selesai**, EcoPoint diberikan satu kali (`point_logs`), lalu lencana dievaluasi.
   - Periode berakhir sebelum 100% → status **gagal** tanpa poin.
   - Pengguna yang belum punya catatan dalam 30 hari sebelumnya belum memiliki garis dasar, jadi progresnya tetap 0%.

Evaluasi berjalan saat pengguna mencatat atau menghapus aktivitas, membuka dashboard atau Tantangan Saya,
menekan *Perbarui progres*, dan setiap hari pukul 00.10 lewat perintah terjadwal `challenges:evaluate`.

### Jenis syarat lencana

| Jenis | Nilai yang dibandingkan |
| --- | --- |
| `activities_logged` | Jumlah catatan aktivitas |
| `streak` | Hari pencatatan berturut-turut (`users.streak_count`) |
| `challenges_completed` | Jumlah tantangan berstatus selesai |
| `points` | Total EcoPoint (`users.points`) |

### Kode utama

- `app/Services/GamificationService.php`: poin, streak, dan pemberian lencana
- `app/Services/ChallengeProgressService.php`: perhitungan progres dan status tantangan
- `app/Http/Controllers/Admin/BadgeController.php` dan `Admin/ChallengeController.php`: CRUD administrator
- `app/Http/Controllers/BadgeController.php`, `ChallengeController.php`, `ChallengeParticipationController.php`: sisi pengguna
- `tests/Feature/Gamification/`: feature test untuk ketiga PBI

## Fitur pendukung (versi minimal, milik modul lain)

Bagian ini dibuat secukupnya agar modul gamifikasi bisa dijalankan dan didemokan.
Pemilik PBI terkait dapat menggantinya dengan versi lengkap.

| Fitur | Versi di repo ini | Versi lengkap |
| --- | --- | --- |
| Registrasi, login, logout | Laravel Breeze | KF-01 |
| Peran admin/user | Tabel `roles`, kolom `users.role_id`, middleware `admin` | KF-04 |
| Kategori & faktor emisi | Hanya seeder (`CategorySeeder`), tanpa CRUD | PBI-07, PBI-08 |
| Pencatatan aktivitas | `/aktivitas`: tambah, lihat, hapus | PBI-04 |
| Perhitungan emisi | `EmissionCalculator` (jumlah × faktor) | KF-20 |
| Dashboard | Ringkasan emisi, poin, streak, tantangan, lencana | KF-21 |

## Menjalankan proyek

Kebutuhan: PHP 8.2+, Composer, Node.js 18+, dan MySQL 8 (Laragon/XAMPP).

```bash
composer install
cp .env.example .env
php artisan key:generate

# buat database "carbontrace" di MySQL, lalu:
php artisan migrate --seed
php artisan storage:link

npm install
npm run build

php artisan serve
```

Buka http://localhost:8000. Jika memakai SQLite, ubah `.env` menjadi `DB_CONNECTION=sqlite`
lalu hapus baris `DB_HOST` sampai `DB_PASSWORD`.

### Akun demo (kata sandi: `password`)

| Surel | Peran | Isi |
| --- | --- | --- |
| `admin@carbontrace.test` | Administrator | Akses Kelola Tantangan dan Kelola Lencana (menu nama pengguna) |
| `user@carbontrace.test` | Pengguna | Riwayat 33 hari, 1 tantangan selesai, 1 tantangan berjalan, 4 lencana |
| `budi@carbontrace.test` | Pengguna | Ikut tantangan tanpa garis dasar (progres 0%) |

### Tugas terjadwal

Untuk menjalankan scheduler secara lokal:

```bash
php artisan schedule:work
# atau sekali jalan:
php artisan challenges:evaluate
```

## Pengujian

```bash
php artisan test
```

Test memakai SQLite in-memory, jadi tidak perlu MySQL atau build aset.
