#!/bin/bash

#############################################
# Scandere API - Database Backup Script
# Creates timestamped database backups
#############################################

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${RED}Please run as root (use sudo)${NC}"
    exit 1
fi

PROJECT_DIR="/var/www/scandere-api"
BACKUP_DIR="/root/backups/scandere"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

# Load .env variables
if [ -f "${PROJECT_DIR}/.env" ]; then
    export $(grep -v '^#' ${PROJECT_DIR}/.env | xargs)
else
    echo -e "${RED}.env file not found!${NC}"
    exit 1
fi

echo -e "${GREEN}=== Scandere API Backup ===${NC}"
echo ""

# Create backup directory
mkdir -p ${BACKUP_DIR}

echo -e "${YELLOW}1. Backing up database...${NC}"
mysqldump -u ${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} > ${BACKUP_DIR}/db_${TIMESTAMP}.sql

echo -e "${YELLOW}2. Backing up .env file...${NC}"
cp ${PROJECT_DIR}/.env ${BACKUP_DIR}/env_${TIMESTAMP}.txt

echo -e "${YELLOW}3. Backing up storage files...${NC}"
tar -czf ${BACKUP_DIR}/storage_${TIMESTAMP}.tar.gz -C ${PROJECT_DIR} storage/app/public 2>/dev/null || true

echo -e "${YELLOW}4. Compressing database backup...${NC}"
gzip ${BACKUP_DIR}/db_${TIMESTAMP}.sql

# Calculate sizes
DB_SIZE=$(du -h ${BACKUP_DIR}/db_${TIMESTAMP}.sql.gz | cut -f1)
STORAGE_SIZE=$(du -h ${BACKUP_DIR}/storage_${TIMESTAMP}.tar.gz 2>/dev/null | cut -f1 || echo "0")

echo -e "${GREEN}=== Backup Complete! ===${NC}"
echo ""
echo "Database backup: ${BACKUP_DIR}/db_${TIMESTAMP}.sql.gz (${DB_SIZE})"
echo "Storage backup: ${BACKUP_DIR}/storage_${TIMESTAMP}.tar.gz (${STORAGE_SIZE})"
echo "Config backup: ${BACKUP_DIR}/env_${TIMESTAMP}.txt"
echo ""

# Clean old backups (keep last 7 days)
echo -e "${YELLOW}5. Cleaning old backups (keeping last 7 days)...${NC}"
find ${BACKUP_DIR} -name "db_*.sql.gz" -mtime +7 -delete 2>/dev/null || true
find ${BACKUP_DIR} -name "storage_*.tar.gz" -mtime +7 -delete 2>/dev/null || true
find ${BACKUP_DIR} -name "env_*.txt" -mtime +7 -delete 2>/dev/null || true

echo -e "${GREEN}Done!${NC}"
echo ""
echo "To restore from backup:"
echo "  gunzip ${BACKUP_DIR}/db_${TIMESTAMP}.sql.gz"
echo "  mysql -u ${DB_USERNAME} -p${DB_PASSWORD} ${DB_DATABASE} < ${BACKUP_DIR}/db_${TIMESTAMP}.sql"
