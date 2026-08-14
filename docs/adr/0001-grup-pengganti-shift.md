# 0001: Kelayakan penggantian shift dimodelkan sebagai Grup Pengganti

Sistem lama membatasi Karyawan Pengganti harus berjabatan sama dengan Pemohon (filter posisi di UI), padahal di Farmasi Apoteker dan Asisten Apoteker saling menggantikan shift secara dua arah. Keputusan: kelayakan penggantian dimodelkan sebagai **Grup Pengganti** — jabatan dalam grup yang sama boleh saling menggantikan; default tiap jabatan grup sendiri sehingga perilaku lama (sesama jabatan) tetap berlaku di departemen lain, dan Farmasi satu grup (Apoteker + Asisten Apoteker). Grup dikelola lewat form Jabatan.

## Considered Options

- **Hardcode khusus Farmasi** — cepat tapi rapuh; tidak menjawab kebutuhan jangka panjang saat struktur jabatan berubah.
- **Pemetaan pairwise (jabatan → daftar jabatan pengganti)** — eksplisit dan mendukung aturan asimetris, tetapi lebih banyak baris data yang dikelola untuk kebutuhan yang pada dasarnya simetris.
- **Longgarkan ke level departemen** — terlalu lebar; di Pelayanan Medis (Perawat/Bidan/Dokter) penggantian lintas jabatan tidak diinginkan.

## Consequences

- Aturan wajib divalidasi server-side di `store()`/`update()` — sebelumnya batasannya murni filter UI.
- Kandidat Karyawan Pengganti tetap dibatasi dalam departemen yang sama dengan Pemohon.
- Jam kerja yang dicatat untuk penggantian adalah jam Shift yang Digantikan.
