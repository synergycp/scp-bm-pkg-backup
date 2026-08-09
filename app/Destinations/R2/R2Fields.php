<?php

namespace Packages\Backup\App\Destinations\R2;

interface R2Fields {
  /**
   * The Cloudflare account ID that owns the R2 bucket.
   *
   * @var string
   */
  const ACCOUNT_ID = 'account_id';

  /**
   * The R2 API token's access key ID.
   *
   * @var string
   */
  const ACCESS_KEY_ID = 'access_key_id';

  /**
   * The R2 API token's secret access key.
   *
   * @var string
   */
  const SECRET_ACCESS_KEY = 'secret_access_key';

  /**
   * The bucket name to store backups in.
   *
   * @var string
   */
  const BUCKET = 'bucket';

  /**
   * Optional path prefix inside the bucket.
   *
   * @var string
   */
  const FOLDER = 'folder';
}
