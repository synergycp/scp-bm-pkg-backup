<?php

namespace Packages\Backup\App\Backup\Recurring;

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
}
