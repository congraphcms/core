<?php

namespace Congraph\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Congraph\Contracts\Core\TrunkContract
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
        return 'Congraph\Contracts\Core\TrunkContract';
    }
}
