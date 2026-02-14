# Deployment Guide - CI/CD dengan GitHub Actions

## Prerequisites

### 1. Server Requirements

- **OS**: Linux (Ubuntu 20.04+ / Debian 11+)
- **Web Server**: Nginx atau Apache
- **PHP**: 8.2 atau lebih tinggi
- **Database**: MySQL 8.0+ / MariaDB 10.3+
- **Composer**: Latest version
- **Git**: Latest version
- **Node.js**: 18+ (untuk build assets, optional)

### 2. Server Access

- SSH access dengan key-based authentication
- Port SSH (default: 22)
- Sudo privileges untuk install dependencies

---

## Setup GitHub Secrets

Buka repository di GitHub → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**

Tambahkan secrets berikut:

| Secret Name    | Contoh Value                             | Keterangan                                 |
| -------------- | ---------------------------------------- | ------------------------------------------ |
| `SSH_HOST`     | `123.45.67.89`                           | IP address atau domain server              |
| `SSH_USERNAME` | `cadisdik`                               | Username SSH server                        |
| `SSH_KEY`      | `-----BEGIN OPENSSH PRIVATE KEY-----...` | Private key SSH (isi file `~/.ssh/id_rsa`) |
| `SSH_PORT`     | `22`                                     | Port SSH (default: 22)                     |
| `PROJECT_PATH` | `/var/www/cadisdik/public_html`          | Path folder project di server              |

---

## Setup SSH Key

### Di Local/Developer Machine:

```bash
# Generate SSH key pair (jika belum ada)
ssh-keygen -t ed25519 -C "github-actions@cadisdik.com"

# Copy isi private key (untuk GitHub Secret SSH_KEY)
cat ~/.ssh/id_ed25519

# Copy public key (untuk ditambahkan ke server)
cat ~/.ssh/id_ed25519.pub
```

### Di Server:

```bash
# Login ke server via SSH
ssh username@server-ip

# Tambahkan public key ke authorized_keys
mkdir -p ~/.ssh
nano ~/.ssh/authorized_keys
# Paste public key dari langkah sebelumnya

# Set permissions
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys

# Test koneksi
exit
ssh -i ~/.ssh/id_ed25519 username@server-ip
```

---

## Setup Project di Server

### 1. Clone Repository

```bash
# Login ke server
ssh username@server-ip

# Navigate ke folder web
cd /var/www/cadisdik/

# Clone project (pakai HTTPS atau SSH)
git clone https://github.com/buku-tamu-kcd-ciamis/buku-tamu-kcd-ciamis.git public_html
cd public_html

# Set Git credentials untuk auto-pull
git config --global credential.helper store
```

### 2. Install Dependencies

```bash
# Install Composer dependencies
composer install --no-dev --optimize-autoloader

# Install NPM dependencies (jika ada)
npm install && npm run build
```

### 3. Setup Environment

```bash
# Copy .env
cp .env.example .env

# Edit .env dengan credentials production
nano .env

# Generate app key
php artisan key:generate

# Setup storage link
php artisan storage:link
```

### 4. Setup Database

```bash
# Run migrations
php artisan migrate --force

# Seed data (jika perlu)
php artisan db:seed --force
```

### 5. Set Permissions

```bash
# Set ownership (sesuaikan dengan user web server)
sudo chown -R www-data:www-data /var/www/cadisdik/public_html
sudo chown -R $USER:www-data storage bootstrap/cache

# Set permissions
sudo chmod -R 775 storage bootstrap/cache
sudo chmod -R 755 /var/www/cadisdik/public_html
```

---

## Web Server Configuration

### Nginx (Recommended)

```nginx
server {
    listen 80;
    server_name cadisdik13.disdik.jabarprov.go.id;
    root /var/www/cadisdik/public_html/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Apache (.htaccess sudah ada di Laravel)

File `.htaccess` sudah tersedia di folder `public/`

---

## Testing Deployment

### Manual Trigger dari GitHub:

1. Buka repository di GitHub
2. Go to **Actions** tab
3. Pilih workflow **Deploy to Production**
4. Klik **Run workflow** → **Run workflow**

### Auto Trigger:

Push ke branch `main`:

```bash
git add .
git commit -m "Test deployment"
git push origin main
```

---

## Troubleshooting

### Error: Permission denied (publickey)

```bash
# Pastikan SSH key sudah ditambahkan dengan benar
# Test koneksi SSH manual:
ssh -i ~/.ssh/id_ed25519 username@server-ip
```

### Error: Composer command not found

```bash
# Install Composer di server
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
```

### Error: Git command not found

```bash
# Install Git di server
sudo apt update
sudo apt install git
```

### Error: PHP version mismatch

```bash
# Update PHP ke versi 8.2+
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-cli php8.2-common php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-gd php8.2-zip
```

### Error: Permission denied di storage/

```bash
# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## Workflow Explanation

### `.github/workflows/deploy.yml`

- **Trigger**: Push ke branch `main`
- **Jobs**:
    1. Checkout code dari repository
    2. Setup PHP environment
    3. Install Composer dependencies
    4. Run tests (optional)
    5. SSH ke server dan jalankan deployment script:
        - `git pull` - Update code
        - `composer install` - Update dependencies
        - `php artisan migrate` - Run migrations
        - Clear & cache config/routes/views
        - Set permissions

### `.github/workflows/tests.yml`

- **Trigger**: Push/PR ke branch `main` atau `develop`
- **Jobs**:
    1. Setup MySQL database untuk testing
    2. Install dependencies
    3. Run PHPUnit tests
    4. Upload coverage report

---

## Security Tips

1. **Jangan commit `.env` ke Git** (sudah ada di `.gitignore`)
2. **Gunakan key-based SSH authentication**, bukan password
3. **Batasi IP yang bisa akses SSH** (via firewall)
4. **Setup SSL/HTTPS** dengan Let's Encrypt
5. **Backup database secara berkala**
6. **Monitor server logs** (`/var/log/nginx/`, `storage/logs/`)

---

## Support

Jika ada error saat deployment, cek:

1. **GitHub Actions Logs**: Repository → Actions → Klik workflow yang gagal
2. **Server Logs**: SSH ke server → `tail -f /var/log/nginx/error.log`
3. **Laravel Logs**: `tail -f storage/logs/laravel.log`

---

**Last Updated**: February 14, 2026
