#!/bin/bash

# Configuration
BACKUP_DIR="/home/heroes/backup"
LOG_FILE="$BACKUP_DIR/cron_mysqldump.log"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/heroescrm_prod_backup_$DATE.sql.gz"
RETENTION_DAYS=7

# Se placer dans le dossier du projet
cd /home/heroes/Heroescrm || exit 1

# Horodatage du log
echo "===== Backup started at $(date) =====" >> "$LOG_FILE"

# Lancer le dump
docker compose exec -T heroescrm mysqldump \
  -h 10.7.226.11 -P 18501 -u heroescrm-laravel10 -p'50EruoJbVOux@' \
  --single-transaction --quick --lock-tables=false --skip-ssl --no-tablespaces \
  heroescrm-laravel10 | gzip > "$BACKUP_FILE" 2>> "$LOG_FILE"

# Vérifier que le fichier n'est pas vide (dump réussi)
if [ -s "$BACKUP_FILE" ]; then
    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "Backup successful: $BACKUP_FILE ($SIZE)" >> "$LOG_FILE"
else
    echo "ERROR: Backup file is empty or missing!" >> "$LOG_FILE"
    rm -f "$BACKUP_FILE"
fi

# Supprimer les sauvegardes de plus de RETENTION_DAYS jours
find "$BACKUP_DIR" -name "heroescrm_prod_backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete

echo "===== Backup finished at $(date) =====" >> "$LOG_FILE"
echo "" >> "$LOG_FILE"