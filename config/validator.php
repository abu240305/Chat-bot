<?php

// ====================================================================
// config/validator.php — Modul Validasi Terpusat
// ====================================================================
// Memuat fungsi validasi untuk SEMUA form aplikasi (utama + opsional):
//   - Form Chat Mahasiswa        -> valChatMessage()
//   - Form Login Admin           -> valLogin()
//   - Form Modal Tambah/Edit Q&A -> valQA()
//   - Form Upload Dokumen        -> valFileUpload()
//   - Form Ubah Password Admin   -> valPasswordChange()
//   - Filter & Search Log        -> valLogFilter()
// Semua fungsi mengembalikan pesan error (string) atau '' bila valid.
// ====================================================================

/** Escape output (anti-XSS) — penyederhana dari htmlspecialchars. */
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/** Validasi teks umum dengan batas min/maks karakter. */
function valText($value, $label, $min = 1, $max = 250) {
    $value = trim((string)$value);

    if ($value === '') {
        return $label . ' tidak boleh kosong.';
    }
    if (mb_strlen($value) < $min) {
        return $label . ' minimal ' . $min . ' karakter.';
    }
    if (mb_strlen($value) > $max) {
        return $label . ' maksimal ' . $max . ' karakter.';
    }
    return '';
}

/** 1) Form Chat Mahasiswa: 1-250 karakter, anti-empty. */
function valChatMessage($message) {
    $message = trim((string)$message);

    if ($message === '') {
        return 'Pesan tidak boleh kosong.';
    }
    if (mb_strlen($message) < 1) {
        return 'Pesan terlalu pendek. Minimal 1 karakter.';
    }
    if (mb_strlen($message) > 250) {
        return 'Pesan terlalu panjang. Maksimal 250 karakter.';
    }
    return '';
}

/** 2) Form Login Admin: username & password wajib. */
function valLogin($username, $password) {
    if (trim((string)$username) === '') {
        return 'Username harus diisi.';
    }
    if ((string)$password === '') {
        return 'Password harus diisi.';
    }
    return '';
}

/** 3) Form Q&A: pertanyaan (1-250) & jawaban (1-2000). */
function valQA($pertanyaan, $jawaban) {
    $err = valText($pertanyaan, 'Pertanyaan', 1, 250);
    if ($err !== '') {
        return $err;
    }
    $err = valText($jawaban, 'Jawaban', 1, 2000);
    if ($err !== '') {
        return $err;
    }
    return '';
}

/** 4) Form Upload Dokumen: wajib ada, ukuran, ekstensi, dan MIME-type. */
function valFileUpload($file, $maxSize, array $allowedExt, array $allowedMime) {
    if (!isset($file) || !is_array($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return 'Silakan pilih file terlebih dahulu.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Terjadi kesalahan saat upload file.';
    }
    if ($file['size'] > $maxSize) {
        return 'Ukuran file melebihi batas maksimal ' . round($maxSize / (1024 * 1024)) . ' MB.';
    }
    if ($file['size'] <= 0) {
        return 'File kosong.';
    }

    $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) {
        return 'Format file tidak diizinkan. Hanya ' . strtoupper(implode(', ', $allowedExt)) . '.';
    }

    if (function_exists('finfo_open')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array((string)$mime, $allowedMime, true)) {
            return 'Isi file tidak valid (MIME: ' . e($mime) . '). Ekstensi tidak sesuai isi file.';
        }
    }

    return '';
}

/** 5) Form Ubah Password: lama wajib, baru min 8, harus cocok dgn konfirmasi. */
function valPasswordChange($old, $new, $confirm) {
    if ((string)$old === '') {
        return 'Password lama harus diisi.';
    }
    if (mb_strlen((string)$new) < 8) {
        return 'Password baru minimal 8 karakter.';
    }
    if ((string)$new !== (string)$confirm) {
        return 'Konfirmasi password baru tidak cocok.';
    }
    return '';
}

/** 6) Filter & Search Log: format aman untuk keyword, status, dan tanggal. */
function valLogFilter($keyword, $status, $date) {
    if (mb_strlen((string)$keyword) > 100) {
        return 'Keyword pencarian maksimal 100 karakter.';
    }
    if (!in_array($status, ['', 'berhasil', 'fallback'], true)) {
        return 'Status filter tidak valid.';
    }
    if ($date !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$date)) {
        return 'Format tanggal tidak valid. Gunakan YYYY-MM-DD.';
    }
    return '';
}