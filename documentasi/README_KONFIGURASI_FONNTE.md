# Konfigurasi Fonnte WhatsApp API

Modul ini memungkinkan Anda untuk menyimpan dan mengubah konfigurasi Fonnte WhatsApp API melalui antarmuka web, tanpa perlu mengedit file konfigurasi secara manual.

## Keuntungan

1. **Mudah diubah**: Admin dapat mengubah konfigurasi melalui UI tanpa perlu akses ke server
2. **Aman**: Tidak perlu mengedit file konfigurasi yang mungkin ter-expose
3. **Konsisten**: Mengikuti pola modul Konfigurasi yang sudah ada
4. **Fallback**: Jika database kosong, akan menggunakan konfigurasi dari `config/app.php`

## Setup

### 1. Import Database Schema

Jalankan SQL berikut untuk membuat table:

```sql
-- File: database/konfigurasi_fonnte_schema.sql
```

Atau import file SQL tersebut ke database Anda.

### 2. Akses Menu

1. Login sebagai **Admin**
2. Buka menu **Setting > Konfigurasi Fonnte**
3. Isi form dengan data dari Fonnte:
   - **API Key**: Dapatkan dari dashboard Fonnte
   - **API URL**: Default `https://api.fonnte.com`
   - **Device ID**: (Opsional) Jika menggunakan multiple device
   - **Webhook URL**: URL untuk menerima update status pesan

### 3. Test Koneksi

Setelah mengisi konfigurasi, klik tombol **"Test Koneksi"** untuk memastikan koneksi ke Fonnte API berhasil.

## Cara Kerja

1. **Prioritas Database**: `FonnteService` akan membaca konfigurasi dari database terlebih dahulu
2. **Fallback ke Config**: Jika database kosong atau error, akan menggunakan `config/app.php`
3. **Update Real-time**: Perubahan konfigurasi langsung berlaku tanpa perlu restart server

## Struktur Database

Table: `konfigurasi_fonnte`

- `id` (int, auto increment)
- `api_key` (varchar 255)
- `api_url` (varchar 255, default: https://api.fonnte.com)
- `device_id` (varchar 50)
- `webhook_url` (varchar 255)
- `created_at` (timestamp)
- `updated_at` (timestamp)

**Catatan**: Hanya ada 1 record di table ini (seperti table `konfigurasi`).

## Migration dari Config File

Jika sebelumnya Anda sudah mengisi konfigurasi di `config/app.php`, Anda bisa:

1. Buka menu **Setting > Konfigurasi Fonnte**
2. Salin nilai dari `config/app.php` ke form
3. Klik **Simpan**

Atau jalankan SQL berikut untuk migrasi otomatis:

```sql
INSERT INTO konfigurasi_fonnte (api_key, api_url, device_id, webhook_url)
SELECT 
    'your_api_key_here' as api_key,
    'https://api.fonnte.com' as api_url,
    'your_device_id_here' as device_id,
    'https://yourdomain.com/wablast/webhook' as webhook_url
WHERE NOT EXISTS (SELECT 1 FROM konfigurasi_fonnte);
```

## Troubleshooting

### Konfigurasi tidak tersimpan

- Pastikan table `konfigurasi_fonnte` sudah dibuat
- Pastikan user memiliki role `admin`
- Periksa error log di `logs/error.log`

### Service masih menggunakan config file

- Pastikan ada data di table `konfigurasi_fonnte`
- Pastikan `api_key` tidak kosong
- Periksa apakah ada error saat membaca dari database (cek error log)

### Test koneksi gagal

- Pastikan API Key valid dan aktif
- Pastikan device di Fonnte sudah terhubung
- Periksa koneksi internet
- Periksa quota/billing di akun Fonnte

## Keamanan

- Hanya user dengan role `admin` yang dapat mengakses menu ini
- API Key disimpan di database (pastikan database aman)
- Disarankan untuk menggunakan environment variables untuk production

