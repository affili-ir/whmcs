<?php

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use WHMCS\Module\Addon\Affili\Setup;

function affili_config()
{
    return Setup::make()->config();
}

function affili_activate()
{
    return Setup::make()->activate();
}

function affili_deactivate()
{
    return Setup::make()->deactivate();
}