<?php

namespace Packages\Backup\App\Configuration;

use App\File\FileManager;
use App\System\SSH\Key\GlobalSSHKey;
use PharData;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ConfigurationDownloader {
  /**
   * @var FileManager
   */
  protected $file;

  /**
   * ConfigurationDownloader constructor.
   *
   * @param FileManager $file
   */
  public function __construct(FileManager $file) {
    $this->file = $file;
  }

  /**
   * @return BinaryFileResponse
   * @throws \Exception
   */
  public function download(): BinaryFileResponse {
    $archive = $this->setupArchive('/tmp/pkg-backup-config-download.tar');

    $this->addFile($archive, base_path('.env'));
    chmod($archive->getPath(), 0600);
    $this->addSshKey($archive);

    /** @var PharData $compressed */
    $compressed = $archive->compress(\Phar::GZ);
    chmod($compressed->getPath(), 0600);

    // TODO: we should somehow delete this after the entire request has completed, but that seems tough to do without
    // storing the file in PHP memory.
    return response()->download(
      $compressed->getPath(),
      'synergycp-config-backup.tar.gz'
    );
  }

  /**
   * @param string $dest
   *
   * @return PharData
   * @throws \Exception
   */
  protected function setupArchive(string $dest): PharData {
    $this->file->deleteIfExists($dest);
    $this->file->deleteIfExists($dest . '.gz');
    return new PharData($dest);
  }

  protected function addSshKey(PharData $archive) {
    $sshKey = new GlobalSSHKey();
    $this->addFileIfExists($archive, $sshKey->getPublicKeyFile());
    $this->addFileIfExists($archive, $sshKey->getPrivateKeyFile());
  }

  protected function addFileIfExists(PharData $archive, string $file) {
    if (file_exists($file)) {
      $this->addFile($archive, $file);
    }
  }

  protected function addFile(PharData $archive, string $file) {
    $archive->addFile($file, basename($file));
  }
}
