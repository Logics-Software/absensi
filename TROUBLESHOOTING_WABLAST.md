# Troubleshooting WA Blast - Pesan Tidak Terkirim

## Langkah-langkah Debugging

### 1. Cek Error Message di Database
Buka detail campaign dan lihat kolom "Error Message" di tabel pesan. Error message akan menunjukkan penyebab kegagalan.

### 2. Cek Error Log
Lokasi error log:
- `logs/error.log` (jika ada)
- PHP error log di server
- Browser console (F12)

Cari log dengan keyword:
- `Fonnte sendMessage`
- `WA Blast Send Error`
- `Fonnte API Error`

### 3. Cek Format Nomor HP
Format yang benar untuk Fonnte API:
- ✅ `6281234567890` (tanpa +, tanpa 0 di depan)
- ❌ `+6281234567890` (dengan +)
- ❌ `081234567890` (dengan 0 di depan)
- ❌ `81234567890` (tanpa kode negara)

### 4. Cek API Configuration
Pastikan di "Konfigurasi Fonnte":
- ✅ API Key sudah diisi dan benar
- ✅ API URL: `https://api.fonnte.com`
- ✅ Device ID sudah diisi (jika diperlukan)
- ✅ Webhook URL sudah dikonfigurasi

### 5. Cek Status Device di Fonnte Dashboard
- Pastikan device terhubung dan aktif
- Pastikan device tidak dalam status "Disconnected" atau "Banned"

### 6. Cek Format Request ke Fonnte API
Request yang dikirim:
```json
{
  "target": "6281234567890",
  "message": "Pesan yang akan dikirim",
  "delay": 1,
  "device": "device_id" // optional
}
```

Headers:
```
Content-Type: application/json
Authorization: YOUR_API_KEY
```

### 7. Kemungkinan Error dan Solusi

#### Error: "API Key tidak valid"
- **Penyebab**: API Key salah atau tidak memiliki akses
- **Solusi**: 
  - Cek API Key di dashboard Fonnte
  - Pastikan API Key sudah di-copy dengan benar (tanpa spasi)
  - Pastikan API Key masih aktif

#### Error: "Format data tidak valid"
- **Penyebab**: Format nomor HP atau pesan tidak sesuai
- **Solusi**:
  - Pastikan nomor HP dalam format `6281234567890`
  - Pastikan pesan tidak kosong
  - Pastikan nomor HP terdaftar di WhatsApp

#### Error: "Device tidak terhubung"
- **Penyebab**: Device tidak aktif atau terputus
- **Solusi**:
  - Cek status device di dashboard Fonnte
  - Pastikan device terhubung dan aktif
  - Restart device jika perlu

#### Error: "Empty response from Fonnte API"
- **Penyebab**: Server Fonnte tidak merespons
- **Solusi**:
  - Cek koneksi internet
  - Cek status server Fonnte
  - Coba lagi beberapa saat kemudian

#### Error: "Invalid JSON response"
- **Penyebab**: Response dari Fonnte bukan JSON
- **Solusi**:
  - Cek error log untuk melihat response lengkap
  - Kemungkinan server Fonnte sedang bermasalah

### 8. Test Manual
Gunakan "Test Koneksi" di halaman "Konfigurasi Fonnte" untuk memastikan:
- API Key valid
- Device terhubung
- Koneksi ke Fonnte API berhasil

### 9. Cek Error Message di View
1. Buka detail campaign: `/wablast/view/{id}`
2. Lihat kolom "Error Message" di tabel
3. Klik tombol "Debug" untuk melihat detail lengkap
4. Salin error message dan cek sesuai daftar di atas

### 10. Kontak Support Fonnte
Jika semua langkah di atas sudah dilakukan tapi masih error:
- Hubungi support Fonnte melalui dashboard
- Sertakan:
  - Error message lengkap
  - Format nomor HP yang digunakan
  - Screenshot error log
  - Detail request yang dikirim

## Format Request yang Benar

```json
POST https://api.fonnte.com/send
Headers:
  Content-Type: application/json
  Authorization: YOUR_API_KEY

Body:
{
  "target": "6281234567890",
  "message": "Pesan yang akan dikirim",
  "delay": 1,
  "device": "device_id" // optional
}
```

## Format Response yang Benar

Success:
```json
{
  "status": true,
  "id": "message_id",
  "message": "Pesan berhasil dikirim"
}
```

Error:
```json
{
  "status": false,
  "message": "Error message",
  "error": "Error details"
}
```

