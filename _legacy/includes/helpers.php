<?php
// ===== Format Durasi =====
function formatDurasi($detik){
    $j = floor($detik / 3600);
    $m = floor(($detik % 3600) / 60);
    if($j > 0) return "$j j $m m";
    if($m > 0) return "$m m";
    return $detik . " d";
}

// ===== Tentukan shift berdasarkan jam real dan pengaturan =====
function tentukanShift($jam, $userJenis, $pengaturan) {
    if ($userJenis == 'non_shift') {
        return 'non_shift_pagi';
    }

    if (!$jam || $jam == '-') {
        return 'shift_pagi';
    }

    $h = intval(date('H', strtotime($jam)));
    // Logika shift UNHAS: Pagi (05-15), Malam (15-05)
    // Bisa disesuaikan jika ada aturan baru
    if ($h >= 5 && $h < 15) {
        return 'shift_pagi';
    } else {
        return 'shift_malam';
    }
}

// ===== Status Tepat Waktu / Terlambat / Cepat Pulang =====
function cekStatus($target_jam, $real_jam, $tipe = 'masuk') {
    if (!$real_jam || $real_jam == '-' || !$target_jam) {
        return '<span class="badge badge-info">Belum Ada</span>';
    }

    try {
        $shift_target = new DateTime($target_jam);
        $real_dt = new DateTime($real_jam);
    } catch (Exception $e) {
        return '<span class="badge badge-info">-</span>';
    }

    if ($tipe == 'masuk') {
        $diff = $real_dt->getTimestamp() - $shift_target->getTimestamp();
        if ($diff <= 0) {
            return "<span class='badge badge-success'>Tepat Waktu</span>";
        } else {
            return "<span class='badge badge-danger'>Terlambat " . formatDurasi($diff) . "</span>";
        }
    } else {
        $diff = $shift_target->getTimestamp() - $real_dt->getTimestamp();
        if ($diff <= 0) {
            return "<span class='badge badge-success'>Tepat Waktu</span>";
        } else {
            return "<span class='badge badge-warning'>Cepat " . formatDurasi($diff) . "</span>";
        }
    }
}
