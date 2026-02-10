#!/bin/bash

#############################################
# Scandere API - Application Deployment
# Run this after uploading project files
#############################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}=== Scandere API Deployment ===${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root (use sudo)${NC}"
    exit 1
fi

PROJECT_DIR="/var/www/scandere-api"

# Check if project directory exists
if [ ! -d "$PROJECT_DIR" ]; then
    echo -e "${RED}Project directory not found: ${PROJECT_DIR}${NC}"
    exit 1
fi

cd ${PROJECT_DIR}

# Check if .env exists
if [ ! -f ".env" ]; then
    echo -e "${RED}.env file not found. Please create it first!${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${YELLOW}Step 2: Generating application key...${NC}"
php artisan key:generate --force

echo -e "${YELLOW}Step 3: Running database migrations...${NC}"
php artisan migrate --force

echo -e "${YELLOW}Step 4: Publishing Sanctum configuration...${NC}"
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider" --force || true

echo -e "${YELLOW}Step 5: Optimizing Laravel...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo -e "${YELLOW}Step 6: Creating symbolic link for storage...${NC}"
php artisan storage:link || true

echo -e "${YELLOW}Step 7: Setting proper permissions...${NC}"
chown -R www-data:www-data ${PROJECT_DIR}
chmod -R 755 ${PROJECT_DIR}
chmod -R 775 ${PROJECT_DIR}/storage
chmod -R 775 ${PROJECT_DIR}/bootstrap/cache
chmod +x ${PROJECT_DIR}/artisan

echo -e "${YELLOW}Step 8: Restarting services...${NC}"
systemctl restart php8.3-fpm
systemctl restart nginx

echo -e "${GREEN}=== Deployment Complete! ===${NC}"
echo ""
echo -e "${YELLOW}Your API is now available at:${NC}"
echo "http://api.scandereai.store"
echo ""
echo -e "${YELLOW}Test the API:${NC}"
echo "curl http://api.scandereai.store/api/products"
echo ""
echo -e "${YELLOW}To setup SSL certificate:${NC}"
echo "certbot --nginx -d api.scandereai.store"
echo ""
echo -e "${GREEN}Done!${NC}"
