#!/bin/bash

#############################################
# Initialize Production .env file
# Run this on the production server
#############################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

PROJECT_DIR="/var/www/scandere-api"

echo -e "${GREEN}=== Initializing Production .env ===${NC}"
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root (use sudo)${NC}"
    exit 1
fi

cd $PROJECT_DIR

# Backup existing .env if exists
if [ -f .env ]; then
    echo -e "${YELLOW}Backing up existing .env to .env.backup${NC}"
    cp .env .env.backup
fi

# Copy production template
echo -e "${YELLOW}Creating .env from .env.production template${NC}"
cp .env.production .env

# Generate APP_KEY
echo -e "${YELLOW}Generating APP_KEY...${NC}"
php artisan key:generate --force

echo ""
echo -e "${GREEN}=== .env file created! ===${NC}"
echo ""
echo -e "${YELLOW}Next steps:${NC}"
echo "1. Edit .env file:"
echo "   nano .env"
echo ""
echo "2. Update these values:"
echo "   - DB_PASSWORD (from deploy-setup.sh output)"
echo "   - STRIPE_KEY (pk_live_xxx)"
echo "   - STRIPE_SECRET (sk_live_xxx)"
echo "   - STRIPE_WEBHOOK_SECRET (whsec_xxx)"
echo "   - MAIL_* (if using email)"
echo ""
echo "3. Secure the .env file:"
echo "   chmod 600 .env"
echo "   chown www-data:www-data .env"
echo ""
echo -e "${GREEN}Done!${NC}"
