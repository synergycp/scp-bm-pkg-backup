<?php

namespace Packages\Backup\App\Archive\File;

use App\File;
use Packages\Backup\App\Archive;
use Packages\Backup\App\Archive\Source\Handler;
use Illuminate\Contracts\Events;
use Illuminate\Contracts\Config;

/**
 * Handle the Business Logic behind turning a Backup into a File.
 */
class FileService
{
    /**
     * @var File\FileManager
     */
    protected $file;

    /**
     * @var Events\Dispatcher
     */
    protected $event;

    /**
     * @var Config\Repository
     */
    protected $config;

    /**
     * @var Handler\HandlerService
     */
    protected $handler;

    /**
     * @var Archive\ArchiveService
     */
    protected $backup;

    /**
     * @param File\FileManager       $file
     * @param Events\Dispatcher      $event
     * @param Config\Repository      $config
     * @param Archive\ArchiveService   $backup
     * @param Handler\HandlerService $handler
     */
    public function __construct(
        File\FileManager $file,
        Events\Dispatcher $event,
        Config\Repository $config,
        Archive\ArchiveService $backup,
        Handler\HandlerService $handler
    ) {
        $this->file = $file;
        $this->event = $event;
        $this->config = $config;
        $this->backup = $backup;
        $this->handler = $handler;
    }

    /**
     * @param Archive\Archive $backup
     *
     * @throws \Exception
     */
    public function save(Backup\Archive $backup)
    {
        try {
            $this->event->fire(
                new FileCompressing($backup)
            );

            $this->handler
                ->get($backup->source)
                ->handle($backup, $this->tempFile($backup))
                ;

            $this->event->fire(
                new FileCreated($backup)
            );
        } catch (\Exception $exc) {
            $this->backup->failed($backup, $exc);

            throw $exc;
        }
    }

    /**
     * @param Archive\Archive $backup
     */
    public function delete(Backup\Archive $backup)
    {
        try {
            $this->file->delete(
                $this->tempFile($backup)
            );

            $this->event->fire(
                new FileDeleted($backup)
            );
        } catch (\Exception $exc) {
            $this->backup->failed($backup, $exc);

            throw $exc;
        }
    }

    /**
     * @param Archive\Archive $backup
     */
    public function tempFile(Backup\Archive $backup)
    {
        return $this->tempDir().$backup->id;
    }

    /**
     * @return string
     */
    protected function tempDir()
    {
        return $this->config->get('app.tmp').'/backup/';
    }
}
