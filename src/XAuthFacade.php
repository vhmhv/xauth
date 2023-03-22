<?php

namespace vhmhv\Xauth;

use Illuminate\Support\Facades\Facade;

class XAuthFacade extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'XAuth';
    }
}
