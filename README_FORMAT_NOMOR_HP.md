# Format Nomor HP yang Benar

## Format di Database

**Format yang disimpan di database:** `+6281234567890`

- Dengan tanda `+` di depan
- Dengan kode negara `62` (Indonesia)
- Tanpa angka `0` di depan
- Contoh: `+6281234567890`, `+6289876543210`

## Format untuk Fonnte API

**Format yang dikirim ke Fonnte API:** `6281234567890`

- Tanpa tanda `+`
- Dengan kode negara `62`
- Tanpa angka `0` di depan
- Contoh: `6281234567890`, `6289876543210`

## Konversi Otomatis

`FonnteService::sendMessage()` akan **otomatis mengkonversi** format dari database ke format Fonnte API:

```php
// Input dari database: +6281234567890
// Proses:
// 1. Hapus semua karakter non-numerik: 6281234567890
// 2. Hapus 62 di depan (jika ada): 81234567890
// 3. Hapus 0 di depan (jika ada): 81234567890
// 4. Tambahkan 62 di depan: 6281234567890
// Output ke Fonnte API: 6281234567890
```

## Format Input di Form

Form sudah otomatis memformat nomor HP dengan benar:

- User memasukkan: `081234567890` atau `81234567890`
- JavaScript otomatis mengkonversi menjadi: `+6281234567890`
- Disimpan ke database sebagai: `+6281234567890`

## Contoh Format yang Benar

### ✅ Format Benar

- `+6281234567890` (di database)
- `6281234567890` (untuk Fonnte API - otomatis dikonversi)

### ❌ Format Salah

- `081234567890` (dengan 0 di depan, tanpa +62)
- `81234567890` (tanpa kode negara)
- `+62081234567890` (ada 0 setelah +62)

## Catatan Penting

1. **Jangan ubah format di database** - biarkan dengan format `+62...`
2. **FonnteService sudah menangani konversi** - tidak perlu konversi manual
3. **Pastikan nomor HP valid** - nomor harus terdaftar di WhatsApp
4. **Format konsisten** - semua nomor HP harus menggunakan format yang sama

## Troubleshooting

Jika WA Blast gagal, periksa:

1. Format nomor HP di database sudah benar (`+62...`)
2. Nomor HP valid dan terdaftar di WhatsApp
3. Device Fonnte sudah terhubung dan aktif
4. Kuota pengiriman masih tersedia
5. Cek error log untuk detail error
