<?php

namespace Packages\Backup\App\Destinations\Scp;

use App\Shell;
use Packages\Backup\App\Backup;
use Illuminate\Support\Collection;

/**
 * Secure Copy Handler.
 */
class ScpHandler
implements Archive\Dest\Handler\Handler
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
    public function copy(Backup\Dest\Dest $dest, $tempFile, $destFile)
    {
        $runCommand = function ($command) {
            $cmd = $this->shell
                ->cmd()
                ->exec($command)
                ;

            if ($errors = $cmd->getErrors()) {
                throw new \Exception(sprintf(
                    "Error with: %s: %s",
                    $command,
                    $errors
                ));
            }
        };

        $this
            ->commands($dest, $tempFile, $destFile)
            ->each($runCommand)
            ;
    }

    /**
     * @inheritDoc
     */
    public function delete(Backup\Dest\Dest $dest, $destFile)
    {
        throw new \LogicException('Not implemented'); // TODO
    }

    /**
     * @param Archive\Dest\Dest $dest
     * @param string           $tempFile
     * @param string           $destFile
     *
     * @return Collection
     */
    protected function commands(Backup\Dest\Dest $dest, $tempFile, $destFile)
    {
        $values = $this->value->all($dest);
        $folder = $values->value(ScpFields::FOLDER);
        $file = $destFile;
        $fileFolder = substr($file, 0, strrpos($file, '/'));

        if ($folder) {
            $file = $folder.'/'.$file;
            $fileFolder = $folder.'/'.$fileFolder;
        }

        $login = sprintf(
            '%s@%s',
            $values->value(ScpFields::USER),
            $values->value(ScpFields::HOST)
        );

        $dest = sprintf(
            '%s:%s',
            $login,
            $file
        );

        return collect([
            implode(' ', [
                'ssh',
                '-o "StrictHostKeyChecking false"',
                $login,
                '--',
                sprintf('mkdir -p "%s"', $fileFolder),
            ]),
            sprintf(
                'scp "%s" %s',
                $tempFile,
                $dest
            ),
        ]);
    }
}
