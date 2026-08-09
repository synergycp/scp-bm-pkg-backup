## About

The Backups package makes manual and automated (recurring) backups of the Synergy database. It does not copy the bandwidth database, to save space.

Backups are encrypted (AES-256) with the panel's secret key before leaving the server, so they can only be restored together with the panel's configuration backup. They can be copied off site to:

- any Linux server over secure copy (SSH), authenticated by private key
- a Backblaze B2 bucket
- a Cloudflare R2 bucket

Optional retention limits (per recurring schedule and/or per destination) automatically delete the oldest backups, including the remote files.

## Setup

Please refer to [the package documentation](https://kb.synergycp.com/docs/packages/backups/) to get started.

Need to restore? Please refer to the [restore documentation](https://kb.synergycp.com/docs/backuprestore/restore/).

Looking for further assistance? Please [contact us](https://kb.synergycp.com/#contacting-support).

## Development

```bash
composer install
composer test              # PHPUnit suite (tests/)
bash dev/restore-tests.sh  # sandboxed tests for the disaster recovery script
```

`dev/restore.sh` is the source of truth for the customer-facing restore script served at `https://install.synergycp.com/bm/scripts/backup-restore.sh` — CI publishes it on every push to master.

To release: bump `semver` in `scp-package.json` and `version` in `composer.json` / `admin/package.json`, add changelog entries in `scp-package.json`, and push to master. GitHub Actions runs the tests, builds the admin frontend, and deploys the package.
