#!/bin/bash

#############################################
# Scandere API - Setup Automated Backups
# Configures daily backups via cron
#############################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root (use sudo)${NC}"
    exit 1
fi

PROJECT_DIR="/var/www/scandere-api"

echo -e "${GREEN}=== Setting up Automated Tasks ===${NC}"
echo ""

# Make backup script executable
chmod +x ${PROJECT_DIR}/backup.sh

# Add to crontab if not exists
CRON_BACKUP="0 2 * * * ${PROJECT_DIR}/backup.sh >> /var/log/scandere-backup.log 2>&1"
CRON_SCHEDULE="0 * * * * cd ${PROJECT_DIR} && php artisan schedule:run >> /dev/null 2>&1"

# Check if cron jobs already exist
if ! crontab -l 2>/dev/null | grep -q "scandere-backup.log"; then
    (crontab -l 2>/dev/null; echo "# Scandere API - Daily backup at 2 AM") | crontab -
    (crontab -l 2>/dev/null; echo "$CRON_BACKUP") | crontab -
    echo -e "${GREEN}✓ Backup cron job added (runs daily at 2 AM)${NC}"
else
    echo -e "${YELLOW}Backup cron job already exists${NC}"
fi

if ! crontab -l 2>/dev/null | grep -q "schedule:run"; then
    (crontab -l 2>/dev/null; echo "# Laravel Scheduler") | crontab -
    (crontab -l 2>/dev/null; echo "$CRON_SCHEDULE") | crontab -
    echo -e "${GREEN}✓ Laravel scheduler added${NC}"
else
    echo -e "${YELLOW}Laravel scheduler already exists${NC}"
fi

echo ""
echo -e "${GREEN}Current crontab:${NC}"
crontab -l

echo ""
echo -e "${GREEN}=== Setup Complete! ===${NC}"
echo ""
echo "Automated tasks configured:"
echo "  • Database backup: Daily at 2:00 AM"
echo "  • Laravel scheduler: Every hour"
echo ""
echo "Backup logs: /var/log/scandere-backup.log"
echo ""
echo "To test backup manually:"
echo "  ${PROJECT_DIR}/backup.sh"
