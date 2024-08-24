<?php

namespace WHMCS\Module\Addon\Affili;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\ConnectionException;
use WHMCS\Module\Addon\Affili\Exceptions\HookException;

class Http
{
    public static function make()
    {
        return new Factory;
    }
}