-- Dummy data untuk database `informasi`
-- Dibuat sebagai data tambahan; jalankan setelah struktur tabel dari informasi.sql.
-- Semua tanggal dan identitas di bawah ini bersifat sintetis.

SET NAMES utf8mb4;
USE `informasi`;
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- Diagnosa rawat darurat
INSERT IGNORE INTO `diagnosa_rd`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', 'A09', 'Gastroenteritis dan kolitis infeksius', 12, '2026-09-03 23:00:00'),
  ('2026-09-03', 'I10', 'Hipertensi esensial', 9, '2026-09-03 23:00:00'),
  ('2026-09-03', 'J18', 'Pneumonia, organisme tidak spesifik', 7, '2026-09-03 23:00:00'),
  ('2026-09-04', 'A09', 'Gastroenteritis dan kolitis infeksius', 15, '2026-09-04 23:00:00'),
  ('2026-09-04', 'I10', 'Hipertensi esensial', 11, '2026-09-04 23:00:00'),
  ('2026-09-04', 'J18', 'Pneumonia, organisme tidak spesifik', 8, '2026-09-04 23:00:00'),
  ('2026-09-05', 'A09', 'Gastroenteritis dan kolitis infeksius', 13, '2026-09-05 23:00:00'),
  ('2026-09-05', 'I10', 'Hipertensi esensial', 10, '2026-09-05 23:00:00'),
  ('2026-09-05', 'J18', 'Pneumonia, organisme tidak spesifik', 9, '2026-09-05 23:00:00');

-- Diagnosa rawat inap
INSERT IGNORE INTO `diagnosa_ri`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', 'I10', 'Hipertensi esensial', 18, '2026-09-03 23:00:00'),
  ('2026-09-03', 'E11', 'Diabetes melitus tipe 2', 14, '2026-09-03 23:00:00'),
  ('2026-09-03', 'J18', 'Pneumonia, organisme tidak spesifik', 11, '2026-09-03 23:00:00'),
  ('2026-09-04', 'I10', 'Hipertensi esensial', 20, '2026-09-04 23:00:00'),
  ('2026-09-04', 'E11', 'Diabetes melitus tipe 2', 16, '2026-09-04 23:00:00'),
  ('2026-09-04', 'J18', 'Pneumonia, organisme tidak spesifik', 12, '2026-09-04 23:00:00'),
  ('2026-09-05', 'I10', 'Hipertensi esensial', 22, '2026-09-05 23:00:00'),
  ('2026-09-05', 'E11', 'Diabetes melitus tipe 2', 15, '2026-09-05 23:00:00'),
  ('2026-09-05', 'J18', 'Pneumonia, organisme tidak spesifik', 13, '2026-09-05 23:00:00');

-- Diagnosa rawat jalan
INSERT IGNORE INTO `diagnosa_rj`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', 'I10', 'Hipertensi esensial', 34, '2026-09-03 23:00:00'),
  ('2026-09-03', 'M54.5', 'Nyeri punggung bawah', 27, '2026-09-03 23:00:00'),
  ('2026-09-03', 'J06.9', 'Infeksi saluran pernapasan akut', 23, '2026-09-03 23:00:00'),
  ('2026-09-04', 'I10', 'Hipertensi esensial', 38, '2026-09-04 23:00:00'),
  ('2026-09-04', 'M54.5', 'Nyeri punggung bawah', 29, '2026-09-04 23:00:00'),
  ('2026-09-04', 'J06.9', 'Infeksi saluran pernapasan akut', 25, '2026-09-04 23:00:00'),
  ('2026-09-05', 'I10', 'Hipertensi esensial', 41, '2026-09-05 23:00:00'),
  ('2026-09-05', 'M54.5', 'Nyeri punggung bawah', 31, '2026-09-05 23:00:00'),
  ('2026-09-05', 'J06.9', 'Infeksi saluran pernapasan akut', 28, '2026-09-05 23:00:00');

