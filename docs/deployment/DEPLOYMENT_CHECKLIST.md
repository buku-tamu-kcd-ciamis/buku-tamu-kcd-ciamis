# Deployment Checklist

Gunakan checklist ini untuk memastikan semua yang diperlukan sudah disiapkan sebelum deployment.

## Server Setup

- [ ] Server sudah tersedia (ISPConfig/SiDAT)
- [ ] PHP 8.2+ terinstall
- [ ] Composer terinstall
- [ ] MySQL/MariaDB terinstall dan berjalan
- [ ] Git terinstall
- [ ] Web server (Nginx/Apache) sudah dikonfigurasi
- [ ] Domain/subdomain sudah pointing ke server

## SSH & Access

- [ ] SSH enabled di server
- [ ] SSH key pair sudah digenerate (`ssh-keygen`)
- [ ] Public key sudah ditambahkan ke server (`~/.ssh/authorized_keys`)
- [ ] Test SSH connection berhasil (`ssh username@server-ip`)
- [ ] User SSH punya akses ke folder web

## Project Setup di Server

- [ ] Repository sudah di-clone ke server
- [ ] File `.env` sudah dikonfigurasi dengan credentials production
- [ ] `APP_KEY` sudah digenerate (`php artisan key:generate`)
- [ ] Database sudah dibuat
- [ ] Migration sudah dijalankan (`php artisan migrate`)
- [ ] Storage link sudah dibuat (`php artisan storage:link`)
- [ ] Permissions folder `storage/` dan `bootstrap/cache/` sudah di-set (775)

## GitHub Secrets

Buka: Repository → Settings → Secrets and variables → Actions

- [ ] `SSH_HOST` - IP/domain server
- [ ] `SSH_USERNAME` - Username SSH
- [ ] `SSH_KEY` - Private key SSH (copy dari `~/.ssh/id_ed25519`)
- [ ] `SSH_PORT` - Port SSH (default: 22)
- [ ] `PROJECT_PATH` - Path project di server (e.g., `/var/www/cadisdik/public_html`)

## GitHub Actions Setup

- [ ] File `.github/workflows/deploy.yml` sudah dibuat
- [ ] File `.github/workflows/tests.yml` sudah dibuat (optional)
- [ ] Branch `main` sudah di-push ke GitHub
- [ ] Workflow permissions sudah di-enable (Settings → Actions → General → Workflow permissions)

## Web Server Configuration

- [ ] Document root pointing ke folder `/public`
- [ ] PHP-FPM configured (Nginx) atau `.htaccess` enabled (Apache)
- [ ] SSL certificate terinstall (Let's Encrypt recommended)
- [ ] Firewall rules sudah dikonfigurasi

## Database

- [ ] Database production sudah dibuat
- [ ] User database dengan privileges yang tepat
- [ ] Backup database development (sebelum migration di production)
- [ ] Credentials database sudah diset di `.env` production

## Email Configuration (Optional)

- [ ] MAIL\_\* variables dikonfigurasi di `.env`
- [ ] Test email berhasil dikirim (`php artisan tinker` → `Mail::raw('test', ...)`)

## Monitoring & Backup (Recommended)

- [ ] Setup cron job untuk backup database
- [ ] Setup log monitoring (Laravel Telescope/Bugsnag/Sentry)
- [ ] Setup uptime monitoring (UptimeRobot/Pingdom)

## ✅ Testing

- [ ] Manual deployment test (push ke `main` → cek Actions tab)
- [ ] Website accessible via HTTPS
- [ ] Login admin panel berhasil
- [ ] Upload file berhasil
- [ ] Print PDF berhasil
- [ ] Email notification berhasil (jika ada)
- [ ] Cek error logs (`storage/logs/laravel.log`)

---

## Ready to Deploy?

Jika semua checklist sudah ✅, siap untuk deployment otomatis!

Push ke branch `main`:

```bash
git add .
git commit -m "feat: deployment configuration"
git push origin main
```

Monitor deployment di GitHub:

- Repository → Actions → Klik workflow **Deploy to Production**

---

**Status Deployment**:

- [ ] Development ✅
- [ ] Staging ⏳
- [ ] Production ⏳

**Last Updated**: February 14, 2026
