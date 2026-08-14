# KaryaOne HRMS

Sistem informasi SDM internal untuk klinik/rumah sakit: data karyawan terpusat, pengajuan cuti & lembur, dan penggantian shift dengan persetujuan berjenjang.

## Language

**Pengajuan Lembur** (Overtime Request):
Pengajuan karyawan untuk mencatat jam kerja lembur pada tanggal tertentu, atas nama dirinya sendiri.
_Avoid_: Lembur, overtime

**Penggantian Shift** (Shift Change Request):
Pengajuan Pemohon agar shift-nya pada tanggal tertentu diambil alih oleh Karyawan Pengganti.
_Avoid_: Tukar shift, lembur pengganti

**Pemohon** (Requester):
Karyawan yang shift-nya digantikan.
_Avoid_: Requester, karyawan yang minta ganti

**Karyawan Pengganti** (Target):
Karyawan yang mengambil alih Shift yang Digantikan dan bekerja sesuai jam shift tersebut.
_Avoid_: Target, pengganti

**Grup Pengganti** (Replacement Group):
Kelompok jabatan yang boleh saling menggantikan shift satu sama lain. Jabatan dalam grup yang sama boleh menjadi Pemohon dan Karyawan Pengganti satu sama lain; jabatan di luar grup tidak.
_Avoid_: Replacement rule, eligibilitas pengganti

**Shift yang Digantikan** (Requester Shift):
Shift milik Pemohon pada tanggal penggantian; jam kerjanya menjadi jam kerja Karyawan Pengganti.
_Avoid_: Requester shift, shift asal
