<?php

namespace Packages\Backup\App\Backup;

use Illuminate\Support\ServiceProvider;

class BackupServiceProvider
extends ServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        BackupEventProvider::class,
    ];

    /**
     * Register the feature's service providers.
     */
    public function register()
    {
        collect($this->providers)->each(_one([$this->app, 'register']));
    }
}
