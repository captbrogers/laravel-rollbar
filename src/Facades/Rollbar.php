<?php

namespace Captbrogers\Rollbar\Facades;

use Illuminate\Support\Facades\Facade;

class Rollbar extends Facade
{
    /**
     * Get a schema builder instance for the default connection.
     *
     * @return \Captbrogers\Rollbar\RollbarLogHandler
     */
    protected static function getFacadeAccessor()
    {
        return 'Captbrogers\Rollbar\RollbarLogHandler';
    }
}
