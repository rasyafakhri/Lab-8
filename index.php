<?php
include("koneksi.php");

// Query untuk menampilkan data barang
$sql = 'SELECT * FROM data_barang ORDER BY id_barang DESC';
$result = mysqli_query($conn, $sql);

$items = [];
$total_items = 0;
$total_aset = 0;
$total_est_jual = 0;
$stok_menipis = 0;
$kategori_counts = [];

if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_array($result)) {
        $items[] = $row;
        $total_items++;
        $total_aset += ($row['harga_beli'] * $row['stok']);
        $total_est_jual += ($row['harga_jual'] * $row['stok']);
        if ($row['stok'] <= 5) {
            $stok_menipis++;
        }
        $kat = $row['kategori'] ?? 'Lainnya';
        $kategori_counts[$kat] = ($kategori_counts[$kat] ?? 0) + 1;
    }
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
    <title>Sistem Manajemen Data Barang</title>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    <div class="container">
        <!-- Page Header -->
        <header class="page-header">
            <div class="header-brand">
                <span class="header-badge">
                    <span class="header-badge-dot"></span>
                    Sistem Database Inventaris
                </span>
                <h1>Katalog Data Barang</h1>
                <p class="subtitle">Kelola dan pantau stok, harga modal, harga jual, dan pergerakan produk toko.</p>
            </div>
            <a href="tambah.php" class="btn btn-primary" aria-label="Tambah barang baru">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Tambah Barang Baru</span>
            </a>
        </header>

        <!-- Bento Metric Cards -->
        <section class="stats-grid" aria-label="Statistik Inventaris">
            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Barang</span>
                    <span class="stat-value"><?= number_format($total_items); ?></span>
                    <span class="stat-desc"><?= count($kategori_counts); ?> kategori aktif</span>
                </div>
                <div class="stat-icon indigo" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 2 7 12 12 22 7 12 2"></polygon>
                        <polyline points="2 17 12 22 22 17"></polyline>
                        <polyline points="2 12 12 17 22 12"></polyline>
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Total Nilai Modal</span>
                    <span class="stat-value">Rp <?= number_format($total_aset, 0, ',', '.'); ?></span>
                    <span class="stat-desc">Akumulasi harga beli stok</span>
                </div>
                <div class="stat-icon emerald" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Estimasi Nilai Jual</span>
                    <span class="stat-value">Rp <?= number_format($total_est_jual, 0, ',', '.'); ?></span>
                    <span class="stat-desc">Potensi pendapatan bruto</span>
                </div>
                <div class="stat-icon cyan" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                        <polyline points="17 6 23 6 23 12"></polyline>
                    </svg>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-info">
                    <span class="stat-label">Stok Kritis / Menipis</span>
                    <span class="stat-value" style="<?= $stok_menipis > 0 ? 'color: var(--badge-rose-text);' : ''; ?>">
                        <?= $stok_menipis; ?>
                    </span>
                    <span class="stat-desc">Stok &le; 5 unit perlu restock</span>
                </div>
                <div class="stat-icon <?= $stok_menipis > 0 ? 'rose' : 'amber'; ?>" aria-hidden="true">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                        <line x1="12" y1="9" x2="12" y2="13"></line>
                        <line x1="12" y1="17" x2="12.01" y2="17"></line>
                    </svg>
                </div>
            </div>
        </section>

        <!-- Main Content Area -->
        <main id="main-content">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                        <span>Daftar Master Barang</span>
                    </div>
                    <span class="badge badge-primary" id="row-count-badge">Total: <?= count($items); ?> Unit</span>
                </div>

                <div class="card-body">
                    <!-- Interactive Toolbar (Search & Category Filter) -->
                    <div class="toolbar">
                        <div class="search-box">
                            <span class="search-icon" aria-hidden="true">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </span>
                            <input
                                type="text"
                                id="table-search"
                                class="search-input"
                                placeholder="Cari nama barang atau kategori..."
                                aria-label="Cari barang"
                                autocomplete="off"
                            >
                        </div>

                        <div class="filter-group" role="tablist" aria-label="Filter kategori">
                            <button type="button" class="filter-btn active" data-category="all">Semua (<?= count($items); ?>)</button>
                            <button type="button" class="filter-btn" data-category="Komputer">Komputer</button>
                            <button type="button" class="filter-btn" data-category="Elektronik">Elektronik</button>
                            <button type="button" class="filter-btn" data-category="Hand Phone">Hand Phone</button>
                        </div>
                    </div>

                    <!-- Responsive Styled Data Table -->
                    <div class="table-wrapper" role="region" aria-label="Tabel Data Barang" tabindex="0">
                        <table class="table" id="data-table">
                            <thead>
                                <tr>
                                    <th scope="col" style="width: 80px;">Gambar</th>
                                    <th scope="col">Nama Barang</th>
                                    <th scope="col" style="width: 140px;">Kategori</th>
                                    <th scope="col" style="width: 160px;">Harga Beli</th>
                                    <th scope="col" style="width: 160px;">Harga Jual</th>
                                    <th scope="col" style="width: 130px;">Stok</th>
                                    <th scope="col" style="width: 170px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($items)): ?>
                                <?php foreach ($items as $row): ?>
                                <tr class="data-row" data-name="<?= strtolower(htmlspecialchars($row['nama'])); ?>" data-category="<?= htmlspecialchars($row['kategori']); ?>">
                                    <td>
                                        <div class="table-img-wrap">
                                            <?php if (!empty($row['gambar']) && file_exists($row['gambar'])): ?>
                                                <img src="<?= htmlspecialchars($row['gambar']); ?>" alt="<?= htmlspecialchars($row['nama']); ?>" class="table-img" loading="lazy">
                                            <?php else: ?>
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="color: var(--ink-faint);">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                    <polyline points="21 15 16 10 5 21"></polyline>
                                                </svg>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--ink-primary); font-size: 0.95rem;">
                                            <?= htmlspecialchars($row['nama']); ?>
                                        </div>
                                        <div style="font-family: var(--font-mono); font-size: var(--font-size-xs); color: var(--ink-faint); margin-top: 2px;">
                                            ID: #<?= htmlspecialchars($row['id_barang']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $kat = $row['kategori'];
                                        $badge_class = 'badge-primary';
                                        if ($kat === 'Komputer') $badge_class = 'badge-komputer';
                                        elseif ($kat === 'Elektronik') $badge_class = 'badge-elektronik';
                                        elseif ($kat === 'Hand Phone') $badge_class = 'badge-handphone';
                                        ?>
                                        <span class="badge <?= $badge_class; ?>">
                                            <?= htmlspecialchars($kat); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="price-tag">
                                            Rp <?= number_format($row['harga_beli'], 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="price-tag profit">
                                            Rp <?= number_format($row['harga_jual'], 0, ',', '.'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php 
                                        $stok = (int)$row['stok'];
                                        if ($stok === 0) {
                                            echo '<span class="stock-indicator empty">Habis (0)</span>';
                                        } elseif ($stok <= 5) {
                                            echo '<span class="stock-indicator med">Menipis (' . $stok . ')</span>';
                                        } else {
                                            echo '<span class="stock-indicator high">Tersedia (' . $stok . ')</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <div class="table-actions">
                                            <a href="ubah.php?id=<?= $row['id_barang']; ?>" class="btn btn-secondary btn-sm" aria-label="Ubah <?= htmlspecialchars($row['nama']); ?>">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                                <span>Ubah</span>
                                            </a>
                                            <a href="hapus.php?id=<?= $row['id_barang']; ?>" class="btn btn-destructive btn-sm" aria-label="Hapus <?= htmlspecialchars($row['nama']); ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus barang <?= htmlspecialchars(addslashes($row['nama'])); ?>?')">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                                <span>Hapus</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="table-empty">
                                        <div class="empty-icon">
                                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="9" y1="9" x2="15" y2="15"></line>
                                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                            </svg>
                                        </div>
                                        <h3 style="font-weight: 700; margin-bottom: 4px; color: var(--ink-primary);">Belum Ada Data Barang</h3>
                                        <p style="font-size: var(--font-size-sm); margin-bottom: var(--space-4);">Tabel database masih kosong. Mulai dengan menambahkan produk pertama Anda.</p>
                                        <a href="tambah.php" class="btn btn-primary">Tambah Barang Pertama</a>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <!-- Empty search result row -->
                            <tr id="no-search-results" style="display: none;">
                                <td colspan="7" class="table-empty">
                                    <div class="empty-icon">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                        </svg>
                                    </div>
                                    <h3 style="font-weight: 700; margin-bottom: 4px; color: var(--ink-primary);">Tidak Ada Produk Yang Cocok</h3>
                                    <p style="font-size: var(--font-size-sm);">Coba periksa kata kunci pencarian atau ganti filter kategori.</p>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Interactive Client Filtering Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('table-search');
        const filterBtns = document.querySelectorAll('.filter-btn');
        const rows = document.querySelectorAll('.data-row');
        const noResultsRow = document.getElementById('no-search-results');
        const rowCountBadge = document.getElementById('row-count-badge');

        let currentCategory = 'all';

        function applyFilters() {
            const query = searchInput.value.toLowerCase().trim();
            let visibleCount = 0;

            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const category = row.getAttribute('data-category') || '';

                const matchesQuery = !query || name.includes(query) || category.toLowerCase().includes(query);
                const matchesCategory = (currentCategory === 'all') || (category === currentCategory);

                if (matchesQuery && matchesCategory) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            if (noResultsRow) {
                noResultsRow.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
            }

            if (rowCountBadge) {
                rowCountBadge.textContent = 'Menampilkan: ' + visibleCount + ' Unit';
            }
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        filterBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                filterBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentCategory = this.getAttribute('data-category');
                applyFilters();
            });
        });
    });
    </script>
</body>
</html>