-- Indikator rumah sakit harian
INSERT IGNORE INTO `indikator_rs`
  (`TANGGAL`, `AWAL`, `MASUK`, `PINDAHAN`, `KURANG48JAM`, `LEBIH48JAM`, `LD`, `SISA`, `TTIDUR`, `HP`, `DIPINDAHKAN`, `JMLKLR`, `JMLHARI`, `LASTUPDATED`) VALUES
  ('2026-09-03', 46, 22, 3, 10, 8, 2, 50, 120, 67, 1, 21, 94, '2026-09-03 23:59:59'),
  ('2026-09-04', 50, 25, 2, 12, 9, 1, 53, 120, 72, 2, 24, 110, '2026-09-04 23:59:59'),
  ('2026-09-05', 53, 28, 4, 11, 12, 2, 57, 120, 78, 3, 28, 123, '2026-09-05 23:59:59'),
  ('2026-09-07', 57, 30, 3, 14, 11, 2, 61, 120, 82, 2, 29, 127, '2026-09-07 23:59:59');

-- Klaim IKS berdasarkan cara bayar
INSERT IGNORE INTO `klaim_iks`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `CARABAYAR`, `IDCARABAYAR`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', '1', 'Klaim selesai diproses', 'JKN', '1', 48, '2026-09-03 23:30:00'),
  ('2026-09-03', '1', 'Klaim selesai diproses', 'NON JKN', '5', 12, '2026-09-03 23:30:00'),
  ('2026-09-04', '1', 'Klaim selesai diproses', 'JKN', '1', 55, '2026-09-04 23:30:00'),
  ('2026-09-04', '1', 'Klaim selesai diproses', 'NON JKN', '5', 15, '2026-09-04 23:30:00'),
  ('2026-09-05', '1', 'Klaim selesai diproses', 'JKN', '1', 59, '2026-09-05 23:30:00'),
  ('2026-09-05', '1', 'Klaim selesai diproses', 'NON JKN', '5', 17, '2026-09-05 23:30:00');

-- Klaim INA-CBG berdasarkan cara bayar
INSERT IGNORE INTO `klaim_inacbg`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `CARABAYAR`, `IDCARABAYAR`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', '1', 'Klaim rawat inap', 'JKN', '1', 35, '2026-09-03 23:30:00'),
  ('2026-09-03', '2', 'Klaim rawat jalan', 'JKN', '1', 84, '2026-09-03 23:30:00'),
  ('2026-09-03', '1', 'Klaim rawat inap', 'NON JKN', '5', 7, '2026-09-03 23:30:00'),
  ('2026-09-04', '1', 'Klaim rawat inap', 'JKN', '1', 39, '2026-09-04 23:30:00'),
  ('2026-09-04', '2', 'Klaim rawat jalan', 'JKN', '1', 91, '2026-09-04 23:30:00'),
  ('2026-09-04', '1', 'Klaim rawat inap', 'NON JKN', '5', 8, '2026-09-04 23:30:00'),
  ('2026-09-05', '1', 'Klaim rawat inap', 'JKN', '1', 43, '2026-09-05 23:30:00'),
  ('2026-09-05', '2', 'Klaim rawat jalan', 'JKN', '1', 96, '2026-09-05 23:30:00'),
  ('2026-09-05', '1', 'Klaim rawat inap', 'NON JKN', '5', 9, '2026-09-05 23:30:00');

-- Sepuluh besar klaim INA-CBG rawat inap dan rawat jalan
INSERT IGNORE INTO `klaim_inacbg_ri`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', 'C-4-13-I', 'Penyakit infeksi dan parasit', 18, '2026-09-03 23:30:00'),
  ('2026-09-04', 'C-4-13-I', 'Penyakit infeksi dan parasit', 21, '2026-09-04 23:30:00'),
  ('2026-09-05', 'C-4-13-I', 'Penyakit infeksi dan parasit', 23, '2026-09-05 23:30:00');

