#!/usr/bin/env bash

# Run script as root
[ "$(whoami)" != "root" ] && exec sudo -- "$0" "$@"

exit-with-error() {
  ERROR=$?
  if [ $ERROR -eq 0 ]; then
    ERROR=1
  fi
  echo ""
  echo "=========="
  echo "ERROR: $@" " (error code: $ERROR)" >&2
  exit ${ERROR}
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
SCP_ROOT_DIR=/scp


if [ "$(which docker)" != "" ]; then
  exit-with-error "Docker is already installed. This probably means that SynergyCP has already been installed on this server. Currently, backup recovery must be done on a fresh Debian OS with nothing else installed."
fi

echo -n "Checking for database file and config file..."
if [ ! -f $DB_FILE ]; then
  exit-with-error "Could not find database file. It must be named ${DB_FILE} in the directory this script was run from ($(pwd))."
fi
if [ ! -f $CONFIG_FILE ]; then
  exit-with-error "Could not find config file. It must be named ${CONFIG_FILE} in the directory this script was run from ($(pwd))."
fi

printf "\t\t\t[OK]\n"

echo "Running app install process..."
cd /tmp && wget -q https://install.synergycp.com/bm/app.sh && bash app.sh || exit-with-error "Failed to install the application."

clear
echo "App install finished. Importing config..."

PHP_CONT=$(
	docker ps |
	grep scp-bm-app_php_server |
	head -n1 |
	awk '{print $NF}'
)

CONT_EXTRACT_CFG_DIR="$CONT_TMP_DIR/extracted"
docker exec "$PHP_CONT" mkdir -p "$CONT_EXTRACT_CFG_DIR" || exit-with-error "Failed to create temp directory for backup extract"
docker cp "$START_DIR/$CONFIG_FILE" "$PHP_CONT:$CONT_TMP_DIR/$CONFIG_FILE" || exit-with-error "Failed to copy config into container"

{
cat <<EOF
tar -zxf "$CONT_TMP_DIR/$CONFIG_FILE" -C "$CONT_EXTRACT_CFG_DIR" || exit \$?
cp "$CONT_EXTRACT_CFG_DIR/.env" . || exit \$?
chmod 0600 .env || exit \$?
mkdir -p storage/keys || exit \$?
chmod 0700 storage/keys || exit \$?
cp "$CONT_EXTRACT_CFG_DIR/id_rsa.pub" "$CONT_EXTRACT_CFG_DIR/id_rsa" storage/keys || exit \$?
chmod 0600 storage/keys/id_rsa storage/keys/id_rsa.pub || exit \$?
php artisan config:cache || exit \$?
php artisan queue:restart || exit \$?
EOF
} | docker exec -i "$PHP_CONT" bash || exit-with-error "Failed to extract config into container"

cd $SCP_ROOT_DIR

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
