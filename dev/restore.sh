#!/usr/bin/env bash

# Restore a SynergyCP installation from a database backup and a
# configuration backup.
#
# Usage: bash backup-restore.sh [-y|--yes]
#
# Run as root from a directory containing database.gz and
# synergycp-config-backup.tar.gz, on a fresh Debian OS (or on a server where
# a previous run of this script already installed SynergyCP - the install
# step is skipped and the restore resumes).

# Run script as root
[ "$(whoami)" != "root" ] && exec sudo -- "$0" "$@"

# Fail pipelines when any command in them fails (not just the last one), and
# treat unset variables as errors.
set -u -o pipefail

exit-with-error() {
  ERROR=$?
  [ "$ERROR" -eq 0 ] && ERROR=1
  {
    echo ""
    echo "=========="
    echo "ERROR: $*  (error code: ${ERROR})"
  } >&2
  exit "${ERROR}"
}

php-exec() {
  echo "$@" | /scp/bin/scp-exec php_server bash
  return $?
}
artisan-cmd() {
  php-exec php artisan "$@"
  return $?
}

START_DIR=$(pwd)

# Config
DB_FILE=database.gz
CONFIG_FILE=synergycp-config-backup.tar.gz
CONT_TMP_DIR=/tmp/scp-backup
CONT_APP_DIR=/var/www/html
SCP_ROOT_DIR=/scp
PHP_CONT_NAME=scp-bm-app_php_server
LOG_FILE="$START_DIR/restore.log"

ASSUME_YES=0
for arg in "$@"; do
  case "$arg" in
    -y | --yes) ASSUME_YES=1 ;;
  esac
done

# Keep a full log of the restore to simplify troubleshooting.
exec > >(tee -a "$LOG_FILE") 2>&1
echo "Restore started at $(date -u '+%Y-%m-%d %H:%M:%S') UTC (logging to $LOG_FILE)"

php-container() {
  docker ps --filter "name=$PHP_CONT_NAME" --format '{{.Names}}' | head -n1
}

# Decide whether this is a fresh server or a resumed restore. A failed
# restore can be run again after fixing the cause: the install step is
# skipped when SynergyCP is already running.
INSTALL_APP=1
if command -v docker > /dev/null 2>&1; then
  if [ -n "$(php-container)" ]; then
    echo "Existing SynergyCP installation detected: skipping the install step and resuming the restore."
    INSTALL_APP=0
  else
    exit-with-error "Docker is installed but no SynergyCP container ($PHP_CONT_NAME) is running. Backup recovery must be done either on a fresh Debian OS with nothing else installed, or on a server where a previous run of this script installed SynergyCP."
  fi
fi

echo -n "Checking for database file and config file..."
if [ ! -f "$DB_FILE" ]; then
  exit-with-error "Could not find database file. It must be named ${DB_FILE} in the directory this script was run from ($(pwd))."
fi
if [ ! -f "$CONFIG_FILE" ]; then
  exit-with-error "Could not find config file. It must be named ${CONFIG_FILE} in the directory this script was run from ($(pwd))."
fi
printf "\t\t\t[OK]\n"

# Verify both backup files BEFORE doing anything destructive, so a truncated
# upload is caught in seconds instead of after the database has been erased.
echo -n "Verifying backup file integrity..."
if ! gunzip -t "$DB_FILE" > /dev/null 2>&1; then
  exit-with-error "${DB_FILE} is not a valid gzip file. The upload is likely incomplete - please re-upload it and run this script again."
fi
for member in .env id_rsa id_rsa.pub; do
  if ! tar -tzf "$CONFIG_FILE" "$member" > /dev/null 2>&1; then
    exit-with-error "${CONFIG_FILE} does not contain ${member}, so it is not a valid SynergyCP configuration backup. Please re-upload it and run this script again."
  fi
done
printf "\t\t[OK]\n"

echo ""
echo "This script will:"
if [ "$INSTALL_APP" -eq 1 ]; then
  echo "  1. Install the latest SynergyCP version on this server."
else
  echo "  1. Use the SynergyCP installation already on this server."
fi
echo "  2. ERASE this installation's database and import ${DB_FILE} in its place."
echo "  3. Import the secret key and SSH keys from ${CONFIG_FILE}."
echo ""
echo "IMPORTANT: the backup must come from the same SynergyCP version that is installed."
echo "Restoring a backup into a different version is not supported."
echo ""
if [ "$ASSUME_YES" -ne 1 ]; then
  if [ -t 0 ]; then
    read -r -p "Continue? [y/N] " REPLY
    case "$REPLY" in
      y | Y | yes | YES) ;;
      *) exit-with-error "Restore cancelled." ;;
    esac
  else
    echo "No terminal available to confirm; continuing. (Pass -y to skip this notice.)"
  fi
fi

if [ "$INSTALL_APP" -eq 1 ]; then
  echo "Running app install process..."
  cd /tmp || exit-with-error "Failed to enter /tmp"
  # -O ensures a leftover app.sh from an earlier attempt is overwritten
  # instead of the download being saved under another name.
  wget -qO app.sh https://install.synergycp.com/bm/app.sh || exit-with-error "Failed to download the installer."
  bash app.sh || exit-with-error "Failed to install the application."
  cd "$START_DIR" || exit-with-error "Failed to return to $START_DIR"
  echo "App install finished."
