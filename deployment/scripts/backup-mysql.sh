#!/usr/bin/env bash
# ==============================================================================
# TailorPro Automated MySQL Backup Script (Linux / Production)
# ==============================================================================
# Add to crontab:
#   0 2 * * * /var/www/tailorpro/deployment/scripts/backup-mysql.sh >> /var/log/tailorpro-backup.log 2>&1
# ==============================================================================

set -euo pipefail

DB_USER="${DB_USER:-tailorpro_user}"
DB_PASSWORD="${DB_PASSWORD:-}"
DB_NAME="${DB_NAME:-tailorpro}"
BACKUP_DIR="${BACKUP_DIR:-/var/backups/tailorpro}"
RETENTION_DAYS=30
TIMESTAMP=$(date +"%Y-%m-%d_%H-%M-%S")
BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}-backup-${TIMESTAMP}.sql.gz"

mkdir -p "${BACKUP_DIR}"

echo "[*] Starting TailorPro database backup: ${DB_NAME} at $(date)..."

# Construct mysqldump command
if [ -n "${DB_PASSWORD}" ]; then
  MYSQLDUMP_CMD="mysqldump --user=${DB_USER} --password=${DB_PASSWORD}"
else
  MYSQLDUMP_CMD="mysqldump --user=${DB_USER}"
fi

# Run dump and stream into gzip
${MYSQLDUMP_CMD} \
  --single-transaction \
  --quick \
  --routines \
  --triggers \
  --databases "${DB_NAME}" \
  | gzip -9 > "${BACKUP_FILE}"

if [ -f "${BACKUP_FILE}" ]; then
  FILE_SIZE=$(du -sh "${BACKUP_FILE}" | cut -f1)
  echo "[✓] Database backup successfully created: ${BACKUP_FILE} (${FILE_SIZE})"

  # Purge backups older than retention window
  find "${BACKUP_DIR}" -type f -name "${DB_NAME}-backup-*.sql.gz" -mtime +${RETENTION_DAYS} -delete
  echo "[✓] Pruned backups older than ${RETENTION_DAYS} days."
else
  echo "[!] Error: Backup file could not be created." >&2
  exit 1
fi
