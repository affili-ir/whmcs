<?php
use WHMCS\Module\Addon\Affili\Helper;

if (!defined('WHMCS')) {
    die('You cannot access this file directly.');
}

require_once __DIR__ . '/vendor/autoload.php';

use WHMCS\Module\Addon\Affili\Hooks\UserAddHook;
use WHMCS\Module\Addon\Affili\Hooks\OrderPaidHook;
use WHMCS\Module\Addon\Affili\Hooks\UserLoginHook;
use WHMCS\Module\Addon\Affili\Hooks\InvoicePaidHook;
use WHMCS\Module\Addon\Affili\Hooks\DailyCronJobHook;
use WHMCS\Module\Addon\Affili\Hooks\InvoiceRefundedHook;
use WHMCS\Module\Addon\Affili\Hooks\CancelAndRefundOrderHook;
use WHMCS\Module\Addon\Affili\Hooks\ClientAreaFooterOutputHook;

add_hook('ClientAreaFooterOutput', 1, function($vars) {
    return ClientAreaFooterOutputHook::render($vars);
});

add_hook('UserAdd', 1, function($vars) {
    return UserAddHook::render($vars);
});

add_hook('UserLogin', 1, function($vars) {
    return UserLoginHook::render($vars);
});

add_hook('InvoicePaid', 1, function($vars) {
    return InvoicePaidHook::render($vars);
});

add_hook('OrderPaid', 1, function($vars) {
    return OrderPaidHook::render($vars);
});

add_hook('DailyCronJob', 1, function ($vars) {
    return DailyCronJobHook::render();
});

add_hook('InvoiceRefunded', 1, function ($vars) {
    return InvoiceRefundedHook::render($vars);
});

add_hook('CancelAndRefundOrder', 1, function ($vars) {
    return CancelAndRefundOrderHook::render($vars);
});