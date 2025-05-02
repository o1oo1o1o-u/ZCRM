<?php

namespace ZCRM;

use Illuminate\Support\Facades\Facade;

class ZCRM extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ZohoManager::class;
    }
}
