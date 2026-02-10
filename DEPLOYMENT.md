# Scandere API - Deployment Guide

## Server Requirements
- Ubuntu 24.04 LTS
- Minimum 2GB RAM
- Root access

## Installation Steps

### 1. Prepare the Server

Upload the setup script to your server:
```bash
scp deploy-setup.sh root@your-server-ip:/root/
```

### 2. Run Server Setup

SSH into your server and run:
```bash
ssh root@your-server-ip
cd /root
chmod +x deploy-setup.sh
./deploy-setup.sh
```

This will install:
- ✅ Nginx web server
- ✅ PHP 8.3 with all required extensions
- ✅ MariaDB database server
- ✅ Composer package manager
- ✅ Project directory structure

**IMPORTANT:** Save the database password shown at the end!

### 3. Upload Project Files

From your local machine, upload the project:
```bash
# Exclude vendor and node_modules
rsync -avz --exclude='vendor' \
           --exclude='node_modules' \
           --exclude='.git' \
           --exclude='storage/logs/*' \
           ./ root@your-server-ip:/var/www/scandere-api/
```

Or use SFTP/SCP:
```bash
scp -r ./* root@your-server-ip:/var/www/scandere-api/
```

### 4. Configure Environment

SSH back into the server:
```bash
ssh root@your-server-ip
cd /var/www/scandere-api
```

Create `.env` file:
```bash
cp .env.production .env
nano .env
```

Update these values in `.env`:
- `DB_PASSWORD` - Use the password from step 2
- `STRIPE_KEY` - Your Stripe publishable key
- `STRIPE_SECRET` - Your Stripe secret key
- `STRIPE_WEBHOOK_SECRET` - Your Stripe webhook secret

### 5. Deploy Application

Make the deployment script executable and run it:
```bash
chmod +x deploy-app.sh
./deploy-app.sh
```

This will:
- ✅ Install PHP dependencies
- ✅ Generate application key
- ✅ Run database migrations
- ✅ Cache configurations
- ✅ Set proper permissions
- ✅ Restart services

### 6. Configure DNS

Point your domain to the server:
```
A Record: api.scandereai.store → your-server-ip
```

Wait for DNS propagation (can take up to 24 hours).

### 7. Setup SSL Certificate

Once DNS is propagated:
```bash
apt install certbot python3-certbot-nginx -y
certbot --nginx -d api.scandereai.store
```

Choose option 2 to redirect HTTP to HTTPS.

### 8. Test the API

```bash
# Health check
curl https://api.scandereai.store/up

# API endpoints
curl https://api.scandereai.store/api/products
curl https://api.scandereai.store/api/categories
```

### 9. Configure Stripe Webhooks

In your Stripe Dashboard:
1. Go to Developers → Webhooks
2. Add endpoint: `https://api.scandereai.store/api/webhook/stripe`
3. Select events:
   - `checkout.session.completed`
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
4. Copy the webhook secret to `.env` as `STRIPE_WEBHOOK_SECRET`

### 10. Setup Queue Worker (Optional)

If you're using queues:
```bash
nano /etc/supervisor/conf.d/scandere-worker.conf
```

Add:
```ini
[program:scandere-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/scandere-api/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/scandere-api/storage/logs/worker.log
stopwaitsecs=3600
```

Start the worker:
```bash
supervisorctl reread
supervisorctl update
supervisorctl start scandere-worker:*
```

## Maintenance

### Update Application
```bash
cd /var/www/scandere-api
php artisan down
git pull  # or upload new files
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan up
systemctl restart php8.3-fpm
```

### View Logs
```bash
tail -f /var/www/scandere-api/storage/logs/laravel.log
```

### Check Services Status
```bash
systemctl status nginx
systemctl status php8.3-fpm
systemctl status mariadb
```

### Database Backup
```bash
mysqldump -u scandere_user -p scandere_store > backup-$(date +%Y%m%d).sql
```

## Firewall Configuration

```bash
ufw allow 22/tcp    # SSH
ufw allow 80/tcp    # HTTP
ufw allow 443/tcp   # HTTPS
ufw enable
```

## Security Checklist

- [ ] SSL certificate installed
- [ ] `.env` file has proper permissions (600)
- [ ] `APP_DEBUG=false` in production
- [ ] Strong database password
- [ ] Firewall configured
- [ ] Regular backups scheduled
- [ ] Stripe webhooks configured
- [ ] CORS properly configured for your frontend domain

## Troubleshooting

### Permission Issues
```bash
cd /var/www/scandere-api
chown -R www-data:www-data .
chmod -R 775 storage bootstrap/cache
```

### Clear All Caches
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### 502 Bad Gateway
```bash
systemctl restart php8.3-fpm
systemctl status php8.3-fpm
```

### Database Connection Issues
```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
```

## Support

For issues, check:
- Laravel logs: `/var/www/scandere-api/storage/logs/laravel.log`
- Nginx error log: `/var/log/nginx/error.log`
- PHP-FPM log: `/var/log/php8.3-fpm.log`
