<?php

namespace Packages\Backup\App\Sources\Mysql;

use App\Shell;
use Packages\Backup\App\Backup;

/**
 * MySQLDump Handler.
 */
class MysqlDumpHandler
implements Backup\Source\Handler\Handler
{
    /**
     * @var Shell\Shell
     */
    protected $shell;

    /**
     * @var Backup\Field\ValueService
     */
    protected $value;

    /**
     * @var string
     */
    protected $exec = 'mysqldump';

    /**
     * @param Shell\Shell               $shell
     * @param Backup\Field\ValueService $value
     */
    public function __construct(
        Shell\Shell $shell,
        Backup\Field\ValueService $value
    ) {
        $this->shell = $shell;
        $this->value = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(Backup\Backup $backup, $tempFile)
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
        $this->run(
            $this->shell->cmd(),
            "mkdir -p $tempFile; rmdir $tempFile"
        );
    }

    /**
     * @param Backup\Backup $backup
     * @param string        $tempFile
     *
     * @throws \Exception
     */
    protected function dump(Backup\Backup $backup, $tempFile)
    {
        $this->run(
            $this->shell->cmd()->setOutputFile($tempFile),
            $this->command($backup)
        );
    }

    /**
     * @param Backup\Backup $backup
     *
     * @return string
     */
    protected function command(Backup\Backup $backup)
    {
        $database = $this->value
            ->byName($backup->source, MysqlDumpFields::DATABASE)
            ->value();
        $arguments = [
            $this->exec,
            // --defaults-file option must be the first option.
            '--defaults-file=$(bash -c "echo ~")/.my.cnf',
            // Efficient, exact MyISAM backups.
            '--single-transaction',
            '--quick',
            // The database that is getting exported.
            $database,

            // Pipe the output through gzip
            '| gzip -9',
        ];

        return implode(' ', $arguments);
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
            throw new \Exception(sprintf(
                'Error with %s: %s',
                $command,
                $errors
            ));
        }
    }
}
