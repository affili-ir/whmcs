<?php

namespace WHMCS\Module\Addon\Affili\Services;

use WHMCS\Module\Addon\Affili\Helper;

abstract class AbstractService
{
    protected string $baseUrl = 'http://host.docker.internal:4545';

    protected Helper $helper;

    public function __construct()
    {
        $this->helper = Helper::make();
    }

    public static function make()
    {
        return new static;
    }
}