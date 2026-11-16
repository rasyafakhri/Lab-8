<?php
error_reporting(E_ALL);
include_once 'koneksi.php';

$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id) {
    die('Error: ID barang tidak ditemukan.');
}

$errors = [];
$data = null;

// Fetch existing data
$sql_fetch = "SELECT * FROM data_barang WHERE id_barang = '" . mysqli_real_escape_string($conn, $id) . "'";
$result_fetch = mysqli_query($conn, $sql_fetch);

if (!$result_fetch || mysqli_num_rows($result_fetch) === 0) {
    die('Error: Data barang tidak tersedia atau ID tidak valid.');
}
$data = mysqli_fetch_array($result_fetch);

if (isset($_POST['submit'])) {
    $nama        = trim($_POST['nama'] ?? '');
    $kategori    = $_POST['kategori'] ?? '';
    $harga_jual  = trim($_POST['harga_jual'] ?? '');
    $harga_beli  = trim($_POST['harga_beli'] ?? '');
    $stok        = trim($_POST['stok'] ?? '');
    $file_gambar = $_FILES['file_gambar'] ?? null;
    $gambar      = $data['gambar']; // Keep existing image by default

    // Validation
    if (empty($nama)) {
        $errors['nama'] = 'Nama barang wajib diisi';
    }
    if (empty($kategori)) {
        $errors['kategori'] = 'Kategori wajib dipilih';
    }
    if (empty($harga_jual) || !is_numeric($harga_jual) || $harga_jual < 0) {
        $errors['harga_jual'] = 'Harga jual harus berupa angka positif';
    }
    if (empty($harga_beli) || !is_numeric($harga_beli) || $harga_beli < 0) {
        $errors['harga_beli'] = 'Harga beli harus berupa angka positif';
    }
    if (empty($stok) || !is_numeric($stok) || $stok < 0) {
        $errors['stok'] = 'Stok harus berupa angka positif';
    }

    // File upload handling
    if ($file_gambar && $file_gambar['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type     = mime_content_type($file_gambar['tmp_name']);

        if (!in_array($file_type, $allowed_types)) {
            $errors['file_gambar'] = 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.';
        } elseif ($file_gambar['size'] > 2 * 1024 * 1024) { // 2MB limit
            $errors['file_gambar'] = 'Ukuran file maksimal 2MB';
        } else {
            // Delete old image if it exists and is not a default placeholder
            if (!empty($data['gambar']) && file_exists($data['gambar']) && strpos($data['gambar'], 'default_image') === false) {
                @unlink($data['gambar']);
            }

            $filename = str_replace(' ', '_', basename($file_gambar['name']));
            $ext      = pathinfo($filename, PATHINFO_EXTENSION);
            $filename = pathinfo($filename, PATHINFO_FILENAME) . '_' . time() . '.' . $ext;
            $destination = dirname(__FILE__) . '/gambar/' . $filename;

            if (move_uploaded_file($file_gambar['tmp_name'], $destination)) {
                $gambar = 'gambar/' . $filename;
            } else {
                $errors['file_gambar'] = 'Gagal mengunggah file';
            }
        }
    } elseif ($file_gambar && $file_gambar['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors['file_gambar'] = 'Terjadi kesalahan saat mengunggah file';
    }

    // If no errors, update database
    if (empty($errors)) {
        $sql_update = 'UPDATE data_barang SET ';
        $sql_update .= "nama = '" . mysqli_real_escape_string($conn, $nama) . "', ";
        $sql_update .= "kategori = '" . mysqli_real_escape_string($conn, $kategori) . "', ";
        $sql_update .= "harga_jual = '" . mysqli_real_escape_string($conn, $harga_jual) . "', ";
        $sql_update .= "harga_beli = '" . mysqli_real_escape_string($conn, $harga_beli) . "', ";
        $sql_update .= "stok = '" . mysqli_real_escape_string($conn, $stok) . "' ";
        if (!empty($gambar)) {
            $sql_update .= ", gambar = '" . mysqli_real_escape_string($conn, $gambar) . "' ";
        }
        $sql_update .= "WHERE id_barang = '" . mysqli_real_escape_string($conn, $id) . "'";

        $result_update = mysqli_query($conn, $sql_update);

        if ($result_update) {
            header('location: index.php');
            exit;
        } else {
            $errors['database'] = 'Gagal memperbarui data: ' . mysqli_error($conn);
        }
    }
}

// Helper function for select option
function is_select($var, $val) {
    if ($var == $val) return 'selected="selected"';
    return '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="style.css" rel="stylesheet" type="text/css" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <title>Ubah Barang - <?= htmlspecialchars($data['nama']); ?></title>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <div class="container">
        <header class="page-header">
            <div class="header-brand">
                <span class="header-badge">
                    <span class="header-badge-dot"></span>
                    Edit Record #<?= htmlspecialchars($data['id_barang']); ?>
                </span>
                <h1>Ubah Data Barang</h1>
                <p class="subtitle">Perbarui informasi spesifikasi, harga, atau stok untuk produk terpilih.</p>
            </div>
            <a href="index.php" class="btn btn-secondary" aria-label="Kembali ke daftar barang">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Tabel</span>
            </a>
        </header>
        
        <main id="main-content">
            <?php if (!empty($errors['database'])): ?>
            <div class="alert alert-error" role="alert">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="15" y1="9" x2="9" y2="15"></line>
                    <line x1="9" y1="9" x2="15" y2="15"></line>
                </svg>
                <span><?= htmlspecialchars($errors['database']); ?></span>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        <span>Edit: <?= htmlspecialchars($data['nama']); ?></span>
                    </div>
                    <span class="badge badge-accent">ID #<?= htmlspecialchars($data['id_barang']); ?></span>
                </div>
                <div class="card-body">
                    <div class="form-layout">
                        <!-- Left: Main Form -->
                        <div class="form-main">
                            <form method="post" action="ubah.php?id=<?= urlencode($id); ?>" enctype="multipart/form-data" class="form" novalidate id="form-ubah">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($data['id_barang']); ?>">

                                <div class="form-group">
                                    <label for="nama" class="form-label">
                                        <span class="required">Nama Barang</span>
                                        <span class="form-hint">Maks. 100 karakter</span>
                                    </label>
                                    <input
                                        type="text"
                                        id="nama"
                                        name="nama"
                                        class="form-input <?= isset($errors['nama']) ? 'error' : ''; ?>"
                                        value="<?= htmlspecialchars($_POST['nama'] ?? $data['nama'] ?? ''); ?>"
                                        required
                                        autocomplete="off"
                                    >
                                    <?php if (isset($errors['nama'])): ?>
                                        <p class="form-error" role="alert">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                            </svg>
                                            <?= htmlspecialchars($errors['nama']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="kategori" class="form-label">
                                        <span class="required">Kategori Produk</span>
                                    </label>
                                    <?php $selected_kat = $_POST['kategori'] ?? $data['kategori'] ?? ''; ?>
                                    <select
                                        id="kategori"
                                        name="kategori"
                                        class="form-select <?= isset($errors['kategori']) ? 'error' : ''; ?>"
                                        required
                                    >
                                        <option value="">-- Pilih Kategori Produk --</option>
                                        <option value="Komputer" <?= is_select('Komputer', $selected_kat); ?>>Komputer (PC / Laptop / Aksesoris)</option>
                                        <option value="Elektronik" <?= is_select('Elektronik', $selected_kat); ?>>Elektronik (Audio / Visual / Rumah Tangga)</option>
                                        <option value="Hand Phone" <?= is_select('Hand Phone', $selected_kat); ?>>Hand Phone (Smartphone / Gadget / Tablet)</option>
                                    </select>
                                    <?php if (isset($errors['kategori'])): ?>
                                        <p class="form-error" role="alert">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="12" y1="8" x2="12" y2="12"></line>
                                                <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                            </svg>
                                            <?= htmlspecialchars($errors['kategori']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-4);">
                                    <div class="form-group">
                                        <label for="harga_beli" class="form-label">
                                            <span class="required">Harga Beli (Modal)</span>
                                        </label>
                                        <div class="input-currency-wrapper">
                                            <span class="input-currency-prefix">Rp</span>
                                            <input
                                                type="number"
                                                id="harga_beli"
                                                name="harga_beli"
                                                class="form-input <?= isset($errors['harga_beli']) ? 'error' : ''; ?>"
                                                value="<?= htmlspecialchars($_POST['harga_beli'] ?? $data['harga_beli'] ?? ''); ?>"
                                                min="0"
                                                step="1"
                                                required
                                            >
                                        </div>
                                        <div id="preview-harga-beli" class="currency-live-preview" style="display: none;">Rp 0</div>
                                        <?php if (isset($errors['harga_beli'])): ?>
                                            <p class="form-error" role="alert">
                                                <?= htmlspecialchars($errors['harga_beli']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="harga_jual" class="form-label">
                                            <span class="required">Harga Jual</span>
                                        </label>
                                        <div class="input-currency-wrapper">
                                            <span class="input-currency-prefix">Rp</span>
                                            <input
                                                type="number"
                                                id="harga_jual"
                                                name="harga_jual"
                                                class="form-input <?= isset($errors['harga_jual']) ? 'error' : ''; ?>"
                                                value="<?= htmlspecialchars($_POST['harga_jual'] ?? $data['harga_jual'] ?? ''); ?>"
                                                min="0"
                                                step="1"
                                                required
                                            >
                                        </div>
                                        <div id="preview-harga-jual" class="currency-live-preview" style="display: none;">Rp 0</div>
                                        <?php if (isset($errors['harga_jual'])): ?>
                                            <p class="form-error" role="alert">
                                                <?= htmlspecialchars($errors['harga_jual']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="stok" class="form-label">
                                        <span class="required">Jumlah Stok</span>
                                        <span class="form-hint">Satuan Unit</span>
                                    </label>
                                    <input
                                        type="number"
                                        id="stok"
                                        name="stok"
                                        class="form-input <?= isset($errors['stok']) ? 'error' : ''; ?>"
                                        value="<?= htmlspecialchars($_POST['stok'] ?? $data['stok'] ?? ''); ?>"
                                        min="0"
                                        step="1"
                                        required
                                        style="font-family: var(--font-mono); font-weight: 600;"
                                    >
                                    <?php if (isset($errors['stok'])): ?>
                                        <p class="form-error" role="alert">
                                            <?= htmlspecialchars($errors['stok']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <div class="form-group">
                                    <label for="file_gambar" class="form-label">Foto / Gambar Produk</label>
                                    <div class="form-file">
                                        <?php if (!empty($data['gambar']) && file_exists($data['gambar'])): ?>
                                        <div class="form-file-preview">
                                            <img src="<?= htmlspecialchars($data['gambar']); ?>" alt="<?= htmlspecialchars($data['nama']); ?>">
                                            <div class="form-file-preview-info">
                                                <span class="form-file-preview-name"><?= htmlspecialchars(basename($data['gambar'])); ?></span>
                                                <span class="form-file-preview-badge">Gambar saat ini tersimpan di server</span>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <div class="file-dropzone" id="dropzone">
                                            <input
                                                type="file"
                                                id="file_gambar"
                                                name="file_gambar"
                                                accept="image/jpeg,image/png,image/gif,image/webp"
                                            >
                                            <div class="dropzone-content">
                                                <div class="dropzone-icon">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                        <polyline points="21 15 16 10 5 21"></polyline>
                                                    </svg>
                                                </div>
                                                <span class="dropzone-text" id="dropzone-text">Pilih file baru untuk mengganti gambar</span>
                                                <span class="dropzone-subtext">JPG, PNG, GIF, WebP (Maks. 2MB). Biarkan kosong jika tidak ingin mengubah.</span>
                                            </div>
                                        </div>
                                        <?php if (isset($errors['file_gambar'])): ?>
                                            <p class="form-error" role="alert">
                                                <?= htmlspecialchars($errors['file_gambar']); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <a href="index.php" class="btn btn-secondary">
                                        Batal
                                    </a>
                                    <button type="submit" name="submit" class="btn btn-primary">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                            <polyline points="7 3 7 8 15 8"></polyline>
                                        </svg>
                                        <span>Simpan Perubahan</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Right: Real-time Live Preview Card -->
                        <div class="form-preview-col">
                            <div class="preview-container">
                                <div class="preview-box">
                                    <div class="preview-box-header">
                                        <span>Live Card Preview</span>
                                        <span class="preview-badge-live">
                                            <span class="header-badge-dot" style="width: 5px; height: 5px;"></span>
                                            Real-time
                                        </span>
                                    </div>
                                    <div class="preview-card-body">
                                        <div class="preview-image-box" id="preview-image-box">
                                            <?php if (!empty($data['gambar']) && file_exists($data['gambar'])): ?>
                                                <img src="<?= htmlspecialchars($data['gambar']); ?>" alt="Pratinjau" id="preview-img">
                                                <div class="preview-image-placeholder" id="preview-placeholder" style="display: none;">
                                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                        <polyline points="21 15 16 10 5 21"></polyline>
                                                    </svg>
                                                    <span style="font-size: var(--font-size-xs); font-weight: 600;">Belum Ada Gambar</span>
                                                </div>
                                            <?php else: ?>
                                                <div class="preview-image-placeholder" id="preview-placeholder">
                                                    <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                        <polyline points="21 15 16 10 5 21"></polyline>
                                                    </svg>
                                                    <span style="font-size: var(--font-size-xs); font-weight: 600;">Belum Ada Gambar</span>
                                                </div>
                                                <img src="" alt="Pratinjau" id="preview-img" style="display: none;">
                                            <?php endif; ?>
                                        </div>

                                        <div class="preview-meta">
                                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                                <span class="badge badge-primary" id="preview-badge-kategori"><?= htmlspecialchars($data['kategori']); ?></span>
                                                <span class="stock-indicator high" id="preview-stock-badge"><?= htmlspecialchars($data['stok']); ?> Unit</span>
                                            </div>
                                            <div class="preview-title" id="preview-nama"><?= htmlspecialchars($data['nama']); ?></div>
                                        </div>

                                        <div class="preview-price-row">
                                            <div>
                                                <div style="font-size: var(--font-size-xs); color: var(--ink-muted); text-transform: uppercase; font-weight: 600;">Harga Jual</div>
                                                <div style="font-family: var(--font-mono); font-size: 1.15rem; font-weight: 700;" id="preview-val-jual">Rp <?= number_format($data['harga_jual'], 0, ',', '.'); ?></div>
                                            </div>
                                            <div style="text-align: right;">
                                                <div style="font-size: var(--font-size-xs); color: var(--ink-muted); text-transform: uppercase; font-weight: 600;">Margin Est.</div>
                                                <?php $initial_margin = $data['harga_jual'] - $data['harga_beli']; ?>
                                                <div class="preview-margin-pill <?= $initial_margin >= 0 ? 'positive' : 'negative'; ?>" id="preview-val-margin">
                                                    <?= ($initial_margin >= 0 ? '+ ' : '- ') . 'Rp ' . number_format(abs($initial_margin), 0, ',', '.'); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Client-side Live Interactive Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const inputNama = document.getElementById('nama');
        const selectKategori = document.getElementById('kategori');
        const inputHargaBeli = document.getElementById('harga_beli');
        const inputHargaJual = document.getElementById('harga_jual');
        const inputStok = document.getElementById('stok');
        const inputFile = document.getElementById('file_gambar');
        const dropzoneText = document.getElementById('dropzone-text');

        // Preview elements
        const previewNama = document.getElementById('preview-nama');
        const previewKategori = document.getElementById('preview-badge-kategori');
        const previewValJual = document.getElementById('preview-val-jual');
        const previewValMargin = document.getElementById('preview-val-margin');
        const previewStockBadge = document.getElementById('preview-stock-badge');
        const previewImg = document.getElementById('preview-img');
        const previewPlaceholder = document.getElementById('preview-placeholder');

        const hintHargaBeli = document.getElementById('preview-harga-beli');
        const hintHargaJual = document.getElementById('preview-harga-jual');

        function formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }

        function updatePreview() {
            // Nama
            previewNama.textContent = inputNama.value.trim() || 'Nama Barang';

            // Kategori
            const kat = selectKategori.value;
            previewKategori.textContent = kat || 'Kategori';
            previewKategori.className = 'badge';
            if (kat === 'Komputer') previewKategori.classList.add('badge-komputer');
            else if (kat === 'Elektronik') previewKategori.classList.add('badge-elektronik');
            else if (kat === 'Hand Phone') previewKategori.classList.add('badge-handphone');
            else previewKategori.classList.add('badge-primary');

            // Prices
            const beli = parseFloat(inputHargaBeli.value) || 0;
            const jual = parseFloat(inputHargaJual.value) || 0;
            const margin = jual - beli;

            previewValJual.textContent = formatRupiah(jual);
            previewValMargin.textContent = (margin >= 0 ? '+ ' : '- ') + formatRupiah(Math.abs(margin));
            previewValMargin.className = 'preview-margin-pill ' + (margin >= 0 ? 'positive' : 'negative');

            if (inputHargaBeli.value) {
                hintHargaBeli.style.display = 'block';
                hintHargaBeli.textContent = formatRupiah(beli);
            } else {
                hintHargaBeli.style.display = 'none';
            }

            if (inputHargaJual.value) {
                hintHargaJual.style.display = 'block';
                hintHargaJual.textContent = formatRupiah(jual);
            } else {
                hintHargaJual.style.display = 'none';
            }

            // Stok
            const stokVal = parseInt(inputStok.value) || 0;
            previewStockBadge.className = 'stock-indicator';
            if (stokVal === 0) {
                previewStockBadge.classList.add('empty');
                previewStockBadge.textContent = 'Stok Habis (0)';
            } else if (stokVal <= 5) {
                previewStockBadge.classList.add('med');
                previewStockBadge.textContent = 'Menipis (' + stokVal + ')';
            } else {
                previewStockBadge.classList.add('high');
                previewStockBadge.textContent = 'Tersedia (' + stokVal + ')';
            }
        }

        inputNama.addEventListener('input', updatePreview);
        selectKategori.addEventListener('change', updatePreview);
        inputHargaBeli.addEventListener('input', updatePreview);
        inputHargaJual.addEventListener('input', updatePreview);
        inputStok.addEventListener('input', updatePreview);

        // Image Preview Handler
        inputFile.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                dropzoneText.textContent = 'File baru: ' + file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                const reader = new FileReader();
                reader.onload = function(event) {
                    previewImg.src = event.target.result;
                    previewImg.style.display = 'block';
                    if (previewPlaceholder) previewPlaceholder.style.display = 'none';
                };
                reader.readAsDataURL(file);
            }
        });

        // Initialize state
        updatePreview();
    });
    </script>
</body>
</html>
