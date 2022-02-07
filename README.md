## About
The backups package can make manual and automated backups of the Synergy database. It does not copy the bandwidth database to save space. The backups are copied off site via secure copy (SSH) authenticated by private key.

## Setup
Set up a remote linux server with a user, we will use `backups` as an example (any username will work). In Synergy, go to System > SSH keys and generate an SSH key if you do not have one yet. Copy the public key and add it to `~/.ssh/authorized_keys` while logged in as the `backups` user on the remote server. Make a folder anywhere in the filesystem, we will use `/home/backups/scp-db` as an example. Make sure the `backups` user has write access to that folder if creating the folder as a user other than `backups`. 

On Synergy, go to System > Backup Destinations and create a new destination. Select secure copy via SSH as the Handler and put in the hostname, username, and folder that you chose earlier. The hostname can include a custom port via the syntax `example.org:22`. Now go to system > backup archives and select main database as the source and the newly-created destination. Click create archive and refresh the list after a minute (or longer if you have a lot of servers) to check the status and make sure that the backup is reported as `Finished`. Then go to System > Recurring Backups and set up a recurring backup using the same information. 

## Restoring from a Backup
1. Copy the backup file from the backup server to your SynergyCP webserver using scp. Run this on your SynergyCP webserver and make sure to replace the credentials and path based on your backup server's info (remove `sudo` if on root). 

    ```bash
    cd /scp
    sudo scp user@your-backup-server:/path/to/backup.sql.gz backup.sql.gz
    ```
 
2. Extract the archive (remove `sudo` if on root): 

    ```bash
    sudo gunzip backup.sql.gz
    ```
 
3. Delete all existing tables (remove `sudo` if on root):
    
    ```bash
    cd /scp
    sudo /scp/bin/scp-db
    ````
    
    ```sql
    SET FOREIGN_KEY_CHECKS = 0; 
    SET @tables = NULL;
    SET GROUP_CONCAT_MAX_LEN=32768;

    SELECT GROUP_CONCAT('`', table_schema, '`.`', table_name, '`') INTO @tables
    FROM   information_schema.tables 
    WHERE  table_schema = (SELECT DATABASE());
    SELECT IFNULL(@tables, '') INTO @tables;

    SET        @tables = CONCAT('DROP TABLE IF EXISTS ', @tables);
    PREPARE    stmt FROM @tables;
    EXECUTE    stmt;
    DEALLOCATE PREPARE stmt;
    SET        FOREIGN_KEY_CHECKS = 1;
    exit
    ```
    
4. Import the archive to the database (remove `sudo` if on root).

    ```bash
    cat backup.sql | sudo /scp/bin/scp-db
    ```
    
5. The DHCP & File server running on the main SynergyCP web instance will now be out of date, since new API key 
   information was generated during the install process. To update them:
   
   ```bash
   cd /scp/pxe/dhcp
   ./bin/install.sh
   # Copy the info to the DHCP server on SynergyCP.
  
   cd /scp/pxe/file
   ./bin/install.sh
   # Copy the info to the File server on SynergyCP.
   ```

6. Run switch scans on every switch to update the bandwidth polling configuration file that is stored on disk. This can 
   be done in bulk using the checkboxes on the switch list page. 