INSERT IGNORE INTO `klaim_inacbg_rj`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', 'A-4-10-I', 'Pemeriksaan dan konsultasi umum', 42, '2026-09-03 23:30:00'),
  ('2026-09-04', 'A-4-10-I', 'Pemeriksaan dan konsultasi umum', 47, '2026-09-04 23:30:00'),
  ('2026-09-05', 'A-4-10-I', 'Pemeriksaan dan konsultasi umum', 50, '2026-09-05 23:30:00');

-- Komentar dan konfirmasi pasien fast track
INSERT IGNORE INTO `komentar_pasien_fast_track`
  (`KUNJUNGAN`, `KOMENTAR`, `OLEH`, `TANGGAL`, `REPLY_FROM`) VALUES
  ('2026090700000000001', 'Pasien sudah datang dan menunggu verifikasi berkas.', 1001, '2026-09-07 08:15:00', 0),
  ('2026090700000000002', 'Dokumen penjamin sudah dilengkapi oleh pasien.', 1002, '2026-09-07 09:05:00', 0),
  ('2026090700000000001', 'Petugas loket mengonfirmasi kelengkapan dokumen.', 1003, '2026-09-07 08:25:00', 0);

INSERT IGNORE INTO `konfirmasi_pasien_fast_track`
  (`KUNJUNGAN`, `STATUS_KONFIRMASI`, `KONFIRMASI_KE`, `CATATAN`, `TANGGAL`, `OLEH`, `UPDATE_TIME`, `STATUS`) VALUES
  ('2026090700000000001', 2, 7, 'Konfirmasi fast track berhasil.', '2026-09-07 08:20:00', 1001, '2026-09-07 08:20:00', 1),
  ('2026090700000000002', 1, 7, 'Menunggu konfirmasi petugas pendaftaran.', '2026-09-07 09:10:00', 1002, '2026-09-07 09:10:00', 1),
  ('2026090700000000003', 3, 7, 'Pasien membatalkan kedatangan.', '2026-09-07 10:00:00', 1004, '2026-09-07 10:00:00', 1);

-- Kunjungan berdasarkan instalasi, unit, subunit, dan cara bayar
INSERT IGNORE INTO `kunjungan`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `IDINSTALASI`, `INSTALASI`, `IDUNIT`, `UNIT`, `IDSUBUNIT`, `SUBUNIT`, `CARABAYAR`, `IDCARABAYAR`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', '1', 'Rawat Jalan', '10201', 'Rawat Jalan', '1020101', 'Poliklinik', '102010101', 'Poli Penyakit Dalam', 'JKN', '1', 86, '2026-09-03 23:00:00'),
  ('2026-09-03', '1', 'Rawat Jalan', '10201', 'Rawat Jalan', '1020101', 'Poliklinik', '102010108', 'Poli Jantung', 'NON JKN', '5', 18, '2026-09-03 23:00:00'),
  ('2026-09-04', '1', 'Rawat Jalan', '10201', 'Rawat Jalan', '1020101', 'Poliklinik', '102010107', 'Poli Saraf', 'JKN', '1', 44, '2026-09-04 23:00:00'),
  ('2026-09-05', '2', 'Rawat Inap', '10203', 'Rawat Inap', '1020301', 'Perawatan Biasa', '102030101', 'Ruang Perawatan', 'JKN', '1', 32, '2026-09-05 23:00:00');

