<?php

namespace Packages\Backup\App\Backup;

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
        Recurring\RecurringServiceProvider::class,
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
    }
}
