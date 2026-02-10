# 🚀 Quick Setup on Ubuntu 24

Complete Laravel API installation on a clean Ubuntu 24 server in 5 steps.

## Prerequisites

- Clean Ubuntu 24.04 server
- Root SSH access
- Domain pointed to server IP (api.scandereai.store)

---

## Step 1: Upload Setup Script

On your **local machine**:

```bash
scp deploy-setup.sh root@YOUR_SERVER_IP:/root/
```

Replace `YOUR_SERVER_IP` with your server's IP address.

---

## Step 2: Server Installation

Connect to your server and run the setup:

```bash
ssh root@YOUR_SERVER_IP
cd /root
chmod +x deploy-setup.sh
./deploy-setup.sh
```

⏱️ **Time: ~5-10 minutes**

The script will install:
- ✅ Nginx
- ✅ PHP 8.3 + all required extensions
- ✅ MariaDB
- ✅ Composer
- ✅ Configure directory structure

**⚠️ IMPORTANT:** Save the database password shown at the end!

---

## Step 3: Upload Project Files

On your **local machine**, from the project directory:

```bash
# Option 1: Using rsync (recommended)
rsync -avz --exclude='vendor' \
           --exclude='node_modules' \
           --exclude='.git' \
           --exclude='storage/logs/*' \
           ./ root@YOUR_SERVER_IP:/var/www/scandere-api/

# Option 2: Using SCP
tar -czf project.tar.gz --exclude='vendor' --exclude='node_modules' .
scp project.tar.gz root@YOUR_SERVER_IP:/var/www/scandere-api/
ssh root@YOUR_SERVER_IP "cd /var/www/scandere-api && tar -xzf project.tar.gz && rm project.tar.gz"
```

---

## Step 4: Configure Environment

On the **server**:

```bash
cd /var/www/scandere-api

# Create .env from template
cp .env.production .env

# Edit .env
nano .env
```

**Must update:**

```env
# Database password from Step 2
DB_PASSWORD=PASTE_PASSWORD_HERE

# Stripe keys
STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
```

Save the file: `Ctrl+X`, then `Y`, then `Enter`

---

## Step 5: Deploy Application

On the **server**:

```bash
cd /var/www/scandere-api
chmod +x deploy-app.sh
./deploy-app.sh
```

⏱️ **Time: ~2-3 minutes**

The script will:
- ✅ Install composer dependencies
- ✅ Generate APP_KEY
- ✅ Run database migrations
- ✅ Cache configurations
- ✅ Set proper permissions
- ✅ Restart services

---

## Step 6: Setup SSL (HTTPS)

On the **server**:

```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d api.scandereai.store
```

Choose option `2` for automatic HTTP to HTTPS redirect.

---

## ✅ Verify Installation

```bash
# Health check
curl https://api.scandereai.store/up

# Test endpoints
curl https://api.scandereai.store/api/products
curl https://api.scandereai.store/api/categories
```

If you get JSON responses - everything works! 🎉

---

## 📦 Additional Setup

### Configure Automated Backups

```bash
cd /var/www/scandere-api
chmod +x setup-cron.sh
./setup-cron.sh
```

Now database will be automatically backed up every day at 2:00 AM.

### Setup Stripe Webhooks

1. Open [Stripe Dashboard](https://dashboard.stripe.com/webhooks)
2. Add endpoint: `https://api.scandereai.store/api/webhook/stripe`
3. Select events:
   - `checkout.session.completed`
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
4. Copy webhook secret
5. Update in `.env`: `STRIPE_WEBHOOK_SECRET=whsec_xxx`
6. Restart: `./update.sh`

### Configure Firewall

```bash
ufw allow 22/tcp
ufw allow 80/tcp
ufw allow 443/tcp
ufw enable
```

---

## 🔄 Update Application

When you need to update code:

```bash
# 1. Upload new files from local machine
rsync -avz --exclude='vendor' ./ root@YOUR_SERVER_IP:/var/www/scandere-api/

# 2. On server, run update
ssh root@YOUR_SERVER_IP
cd /var/www/scandere-api
./update.sh
```

The script will automatically:
- Enable maintenance mode
- Update dependencies
- Run migrations
- Clear and rebuild cache
- Restart services
- Disable maintenance mode

---

## 🆘 Help

### Logs
```bash
# Laravel logs
tail -f /var/www/scandere-api/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/error.log
```

### Check Services
```bash
systemctl status nginx
systemctl status php8.3-fpm
systemctl status mariadb
```

### Clear Cache
```bash
cd /var/www/scandere-api
php artisan cache:clear
php artisan config:clear
```

### Permission Issues
```bash
cd /var/www/scandere-api
chown -R www-data:www-data .
chmod -R 775 storage bootstrap/cache
```

---

## 📋 Post-Installation Checklist

- [ ] API responds to requests
- [ ] SSL certificate installed (HTTPS works)
- [ ] Stripe webhook configured
- [ ] Automated backups configured
- [ ] Firewall configured
- [ ] `.env` contains correct settings
- [ ] Logs show no errors

---

## 📞 Deployment Files Structure

- `deploy-setup.sh` - Initial server setup
- `deploy-app.sh` - Laravel deployment
- `update.sh` - Quick code update
- `backup.sh` - Database backup
- `setup-cron.sh` - Configure automated tasks

---

**Questions?** See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed documentation.