-- Indeks kepadatan IGD (NEDOCS). ID sengaja tidak diisi agar AUTO_INCREMENT bekerja.
INSERT IGNORE INTO `nedocs_igd`
  (`TANGGAL`, `RUANGAN`, `UNIT`, `X`, `Y`, `A`, `B`, `C`, `D`, `E`, `SKOR`, `LEVEL`, `LEVEL_DESKRIPSI`, `WARNA`, `LAST_UPDATE`, `STATUS`) VALUES
  ('2026-09-07 08:00:00', '', 'SEMUA UNIT GAWAT DARURAT', 20, 10, 4, 1, 0, 0.00, 0.00, 12.00, 1, 'Tidak Sibuk', '#7BC67B', '2026-09-07 08:00:00', 1),
  ('2026-09-07 08:00:00', '102020101', 'IGD', 10, 6, 5, 2, 1, 0.50, 0.00, 48.00, 2, 'Sibuk', '#A8CF6E', '2026-09-07 08:00:00', 1),
  ('2026-09-07 12:00:00', '', 'SEMUA UNIT GAWAT DARURAT', 20, 10, 8, 3, 1, 1.50, 0.50, 78.00, 3, 'Luar Biasa Sibuk (Not Overcrowded)', '#D6D96F', '2026-09-07 12:00:00', 1),
  ('2026-09-07 12:00:00', '102020101', 'IGD', 10, 6, 8, 4, 2, 2.00, 1.00, 118.00, 4, 'Overcrowded', '#F3C56A', '2026-09-07 12:00:00', 1),
  ('2026-09-07 16:00:00', '', 'SEMUA UNIT GAWAT DARURAT', 20, 10, 12, 5, 3, 3.00, 2.00, 156.00, 5, 'Severely Overcrowded', '#F59C6A', '2026-09-07 16:00:00', 1),
  ('2026-09-07 16:00:00', '102020101', 'IGD', 10, 6, 10, 6, 4, 4.00, 3.00, 205.00, 6, 'Dangerously Overcrowded', '#E96A6A', '2026-09-07 16:00:00', 1);

-- Pasien rawat inap
INSERT IGNORE INTO `pasien_rawat_inap`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `CARABAYAR`, `IDCARABAYAR`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', '1', 'Pasien masuk rawat inap', 'JKN', '1', 24, '2026-09-03 23:00:00'),
  ('2026-09-03', '2', 'Pasien keluar rawat inap', 'JKN', '1', 19, '2026-09-03 23:00:00'),
  ('2026-09-04', '1', 'Pasien masuk rawat inap', 'JKN', '1', 27, '2026-09-04 23:00:00'),
  ('2026-09-04', '2', 'Pasien keluar rawat inap', 'JKN', '1', 22, '2026-09-04 23:00:00'),
  ('2026-09-05', '1', 'Pasien masuk rawat inap', 'JKN', '1', 30, '2026-09-05 23:00:00'),
  ('2026-09-05', '2', 'Pasien keluar rawat inap', 'JKN', '1', 25, '2026-09-05 23:00:00');

-- Pendapatan pelayanan
INSERT IGNORE INTO `pendapatan`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `CARABAYAR`, `IDCARABAYAR`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', '1', 'Pendapatan rawat jalan', 'JKN', '1', 18500000, '2026-09-03 23:30:00'),
  ('2026-09-03', '2', 'Pendapatan rawat inap', 'NON JKN', '5', 12750000, '2026-09-03 23:30:00'),
  ('2026-09-04', '1', 'Pendapatan rawat jalan', 'JKN', '1', 21400000, '2026-09-04 23:30:00'),
  ('2026-09-04', '2', 'Pendapatan rawat inap', 'NON JKN', '5', 14200000, '2026-09-04 23:30:00'),
  ('2026-09-05', '1', 'Pendapatan rawat jalan', 'JKN', '1', 23600000, '2026-09-05 23:30:00'),
  ('2026-09-05', '2', 'Pendapatan rawat inap', 'NON JKN', '5', 15800000, '2026-09-05 23:30:00');

-- Penerimaan kas
INSERT IGNORE INTO `penerimaan`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `SHORTDESK`, `CARABAYAR`, `IDCARABAYAR`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', '1', 'Penerimaan pelayanan rawat jalan', 'RJ', 'NON JKN', '5', 8200000, '2026-09-03 23:30:00'),
  ('2026-09-03', '2', 'Penerimaan pelayanan rawat inap', 'RI', 'NON JKN', '5', 5400000, '2026-09-03 23:30:00'),
  ('2026-09-04', '1', 'Penerimaan pelayanan rawat jalan', 'RJ', 'NON JKN', '5', 9100000, '2026-09-04 23:30:00'),
  ('2026-09-04', '2', 'Penerimaan pelayanan rawat inap', 'RI', 'NON JKN', '5', 6250000, '2026-09-04 23:30:00'),
  ('2026-09-05', '1', 'Penerimaan pelayanan rawat jalan', 'RJ', 'NON JKN', '5', 10400000, '2026-09-05 23:30:00'),
  ('2026-09-05', '2', 'Penerimaan pelayanan rawat inap', 'RI', 'NON JKN', '5', 7100000, '2026-09-05 23:30:00');

