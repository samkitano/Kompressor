<?php

namespace samkitano\Kompressor\Facades;

use Illuminate\Support\Facades\Facade;

class Kompressor extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return 'Kompressor';
    }
}
