<?php

namespace Packages\Backup\App\Sources\Mysql;

use App\Shell;
use Packages\Backup\App\Archive;

/**
 * MySQLDump Handler.
 */
class MysqlDumpHandler implements Archive\Source\Handler\Handler {
  /**
   * @var Shell\Shell
   */
  protected $shell;

  /**
   * @var Archive\Field\ValueService
   */
  protected $value;

  /**
   * @var string
   */
  protected $exec = 'mysqldump';

  /**
   * @param Shell\Shell                $shell
   * @param Archive\Field\ValueService $value
   */
  public function __construct(
    Shell\Shell $shell,
    Archive\Field\ValueService $value
  ) {
    $this->shell = $shell;
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function handle(Archive\Archive $backup, $tempFile) {
    $this->makeOutputDir($tempFile);

    $credentialsFile = $tempFile . '.cnf';
    $encryptionKeyFile = $tempFile . '.key';

    try {
      $this->writeSecretFile(
        $credentialsFile,
        $this->credentialsFileContents()
      );
      $this->writeSecretFile($encryptionKeyFile, $this->encryptionKey());

      $this->dump($backup, $tempFile, $credentialsFile, $encryptionKeyFile);
      $this->verify($tempFile, $encryptionKeyFile);
    } finally {
      foreach ([$credentialsFile, $encryptionKeyFile] as $secretFile) {
        if (is_file($secretFile)) {
          unlink($secretFile);
        }
      }
    }
  }

  /**
   * @param string $tempFile
   *
   * @throws \Exception
   */
  protected function makeOutputDir($tempFile) {
    $this->run(
      $this->shell->cmd(),
      sprintf('mkdir -p %s', escapeshellarg(dirname($tempFile)))
    );
  }

  /**
   * @param Shell\ShellCommand $cmd
   * @param string             $command
   *
   * @throws \Exception
   */
  protected function run(Shell\ShellCommand $cmd, $command) {
    $cmd->exec($command);

    // Drain stderr before reading the exit code so a chatty command cannot
    // deadlock on a full pipe buffer.
    $errors = $cmd->getErrors();

    if ($status = $cmd->getExitCode()) {
      throw new \Exception(
        sprintf('Error %d with %s: %s', $status, $command, $errors)
      );
    }
  }

  /**
   * @param Archive\Archive $backup
   * @param string          $tempFile
   * @param string          $credentialsFile
   * @param string          $encryptionKeyFile
   *
   * @throws \Exception
   */
  protected function dump(
    Archive\Archive $backup,
    $tempFile,
    $credentialsFile,
    $encryptionKeyFile
  ) {
    $this->run(
      $this->shell->cmd()->setOutputFile($tempFile),
      // pipefail makes the dump fail when mysqldump fails, even though the
      // last command in the pipeline exits successfully.
      sprintf(
        'bash -o pipefail -c %s',
        escapeshellarg(
          $this->command($backup, $credentialsFile, $encryptionKeyFile)
        )
      )
    );
  }

  /**
   * Ensure the dump produced a non-empty file that decrypts to valid gzip
   * data with the panel's key.
   *
   * @param string $tempFile
   * @param string $encryptionKeyFile
   *
   * @throws \Exception
   */
  protected function verify($tempFile, $encryptionKeyFile) {
    $file = escapeshellarg($tempFile);

    $this->run(
      $this->shell->cmd(),
      sprintf(
        'bash -o pipefail -c %s',
        escapeshellarg(sprintf(
          'test -s %s && %s < %s | gzip -t',
          $file,
          $this->decryptCommand($encryptionKeyFile),
          $file
        ))
      )
    );
  }

  /**
   * @param Archive\Archive $backup
   * @param string          $credentialsFile
   * @param string          $encryptionKeyFile
   *
   * @return string
   */
  protected function command(
    Archive\Archive $backup,
    $credentialsFile,
    $encryptionKeyFile
  ) {
    $arguments = [
      $this->exec,

      // Keeps the credentials out of the process list.
      // --defaults-extra-file must be the first option.
      sprintf('--defaults-extra-file=%s', escapeshellarg($credentialsFile)),

      // Consistent snapshot of InnoDB tables without locking out writes.
      // NOTE: MyISAM tables are copied without a lock and may be inconsistent
      // if written to during the dump.
      '--single-transaction --quick',

      // Stored procedures, functions, scheduled events and triggers are not
      // all included by default.
      '--routines --events --triggers',

      // The database that is getting exported.
      escapeshellarg($this->getDatabase($backup)),

      // Pipe the output through gzip. Level 6 compresses nearly as well as 9
      // at a fraction of the CPU time, keeping the dump window short.
      '| gzip -f -6',

      // Encrypt with the panel's secret key so the backup can only be read
      // together with the configuration backup. The restore script and docs
      // depend on these exact parameters.
      sprintf('| %s', $this->encryptCommand($encryptionKeyFile)),
    ];

    return implode(' ', $arguments);
  }

  /**
   * @param string $encryptionKeyFile
   *
   * @return string
   */
  protected function encryptCommand($encryptionKeyFile) {
    return sprintf(
      'openssl enc -aes-256-cbc -pbkdf2 -iter 100000 -md sha256 -salt -pass file:%s',
      escapeshellarg($encryptionKeyFile)
    );
  }

  /**
   * @param string $encryptionKeyFile
   *
   * @return string
   */
  protected function decryptCommand($encryptionKeyFile) {
    return sprintf(
      'openssl enc -d -aes-256-cbc -pbkdf2 -iter 100000 -md sha256 -pass file:%s',
      escapeshellarg($encryptionKeyFile)
    );
  }

  /**
   * The passphrase backups are encrypted with: the panel's application key,
   * which is included in (and only in) the configuration backup.
   *
   * @return string
   *
   * @throws \Exception
   */
  protected function encryptionKey() {
    $key = (string) config('app.key');

    if ($key === '') {
      throw new \Exception(
        'The application key is empty; refusing to create an unencrypted backup.'
      );
    }

    return $key;
  }

  /**
   * @return string
   */
  protected function credentialsFileContents() {
    return implode("\n", [
      '[client]',
      sprintf('user="%s"', $this->escapeCnf(config('database.connections.mysql.username'))),
      sprintf('password="%s"', $this->escapeCnf(config('database.connections.mysql.password'))),
      sprintf('host="%s"', $this->escapeCnf(config('database.connections.mysql.host'))),
      '',
    ]);
  }

  /**
   * Write a secret to a file only readable by us.
   *
   * @param string $path
   * @param string $contents
   *
   * @throws \Exception
   */
  protected function writeSecretFile($path, $contents) {
    // Create the file empty and lock its permissions down before the
    // secret is written to it.
    if (
      file_put_contents($path, '') === false ||
      !chmod($path, 0600) ||
      file_put_contents($path, $contents) === false
    ) {
      throw new \Exception(
        sprintf('Unable to write secret file %s for backup.', $path)
      );
    }
  }

  /**
   * Escape a value for use inside a double quoted my.cnf string.
   *
   * @param string $value
   *
   * @return string
   */
  protected function escapeCnf($value) {
    return addcslashes((string) $value, "\\\"");
  }

  /**
   * @param Archive\Archive $backup
   *
   * @return string
   */
  protected function getDatabase(Archive\Archive $backup) {
    return $this->value
      ->byName($backup->source, MysqlDumpFields::DATABASE)
      ->value();
  }
}
