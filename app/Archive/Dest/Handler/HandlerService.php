<?php

namespace Packages\Backup\App\Archive\Dest\Handler;

use Packages\Backup\App\Archive;
use Illuminate\Foundation\Application;

/**
 * Handle the Business Logic for Backup Handlers.
 */
class HandlerService
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param Archive\Dest\Dest $source
     *
     * @return Handler
     */
    public function get(Backup\Dest\Dest $source)
    {
        return $this->make($source->handler);
    }

    /**
     * @param Archive\Handler\Handler $handler
     *
     * @return Handler
     */
    public function make(Backup\Handler\Handler $handler)
    {
        return $this->app->make($handler->class);
    }
}
