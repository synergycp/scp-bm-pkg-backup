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

    // Create a temporary file and write header
    $tempContentFile = $tempFile . '.tmp';
    $handle = fopen($tempContentFile, 'w');

    if (!$handle) {
      throw new \Exception('Cannot create temporary backup file');
    }

    // Write header
    fwrite($handle, "-- Custom MySQL Backup\n");
    fwrite($handle, "-- Generated on: " . date('Y-m-d H:i:s') . "\n");
    fwrite($handle, "-- Database: {$database}\n\n");

    // Process each table
    foreach ($tableNames as $tableName) {
      $this->backupTableToFile($connection, $database, $tableName, $handle);
    }

    fclose($handle);

    // Move the temporary file to the final location
    $this->run(
      $this->shell->cmd(),
      "mv " . escapeshellarg($tempContentFile) . " " . escapeshellarg($tempFile)
    );

    // Compress the file
    $this->run(
      $this->shell->cmd(),
      "gzip -c -9 " . escapeshellarg($tempFile) . " > " . escapeshellarg($tempFile) . ".tmp && mv " . escapeshellarg($tempFile) . ".tmp " . escapeshellarg($tempFile)
    );
  }

  /**
   * Backup a single table to a file handle.
   */
  protected function backupTableToFile($connection, $database, $tableName, $handle)
  {
    // Write table header
    fwrite($handle, "\n-- Table structure for table `{$tableName}`\n");
    fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

    // Get table structure
    $createTable = $connection->select("SHOW CREATE TABLE `{$database}`.`{$tableName}`");
    if (!empty($createTable)) {
      fwrite($handle, $createTable[0]->{'Create Table'} . ";\n\n");
    }

    // Write data header
    fwrite($handle, "-- Data for table `{$tableName}`\n");

    try {
      // Get total row count for pagination
      $countResult = $connection->select("SELECT COUNT(*) as total FROM `{$database}`.`{$tableName}`");
      $totalRows = $countResult[0]->total;

      if ($totalRows > 0) {
        // Get column names from first row
        $firstRow = $connection->select("SELECT * FROM `{$database}`.`{$tableName}` LIMIT 1");
        if (!empty($firstRow)) {
          $columns = array_keys((array) $firstRow[0]);
          fwrite($handle, "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES\n");

          // Process data in chunks to avoid memory issues
          $chunkSize = 500; // Process 500 rows at a time
          $offset = 0;
          $firstChunk = true;

          while ($offset < $totalRows) {
            $rows = $connection->select("SELECT * FROM `{$database}`.`{$tableName}` LIMIT {$chunkSize} OFFSET {$offset}");

            if (!empty($rows)) {
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

              // Add comma separator between chunks (except for first chunk)
              if (!$firstChunk) {
                fwrite($handle, ",\n");
              }

              fwrite($handle, implode(",\n", $values));
              $firstChunk = false;
            }

            $offset += $chunkSize;
          }

          fwrite($handle, ";\n");
        }
      }
    } catch (\Exception $e) {
      fwrite($handle, "-- Error reading data from table {$tableName}: " . $e->getMessage() . "\n");
    }
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
      '| gzip -f -9',
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