-- Pengunjung
INSERT IGNORE INTO `pengunjung`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `CARABAYAR`, `IDCARABAYAR`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', '1', 'Pengunjung rawat jalan', 'JKN', '1', 102, '2026-09-03 23:00:00'),
  ('2026-09-03', '2', 'Pengunjung rawat jalan', 'NON JKN', '5', 31, '2026-09-03 23:00:00'),
  ('2026-09-04', '1', 'Pengunjung rawat jalan', 'JKN', '1', 118, '2026-09-04 23:00:00'),
  ('2026-09-04', '2', 'Pengunjung rawat jalan', 'NON JKN', '5', 36, '2026-09-04 23:00:00'),
  ('2026-09-05', '1', 'Pengunjung rawat jalan', 'JKN', '1', 126, '2026-09-05 23:00:00'),
  ('2026-09-05', '2', 'Pengunjung rawat jalan', 'NON JKN', '5', 39, '2026-09-05 23:00:00');

-- Penunjang: laboratorium dan radiologi
INSERT IGNORE INTO `penunjang`
  (`TANGGAL`, `ID`, `DESKRIPSI`, `IDINSTALASI`, `INSTALASI`, `IDUNIT`, `UNIT`, `IDSUBUNIT`, `SUBUNIT`, `CARABAYAR`, `IDCARABAYAR`, `VALUE`, `LASTUPDATED`) VALUES
  ('2026-09-03', '1', 'Pemeriksaan Laboratorium', '10204', 'Penunjang Medik', '1020401', 'Laboratorium', '102040101', 'Laboratorium Klinik', 'JKN', '1', 76, '2026-09-03 23:00:00'),
  ('2026-09-03', '2', 'Pemeriksaan Radiologi', '10204', 'Penunjang Medik', '1020402', 'Radiologi', '102040201', 'Radiologi Diagnostik', 'JKN', '1', 34, '2026-09-03 23:00:00'),
  ('2026-09-04', '1', 'Pemeriksaan Laboratorium', '10204', 'Penunjang Medik', '1020401', 'Laboratorium', '102040101', 'Laboratorium Klinik', 'JKN', '1', 83, '2026-09-04 23:00:00'),
  ('2026-09-04', '2', 'Pemeriksaan Radiologi', '10204', 'Penunjang Medik', '1020402', 'Radiologi', '102040201', 'Radiologi Diagnostik', 'JKN', '1', 39, '2026-09-04 23:00:00'),
  ('2026-09-05', '1', 'Pemeriksaan Laboratorium', '10204', 'Penunjang Medik', '1020401', 'Laboratorium', '102040101', 'Laboratorium Klinik', 'JKN', '1', 91, '2026-09-05 23:00:00'),
  ('2026-09-05', '2', 'Pemeriksaan Radiologi', '10204', 'Penunjang Medik', '1020402', 'Radiologi', '102040201', 'Radiologi Diagnostik', 'JKN', '1', 42, '2026-09-05 23:00:00');

-- Statistik 10 besar diagnosa rujukan
INSERT IGNORE INTO `statistik_10_besar_diagnosa_rujukan`
  (`TAHUN`, `BULAN`, `JENIS_RUJUKAN`, `REF_ID_KEMKES`, `KONTEN`, `TANGGAL_UPDATED`, `KIRIM`) VALUES
  (2026, 9, 1, 'DUMMY-DIAG-RUJUKAN-2026-09', '{"periode":"2026-09","jenis":"dirujuk","data":[{"kode":"I10","nama":"Hipertensi esensial","jumlah":18},{"kode":"J18","nama":"Pneumonia","jumlah":12}]}', '2026-09-07 23:00:00', 1),
  (2026, 9, 2, 'DUMMY-DIAG-RUJUKAN-2026-09', '{"periode":"2026-09","jenis":"rujukan balik","data":[{"kode":"E11","nama":"Diabetes melitus tipe 2","jumlah":9},{"kode":"A09","nama":"Gastroenteritis","jumlah":6}]}', '2026-09-07 23:00:00', 1);

