<?php

namespace WHMCS\Module\Addon\Affili\Hooks;

use Throwable;
use WHMCS\Module\Addon\Affili\Models\Affili\Customer;
use WHMCS\Module\Addon\Affili\Services\CustomerService;
use WHMCS\Module\Addon\Affili\Exceptions\SilentException;
use WHMCS\Module\Addon\Affili\Services\ConversionService;

class InvoicePaidHook extends AbstractHook
{
    public function handle()
    {
        try {
            $referrer = $this->helper->getAffiliCookiesData('referrer', '');
            $invoiceId = $this->getVar('invoiceid');

            $invoice = $this->helper->getInvoice($invoiceId);
            $customer = Customer::query()->where('user_id', $invoice['user_id'])->first();

            throw_if(!($customer || $referrer), SilentException::class, 'do not need to track');

            if (!$customer) {
                $customer = CustomerService::make()->create($invoice['user_id'], $referrer);
            }

            ConversionService::make()->sale($invoice, $customer, $referrer);

            $this->helper->deleteAffiliCookieData();
        } catch (SilentException $e) {
            // Nothing to do;
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }
    }
}