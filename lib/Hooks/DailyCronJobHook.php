<?php

namespace WHMCS\Module\Addon\Affili\Hooks;

use Throwable;
use WHMCS\Module\Addon\Affili\Models\Affili\Customer;
use WHMCS\Module\Addon\Affili\Models\Affili\Conversion;
use WHMCS\Module\Addon\Affili\Services\CustomerService;
use WHMCS\Module\Addon\Affili\Services\ConversionService;

class DailyCronJobHook extends AbstractHook
{
    public function handle()
    {
        try {
            $notSyncedCustomers = Customer::query()->whereNotSynced()->get();

            foreach ($notSyncedCustomers as $customer) {
                CustomerService::make()->sync($customer);
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }

        try {
            $notSyncedConversions = Conversion::query()->with(['customer'])->whereHasCustomerSynced()->whereNotSynced()->get();

            foreach ($notSyncedConversions as $conversion) {
                ConversionService::make()->sync($conversion);
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }

        try {
            $notStatusSyncedConversions = Conversion::query()->with(['customer'])->whereHasCustomerSynced()->whereSynced()->whereStatusNotSynced()->get();

            foreach ($notStatusSyncedConversions as $conversion) {
                ConversionService::make()->updateStatus($conversion, $conversion->status);
            }
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }
    }
}