## About
The backups package can make manual and automated backups of the Synergy database. It does not copy the bandwidth database to save space. The backups are copied off site via secure copy (SSH) authenticated by private key.

## Setup
Set up a remote linux server with a user, we will use `backups` as an example (any username will work). In Synergy, go to System > SSH keys and generate an SSH key if you do not have one yet. Copy the public key and add it to `~/.ssh/authorized_keys` while logged in as the `backups` user on the remote server. Make a folder anywhere in the filesystem, we will use `/home/backups/scp-db` as an example. Make sure the `backups` user has write access to that folder if creating the folder as a user other than `backups`. 

On Synergy, go to System > Backup Destinations and create a new destination. Select secure copy via SSH as the Handler and put in the hostname, username, and folder that you chose earlier. Now go to system > backup archives and select main database as the source and the newly-created destination. Click create archive and refresh the list after a minute (or longer if you have a lot of servers) to check the status and make sure that the backup is reported as `Finished`. Then go to System > Recurring Backups and set up a recurring backup using the same information. 
