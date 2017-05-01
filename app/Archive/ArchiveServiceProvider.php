<?php

namespace Packages\Backup\App\Archive;

use App\Support\ClassMap;
use Illuminate\Support\ServiceProvider;

class ArchiveServiceProvider
extends ServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        ArchiveEventProvider::class,
        ArchiveRoutesProvider::class,

        Dest\DestServiceProvider::class,
        Source\SourceServiceProvider::class,
    ];

    /**
     * Register the feature's service providers.
     */
    public function register()
    {
        collect($this->providers)->each(function ($provider) {
            $this->app->register($provider);
        });
    }

    /**
     * Boot the Backup Service Feature.
     *
     * @param ClassMap $classMap
     */
    public function boot(ClassMap $classMap)
    {
        $classMap->map('pkg.backup.archive', Archive::class);

        $this->loadTranslationsFrom(
            $this->basePath().'/resources/lang',
            'pkg.'.$this->folder()
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
