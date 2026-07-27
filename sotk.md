# Struktur Organisasi dan Tata Kerja (SOTK)
# Klinik Utama Bukit Raya

## Informasi Umum
- **Lokasi**: Pekanbaru
- **Tanggal Dokumen**: 27 Juli 2026
- **Otoritas Tertinggi**: dr. Putri Sari Yenni

---

## Hierarki Kepemimpinan

### 1. Pimpinan Puncak
- **Kepala Klinik**: dr. Putri Sari Yenni
- **Penanggung Jawab Klinik**: dr. Putri Sari Yenni

---

## Pembagian Bidang & Unit Kerja

### A. Penanggung Jawab Pelayanan
**Kepala**: dr. Ahmad Septriadi

| Unit Kerja | Nama Penanggung Jawab | Gelar |
| :--- | :--- | :--- |
| Ruang Pendaftaran | Devi Amelia Putri | Amd.Kes |
| Ruang Rekam Medis | Pebri Dwi Putri | Amd.Kes |
| Ruang Tindakan | Restu Damayanti | Amd.Keb |
| Ruang Pemeriksaan Umum | dr. Adib Aulia Rahman | - |
| Ruang Pemeriksaan Gigi | Noferta Ersa Febi | Amd.Gz |
| Ruang Pemeriksaan KIA | Dewi Romanti | Amd.Keb |
| Ruang Bersalin | Weni Saputri | Amd.Keb |
| Ruang Farmasi | Vany Rahmayani Sihombing | S.Farm.Apt |
| Laboratorium | Erma Putri | Amd.AK |

### B. Penanggung Jawab Administrasi
**Kepala**: Widya Habibie, Amd

| Unit Kerja | Nama Penanggung Jawab | Keterangan |
| :--- | :--- | :--- |
| Kepegawaian | Syafrul Andri Savero | Amd.Kes |
| Keuangan | Widya Habibie | Amd |
| Sarana Prasarana | Widya Habibie | Amd |
| Linen & Kebersihan | Yud Media Putra | SE |
| Keamanan | Dedy Vintor P. Tampubolon | - |
| Teknologi & Informasi | Muhammad Reyhan Perdana Asta | S.tr.Kom |

### C. Penanggung Jawab Mutu
**Kepala**: dr. Thiara Anggun Mauldina

| Unit Kerja / Program | Nama Penanggung Jawab | Gelar |
| :--- | :--- | :--- |
| PPI (Pencegahan & Pengendalian Infeksi) | Weni Saputri | Amd.Keb |
| Manajemen Risiko | Restu Damayanti | Amd.Keb |
| Keselamatan Pasien | dr. Adib Aulia Rahman | - |
| K3 (Keselamatan & Kesehatan Kerja) | Erma Putri | Amd.AK |
| Audit Maternal | Widya Habibie | Amd |

---

## Mapping System KaryaOne HRIS

### 1. Structure Mapping (Departemen & Jabatan di Database)

| Departemen System | Jabatan / Posisi System |
| :--- | :--- |
| **Manajemen** | Direktur, Manajer, Casemix HRD, IT, Staff Laboratorium, Keuangan |
| **Pelayanan Medis** | Perawat, Bidan, Rekam Medis, Dokter |
| **Farmasi & Keuangan** | Apoteker, Asisten Apoteker |
| **Security & Driver** | Security |
| **Cleaning Service** | Cleaning Service |

### 2. Role-Based Access Control (RBAC) & Multi-Level Approval
- **Super Admin**: Akses penuh ke seluruh sistem & modul.
- **Director**: Persetujuan tingkat akhir (Cuti, Lembur, Tukar Shift).
- **HR Admin (`hr-admin`)**: Mengelola data karyawan, kuota cuti, dan persetujuan HRD (`pending_hrd`).
- **Manager (`manager`)**: Persetujuan manajerial tingkat departemen (`pending_manager`).
- **Kepala Ruangan (`karu`)**: Persetujuan tingkat pertama unit/ruangan untuk pengajuan anggotanya.
- **Employee**: Mengajukan cuti, lembur, dan tukar shift serta melihat data personal.

---

## Metadata AI (Contextual Data)
- **Total Penanggung Jawab Utama**: 3 Orang
- **Total Unit di Bawah Pelayanan**: 9 Unit
- **Total Unit di Bawah Administrasi**: 6 Unit
- **Total Unit di Bawah Mutu**: 5 Unit
- **Double Role (Perangkapan Jabatan Operational)**: 
  - Widya Habibie (Administrasi, Keuangan, Sarpras, Audit Maternal)
  - Weni Saputri (Ruang Bersalin, PPI)
  - Restu Damayanti (Ruang Tindakan, Manajemen Risiko)
  - dr. Adib Aulia Rahman (Pemeriksaan Umum, Keselamatan Pasien)
  - Erma Putri (Laboratorium, K3)
- **Terakhir Diperbarui**: 27 Juli 2026
