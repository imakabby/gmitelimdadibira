<?php

function formatTanggalIndonesia($date) {
    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );

    $split = explode('-', date('Y-m-d', strtotime($date)));
    
    return $split[2] . ' ' . $bulan[(int)$split[1]] . ' ' . $split[0];
} 

function formatTanggalJamIndonesia($datetime) {
    $bulan = array(
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    );
    
    $hari = array(
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    );
    
    $timestamp = strtotime($datetime);
    
    // Format tanggal
    $tanggal = date('d', $timestamp);
    $bulan_index = date('n', $timestamp);
    $tahun = date('Y', $timestamp);
    $nama_bulan = $bulan[$bulan_index];
    
    // Format hari
    $nama_hari_en = date('l', $timestamp);
    $nama_hari = $hari[$nama_hari_en];
    
    // Format jam
    $jam = date('H:i', $timestamp);
    
    // return $nama_hari . ', ' . $tanggal . ' ' . $nama_bulan . ' ' . $tahun . ' ' . $jam . ' WITA';
    return $tanggal . ' ' . $nama_bulan . ' ' . $tahun . ' ' . $jam . ' WITA';
}

function waktuYangLalu($datetime) {
    $timestamp = strtotime($datetime);
    $selisih = time() - $timestamp;
    
    // Jika waktu kurang dari 60 detik yang lalu
    if ($selisih < 60) {
        return 'Baru saja';
    }
    
    // Jika waktu kurang dari 60 menit yang lalu
    elseif ($selisih < 60 * 60) {
        $menit = floor($selisih / 60);
        return $menit . ' menit lalu';
    }
    
    // Jika waktu kurang dari 24 jam yang lalu
    elseif ($selisih < 60 * 60 * 24) {
        $jam = floor($selisih / (60 * 60));
        return $jam . ' jam lalu';
    }
    
    // Jika waktu kurang dari 2 hari yang lalu
    elseif ($selisih < 60 * 60 * 24 * 2) {
        return 'Kemarin pukul ' . date('H:i', $timestamp) . ' WITA';
    }
    
    // Jika waktu kurang dari 7 hari yang lalu
    elseif ($selisih < 60 * 60 * 24 * 7) {
        $hari = floor($selisih / (60 * 60 * 24));
        return $hari . ' hari lalu';
    }
    
    // Jika waktu lebih dari 7 hari yang lalu, gunakan format tanggal standar
    else {
        return formatTanggalIndonesia($datetime);
    }
} 