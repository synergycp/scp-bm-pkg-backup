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
    $this->writeCredentialsFile($credentialsFile);

    try {
      $this->dump($backup, $tempFile, $credentialsFile);
      $this->verify($tempFile);
    } finally {
      if (is_file($credentialsFile)) {
        unlink($credentialsFile);
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
   *
   * @throws \Exception
   */
  protected function dump(Archive\Archive $backup, $tempFile, $credentialsFile) {
    $this->run(
      $this->shell->cmd()->setOutputFile($tempFile),
      // pipefail makes the dump fail when mysqldump fails, even though gzip
      // (the last command in the pipeline) exits successfully.
      sprintf(
        'bash -o pipefail -c %s',
        escapeshellarg($this->command($backup, $credentialsFile))
      )
    );
  }

  /**
   * Ensure the dump produced a non-empty, valid gzip file.
   *
   * @param string $tempFile
   *
   * @throws \Exception
   */
  protected function verify($tempFile) {
    $file = escapeshellarg($tempFile);

    $this->run(
      $this->shell->cmd(),
      sprintf('test -s %s && gzip -t < %s', $file, $file)
    );
  }
  /**
   * @param Archive\Archive $backup
   * @param string          $credentialsFile
   *
   * @return string
   */
  protected function command(Archive\Archive $backup, $credentialsFile) {
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
    ];

    return implode(' ', $arguments);
  }

  /**
   * Write the MySQL client credentials to a file only readable by us.
   *
   * @param string $path
   *
   * @throws \Exception
   */
  protected function writeCredentialsFile($path) {
    $contents = implode("\n", [
      '[client]',
      sprintf('user="%s"', $this->escapeCnf(config('database.connections.mysql.username'))),
      sprintf('password="%s"', $this->escapeCnf(config('database.connections.mysql.password'))),
      sprintf('host="%s"', $this->escapeCnf(config('database.connections.mysql.host'))),
      '',
    ]);

    // Create the file empty and lock its permissions down before the
    // credentials are written to it.
    if (
      file_put_contents($path, '') === false ||
      !chmod($path, 0600) ||
      file_put_contents($path, $contents) === false
    ) {
      throw new \Exception(
        sprintf('Unable to write MySQL credentials file %s for backup.', $path)
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
