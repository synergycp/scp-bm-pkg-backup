#!/usr/bin/env bash

cp dev/restore.sh /scp/install.synergycp.com/bm/scripts/backup-restore.sh

echo "Backup script exported! It can now be synced with:"
echo "cd /scp/install.synergycp.com"
echo "git add bm/scripts/backup-restore.sh"
echo "git status"
echo "git commit -m 'Latest backup restore script'"
echo "git push"
echo "./sync.sh"