-- Statistik 10 besar penyakit
INSERT IGNORE INTO `statistik_10_besar_penyakit`
  (`TAHUN`, `BULAN`, `JENIS_PELAYANAN`, `REF_ID_KEMKES`, `KONTEN`, `TANGGAL_UPDATED`, `KIRIM`) VALUES
  (2026, 9, 1, 'DUMMY-PENYAKIT-2026-09', '{"periode":"2026-09","pelayanan":"rawat inap","data":[{"kode":"I10","nama":"Hipertensi esensial","jumlah":60},{"kode":"E11","nama":"Diabetes melitus tipe 2","jumlah":45}]}', '2026-09-07 23:00:00', 1),
  (2026, 9, 2, 'DUMMY-PENYAKIT-2026-09', '{"periode":"2026-09","pelayanan":"rawat jalan","data":[{"kode":"I10","nama":"Hipertensi esensial","jumlah":113},{"kode":"M54.5","nama":"Nyeri punggung bawah","jumlah":87}]}', '2026-09-07 23:00:00', 1);

-- Statistik golongan darah
INSERT IGNORE INTO `statistik_gol_darah`
  (`TAHUN`, `BULAN`, `KODE`, `REF_ID_KEMKES`, `JUMLAH_PASIEN`, `TANGGAL_UPDATED`, `KIRIM`) VALUES
  (2026, 9, 1, 'DUMMY-GOLDAR-2026-09', 48, '2026-09-07 23:00:00', 1),
  (2026, 9, 2, 'DUMMY-GOLDAR-2026-09', 56, '2026-09-07 23:00:00', 1),
  (2026, 9, 3, 'DUMMY-GOLDAR-2026-09', 12, '2026-09-07 23:00:00', 1),
  (2026, 9, 4, 'DUMMY-GOLDAR-2026-09', 8, '2026-09-07 23:00:00', 1);

-- Statistik indikator BOR, ALOS, BTO, TOI, NDR, dan GDR
INSERT IGNORE INTO `statistik_indikator`
  (`TAHUN`, `PERIODE`, `JENIS`, `REF_ID_KEMKES`, `BOR`, `ALOS`, `BTO`, `TOI`, `NDR`, `GDR`, `TANGGAL_UPDATED`, `KIRIM`) VALUES
  (2026, '9', 1, 'DUMMY-INDIKATOR-BULANAN-2026-09', 68.40, 4.25, 6.80, 1.95, 12.50, 28.30, '2026-09-07 23:00:00', 1),
  (2026, 'tw3', 2, 'DUMMY-INDIKATOR-TRIWULAN-2026-T3', 65.75, 4.10, 19.40, 2.10, 11.80, 26.40, '2026-09-07 23:00:00', 1);

-- Statistik jumlah kematian
INSERT IGNORE INTO `statistik_jumlah_kematian`
  (`TAHUN`, `BULAN`, `REF_ID_KEMKES`, `KONTEN`, `TANGGAL_UPDATED`, `KIRIM`) VALUES
  (2026, 9, 'DUMMY-KEMATIAN-2026-09', '{"periode":"2026-09","jumlah_kematian":7,"neonatal":1,"maternal":0}', '2026-09-07 23:00:00', 1);

-- Statistik kunjungan harian
INSERT IGNORE INTO `statistik_kunjungan`
  (`TANGGAL`, `REF_ID_KEMKES`, `RJ`, `RD`, `RI`, `TANGGAL_UPDATED`, `KIRIM`) VALUES
  ('2026-09-03', 'DUMMY-KUNJUNGAN-2026-09-03', 133, 28, 24, '2026-09-03 23:00:00', 1),
  ('2026-09-04', 'DUMMY-KUNJUNGAN-2026-09-04', 154, 31, 27, '2026-09-04 23:00:00', 1),
  ('2026-09-05', 'DUMMY-KUNJUNGAN-2026-09-05', 165, 34, 30, '2026-09-05 23:00:00', 1);

