<?php

namespace Packages\Backup\App\Archive\Dest;

use App\Support\ClassMap;
use Illuminate\Support\ServiceProvider;

class DestServiceProvider
extends ServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        DestRoutesProvider::class,
        DestEventProvider::class
    ];

    /**
     * Register the feature's service providers.
     */
    public function register()
    {
        $this->app->singleton(Handler\HandlerService::class);

        collection($this->providers)->each(_one([$this->app, 'register']));
    }

    /**
     * @return array
     */
    public function provides()
    {
        return [
            Handler\HandlerService::class,
        ];
    }

    /**
     * Boot the Backup Service Feature.
     *
     * @param ClassMap $classMap
     */
    public function boot(ClassMap $classMap)
    {
        $classMap->map('pkg.backup.destination', Dest::class);

        $this->loadTranslationsFrom(
            $this->basePath().'/resources/lang',
            'pkg.backup'.$this->folder()
        );
    }

    protected function folder()
    {
        return 'backup';
    }

    protected function basePath()
    {
        return sprintf(
            '%s/packages/%s',
            $this->app->basePath(),
            $this->folder()
        );
    }
}
