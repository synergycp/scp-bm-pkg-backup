## About
The backups package can make manual and automated backups of the Synergy database. It does not copy the bandwidth database to save space. The backups are copied off site via secure copy (SSH) authenticated by private key.

## Setup
Set up a remote linux server with a user, we will use `backups` as an example (any username will work). In Synergy, go to System > SSH keys and generate an SSH key if you do not have one yet. Copy the public key and add it to `~/.ssh/authorized_keys` while logged in as the `backups` user on the remote server. Make a folder anywhere in the filesystem, we will use `/home/backups/scp-db` as an example. Make sure the `backups` user has write access to that folder if creating the folder as a user other than `backups`. 

1. On Synergy, go to System > Backups > Configure Remote Backups. Select secure copy via SSH as the Handler and put in the hostname, username, and folder that you chose earlier. The hostname can include a custom port via the syntax `example.org:22`.
2. Go to System > Backups > View All Backups and create a backup. Select main database as the source and the newly-created destination. Click create archive and refresh the list after a minute (or longer if you have a large database) to check the status and make sure that the backup is reported as `Finished`.
3. Go to System > Backups > Configure Recurring Backups and set up a recurring backup using the same information.
4. Go to System > Backups > Save Configuration Backup, read the instructions there, and download the backup. Make sure to store it in a safe place, ideally separate from the main SynergyCP backup. 

## Restoring from a Backup
1. Start with a fresh OS that will serve as the new SynergyCP master server, following the instructions from our [installation page](https://synergycp.zendesk.com/hc/en-us/articles/115009722328-Installation) for hardware requirements. *DO NOT* install SynergyCP.
2. Copy the backup file from the backup server to your new SynergyCP webserver using scp. For example, you could run this on your new SynergyCP server and make sure to replace the credentials and path based on your backup server's info (remove `sudo` if on root). The backup file *must* be named `database.gz`. 

    ```bash
    cd ~
    scp user@your-backup-server:/path/to/backup.sql.gz database.gz
    ```
 
3. Copy the configuration file from wherever you backed it up during the initial SynergyCP backups package setup to the new SynergyCP master server. This configuration backup is usually saved separately from the database backup as it stores the encryption keys for the backed up database.

4. Run this command as the root user in the same directory where you copied the two previously mentioned files:

   ```bash
   wget http://install.synergycp.com/bm/scripts/backup-restore.sh && bash backup-restore.sh
   ```

5. Run switch scans on every switch to update the bandwidth polling configuration file that is stored on disk. This can 
   be done in bulk using the checkboxes on the switch list page. 