-- Statistik mutu pelayanan
INSERT IGNORE INTO `statistik_mutu_pelayanan`
  (`TAHUN`, `BULAN`, `KODE`, `REF_ID_KEMKES`, `NILAI`, `MANUAL`, `USER`, `TANGGAL_UPDATED`, `KIRIM`) VALUES
  (2026, 9, 'MUT001', 'DUMMY-MUTU-2026-09', 92.50, 1, 1001, '2026-09-07 23:00:00', 1),
  (2026, 9, 'MUT002', 'DUMMY-MUTU-2026-09', 88.75, 1, 1001, '2026-09-07 23:00:00', 1),
  (2026, 9, 'MUT003', 'DUMMY-MUTU-2026-09', 95.00, 0, 0, '2026-09-07 23:00:00', 1);

-- Statistik pemeriksaan laboratorium
INSERT IGNORE INTO `statistik_pemeriksaan_laboratorium`
  (`TAHUN`, `BULAN`, `KODE`, `REF_ID_KEMKES`, `RATA_RATA`, `JUMLAH_PASIEN`, `TANGGAL_UPDATED`, `KIRIM`) VALUES
  (2026, 9, 1, 'DUMMY-LAB-2026-09', 76.50, 91, '2026-09-07 23:00:00', 1),
  (2026, 9, 2, 'DUMMY-LAB-2026-09', 82.25, 64, '2026-09-07 23:00:00', 1),
  (2026, 9, 3, 'DUMMY-LAB-2026-09', 69.80, 37, '2026-09-07 23:00:00', 1);

-- Statistik rujukan
INSERT IGNORE INTO `statistik_rujukan`
  (`TANGGAL`, `REF_ID_KEMKES`, `MASUK`, `KELUAR`, `BALIK`, `TANGGAL_UPDATED`, `KIRIM`) VALUES
  ('2026-09-03', 'DUMMY-RUJUKAN-2026-09-03', 8, 11, 3, '2026-09-03 23:00:00', 1),
  ('2026-09-04', 'DUMMY-RUJUKAN-2026-09-04', 10, 13, 4, '2026-09-04 23:00:00', 1),
  ('2026-09-05', 'DUMMY-RUJUKAN-2026-09-05', 9, 12, 5, '2026-09-05 23:00:00', 1);

-- Tempat tidur berdasarkan kamar dan kelas
INSERT IGNORE INTO `tempat_tidur_kemkes`
  (`IDINSTALASI`, `INSTALASI`, `IDUNIT`, `UNIT`, `IDSUBUNIT`, `SUBUNIT`, `IDKAMAR`, `KAMAR`, `IDKELAS`, `KELAS`, `JENISKAMAR`, `TTLAKI`, `TTPEREMPUAN`, `JMLLAKI`, `JMLPEREMPUAN`, `TERPAKAI_KONFIRMASI`, `LASTUPDATED`) VALUES
  ('10203', 'Rawat Inap', '1020301', 'Perawatan Biasa', '102030101', 'Ruang Perawatan', '3', 'Kamar 003', '1', 'Kelas I', 1, 2, 0, 1, 0, 0, '2026-09-07 18:00:00'),
  ('10203', 'Rawat Inap', '1020301', 'Perawatan Biasa', '102030101', 'Ruang Perawatan', '4', 'Kamar 004', '2', 'Kelas II', 2, 1, 1, 1, 0, 0, '2026-09-07 18:00:00'),
  ('10203', 'Rawat Inap', '1020301', 'Perawatan Biasa', '102030101', 'Ruang Perawatan', '5', 'Kamar 005', '3', 'Kelas III', 0, 0, 4, 2, 1, 1, '2026-09-07 18:00:00');

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

-- Ringkasan: seluruh 27 tabel pada dump informasi.sql memiliki sekurangnya satu baris dummy.
