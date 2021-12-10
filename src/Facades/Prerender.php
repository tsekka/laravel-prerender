<?php

namespace Tsekka\Prerender\Facades;

use Illuminate\Support\Facades\Facade;

class Prerender extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'prerender';
    }
}
