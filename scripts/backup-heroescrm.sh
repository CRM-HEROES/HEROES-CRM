#!/bin/bash
#
# Sauvegarde de la base MySQL de production (mysqldump compressé, rotation).
#
# Les identifiants ne sont PAS dans ce fichier. Ils sont lus dans un fichier
# hors du dépôt (par défaut /home/heroes/.heroescrm-backup.env, chmod 600) :
#
#   DB_PASSWORD='...'              (obligatoire)
#   DB_HOST=10.7.226.11            (optionnel)
#   DB_PORT=18501                  (optionnel)
#   DB_USER=heroescrm-laravel10    (optionnel)
#   DB_NAME=heroescrm-laravel10    (optionnel)
#
# Modèle : scripts/backup-heroescrm.env.example
#
# Cron conseillé (tous les jours à 02h00) :
#   0 2 * * * /home/heroes/Heroescrm/scripts/backup-heroescrm.sh

# Configuration
PROJECT_DIR="${PROJECT_DIR:-/home/heroes/Heroescrm}"
BACKUP_DIR="${BACKUP_DIR:-/home/heroes/backup}"
CONFIG_FILE="${BACKUP_ENV_FILE:-/home/heroes/.heroescrm-backup.env}"
LOG_FILE="$BACKUP_DIR/cron_mysqldump.log"
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_FILE="$BACKUP_DIR/heroescrm_prod_backup_$DATE.sql.gz"
RETENTION_DAYS=7

mkdir -p "$BACKUP_DIR"

# Identifiants (hors dépôt)
if [ -f "$CONFIG_FILE" ]; then
    # shellcheck disable=SC1090
    . "$CONFIG_FILE"
fi

DB_HOST="${DB_HOST:-10.7.226.11}"
DB_PORT="${DB_PORT:-18501}"
DB_USER="${DB_USER:-heroescrm-laravel10}"
DB_NAME="${DB_NAME:-heroescrm-laravel10}"

if [ -z "$DB_PASSWORD" ]; then
    echo "ERROR: DB_PASSWORD manquant (voir $CONFIG_FILE)" >> "$LOG_FILE"
    exit 1
fi

# Se placer dans le dossier du projet
cd "$PROJECT_DIR" || exit 1

# Horodatage du log
echo "===== Backup started at $(date) =====" >> "$LOG_FILE"

# Lancer le dump
# Le mot de passe est transmis par la variable d'environnement MYSQL_PWD, sans
# valeur sur la ligne de commande : il n'apparaît pas dans la liste des processus.
export MYSQL_PWD="$DB_PASSWORD"
docker compose exec -T -e MYSQL_PWD heroescrm mysqldump \
  -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USER" \
  --single-transaction --quick --lock-tables=false --skip-ssl --no-tablespaces \
  "$DB_NAME" | gzip > "$BACKUP_FILE" 2>> "$LOG_FILE"
DUMP_STATUS=${PIPESTATUS[0]}

# Vérifier que le dump a réussi et que le fichier n'est pas vide
if [ "$DUMP_STATUS" -eq 0 ] && [ -s "$BACKUP_FILE" ]; then
    SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
    echo "Backup successful: $BACKUP_FILE ($SIZE)" >> "$LOG_FILE"
else
    echo "ERROR: mysqldump failed (status $DUMP_STATUS) or backup file is empty!" >> "$LOG_FILE"
    rm -f "$BACKUP_FILE"
fi

# Supprimer les sauvegardes de plus de RETENTION_DAYS jours
find "$BACKUP_DIR" -name "heroescrm_prod_backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete

echo "===== Backup finished at $(date) =====" >> "$LOG_FILE"
echo "" >> "$LOG_FILE"
