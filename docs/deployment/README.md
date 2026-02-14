# Deployment Documentation

Folder ini berisi dokumentasi dan script untuk deployment aplikasi Sistem Informasi Cadisdik XIII.

## File Overview

### Documentation Files

- **[DEPLOYMENT.md](DEPLOYMENT.md)** - Panduan lengkap deployment dengan GitHub Actions CI/CD
- **[ISPCONFIG_DEPLOYMENT.md](ISPCONFIG_DEPLOYMENT.md)** - Panduan khusus deployment di ISPConfig (SiDAT WebHost)
- **[DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md)** - Checklist untuk memastikan semua prasyarat deployment terpenuhi

### Deployment Scripts

- **[deploy.sh](deploy.sh)** - Bash script untuk automasi deployment di server
- **[.env.production.example](.env.production.example)** - Template file environment untuk production

## Quick Links

### Pertama Kali Deploy?

1. Baca [DEPLOYMENT_CHECKLIST.md](DEPLOYMENT_CHECKLIST.md) untuk memastikan semua prasyarat terpenuhi
2. Pilih panduan sesuai server:
    - **Server Umum (VPS/Dedicated)**: Ikuti [DEPLOYMENT.md](DEPLOYMENT.md)
    - **ISPConfig (SiDAT WebHost)**: Ikuti [ISPCONFIG_DEPLOYMENT.md](ISPCONFIG_DEPLOYMENT.md)
3. Setup GitHub Actions untuk deployment otomatis

### Update Deployment?

Jika sudah pernah deploy dan ingin update:

```bash
# Push ke branch main untuk trigger auto-deployment
git add .
git commit -m "Update feature"
git push origin main
```

Atau jalankan manual di server:

```bash
# SSH ke server
ssh username@server-ip

# Masuk ke folder project
cd /path/to/project

# Jalankan deployment script
bash deploy.sh
```

## Support

Jika mengalami masalah deployment, cek:

1. **GitHub Actions Logs**: Repository → Actions → Klik workflow yang gagal
2. **Server Logs**: `tail -f /var/log/nginx/error.log`
3. **Laravel Logs**: `tail -f storage/logs/laravel.log`

---

**Last Updated**: February 14, 2026
