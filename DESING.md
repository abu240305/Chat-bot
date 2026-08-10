## 1. Skema Warna (UNDIPA Identity)
| Elemen UI | Warna | Kode Hex |
|---|---|---|
| **Header & Bubble User** | Deep Blue | `#1E3A8A` |
| **Aksen / Quick Reply** | UNDIPA Gold | `#D97706` |
| **Peringatan / Error** | Red Alert | `#DC2626` |
| **Background Chat** | Off-White | `#F8FAFC` |
| **Bubble DIPA-Bot** | White | `#FFFFFF` |

---

## 2. Typography
* **Font Family:** `Inter`, sans-serif.
* **Body Text:** `13px`, warna teks `#0F172A` (Dark Slate).

---

## 3. Layout Wireframe
+-------------------------------------------------------------+
|  [Logo]   Layanan Akademik UNDIPA                 [Online]  |
+-------------------------------------------------------------+
|                                                             |
|  [Bot] Halo! Saya DIPA-Bot. Ada yang bisa dibantu?          |
|                                                             |
|        [Topik: KRS]  [Topik: Skripsi]  [Topik: Jadwal]      | <- Quick Replies
|                                                             |
|  [User] Minta template proposal skripsi dong.       [10:00] |
|                                                             |
|  [Bot] Berikut adalah template proposal skripsi resmi:      |
|        [ 📥 Download Template Proposal (.DOCX) ]            | <- Tombol File
|                                                     [10:00] |
|                                                             |
|  [ Animasi Titik-Titik: Bot sedang mengetik... ]            | <- Loading State
+-------------------------------------------------------------+
|  Ketik pesan (maks 250 karakter)...             [ Kirim ]   | <- Input Teks
+-------------------------------------------------------------+

---

## 4. State Management & Error UI
1. **Disabled Button:** Tombol "Kirim" abu-abu jika form kosong/hanya spasi.
2. **Double-Click Prevention:** Saat proses AJAX berjalan, input form dikunci (*readonly*) dan animasi *typing* muncul.
3. **Toast Connection Error:** Pop-up UI di bawah layar jika server down atau mahasiswa kehilangan koneksi internet.