<?php

namespace Packages\Backup\App\Sources\Mysql;

use App\Shell;
use Packages\Backup\App\Archive;

/**
 * MySQLDump Handler.
 */
class MysqlDumpHandler implements Archive\Source\Handler\Handler
{
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
  public function handle(Archive\Archive $backup, $tempFile)
  {
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
  protected function run(Shell\ShellCommand $cmd, $command)
  {
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
  protected function dump(Archive\Archive $backup, $tempFile)
  {
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
  protected function command(Archive\Archive $backup)
  {
    $database = $this->getDatabase($backup);

    // Usar mysql para hacer backup solo de datos con manejo de errores
    return sprintf(
      "mysql -u %s -p%s -h %s %s -e \"SHOW TABLES\" | tail -n +2 | while read table; do echo \"-- Table: \$table\"; mysql -u %s -p%s -h %s %s -e \"SELECT * FROM \$table\" 2>/dev/null | sed \"1d\"; done | gzip -9",
      escapeshellarg(config('database.connections.mysql.username')),
      escapeshellarg(config('database.connections.mysql.password')),
      escapeshellarg(config('database.connections.mysql.host')),
      escapeshellarg($database),
      escapeshellarg(config('database.connections.mysql.username')),
      escapeshellarg(config('database.connections.mysql.password')),
      escapeshellarg(config('database.connections.mysql.host')),
      escapeshellarg($database)
    );
  }

  /**
   * @param Archive\Archive $backup
   *
   * @return string
   */
  protected function getDatabase(Archive\Archive $backup)
  {
    return $this->value
      ->byName($backup->source, MysqlDumpFields::DATABASE)
      ->value();
  }
}
