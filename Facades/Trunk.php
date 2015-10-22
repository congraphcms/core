<?php

namespace Cookbook\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cookbook\Contracts\Core\TrunkContract
 */
class Trunk extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Cookbook\Contracts\Core\TrunkContract';
    }
}
