<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once dirname(__DIR__) . '/koneksi.php';

// Security Check
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Current Page Detection
$current_page = basename($_SERVER['PHP_SELF']);

// Navigation Configuration (Centralized)
$nav_items = [
    [
        'title' => 'Dashboard',
        'icon' => 'layout-dashboard',
        'url' => 'dashboard_admin.php',
    ],
    [
        'title' => 'Verifikasi User',
        'icon' => 'users',
        'url' => 'verifikasi_user.php',
    ],
    [
        'title' => 'Terima Laporan',
        'icon' => 'alert-triangle',
        'url' => 'laporan_admin.php',
    ],
    [
        'title' => 'Pengaturan',
        'icon' => 'settings',
        'url' => 'pengaturan.php',
        'submenu' => [
            [
                'title' => 'Ringkasan Sistem',
                'icon' => 'layout',
                'url' => 'pengaturan.php'
            ],
            [
                'title' => 'Tambah Pos Jaga',
                'icon' => 'plus-circle',
                'url' => 'javascript:void(0)',
                'onclick' => 'openPosModal()'
            ],
            [
                'title' => 'Edit Jam Kerja',
                'icon' => 'clock',
                'url' => 'javascript:void(0)',
                'onclick' => 'openJamModal()'
            ]
        ]
    ],
    [
        'title' => 'Export Absen',
        'icon' => 'download-cloud',
        'url' => 'export_excel.php',
    ]
];
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($page_title) ? $page_title . ' - Admin UNHAS' : 'Admin Presensi Satpam UNHAS' ?></title>
    <link rel="stylesheet" href="assets/style.css?v=<?= time() ?>">
    <?php if (isset($extra_css) && is_array($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link rel="stylesheet" href="<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body>

    <div class="hamburger" onclick="toggleSidebar()">
        <i data-lucide="menu"></i>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <img src="assets/logo_unhas.png" alt="UNHAS"
                    style="width: 32px; height: 32px; object-fit: contain; background: #fff; border-radius: 50%; padding: 2px;">
                <span>Admin UNHAS</span>
            </div>
        </div>
        <div class="sidebar-nav">
            <?php foreach ($nav_items as $item):
                $isActive = ($current_page == $item['url']) ? 'active' : '';
                $hasSubmenu = isset($item['submenu']);
                ?>
                <?php if ($hasSubmenu): ?>
                    <div class="sidebar-group">
                        <a href="<?= $item['url'] ?>" class="<?= $isActive ?>"><i data-lucide="<?= $item['icon'] ?>"></i>
                            <?= $item['title'] ?></a>
                        <div class="sidebar-submenu">
                            <?php foreach ($item['submenu'] as $sub): ?>
                                <a href="<?= $sub['url'] ?>" <?= isset($sub['onclick']) ? 'onclick="' . $sub['onclick'] . '"' : '' ?>>
                                    <i data-lucide="<?= $sub['icon'] ?>"></i> <?= $sub['title'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= $item['url'] ?>" class="<?= $isActive ?>"><i data-lucide="<?= $item['icon'] ?>"></i>
                        <?= $item['title'] ?></a>
                    <?php Kalat: ?>    <?php endif; ?>
            <?php endforeach; ?>

            <a href="logout.php" style="margin-top: 50px; color: #f87171;"><i data-lucide="log-out"></i> Keluar</a>
        </div>
    </div>

    <div class="main" id="mainContent">