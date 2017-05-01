<?php

namespace Packages\Backup\App\Recurring;

use App\Support\ClassMap;
use Illuminate\Support\ServiceProvider;

class RecurringServiceProvider
extends ServiceProvider
{
    /**
     * @var array
     */
    protected $providers = [
        RecurringEventProvider::class,
        RecurringCommandProvider::class,
    ];

    /**
     * Register the feature's service providers.
     */
    public function register()
    {
        collect($this->providers)->each(_one([$this->app, 'register']));
    }

    /**
     * Boot the Backup Service Feature.
     *
     * @param ClassMap $classMap
     */
    public function boot(ClassMap $classMap)
    {
        $classMap->map('pkg.backup.recurring', Recurring::class);
    }
}
