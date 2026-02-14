# ISPConfig Deployment Guide

Panduan khusus untuk deployment di server ISPConfig (seperti SiDAT WebHost).

---

## Karakteristik ISPConfig

ISPConfig biasanya punya struktur:

```
/var/www/clients/client1/web1/
├── web/           ← Document root (folder public Laravel)
├── private/       ← Tempat folder Laravel lainnya
├── log/          ← Web server logs
└── tmp/          ← Temporary files
```

---

## Setup di ISPConfig Panel

### 1. Buat Website/Domain

1. Login ke ISPConfig panel (biasanya https://server-ip:8080)
2. **Sites** → **Website** → **Add new website**
    - Domain: `cadisdik13.disdik.jabarprov.go.id`
    - Client: Pilih client sekolah
    - PHP: PHP-FPM 8.2+
    - Auto-subdomain: `www`

### 2. Setup SSH Access

1. **Sites** → **Website** → Pilih website → tab **Options**
2. Enable **SSH Access**: Yes
3. **SSH Chroot**: No shell (atau jailkit jika perlu full access)
4. Username SSH: Akan otomatis dibuat (contoh: `web1`)

### 3. Setup Database

1. **Sites** → **Database** → **Add new database**
    - Database name: `c1cadisdik` (akan ada prefix client)
    - Database user: `c1user`
    - Password: [generate strong password]
    - Grant access to: Select web1

---

## Struktur Project di ISPConfig

### Recommended Setup:

```bash
/var/www/clients/client1/web1/
├── private/
│   └── laravel/           ← Clone repository disini
│       ├── app/
│       ├── bootstrap/
│       ├── config/
│       ├── database/
│       ├── public/        ← Point symbolic link kesini
│       ├── resources/
│       ├── routes/
│       ├── storage/
│       └── vendor/
└── web/                   ← Document root
    └── (symbolic link ke private/laravel/public/*)
```

---

## Setup Step-by-Step di Server

### 1. SSH ke Server

```bash
# Login dengan user SSH yang dibuat ISPConfig
ssh web1@server-ip

# Atau pakai password jika key belum di-setup
ssh web1@server-ip -p 22
```

### 2. Clone Project

```bash
# Masuk ke folder private
cd ~/private

# Clone repository (pakai HTTPS tanpa install SSH key di GitHub)
git clone https://github.com/buku-tamu-kcd-ciamis/buku-tamu-kcd-ciamis.git laravel
cd laravel

# Atau pakai SSH jika sudah setup deploy key
# git clone git@github.com:buku-tamu-kcd-ciamis/buku-tamu-kcd-ciamis.git laravel
```

### 3. Setup Symbolic Links

```bash
# Backup folder web default
cd ~/web
rm -rf *  # Hati-hati! Pastikan backup dulu jika ada file penting

# Buat symbolic link dari public/ Laravel ke document root
ln -s ~/private/laravel/public/* ~/web/
ln -s ~/private/laravel/public/.htaccess ~/web/

# Atau cara alternatif: change document root di ISPConfig
# Sites → Website → tab Options → Document Root: /web/public
# (Tapi ini membutuhkan akses admin ISPConfig)
```

### 4. Install Dependencies

```bash
cd ~/private/laravel

# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Set .env
cp .env.production.example .env
nano .env  # Edit dengan credentials yang sesuai
```

### 5. Setup Laravel

```bash
# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Seed data (optional)
php artisan db:seed --force

# Create storage link
php artisan storage:link

# Cache everything
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan filament:optimize
```

### 6. Set Permissions

```bash
# ISPConfig otomatis set owner ke web1:client1
# Pastikan storage writeable
chmod -R 775 storage bootstrap/cache
```

---

## Setup SSH Key untuk GitHub Actions

### Generate Key di Server

```bash
# Login ke server sebagai user web1
ssh web1@server-ip

# Generate SSH key
ssh-keygen -t ed25519 -C "github-actions-cadisdik"
# Tekan Enter untuk default path: /var/www/clients/client1/.ssh/id_ed25519

# Copy private key (untuk GitHub Secret SSH_KEY)
cat ~/.ssh/id_ed25519
# Copy seluruh output termasuk BEGIN dan END

# Copy public key (untuk authorized_keys)
cat ~/.ssh/id_ed25519.pub

# Tambahkan public key ke authorized_keys
cat ~/.ssh/id_ed25519.pub >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys

# Test SSH connection
exit
ssh -i ~/.ssh/id_ed25519 web1@server-ip
```

---

## ISPConfig-specific .env Configuration

```env
# Database (dengan prefix client dari ISPConfig)
DB_DATABASE=c1cadisdik
DB_USERNAME=c1user
DB_PASSWORD=your_password_here

# URLs (sesuaikan dengan domain)
APP_URL=https://cadisdik13.disdik.jabarprov.go.id
ASSET_URL=https://cadisdik13.disdik.jabarprov.go.id

# Session (important untuk multi-site)
SESSION_DOMAIN=.cadisdik13.disdik.jabarprov.go.id
SESSION_SECURE_COOKIE=true

# Mail (jika pakai SMTP ISPConfig)
MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=587
MAIL_USERNAME=noreply@cadisdik13.disdik.jabarprov.go.id
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@cadisdik13.disdik.jabarprov.go.id
MAIL_FROM_NAME="Cadisdik XIII"
```

---

## Update GitHub Secrets untuk ISPConfig

```yaml
SSH_HOST: 123.45.67.89
SSH_USERNAME: web1 # Username ISPConfig
SSH_KEY: [private key dari ~/.ssh/id_ed25519]
SSH_PORT: 22
PROJECT_PATH: /var/www/clients/client1/web1/private/laravel # Path lengkap
```

---

## Web Server Configuration

ISPConfig otomatis generate config, tapi pastikan:

### Apache (Default ISPConfig)

File: `/etc/apache2/sites-available/cadisdik13.xxx.conf`

```apache
<Directory /var/www/clients/client1/web1/web>
    Options FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
```

### PHP Settings

Di ISPConfig Panel:

1. **Sites** → **Website** → tab **Options**
2. **PHP Settings**:
    ```ini
    upload_max_filesize = 10M
    post_max_size = 10M
    max_execution_time = 60
    memory_limit = 256M
    ```

---

## ⚠️ Common Issues di ISPConfig

### Issue 1: Permission Denied pada storage/

```bash
# Fix ownership
cd ~/private/laravel
chmod -R 775 storage bootstrap/cache

# Jika masih error, cek user web server
ps aux | grep apache
# Biasanya www-data atau nama client (client1)
```

### Issue 2: Symbolic Link tidak bekerja

```bash
# Pastikan .htaccess ada di ~/web/
ls -la ~/web/.htaccess

# Jika tidak ada:
cp ~/private/laravel/public/.htaccess ~/web/
```

### Issue 3: Composer tidak ditemukan

```bash
# Install Composer di user directory
cd ~
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar ~/bin/composer

# Add to PATH
echo 'export PATH="$HOME/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc
```

### Issue 4: Git credential helper

```bash
# Setup credential helper untuk auto-pull tanpa password
cd ~/private/laravel
git config credential.helper store
git pull  # Masukkan username & password sekali, akan disimpan
```

---

## Hubungi Admin ISPConfig Jika:

- Perlu PHP version upgrade (PHP 8.2+)
- Perlu adjust DocumentRoot ke `/web/public`
- Perlu install ekstensi PHP tambahan
- Perlu akses Cron jobs untuk queue workers
- Perlu setup SSL certificate (Let's Encrypt)

---

**Last Updated**: February 14, 2026
