<?php

namespace vhmhv\Xauth;

use Illuminate\Support\Facades\Facade;

/**
 * @see \vhmhv\Xauth\Xauth
 */
class XauthFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'xauth';
    }
}
