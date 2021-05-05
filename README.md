## About

The backups package can make manual and automated backups of the Synergy database. It does not copy the bandwidth database to save space. The backups are copied off site via secure copy (SSH) authenticated by private key.

## Setup

Set up a remote linux server with a user, we will use `backups` as an example (any username will work). In Synergy, go to System > SSH keys and generate an SSH key if you do not have one yet. Copy the public key and add it to `~/.ssh/authorized_keys` while logged in as the `backups` user on the remote server. Make a folder anywhere in the filesystem, we will use `/home/backups/scp-db` as an example. Make sure the `backups` user has write access to that folder if creating the folder as a user other than `backups`.

On Synergy, go to System > Backup Destinations and create a new destination. Select secure copy via SSH as the Handler and put in the hostname, username, and folder that you chose earlier. The hostname can include a custom port via the syntax `example.org:22`. Now go to system > backup archives and select main database as the source and the newly-created destination. Click create archive and refresh the list after a minute (or longer if you have a lot of servers) to check the status and make sure that the backup is reported as `Finished`. Then go to System > Recurring Backups and set up a recurring backup using the same information.

## Restoring from a Backup

1. Gather up all the files. You'll need our [backup-restore.sh](https://install.synergycp.com/bm/scripts/backup-restore.sh) script, the backup.sql.gz file from your backup server, and your synergycp-config-backup.tar.gz config backup file.
2. Upload all 3 of those files, using the same names above, to any one folder on your new SynergyCP master server. Make sure that server is a fresh server with nothing installed and meets all of the [install requirements](https://synergycp.zendesk.com/hc/en-us/articles/115009722328-Installation).
3. Run the restore script: `bash backup-restore.sh` as the root user on the new server from the directory with those 3 files.
4. The DHCP & File server running on the main SynergyCP web instance will now be out of date, since new API key
   information was generated during the install process. To update them:

   ```bash
   cd /scp/pxe/dhcp
   ./bin/install.sh
   # Copy the info to the DHCP server on SynergyCP.

   cd /scp/pxe/file
   ./bin/install.sh
   # Copy the info to the File server on SynergyCP.
   ```

5. Run switch scans on every switch to update the bandwidth polling configuration file that is stored on disk. This can
   be done in bulk using the checkboxes on the switch list page.
