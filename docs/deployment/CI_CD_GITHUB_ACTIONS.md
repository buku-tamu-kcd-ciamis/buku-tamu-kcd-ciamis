# CI/CD Production Setup (GitHub Actions)

Dokumen ini untuk deploy otomatis aplikasi ke:
- Domain: etamu-kcd.smkn1ciamis.id
- Server user: pplgkcdssh

Workflow yang dipakai:
- .github/workflows/ci-cd-production.yml

## 1) Siapkan GitHub Secrets

Masuk ke:
- Repository Settings
- Secrets and variables
- Actions
- New repository secret

Buat secret berikut:

1. PROD_SSH_USER
- Value contoh: deployuser

2. PROD_SSH_PRIVATE_KEY
- Value: isi private key SSH (format OpenSSH, multi-line), contoh dimulai dari:
	-----BEGIN OPENSSH PRIVATE KEY-----
	...
	-----END OPENSSH PRIVATE KEY-----

3. PROD_SSH_PORT
- Value: 22

4. PROD_DEPLOY_PATH
- Value contoh: /home/deployuser/etamu-kcd.smkn1ciamis.id
- Catatan: sesuaikan dengan path project di server Anda

5. PROD_DB_HOST
- Value contoh umum: 127.0.0.1

6. PROD_DB_PORT
- Value: 3306

7. PROD_DB_DATABASE
- Value contoh: app_production_db

8. PROD_DB_USERNAME
- Value contoh: app_production_user

9. PROD_DB_PASSWORD
- Value: password database production Anda

## 2) Cara kerja workflow

Saat push ke branch main atau trigger manual:
1. Job CI berjalan:
- composer validate
- composer install
- npm ci
- npm run build

2. Job Deploy berjalan:
- sync file ke server via rsync over SSH
- inisialisasi .env jika belum ada
- update env DB production dari secrets
- composer install --no-dev
- npm ci + npm run build (jika npm ada di server)
- php artisan migrate --force
- cache config dan view
- restart queue

## 3) Trigger deploy

Deploy otomatis:
- Push ke branch main

Deploy manual:
- GitHub Actions
- Pilih workflow CI/CD Production
- Run workflow

## 4) Prasyarat server

Pastikan server memiliki:
- PHP minimal 8.3 (sesuai composer.json)
- Composer 2
- Node.js + npm (jika asset build dilakukan di server)
- Akses tulis ke folder project

## 5) Checklist pertama kali

1. Test SSH login manual ke server
2. Pastikan public key sudah ditambahkan ke file ~/.ssh/authorized_keys pada user server
3. Pastikan path PROD_DEPLOY_PATH benar
4. Pastikan project owner/group tepat
5. Jalankan workflow manual sekali
6. Cek aplikasi setelah deploy

## 6) Keamanan penting

- Jangan commit kredensial ke repo
- Simpan seluruh credential di GitHub Secrets
- Disarankan migrasi dari password SSH ke SSH key setelah sistem stabil
