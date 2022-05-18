<?php

namespace Mralston\Bark\Facades;

use Illuminate\Support\Facades\Facade;
use Mralston\Bark\Client;

/**
 * @method static listBarks(): Generator
 *
 * @see \Mralston\Bark\Client
 */
class Bark extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