fi

echo "Importing config..."

PHP_CONT=$(php-container)
if [ -z "$PHP_CONT" ]; then
  exit-with-error "Could not find the SynergyCP PHP container ($PHP_CONT_NAME). Is the application running?"
fi

CONT_EXTRACT_CFG_DIR="$CONT_TMP_DIR/extracted"
docker exec "$PHP_CONT" mkdir -p "$CONT_EXTRACT_CFG_DIR" || exit-with-error "Failed to create temp directory for backup extract"
docker cp "$START_DIR/$CONFIG_FILE" "$PHP_CONT:$CONT_TMP_DIR/$CONFIG_FILE" || exit-with-error "Failed to copy config into container"

{
cat <<EOF
set -e
cd "$CONT_APP_DIR"
tar -zxf "$CONT_TMP_DIR/$CONFIG_FILE" -C "$CONT_EXTRACT_CFG_DIR"
cp "$CONT_EXTRACT_CFG_DIR/.env" .
chmod 0600 .env
mkdir -p storage/keys
chmod 0700 storage/keys
cp "$CONT_EXTRACT_CFG_DIR/id_rsa.pub" "$CONT_EXTRACT_CFG_DIR/id_rsa" storage/keys
chmod 0600 storage/keys/id_rsa storage/keys/id_rsa.pub
php artisan config:cache
php artisan queue:restart
EOF
} | docker exec -i "$PHP_CONT" bash || exit-with-error "Failed to extract config into container"

# The extracted copy contains the panel's secret key and SSH private key;
# remove it now that it has been imported.
docker exec "$PHP_CONT" rm -rf "$CONT_TMP_DIR" || exit-with-error "Failed to remove the temporary config copy from the container"

cd "$SCP_ROOT_DIR" || exit-with-error "Failed to enter $SCP_ROOT_DIR"

echo -n "Config import finished, clearing database..."
{
cat <<EOF
SET FOREIGN_KEY_CHECKS = 0;
SET @tables = NULL;
SET @drop_tables = NULL;
SET GROUP_CONCAT_MAX_LEN=32768;

SELECT GROUP_CONCAT('\`', table_schema, '\`.\`', table_name, '\`') INTO @tables
FROM   information_schema.tables
WHERE  table_schema = (SELECT DATABASE());
SELECT IFNULL(@tables, 'something_nonexistent') INTO @tables;

SELECT CONCAT('DROP TABLE IF EXISTS ', @tables) INTO @drop_tables;
PREPARE    stmt FROM @drop_tables;
EXECUTE    stmt;
DEALLOCATE PREPARE stmt;
SET        FOREIGN_KEY_CHECKS = 1;
EOF
} | ./bin/scp-db || exit-with-error "Failed to clear database"

printf "\t\t[OK]\n"

echo -n "Database cleared. Importing database backup..."
# pipefail (set above) makes this fail when gunzip fails mid-stream, so a
# corrupt dump cannot produce a silently partial import.
(gunzip < "$START_DIR/$DB_FILE" | ./bin/scp-db) || exit-with-error "Failed to import database"
printf "\t\t[OK]\n"

# This is required so that the settings cache gets rewritten (and possibly other caches).
echo -n "Database backup imported. Clearing application cache..."
artisan-cmd system:cache:flush || exit-with-error "Failed to flush system cache"
printf "\t\t[OK]\n"

echo "Regenerating config files..."
artisan-cmd domain:sync
DOMAIN_SYNC_EXIT_CODE=$?

if [ $DOMAIN_SYNC_EXIT_CODE -gt 0 ]; then
  echo "Failed to sync domain config. Removing SSL then reattempting."
  artisan-cmd ssl:remove || exit-with-error "Failed to remove SSL"
  artisan-cmd domain:sync || exit-with-error "Failed to sync domain config"
fi

artisan-cmd theme:sync || exit-with-error "Failed to sync theme config"

# This is required e.g. to make sure that database migrations are run.
echo "Running application update..."
artisan-cmd version:update:complete || exit-with-error "Failed to update application"

echo "Application updated. Reinstalling packages..."
artisan-cmd pkg:reinstall || exit-with-error "Failed to reinstall packages"

echo ""
echo "========="
echo "All done! The database has been imported. You should now be able to access the application."
echo ""
echo "Remaining manual steps:"
echo "  1. Regenerate the DHCP server API key: run ./bin/install.sh in /scp/pxe/dhcp"
echo "     on your DHCP server and copy the generated information into SynergyCP."
echo "  2. Regenerate the File server API key: run ./bin/install.sh in /scp/pxe/file"
echo "     on your file server and copy the generated information into SynergyCP."
echo "  3. Run a switch scan on every switch so bandwidth polling is reconfigured."
echo "  4. Once everything checks out, delete ${DB_FILE}, ${CONFIG_FILE} and"
echo "     ${LOG_FILE} from this server."
