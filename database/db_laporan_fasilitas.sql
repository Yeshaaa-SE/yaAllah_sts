-- ===============================================================
-- DATABASE STARTER PACK STS DTP SOFTWARE DEVELOPER
-- Studi kasus: Sistem Pelaporan Kerusakan Fasilitas Sekolah
--
-- Isi awal: 4 kategori + 10 laporan kerusakan.
-- Impor ke MySQL / MariaDB menggunakan phpMyAdmin atau DBeaver.
-- Skrip dapat dijalankan ulang: INSERT IGNORE mencegah data awal
-- dengan ID yang sama menjadi duplikat.
-- ===============================================================

CREATE DATABASE IF NOT EXISTS db_laporan_fasilitas
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE db_laporan_fasilitas;

CREATE TABLE IF NOT EXISTS kategori_fasilitas (
    id_kategori INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS laporan_kerusakan (
    id_laporan INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_pelapor VARCHAR(100) NOT NULL,
    nama_fasilitas VARCHAR(100) NOT NULL,
    lokasi VARCHAR(100) NOT NULL,
    id_kategori INT UNSIGNED NOT NULL,
    deskripsi TEXT NOT NULL,
    prioritas ENUM('Rendah', 'Sedang', 'Tinggi') NOT NULL DEFAULT 'Sedang',
    status ENUM('Menunggu', 'Diproses', 'Selesai') NOT NULL DEFAULT 'Menunggu',
    tanggal_laporan DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_laporan_kategori
        FOREIGN KEY (id_kategori)
        REFERENCES kategori_fasilitas(id_kategori)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT IGNORE INTO kategori_fasilitas (id_kategori, nama_kategori) VALUES
    (1, 'Elektronik'),
    (2, 'Furnitur'),
    (3, 'Jaringan'),
    (4, 'Peralatan Kelas');

INSERT IGNORE INTO laporan_kerusakan
    (id_laporan, nama_pelapor, nama_fasilitas, lokasi, id_kategori, deskripsi, prioritas, status, tanggal_laporan)
VALUES
    (1, 'Alya Putri', 'AC Ruangan', 'Lab Komputer 1', 1,
        'AC tidak dingin meskipun sudah dinyalakan selama satu jam.', 'Tinggi', 'Menunggu', '2026-10-01 08:15:00'),
    (2, 'Bima Saputra', 'Proyektor', 'Kelas XI SIJA 1', 1,
        'Gambar proyektor redup dan sering berkedip.', 'Sedang', 'Diproses', '2026-10-01 09:20:00'),
    (3, 'Citra Maharani', 'Kursi Siswa', 'Kelas X SIJA 2', 2,
        'Salah satu kaki kursi patah.', 'Rendah', 'Selesai', '2026-10-02 07:40:00'),
    (4, 'Dimas Pratama', 'Komputer Praktik', 'Lab Komputer 2', 1,
        'Komputer tidak dapat menyala setelah tombol power ditekan.', 'Tinggi', 'Menunggu', '2026-10-02 10:30:00'),
    (5, 'Eka Ramadhani', 'Router Wi-Fi', 'Ruang Guru', 3,
        'Koneksi internet sering putus pada jam pembelajaran.', 'Tinggi', 'Diproses', '2026-10-03 11:05:00'),
    (6, 'Farhan Hidayat', 'Papan Tulis', 'Kelas X TJAT 1', 4,
        'Permukaan papan tulis sulit dibersihkan.', 'Rendah', 'Menunggu', '2026-10-03 13:10:00'),
    (7, 'Gita Anggraini', 'Meja Komputer', 'Lab Komputer 3', 2,
        'Bagian laci meja terlepas.', 'Sedang', 'Menunggu', '2026-10-04 08:00:00'),
    (8, 'Hafiz Maulana', 'Switch Jaringan', 'Lab Jaringan', 3,
        'Beberapa port LAN tidak berfungsi.', 'Tinggi', 'Diproses', '2026-10-04 09:45:00'),
    (9, 'Intan Lestari', 'Speaker Kelas', 'Kelas XI TJAT 2', 1,
        'Suara speaker pecah saat volume dinaikkan.', 'Sedang', 'Selesai', '2026-10-05 10:15:00'),
    (10, 'Jovan Aditya', 'Jam Dinding', 'Ruang Perpustakaan', 4,
        'Jarum jam tidak bergerak meskipun baterai diganti.', 'Rendah', 'Menunggu', '2026-10-05 12:30:00');

-- Untuk guru: contoh verifikasi jumlah data awal.
-- SELECT COUNT(*) AS jumlah_laporan FROM laporan_kerusakan;
-- SELECT COUNT(*) AS jumlah_kategori FROM kategori_fasilitas;
