# Modul WA Blast dengan Fonnte

Modul WA Blast memungkinkan pengiriman pesan WhatsApp secara massal ke siswa, wali siswa, dan guru menggunakan layanan Fonnte.

## Instalasi

### 1. Database Setup

Jalankan file SQL untuk membuat tabel-tabel yang diperlukan:

```bash
mysql -u username -p database_name < database/wablast_schema.sql
```

Atau import file `database/wablast_schema.sql` melalui phpMyAdmin atau tool database lainnya.

### 2. Konfigurasi Fonnte API

#### Mendapatkan API Key dari Fonnte

1. Daftar/Login ke akun Fonnte di https://fonnte.com
2. Buat device baru atau gunakan device yang sudah ada
3. Salin API Key dan Device ID dari dashboard Fonnte

#### Konfigurasi di Aplikasi

Ada 2 cara untuk mengkonfigurasi:

**Cara 1: Environment Variables (Recommended)**

Buat file `.env` di root aplikasi atau set environment variables:

```env
FONNTE_API_KEY=your_api_key_here
FONNTE_API_URL=https://api.fonnte.com
FONNTE_DEVICE_ID=your_device_id_here
FONNTE_WEBHOOK_URL=https://yourdomain.com/wablast/webhook
```

**Cara 2: Langsung di config/app.php**

Edit file `config/app.php` dan isi langsung:

```php
'fonnte' => [
    'api_key' => 'your_api_key_here',
    'api_url' => 'https://api.fonnte.com',
    'device_id' => 'your_device_id_here',
    'webhook_url' => 'https://yourdomain.com/wablast/webhook',
],
```

**Catatan tentang Webhook URL:**

`webhook_url` adalah URL endpoint yang akan dipanggil oleh Fonnte untuk mengirim update status pesan (delivered, read, failed, dll).

- **Format URL**: `https://yourdomain.com/wablast/webhook`
- **Ganti `yourdomain.com`** dengan domain aplikasi Anda
- **Untuk development lokal**, Anda bisa menggunakan ngrok atau service sejenis:
  ```bash
  ngrok http 8000
  # Gunakan URL yang diberikan, contoh: https://abc123.ngrok.io/wablast/webhook
  ```

**Setup Webhook di Fonnte:**

1. Login ke dashboard Fonnte
2. Buka menu "Webhook" atau "Settings"
3. Set webhook URL ke: `https://yourdomain.com/wablast/webhook`
4. Pilih event yang ingin diterima (delivered, read, failed, dll)
5. Save settings

### 3. Testing Koneksi

Setelah konfigurasi, buat campaign test untuk memastikan koneksi ke Fonnte berjalan dengan baik.

## Fitur

### 1. Template Pesan

- Template default sudah tersedia untuk:

  - Notifikasi Absensi Siswa
  - Reminder Kalender Akademik
  - Pesan Umum

- Template dapat menggunakan variabel dinamis:
  - `{{nama}}` - Nama penerima
  - `{{nama_siswa}}` - Nama siswa
  - `{{tanggal}}` - Tanggal
  - `{{status}}` - Status
  - `{{jam_masuk}}` - Jam masuk
  - `{{jam_keluar}}` - Jam keluar
  - dll

### 2. Tipe Penerima

- **Semua Siswa Aktif**: Mengirim ke semua siswa yang memiliki nomor HP
- **Semua Wali Siswa Aktif**: Mengirim ke semua wali siswa yang memiliki nomor HP
- **Semua Guru Aktif**: Mengirim ke semua guru yang memiliki nomor HP
- **Pilih Manual (Custom)**: Pilih penerima secara manual

### 3. Campaign Management

- Buat campaign dalam status "Draft"
- Preview pesan sebelum dikirim
- Kirim campaign dengan satu klik
- Tracking status pengiriman (pending, sent, delivered, read, failed)
- History semua campaign

### 4. Delivery Status

- **Pending**: Pesan belum dikirim
- **Sent**: Pesan berhasil dikirim ke Fonnte
- **Delivered**: Pesan berhasil terkirim ke penerima
- **Read**: Pesan sudah dibaca oleh penerima
- **Failed**: Pesan gagal dikirim

## Penggunaan

### Membuat Campaign Baru

1. Buka menu **Setting > WA Blast**
2. Klik **Buat Campaign Baru**
3. Isi nama campaign
4. Pilih template (opsional) atau tulis pesan manual
5. Pilih tipe penerima
6. Klik **Buat Campaign**

### Mengirim Campaign

1. Buka detail campaign
2. Klik tombol **Kirim Campaign**
3. Konfirmasi pengiriman
4. Monitor status pengiriman di halaman detail

### Melihat History

1. Buka menu **Setting > WA Blast**
2. Lihat daftar semua campaign
3. Klik **Detail** untuk melihat detail campaign dan status pengiriman

## Integrasi dengan Modul Lain

### Notifikasi Absensi

Modul ini dapat diintegrasikan dengan modul Absensi untuk mengirim notifikasi otomatis saat:

- Siswa absen
- Siswa terlambat
- dll

### Reminder Kalender Akademik

Modul ini dapat diintegrasikan dengan modul Kalender Akademik untuk mengirim reminder:

- Hari libur
- Jadwal ujian
- Event sekolah
- dll

## Troubleshooting

### Error: "Fonnte API Key tidak dikonfigurasi"

- Pastikan API Key sudah dikonfigurasi di `config/app.php` atau environment variables
- Pastikan format konfigurasi benar

### Error: "CURL Error" atau "Fonnte API Error"

- Periksa koneksi internet
- Pastikan API Key valid dan aktif
- Periksa apakah device di Fonnte sudah terhubung
- Periksa quota/billing di akun Fonnte

### Pesan tidak terkirim

- Periksa format nomor HP (harus dalam format internasional: 62xxxxxxxxxx)
- Pastikan nomor HP valid dan terdaftar di WhatsApp
- Periksa status device di dashboard Fonnte
- Periksa log error di halaman detail campaign

## Catatan Penting

1. **Rate Limiting**: Fonnte memiliki rate limiting. Pengiriman massal akan otomatis menambahkan delay antar pesan (0.5 detik).

2. **Format Nomor HP**:

   - Nomor HP akan otomatis diformat ke format internasional (62xxxxxxxxxx)
   - Nomor yang dimulai dengan 0 akan diubah menjadi 62
   - Nomor yang sudah dimulai dengan 62 akan tetap digunakan

3. **Template Variables**:

   - Variabel dalam template akan diganti saat pengiriman
   - Pastikan variabel yang digunakan sesuai dengan data yang tersedia

4. **Privacy**:
   - Pastikan nomor HP siswa, wali, dan guru sudah mendapat persetujuan untuk digunakan
   - Ikuti regulasi privasi data yang berlaku

## Support

Untuk bantuan lebih lanjut:

- Dokumentasi Fonnte: https://docs.fonnte.com
- Support Fonnte: https://fonnte.com/support
