<?php

namespace Packages\Backup\App\Archive\Source\Handler;

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
     * @param Archive\Source\Source $source
     *
     * @return Handler
     */
    public function get(Archive\Source\Source $source)
    {
        return $this->make($source->handler);
    }

    /**
     * @param Archive\Handler\Handler $handler
     *
     * @return Handler
     */
    public function make(Archive\Handler\Handler $handler)
    {
        return $this->app->make($handler->class);
    }
}
