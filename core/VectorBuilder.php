<?php

// ====================================================================
// core/VectorBuilder.php — Pembangun Ulang Vektor TF-IDF Knowledge Base
// ====================================================================
// Menghitung bobot TF-IDF yang BENAR (tf * idf) untuk seluruh dokumen
// di tb_pengetahuan, lalu mengisi ulang tabel cache tb_vektor_tfidf.
// Dipakai oleh:
//   - admin/qa_manage.php  (setelah tambah/edit Q&A)
//   - admin/rebuild_vektor.php (rebuild massal sekali jalan)
// Chat tetap cepat karena perhitungan hanya terjadi saat aksi admin,
// bukan saat mahasiswa mengirim pertanyaan.
// ====================================================================

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/Preprocessing.php';
require_once __DIR__ . '/Tfidf.php';

function rebuildAllVectors($db) {
    $preprocessing = new Preprocessing($db);
    $tfidf = new Tfidf();

    $rows = $db->query("SELECT id_pengetahuan, pertanyaan FROM tb_pengetahuan ORDER BY id_pengetahuan ASC")
               ->fetchAll(PDO::FETCH_ASSOC);

    $totalDokumen = count($rows);
    $totalTerm = 0;

    if ($totalDokumen === 0) {
        $db->exec("DELETE FROM tb_vektor_tfidf");
        return ['total_dokumen' => 0, 'total_term' => 0];
    }

    // 1) Preprocess semua dokumen (tokenisasi) untuk vocabulary + IDF
    $tokenDocs = [];
    foreach ($rows as $row) {
        $tokenDocs[$row['id_pengetahuan']] = $preprocessing->process($row['pertanyaan'], $db);
    }

    // 2) Bangun vocabulary & hitung IDF (log N/df) dari seluruh korpus
    $tfidf->buildVocabulary(array_values($tokenDocs));

    // 3) Kosongkan cache, lalu isi bobot tf * idf per dokumen
    $db->exec("DELETE FROM tb_vektor_tfidf");

    $stmt = $db->prepare("INSERT INTO tb_vektor_tfidf (id_pengetahuan, term, bobot_tfidf) VALUES (?, ?, ?)");

    foreach ($tokenDocs as $docId => $tokens) {
        $vector = $tfidf->calculateTFIDF($tokens);

        foreach ($vector as $term => $bobot) {
            if ((float)$bobot > 0) {
                $stmt->execute([(int)$docId, (string)$term, round((float)$bobot, 6)]);
                $totalTerm++;
            }
        }
    }

    return [
        'total_dokumen' => $totalDokumen,
        'total_term'    => $totalTerm
    ];
}