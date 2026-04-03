# Android App (PWA Wrapper) - Buku Tamu KCD

Dokumen ini menjelaskan cara build aplikasi Android dari PWA menggunakan Capacitor.

## Hasil Saat Ini

- Project Android native sudah dibuat di folder `android/`.
- APK debug berhasil dibuild di:
  - `android/app/build/outputs/apk/debug/app-debug.apk`

## Arsitektur

- Web app utama tetap berjalan dari server PWA.
- Android app ini bertindak sebagai wrapper native yang membuka aplikasi web pada URL produksi.
- Konfigurasi utama ada di:
  - `capacitor.config.json`

## Konfigurasi URL Aplikasi

Nilai saat ini:

- `server.url`: `https://etamu-kcd.smkn1ciamis.id`

Jika domain berubah, edit file `capacitor.config.json` lalu jalankan:

```powershell
npm run android:sync
```

## Testing Lokal (Saat Belum Upload Online)

Jika website belum di-upload, Anda tetap bisa test Android app ke server lokal:

1. Jalankan Laravel agar bisa diakses dari jaringan lokal:

```powershell
php artisan serve --host=0.0.0.0 --port=8000
```

2. Cek IP laptop/PC Anda (contoh `192.168.1.10`) dengan:

```powershell
ipconfig
```

3. Ubah sementara `server.url` di `capacitor.config.json` jadi:

```json
"server": {
  "url": "http://192.168.1.10:8000",
  "cleartext": true,
  "androidScheme": "http"
}
```

4. Jalankan sync ulang lalu build APK debug:

```powershell
npm run android:sync
npm run android:apk:debug
```

5. Pastikan HP Android dan laptop/PC berada di jaringan Wi-Fi yang sama.

Setelah selesai test lokal, kembalikan lagi ke URL produksi (`https://etamu-kcd.smkn1ciamis.id`), set `cleartext` ke `false`, `androidScheme` ke `https`, lalu rebuild APK.

## Perintah Penting

Dari root project:

```powershell
npm run android:sync
npm run android:open
npm run android:apk:debug
```

Keterangan:

- `android:sync`: sinkronisasi konfigurasi web ke project Android.
- `android:open`: buka project di Android Studio.
- `android:apk:debug`: build APK debug (untuk testing/install langsung).

## Install APK Debug ke HP

1. Ambil file `android/app/build/outputs/apk/debug/app-debug.apk`.
2. Transfer ke perangkat Android.
3. Aktifkan izin install dari sumber lain (jika diminta).
4. Install APK.

## Build Release (Siap Distribusi)

### 1. Buat keystore

```powershell
keytool -genkeypair -v -keystore buku-tamu-kcd-release.jks -alias buku-tamu-kcd -keyalg RSA -keysize 2048 -validity 10000
```

Simpan keystore di lokasi aman (jangan hilang).

### 2. Konfigurasi signing di Android Studio

- Buka `android/` di Android Studio.
- `Build` -> `Generate Signed Bundle / APK`.
- Pilih:
  - `Android App Bundle (.aab)` untuk Play Store, atau
  - `APK` untuk distribusi langsung.
- Pilih keystore, alias, dan password.

### 3. Output release

Umumnya di:

- AAB: `android/app/build/outputs/bundle/release/`
- APK: `android/app/build/outputs/apk/release/`

## Catatan Produksi

- Pastikan domain `server.url` selalu HTTPS.
- Untuk update konten aplikasi, cukup deploy web server; app Android otomatis membaca perubahan dari server.
- Jika mengganti domain/protokol, jalankan `npm run android:sync` lalu rebuild aplikasi Android.

## Troubleshooting

### Error SDK location not found

Pastikan `android/local.properties` berisi lokasi SDK Android, contoh:

```text
sdk.dir=C:\\Users\\<username>\\AppData\\Local\\Android\\Sdk
```

### Error saat sync dari folder `public`

Project ini menggunakan symlink `public/storage`. Untuk menghindari error copy symlink di Windows, `webDir` diarahkan ke folder `android-web` yang aman untuk proses sync.
