<?php

namespace WHMCS\Module\Addon\Affili\Hooks;

use Throwable;
use WHMCS\Module\Addon\Affili\Exceptions\SilentException;
use WHMCS\Module\Addon\Affili\Services\CustomerService;

class UserAddHook extends AbstractHook
{
    public function handle()
    {
        try {
            $userId = $this->getVar('user_id');
            $referrer = $this->helper->getAffiliCookiesData('referrer');

            throw_if(is_null($referrer), SilentException::class);

            CustomerService::make()->create($userId, $referrer);
        } catch (SilentException $e) {
            // Nothing to do;
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }
    }
}