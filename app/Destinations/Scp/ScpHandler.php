<?php

namespace Packages\Backup\App\Destinations\Scp;

use App\Shell;
use Packages\Backup\App\Archive;
use Illuminate\Support\Collection;
use App\System\SSH\Key\GlobalSSHKey;

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
     * @var GlobalSSHKey
     */
    protected $sshKey;

    /**
     * ScpHandler constructor.
     *
     * @param Shell\Shell                $shell
     * @param Archive\Field\ValueService $value
     * @param GlobalSSHKey               $sshKey
     */
    public function __construct(Shell\Shell $shell, Archive\Field\ValueService $value, GlobalSSHKey $sshKey)
    {
        $this->shell = $shell;
        $this->value = $value;
        $this->sshKey = $sshKey;
    }

    /**
     * {@inheritdoc}
     */
    public function copy(Archive\Dest\Dest $dest, $tempFile, $destFile)
    {
        $runCommand = function ($command) {
            $cmd = $this->shell
                ->cmd()
                ->exec($command)
                ;


            if ($status = $cmd->getExitCode()) {
                $errors = $cmd->getErrors();

                throw new \Exception(sprintf(
                    "Error %d with: %s: %s",
                    $status,
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
    public function delete(Archive\Dest\Dest $dest, $destFile)
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
    protected function commands(Archive\Dest\Dest $dest, $tempFile, $destFile)
    {
        $values = $this->value->all($dest);
        $folder = $values->value(ScpFields::FOLDER);
        $file = $destFile;
        $fileFolder = substr($file, 0, strrpos($file, '/'));

        if ($folder) {
            $file = $folder.'/'.$file;
            $fileFolder = $folder.'/'.$fileFolder;
        }

        $hostParts = explode(':', $values->value(ScpFields::HOST));
        $host = $hostParts[0];
        $port = (int)array_get($hostParts, 1, 22);

        $login = sprintf(
            '%s@%s',
            escapeshellarg($values->value(ScpFields::USER)),
            escapeshellarg($host)
        );

        $dest = sprintf(
            '%s:"%s"',
            $login,
            $file
        );

        $sshKeyFile = $this->sshKey->getPrivateKeyFile();

        return collection([
            implode(' ', [
                'ssh',
                '-o "StrictHostKeyChecking false"',
                sprintf('-p %d', $port),
                sprintf('-i "%s"', $sshKeyFile),
                $login,
                '--',
                sprintf('mkdir -p "%s"', $fileFolder),
            ]),
            implode(' ', [
                'scp',
                sprintf('-i "%s"', $sshKeyFile),
                sprintf('-P %d', $port),
                sprintf('"%s" %s', $tempFile, $dest),
            ]),
        ]);
    }
}
