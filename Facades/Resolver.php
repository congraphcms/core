<?php

namespace Cookbook\Core\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cookbook\Contracts\Core\ObjectResolverContract
 */
class Resolver extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'Cookbook\Contracts\Core\ObjectResolverContract';
    }
}
