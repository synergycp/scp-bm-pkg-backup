<?php

namespace Packages\Backup\App\Backup\File;

use App\File;
use Packages\Backup\App\Backup;
use Illuminate\Contracts\Events;

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
     * @var Handler\HandlerService
     */
    protected $handler;

    /**
     * @param File\FileManager       $event
     * @param Events\Dispatcher      $event
     * @param Handler\HandlerService $handler
     */
    public function __construct(
        File\FileManager $file,
        Events\Dispatcher $event,
        Handler\HandlerService $handler
    ) {
        $this->file = $file;
        $this->event = $event;
        $this->handler = $handler;
    }

    /**
     * @param Backup\Backup $backup
     *
     * @throws \Exception
     */
    public function save(Backup\Backup $backup)
    {
        try {
            $this->handler
                ->get($backup->target)
                ->handle($backup, $this->tempFile($backup))
                ;
        } catch (\Exception $exc) {
            $this->failed($backup, $exc);

            throw $exc;
        }
    }

    /**
     * @param Backup\Backup $backup
     */
    public function delete(Backup\Backup $backup)
    {
        $this->file->delete(
            $this->tempFile($backup)
        );
    }

    /**
     * @param Backup\Backup $backup
     * @param \Exception    $exc
     */
    public function failed(Backup\Backup $backup, \Exception $exc)
    {
        $this->event->fire(
            new Backup\Events\BackupFailed($backup, $exc)
        );
    }

    /**
     * @param Backup\Backup $backup
     */
    protected function tempFile(Backup\Backup $backup)
    {
        return $this->tempDir().$backup->id;
    }

    /**
     * @return string
     */
    protected function tempDir()
    {
        throw new \Exception('TODO: tempDir');
    }
}
