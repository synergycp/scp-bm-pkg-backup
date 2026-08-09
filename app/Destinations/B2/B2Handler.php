<?php

namespace Packages\Backup\App\Destinations\B2;

use Illuminate\Support\Facades\Http;
use Packages\Backup\App\Archive;

/**
 * Backblaze B2 Handler.
 *
 * Stores backups in a Backblaze B2 bucket using the B2 native API. Requires
 * an application key with read/write (and delete, for retention) access to
 * the configured bucket.
 */
class B2Handler implements Archive\Dest\Handler\Handler {
  /**
   * @var string
   */
  const AUTHORIZE_URL =
    'https://api.backblazeb2.com/b2api/v2/b2_authorize_account';

  /**
   * B2's limit for a single-request upload (5 GB).
   *
   * @var int
   */
  const MAX_UPLOAD_BYTES = 5000000000;

  /**
   * @var int
   */
  const CONNECT_TIMEOUT = 30;

  /**
   * @var int
   */
  const API_TIMEOUT = 120;

  /**
   * @var int
   */
  const UPLOAD_TIMEOUT = 3600;

  /**
   * @var Archive\Field\ValueService
   */
  protected $value;

  /**
   * @param Archive\Field\ValueService $value
   */
  public function __construct(Archive\Field\ValueService $value) {
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function copy(Archive\Dest\Dest $dest, $tempFile, $destFile) {
    $values = $this->value->all($dest);
    $fileName = $this->fileName($values, $destFile);

    $size = is_file($tempFile) ? filesize($tempFile) : false;
    if ($size === false) {
      throw new \Exception(
        sprintf('Backup temp file %s is missing.', $tempFile)
      );
    }
    if ($size > self::MAX_UPLOAD_BYTES) {
      throw new \Exception(
        'Backup is larger than the 5 GB Backblaze B2 single-file upload limit.'
      );
    }

    $auth = $this->authorize($values);
    $bucketId = $this->bucketId($auth, $values);

    try {
      $this->upload($auth, $bucketId, $fileName, $tempFile);
    } catch (\Exception $exc) {
      // B2 upload URLs are transient by design: the API contract is to fetch
      // a fresh upload URL and retry once when an upload fails.
      $this->upload($auth, $bucketId, $fileName, $tempFile);
    }
  }

  /**
   * @inheritDoc
   */
  public function delete(Archive\Dest\Dest $dest, $destFile) {
    $values = $this->value->all($dest);
    $fileName = $this->fileName($values, $destFile);

    $auth = $this->authorize($values);
    $bucketId = $this->bucketId($auth, $values);

    $versions = $this->api($auth, 'b2_list_file_versions', [
      'bucketId' => $bucketId,
      'startFileName' => $fileName,
      'prefix' => $fileName,
    ])->json('files') ?: [];

    foreach ($versions as $version) {
      if ($version['fileName'] !== $fileName) {
        continue;
      }

      $this->api($auth, 'b2_delete_file_version', [
        'fileName' => $version['fileName'],
        'fileId' => $version['fileId'],
      ]);
    }
  }

  /**
   * The full file name inside the bucket, including the optional folder.
   *
   * @param object $values
   * @param string $destFile
   *
   * @return string
   */
  protected function fileName($values, $destFile) {
    $folder = trim((string) $values->value(B2Fields::FOLDER), "/ \t");

    return $folder === '' ? $destFile : $folder . '/' . $destFile;
  }

  /**
   * Log in to the B2 API.
   *
   * @param object $values
   *
   * @return array the b2_authorize_account response
   *
   * @throws \Exception
   */
  protected function authorize($values) {
    $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
      ->timeout(self::API_TIMEOUT)
      ->withBasicAuth(
        (string) $values->value(B2Fields::KEY_ID),
        (string) $values->value(B2Fields::APPLICATION_KEY)
      )
      ->get(self::AUTHORIZE_URL);

    if ($response->failed()) {
      throw new \Exception(
        sprintf(
          'Backblaze B2 authorization failed (check the key_id and application_key): %s',
          $this->error($response)
        )
      );
    }

    return $response->json();
  }

  /**
   * Resolve the ID of the configured bucket.
   *
   * @param array  $auth
   * @param object $values
   *
   * @return string
   *
   * @throws \Exception
   */
  protected function bucketId(array $auth, $values) {
    $bucket = (string) $values->value(B2Fields::BUCKET);

    // Keys restricted to a single bucket already carry its ID and may not
    // call b2_list_buckets.
    $allowedId = isset($auth['allowed']['bucketId'])
      ? $auth['allowed']['bucketId']
      : null;

    if ($allowedId) {
      $allowedName = isset($auth['allowed']['bucketName'])
        ? $auth['allowed']['bucketName']
        : null;

      if ($allowedName !== null && $allowedName !== $bucket) {
        throw new \Exception(
          sprintf(
            'The Backblaze B2 application key is restricted to bucket "%s" but the destination is configured for bucket "%s".',
            $allowedName,
            $bucket
          )
        );
      }

      return $allowedId;
    }

    $buckets = $this->api($auth, 'b2_list_buckets', [
      'accountId' => $auth['accountId'],
      'bucketName' => $bucket,
    ])->json('buckets') ?: [];

    foreach ($buckets as $candidate) {
      if ($candidate['bucketName'] === $bucket) {
        return $candidate['bucketId'];
      }
    }

    throw new \Exception(
      sprintf('Backblaze B2 bucket "%s" was not found on the account.', $bucket)
    );
  }

  /**
   * Upload the file via a fresh B2 upload URL.
   *
   * @param array  $auth
   * @param string $bucketId
   * @param string $fileName
   * @param string $tempFile
   *
   * @throws \Exception
   */
  protected function upload(array $auth, $bucketId, $fileName, $tempFile) {
    $target = $this->api($auth, 'b2_get_upload_url', [
      'bucketId' => $bucketId,
    ])->json();

    $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
      ->timeout(self::UPLOAD_TIMEOUT)
      ->withHeaders([
        'Authorization' => $target['authorizationToken'],
        'X-Bz-File-Name' => $this->encodeFileName($fileName),
        'Content-Type' => 'b2/x-auto',
        // B2 verifies the upload against this checksum before accepting it.
        'X-Bz-Content-Sha1' => sha1_file($tempFile),
      ])
      ->send('POST', $target['uploadUrl'], [
        'body' => fopen($tempFile, 'r'),
      ]);

    if ($response->failed()) {
      throw new \Exception(
        sprintf('Backblaze B2 upload failed: %s', $this->error($response))
      );
    }
  }

  /**
   * Run a B2 API call with the account-level authorization token.
   *
   * @param array  $auth
   * @param string $endpoint
   * @param array  $data
   *
   * @return mixed the successful response
   *
   * @throws \Exception
   */
  protected function api(array $auth, $endpoint, array $data) {
    $response = Http::connectTimeout(self::CONNECT_TIMEOUT)
      ->timeout(self::API_TIMEOUT)
      ->withHeaders(['Authorization' => $auth['authorizationToken']])
      ->post(sprintf('%s/b2api/v2/%s', $auth['apiUrl'], $endpoint), $data);

    if ($response->failed()) {
      throw new \Exception(
        sprintf('Backblaze B2 %s failed: %s', $endpoint, $this->error($response))
      );
    }

    return $response;
  }

  /**
   * Percent-encode a file name for the X-Bz-File-Name header, keeping the
   * path separators.
   *
   * @param string $fileName
   *
   * @return string
   */
  protected function encodeFileName($fileName) {
    return implode('/', array_map('rawurlencode', explode('/', $fileName)));
  }

  /**
   * A readable error from a failed B2 response.
   *
   * @param mixed $response
   *
   * @return string
   */
  protected function error($response) {
    $message = $response->json('message');

    return sprintf(
      'HTTP %d: %s',
      $response->status(),
      $message ?: $response->body()
    );
  }
}
