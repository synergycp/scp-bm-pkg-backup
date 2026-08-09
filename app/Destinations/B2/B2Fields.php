<?php

namespace Packages\Backup\App\Destinations\B2;

interface B2Fields {
  /**
   * The Backblaze application key ID.
   *
   * @var string
   */
  const KEY_ID = 'key_id';

  /**
   * The Backblaze application key secret.
   *
   * @var string
   */
  const APPLICATION_KEY = 'application_key';

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
