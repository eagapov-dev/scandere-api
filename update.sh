#!/bin/bash

#############################################
# Scandere API - Quick Update Script
# Use this for deploying updates
#############################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

PROJECT_DIR="/var/www/scandere-api"

echo -e "${GREEN}=== Updating Scandere API ===${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root (use sudo)${NC}"
    exit 1
fi

cd ${PROJECT_DIR}

echo -e "${YELLOW}1. Enabling maintenance mode...${NC}"
php artisan down || true

echo -e "${YELLOW}2. Installing dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

echo -e "${YELLOW}3. Running migrations...${NC}"
php artisan migrate --force

echo -e "${YELLOW}4. Clearing caches...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo -e "${YELLOW}5. Optimizing...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo -e "${YELLOW}6. Setting permissions...${NC}"
chown -R www-data:www-data ${PROJECT_DIR}
chmod -R 775 ${PROJECT_DIR}/storage ${PROJECT_DIR}/bootstrap/cache

echo -e "${YELLOW}7. Restarting services...${NC}"
systemctl restart php8.3-fpm
systemctl restart nginx

echo -e "${YELLOW}8. Disabling maintenance mode...${NC}"
php artisan up

echo -e "${GREEN}=== Update Complete! ===${NC}"
echo ""
echo -e "Current version info:"
php artisan about --only=environment
