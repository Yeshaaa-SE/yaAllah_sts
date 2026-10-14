<?php
// Tangkap output dari koneksi.php (mis. teks "Connection succesfully")
// supaya tidak tampil di halaman dan tidak mengganggu header() redirect.
ob_start();
include_once "koneksi.php";
ob_end_clean();

// Samakan nama variabel koneksi: pakai nama apa pun yang dipakai di koneksi.php
if (!isset($koneksi)) {
    foreach (['conn', 'connect', 'con', 'mysqli', 'db', 'link', 'koneksi_db'] as $nama_var) {
        if (isset($$nama_var) && $$nama_var instanceof mysqli) {
            $koneksi = $$nama_var;
            break;
        }
    }
}
if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
    die('Variabel koneksi mysqli tidak ditemukan. Cek nama variabel di koneksi.php.');
}

$errors  = [];
$sukses  = false;
$old     = [];

// ===== CREATE =====
if (isset($_POST['submit'])) {
    $nama_pelapor   = trim($_POST['nama_pelapor'] ?? '');
    $nama_fasilitas = trim($_POST['nama_fasilitas'] ?? '');
    $lokasi         = trim($_POST['lokasi'] ?? '');
    $id_kategori    = (int)($_POST['id_kategori'] ?? 0);
    $deskripsi      = trim($_POST['deskripsi'] ?? '');
    $prioritas      = trim($_POST['prioritas'] ?? '');
    $old = compact('nama_pelapor', 'nama_fasilitas', 'lokasi', 'id_kategori', 'deskripsi', 'prioritas');

    if ($nama_pelapor === '')   $errors[] = 'Nama pelapor wajib diisi.';
    if ($nama_fasilitas === '') $errors[] = 'Nama fasilitas wajib diisi.';
    if ($lokasi === '')         $errors[] = 'Lokasi kerusakan wajib diisi.';
    if ($id_kategori < 1)       $errors[] = 'Kategori fasilitas wajib dipilih.';
    if ($deskripsi === '')      $errors[] = 'Deskripsi kerusakan wajib diisi.';
    if (!in_array($prioritas, ['Rendah', 'Sedang', 'Tinggi'], true)) $errors[] = 'Prioritas tidak valid.';

    if (!$errors) {
        $stmt = mysqli_prepare($koneksi,
            "INSERT INTO laporan_kerusakan (nama_pelapor, nama_fasilitas, lokasi, id_kategori, deskripsi, prioritas)
             VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssiss", $nama_pelapor, $nama_fasilitas, $lokasi, $id_kategori, $deskripsi, $prioritas);
        if (mysqli_stmt_execute($stmt)) {
            // Post/Redirect/Get supaya tidak terkirim ulang saat refresh
            header("Location: " . $_SERVER['PHP_SELF'] . "?sukses=1#daftar-laporan");
            exit;
        }
        $errors[] = 'Gagal menyimpan: ' . mysqli_error($koneksi);
    }
}
$sukses = isset($_GET['sukses']);

// ===== READ =====
$hasil = mysqli_query($koneksi, "SELECT * FROM laporan_kerusakan ORDER BY 1 DESC");
function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pelaporan Kerusakan Fasilitas Sekolah</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 15px;
            line-height: 1.5;
            color: #222;
            background: #f5f3ee;
        }

        a { color: #1f5f4a; }

        /* bar atas */
        header {
            background: #1f5f4a;
            color: #fff;
            padding: 22px 20px 14px;
        }
        header .isi { max-width: 900px; margin: 0 auto; }
        header h1 { margin: 0 0 4px; font-size: 22px; }
        header p  { margin: 0 0 12px; font-size: 14px; color: #d6e6df; }
        header nav a {
            color: #fff;
            margin-right: 16px;
            font-size: 14px;
            text-decoration: none;
            padding-bottom: 2px;
            border-bottom: 1px solid transparent;
        }
        header nav a:hover { border-bottom-color: #fff; }

        main { max-width: 900px; margin: 0 auto; padding: 24px 20px 10px; }
        section { margin-bottom: 40px; }
        h2 { font-size: 18px; margin: 0 0 4px; }
        .ket { margin: 0 0 14px; color: #555; font-size: 14px; }

        /* form */
        form {
            background: #fff;
            border: 1px solid #cfcabd;
            padding: 18px 20px 20px;
        }
        .baris { margin-bottom: 14px; }
        label { display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px; }
        input[type=text], select, textarea {
            width: 100%;
            padding: 7px 9px;
            font-family: inherit;
            font-size: 14px;
            border: 1px solid #aaa;
            background: #fff;
        }
        input:focus, select:focus, textarea:focus {
            outline: 2px solid #9cc7b8;
            border-color: #1f5f4a;
        }
        textarea { resize: vertical; }
        button {
            background: #1f5f4a;
            color: #fff;
            border: 0;
            padding: 9px 18px;
            font-size: 14px;
            cursor: pointer;
        }
        button:hover { background: #174a39; }

        /* pesan */
        .pesan { padding: 10px 14px; margin-bottom: 14px; font-size: 14px; border-left: 4px solid; }
        .pesan.ok    { background: #e4f1ea; border-color: #1f5f4a; }
        .pesan.gagal { background: #f8e3e0; border-color: #b3382c; }
        .pesan ul { margin: 0; padding-left: 18px; }

        /* tabel */
        .bungkus-tabel { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { border: 1px solid #cfcabd; padding: 8px 10px; text-align: left; font-size: 14px; }
        th { background: #e9e5da; }
        tbody tr:nth-child(even) { background: #faf9f6; }
        .kosong { text-align: center; color: #777; padding: 18px; }

        .prio-Rendah { color: #2e7d32; font-weight: bold; }
        .prio-Sedang { color: #b26a00; font-weight: bold; }
        .prio-Tinggi { color: #b3382c; font-weight: bold; }

        footer {
            max-width: 900px;
            margin: 0 auto;
            padding: 14px 20px 30px;
            font-size: 13px;
            color: #777;
            border-top: 1px solid #d8d3c6;
        }

        @media (max-width: 600px) {
            header h1 { font-size: 19px; }
            form { padding: 14px; }
        }
    </style>
</head>
<body>
    <header>
        <div class="isi">
            <h1>Sistem Pelaporan Kerusakan Fasilitas Sekolah</h1>
            <p>Catat dan pantau laporan kerusakan fasilitas sekolah.</p>
            <nav aria-label="Navigasi utama">
                <a href="#form-laporan">Buat Laporan</a>
                <a href="#daftar-laporan">Daftar Laporan</a>
            </nav>
        </div>
    </header>

    <main>
        <section id="form-laporan" aria-labelledby="judul-form">
            <h2 id="judul-form">Form Pelaporan Kerusakan</h2>
            <p class="ket">Lengkapi data berikut untuk melaporkan fasilitas yang rusak.</p>

            <?php if ($sukses): ?>
                <div class="pesan ok">Laporan berhasil disimpan.</div>
            <?php endif; ?>
            <?php if ($errors): ?>
                <div class="pesan gagal">
                    <ul>
                        <?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="post">
                <div class="baris">
                    <label for="nama_pelapor">Nama pelapor</label>
                    <input type="text" id="nama_pelapor" name="nama_pelapor" maxlength="100"
                           value="<?= e($old['nama_pelapor'] ?? '') ?>" required>
                </div>

                <div class="baris">
                    <label for="nama_fasilitas">Nama fasilitas</label>
                    <input type="text" id="nama_fasilitas" name="nama_fasilitas" maxlength="100"
                           value="<?= e($old['nama_fasilitas'] ?? '') ?>" required>
                </div>

                <div class="baris">
                    <label for="lokasi">Lokasi kerusakan</label>
                    <input type="text" id="lokasi" name="lokasi" maxlength="100"
                           value="<?= e($old['lokasi'] ?? '') ?>" required>
                </div>

                <div class="baris">
                    <label for="id_kategori">Kategori fasilitas</label>
                    <select id="id_kategori" name="id_kategori" required>
                        <option value="">-- Pilih kategori --</option>
                        <?php
                        $kategori = [1 => 'Elektronik', 2 => 'Furnitur', 3 => 'Jaringan', 4 => 'Peralatan Kelas'];
                        foreach ($kategori as $id => $nama):
                        ?>
                            <option value="<?= $id ?>" <?= ($old['id_kategori'] ?? 0) == $id ? 'selected' : '' ?>><?= e($nama) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="baris">
                    <label for="deskripsi">Deskripsi kerusakan</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4" required><?= e($old['deskripsi'] ?? '') ?></textarea>
                </div>

                <div class="baris">
                    <label for="prioritas">Prioritas</label>
                    <select id="prioritas" name="prioritas" required>
                        <option value="">-- Pilih prioritas --</option>
                        <?php foreach (['Rendah', 'Sedang', 'Tinggi'] as $p): ?>
                            <option value="<?= $p ?>" <?= ($old['prioritas'] ?? '') === $p ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" name="submit">Kirim Laporan</button>
            </form>
        </section>

        <section id="daftar-laporan" aria-labelledby="judul-daftar">
            <h2 id="judul-daftar">Daftar Laporan Kerusakan</h2>
            <p class="ket">Seluruh laporan yang tercatat pada database ditampilkan di bagian ini.</p>

            <div class="bungkus-tabel">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">No.</th>
                            <th scope="col">Fasilitas</th>
                            <th scope="col">Lokasi</th>
                            <th scope="col">Pelapor</th>
                            <th scope="col">Prioritas</th>
                            <th scope="col">Status</th>
                            <th scope="col">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($hasil && mysqli_num_rows($hasil) > 0): $no = 1; ?>
                            <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><?= e($row['nama_fasilitas'] ?? '') ?></td>
                                    <td><?= e($row['lokasi'] ?? '') ?></td>
                                    <td><?= e($row['nama_pelapor'] ?? '') ?></td>
                                    <td class="prio-<?= e($row['prioritas'] ?? '') ?>"><?= e($row['prioritas'] ?? '') ?></td>
                                    <td><?= e($row['status'] ?? '-') ?></td>
                                    <td><?= e($row['tanggal_lapor'] ?? '-') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="kosong">Belum ada laporan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <footer>
        &copy; <?= date('Y') ?> Sistem Pelaporan Kerusakan Fasilitas Sekolah.
    </footer>
</body>
</html>