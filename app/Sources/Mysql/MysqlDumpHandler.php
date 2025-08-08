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
    $this->dump($backup, $tempFile);
  }

  /**
   * @param string $tempFile
   *
   * @throws \Exception
   */
  protected function makeOutputDir($tempFile)
  {
    $dir = dirname($tempFile);
    $this->run($this->shell->cmd(), "mkdir -p $dir");
  }

  /**
   * @param Shell\ShellCommand $cmd
   * @param string             $command
   *
   * @throws \Exception
   */
  protected function run(Shell\ShellCommand $cmd, $command) {
    $cmd->exec($command);

    if ($errors = $cmd->getErrors()) {
      throw new \Exception(sprintf('Error with %s: %s', $command, $errors));
    }
  }

  /**
   * @param Archive\Archive $backup
   * @param string          $tempFile
   *
   * @throws \Exception
   */
  protected function dump(Archive\Archive $backup, $tempFile) {
    $this->run(
      $this->shell->cmd()->setOutputFile($tempFile),
      $this->command($backup)
    );
  }

  /**
   * @param Archive\Archive $backup
   *
   * @return string
   */
  protected function command(Archive\Archive $backup) {
    $arguments = [
      $this->exec,

      // --defaults-file option must be the first option.
      // '--defaults-file=$(bash -c "echo ~")/.my.cnf',

      sprintf(
        '-u %s -p%s -h %s',
        escapeshellarg(config('database.connections.mysql.username')),
        escapeshellarg(config('database.connections.mysql.password')),
        escapeshellarg(config('database.connections.mysql.host'))
      ),

      // Efficient, exact MyISAM backups.
      '--single-transaction --quick',

      // The database that is getting exported.
      $this->getDatabase($backup),

      // Pipe the output through gzip with maximum compression
      '| gzip -9',
    ];

    return implode(' ', $arguments);
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
