# Dokumentasi Import Data Fingerprint Excel

## Overview

Modul ini digunakan untuk membaca dan memproses data dari file Excel `Finger.xlsx` yang berisi data fingerprint karyawan dan log absensi.

## Struktur File Excel

File `Finger.xlsx` memiliki struktur sebagai berikut:

### Bagian 1: Informasi Karyawan (Baris 1-10)
- **Row 1**: Label "Fingerprint ID"
- **Row 2**: Label "Kode Karyawan"
- **Row 3-4**: Data kode karyawan (bisa duplikat)
- **Row 5**: Label "Jabatan Karyawan"
- **Row 6**: Label "Tanggal Gabung"
- **Row 7**: Data tanggal gabung (format Excel serial number)
- **Row 8**: Label + Data "Nama Karyawan [NAMA]"
- **Row 9**: Label "Nama Departemen"
- **Row 10**: Data departemen

### Bagian 2: Log Absensi (Baris 11+)
- **Row 11**: Header kolom
  - A: Tanggal Log
  - B: Nomor Terminal
  - D: Terminal Location
  - F: Jam Log
  - G: Fungsi Tombol (0=Check In, 1=Check Out)
  - H: Keterangan
  - K: Cara Attendan
  - L: Tanggal Edit
  - M: Nama User
- **Row 12+**: Data log absensi

## Komponen

### 1. FingerExcelService (`services/FingerExcelService.php`)

Service utama untuk membaca file Excel.

#### Methods:

- `load()` - Load file Excel
- `getEmployeeInfo()` - Baca informasi karyawan
- `getAttendanceLogs($startRow = 11)` - Baca log absensi
- `getAllData()` - Baca semua data (employee + logs)

#### Contoh Penggunaan:

```php
require_once __DIR__ . '/services/FingerExcelService.php';

$service = new FingerExcelService();
$service->load();

// Get employee info
$employee = $service->getEmployeeInfo();
echo "Nama: " . $employee['nama'];
echo "Kode: " . $employee['kode_karyawan'];

// Get attendance logs
$logs = $service->getAttendanceLogs();
foreach ($logs as $log) {
    echo "Tanggal: " . $log['tanggal_log'];
    echo "Jam: " . $log['jam_log'];
    echo "Tipe: " . $log['tipe']; // Check In atau Check Out
}
```

### 2. FingerImportController (`controllers/FingerImportController.php`)

Controller untuk menampilkan dan mengelola data import.

#### Routes:

- `GET /fingerimport` - Halaman index (preview data)
- `GET /fingerimport/preview` - API untuk preview data (JSON)
- `GET /fingerimport/exportjson` - Export data ke JSON

### 3. View (`views/fingerimport/index.php`)

Halaman untuk menampilkan:
- Informasi karyawan
- Summary log absensi
- Sample data log (10 pertama)

## Cara Menggunakan

### 1. Akses Halaman Import

Buka browser dan akses:
```
http://your-domain/fingerimport
```

### 2. Upload File Excel

1. Klik tombol "Choose File" atau "Pilih File"
2. Pilih file Excel (.xlsx atau .xls) yang ingin diproses
3. Klik tombol "Upload & Proses"
4. File akan diupload ke folder `uploads/finger_excel/`

**Catatan:**
- Format yang didukung: `.xlsx`, `.xls`
- Ukuran maksimal: 10MB
- File akan disimpan dengan nama unik untuk menghindari konflik

### 3. Preview Data

Setelah file berhasil diupload, halaman akan menampilkan:
- Informasi karyawan yang ditemukan
- Total log absensi
- Sample 10 log pertama

### 4. Export ke JSON (Opsional)

Klik tombol "Export JSON" untuk mendapatkan data dalam format JSON.

### 5. Hapus File (Opsional)

Klik tombol "Hapus File" untuk menghapus file yang sudah diupload dari server.

## Format Data

### Employee Info

```php
[
    'fingerprint_id' => '431241',
    'kode_karyawan' => '431241',
    'jabatan' => null,
    'tanggal_gabung' => '2018-09-09',
    'nama' => 'DIDI',
    'departemen' => 'DPP2'
]
```

### Attendance Log

```php
[
    'tanggal_log' => '2018-08-01',
    'nomor_terminal' => '1',
    'terminal_location' => null,
    'jam_log' => '18:51:00',
    'fungsi_tombol' => 0, // 0=Check In, 1=Check Out
    'tipe' => 'Check In',
    'keterangan' => 'Check In',
    'cara_attendan' => '1',
    'tanggal_edit' => null,
    'nama_user' => null
]
```

## Konversi Excel Date/Time

Service ini secara otomatis mengkonversi:
- **Excel Date Serial Number** → PHP Date (Y-m-d)
  - Contoh: `43352` → `2018-09-09`
- **Excel Time Serial Number** → PHP Time (H:i:s)
  - Contoh: `0.78541666666667` → `18:51:00`

## Testing

### Test Service Langsung

```bash
php test_finger_service.php
```

### Test Excel Reading

```bash
php test_phpspreadsheet.php
```

### Analisis Struktur Excel

```bash
php analyze_finger_excel.php
```

## Catatan Penting

1. **File Upload**: File Excel diupload melalui form di halaman import
2. **Storage Location**: File yang diupload disimpan di `uploads/finger_excel/` dengan nama unik
3. **Session Storage**: Path file disimpan di session, sehingga tetap tersedia selama session aktif
4. **Excel Format**: File harus dalam format `.xlsx` atau `.xls`
5. **File Size**: Maksimal 10MB per file
6. **Date Format**: Tanggal dalam Excel menggunakan serial number, akan dikonversi otomatis
7. **Time Format**: Waktu bisa berupa string (07:05) atau decimal (0.7854), akan diproses otomatis
8. **Empty Cells**: Cell kosong akan diabaikan
9. **File Validation**: File akan divalidasi setelah upload untuk memastikan dapat dibaca oleh PhpSpreadsheet

## Troubleshooting

### File tidak terupload
- Cek permission folder `uploads/finger_excel/` (harus writable)
- Pastikan ukuran file tidak melebihi 10MB
- Cek format file (harus .xlsx atau .xls)
- Pastikan `upload_max_filesize` dan `post_max_size` di php.ini cukup besar

### Error membaca Excel
- Pastikan PhpSpreadsheet sudah terinstall
- Cek format file (harus .xlsx atau .xls)
- Pastikan extension PHP `zip` dan `xml` aktif
- File mungkin rusak atau terenkripsi

### Data tidak lengkap
- Cek struktur file Excel sesuai dokumentasi
- Pastikan header di baris 11 sesuai format
- Cek apakah ada cell yang kosong atau format berbeda

### File hilang setelah refresh
- File disimpan di session, jika session expired file akan hilang
- Upload ulang file jika diperlukan
- File fisik masih ada di `uploads/finger_excel/` meskipun session expired

## Next Steps

Untuk mengintegrasikan data ini ke database, Anda bisa:

1. Buat tabel untuk menyimpan log absensi
2. Modifikasi `FingerImportController` untuk menambahkan method `import()`
3. Map data dari Excel ke struktur database
4. Handle duplikasi data (jika sudah ada)

Contoh implementasi import ke database bisa ditambahkan sesuai kebutuhan.

