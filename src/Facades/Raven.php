<?php

namespace Clowdy\Raven\Facades;

use Illuminate\Support\Facades\Facade;

class Raven extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'log.raven';
    }
}
