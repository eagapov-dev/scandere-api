#!/bin/bash

#############################################
# Scandere API - Ubuntu 24 Server Setup
# Installs: Nginx, PHP 8.3, MariaDB, Composer
#############################################

set -e  # Exit on error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Scandere API Server Setup ===${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root (use sudo)${NC}"
    exit 1
fi

# Variables (modify these as needed)
PROJECT_DIR="/var/www/scandere-api"
DOMAIN="api.scandereai.store"
DB_NAME="scandere_store"
DB_USER="scandere_user"
DB_PASS=""  # Will be generated

echo -e "${YELLOW}Step 1: Updating system packages...${NC}"
apt update && apt upgrade -y

echo -e "${YELLOW}Step 2: Installing required software...${NC}"
apt install -y software-properties-common curl git unzip supervisor

echo -e "${YELLOW}Step 3: Installing PHP 8.3 and extensions...${NC}"
add-apt-repository -y ppa:ondrej/php
apt update
apt install -y \
    php8.3-fpm \
    php8.3-cli \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-gd \
    php8.3-intl \
    php8.3-bcmath \
    php8.3-redis \
    php8.3-tokenizer \
    php8.3-dom

# Configure PHP-FPM
sed -i 's/upload_max_filesize = .*/upload_max_filesize = 100M/' /etc/php/8.3/fpm/php.ini
sed -i 's/post_max_size = .*/post_max_size = 100M/' /etc/php/8.3/fpm/php.ini
sed -i 's/memory_limit = .*/memory_limit = 512M/' /etc/php/8.3/fpm/php.ini
sed -i 's/max_execution_time = .*/max_execution_time = 300/' /etc/php/8.3/fpm/php.ini

echo -e "${YELLOW}Step 4: Installing Composer...${NC}"
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

echo -e "${YELLOW}Step 5: Installing Nginx...${NC}"
apt install -y nginx

echo -e "${YELLOW}Step 6: Installing MariaDB...${NC}"
apt install -y mariadb-server mariadb-client

# Start MariaDB
systemctl start mariadb
systemctl enable mariadb

# Generate secure password
DB_PASS=$(openssl rand -base64 32 | tr -d "=+/" | cut -c1-25)

echo -e "${YELLOW}Step 7: Configuring MariaDB...${NC}"
# Secure installation
mysql -e "DELETE FROM mysql.user WHERE User='';"
mysql -e "DELETE FROM mysql.user WHERE User='root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');"
mysql -e "DROP DATABASE IF EXISTS test;"
mysql -e "DELETE FROM mysql.db WHERE Db='test' OR Db='test\\_%';"
mysql -e "FLUSH PRIVILEGES;"

# Create database and user
mysql -e "CREATE DATABASE IF NOT EXISTS ${DB_NAME} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON ${DB_NAME}.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo -e "${GREEN}Database created: ${DB_NAME}${NC}"
echo -e "${GREEN}Database user: ${DB_USER}${NC}"
echo -e "${GREEN}Database password: ${DB_PASS}${NC}"
echo -e "${YELLOW}(Save this password, you'll need it!)${NC}"

echo -e "${YELLOW}Step 8: Creating project directory...${NC}"
mkdir -p ${PROJECT_DIR}
cd ${PROJECT_DIR}

echo -e "${YELLOW}Step 9: Configuring Nginx...${NC}"
cat > /etc/nginx/sites-available/${DOMAIN} << 'NGINX_EOF'
server {
    listen 80;
    listen [::]:80;
    server_name api.scandereai.store;
    root /var/www/scandere-api/public;

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
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Security headers
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # CORS headers for API
    add_header 'Access-Control-Allow-Origin' 'https://scandereai.store' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
    add_header 'Access-Control-Allow-Headers' 'Authorization, Content-Type, X-Requested-With' always;
    add_header 'Access-Control-Allow-Credentials' 'true' always;

    if ($request_method = 'OPTIONS') {
        return 204;
    }
}
NGINX_EOF

# Enable site
ln -sf /etc/nginx/sites-available/${DOMAIN} /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test nginx configuration
nginx -t

echo -e "${YELLOW}Step 10: Setting up directory structure...${NC}"
mkdir -p ${PROJECT_DIR}/{storage,bootstrap/cache}
mkdir -p ${PROJECT_DIR}/storage/{app/{public,private},framework/{cache/{data,routes,views},sessions,testing,views},logs}

echo -e "${YELLOW}Step 11: Setting permissions...${NC}"
chown -R www-data:www-data ${PROJECT_DIR}
chmod -R 755 ${PROJECT_DIR}
chmod -R 775 ${PROJECT_DIR}/storage
chmod -R 775 ${PROJECT_DIR}/bootstrap/cache

echo -e "${GREEN}=== Setup Complete! ===${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Upload your Laravel project files to: ${PROJECT_DIR}"
echo "2. Create .env file with these database credentials:"
echo "   DB_DATABASE=${DB_NAME}"
echo "   DB_USERNAME=${DB_USER}"
echo "   DB_PASSWORD=${DB_PASS}"
echo ""
echo "3. Run these commands:"
echo "   cd ${PROJECT_DIR}"
echo "   composer install --no-dev --optimize-autoloader"
echo "   php artisan key:generate"
echo "   php artisan migrate --force"
echo "   php artisan config:cache"
echo "   php artisan route:cache"
echo "   php artisan view:cache"
echo ""
echo "4. Restart services:"
echo "   systemctl restart php8.3-fpm"
echo "   systemctl restart nginx"
echo ""
echo "5. Configure SSL with Let's Encrypt:"
echo "   apt install certbot python3-certbot-nginx"
echo "   certbot --nginx -d ${DOMAIN}"
echo ""
echo -e "${GREEN}Database Password: ${DB_PASS}${NC}"
echo -e "${YELLOW}(Save this password in a secure location!)${NC}"
