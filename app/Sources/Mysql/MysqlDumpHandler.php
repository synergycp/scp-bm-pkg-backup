<?php

namespace Packages\Backup\App\Sources\Mysql;

use App\Shell;
use Packages\Backup\App\Archive;
use Illuminate\Support\Facades\DB;

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
    try {
      $this->run(
        $this->shell->cmd()->setOutputFile($tempFile),
        $this->command($backup)
      );
    } catch (\Exception $e) {
      // Fallback to original mysqldump approach
      // Use Laravel's database connection to create backup
      $this->createCustomBackup($backup, $tempFile);
    }
  }

  /**
   * Create backup using direct database queries instead of mysqldump
   */
  protected function createCustomBackup(Archive\Archive $backup, $tempFile)
  {
    $database = $this->getDatabase($backup);
    $connection = DB::connection('mysql');

    // Get all tables in the database
    $tables = $connection->select("SHOW TABLES FROM `{$database}`");
    $tableNames = array_column($tables, "Tables_in_{$database}");

    $backupContent = "-- Custom MySQL Backup\n";
    $backupContent .= "-- Generated on: " . date('Y-m-d H:i:s') . "\n";
    $backupContent .= "-- Database: {$database}\n\n";

    foreach ($tableNames as $tableName) {
      $backupContent .= $this->backupTable($connection, $database, $tableName);
    }
    // Create a temporary file with the backup content
    $tempContentFile = $tempFile . '.tmp';
    file_put_contents($tempContentFile, $backupContent);

    // Move the temporary file to the final location
    $this->run(
      $this->shell->cmd(),
      "mv " . escapeshellarg($tempContentFile) . " " . escapeshellarg($tempFile)
    );

    // Remove existing .gz file if it exists
    $gzFile = $tempFile . '-custom.gz';
    if (file_exists($gzFile)) {
      unlink($gzFile);
    }

    // Compress the file
    $this->run(
      $this->shell->cmd(),
      "gzip -9 " . escapeshellarg($tempFile)
    );
  }

  /**
   * Backup a single table
   */
  protected function backupTable($connection, $database, $tableName)
  {
    $output = "\n-- Table structure for table `{$tableName}`\n";
    $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";

    // Get table structure
    $createTable = $connection->select("SHOW CREATE TABLE `{$database}`.`{$tableName}`");
    if (!empty($createTable)) {
      $output .= $createTable[0]->{'Create Table'} . ";\n\n";
    }

    // Get table data
    $output .= "-- Data for table `{$tableName}`\n";

    try {
      $rows = $connection->select("SELECT * FROM `{$database}`.`{$tableName}`");
      if (!empty($rows)) {
        $columns = array_keys((array) $rows[0]);
        $output .= "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES\n";

        $values = [];
        foreach ($rows as $row) {
          $rowValues = [];
          foreach ($columns as $column) {
            $value = $row->$column;
            if ($value === null) {
              $rowValues[] = 'NULL';
            } else {
              $rowValues[] = "'" . addslashes($value) . "'";
            }
          }
          $values[] = "(" . implode(', ', $rowValues) . ")";
        }

        $output .= implode(",\n", $values) . ";\n";
      }
    } catch (\Exception $e) {
      $output .= "-- Error reading data from table {$tableName}: " . $e->getMessage() . "\n";
    }

    return $output;
  }

  /**
   * @param Archive\Archive $backup
   *
   * @return string
   */
  protected function command(Archive\Archive $backup)
  {
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
  protected function getDatabase(Archive\Archive $backup)
  {
    return $this->value
      ->byName($backup->source, MysqlDumpFields::DATABASE)
      ->value();
  }
}
