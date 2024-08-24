<?php

namespace WHMCS\Module\Addon\Affili\Hooks;

use Throwable;
use WHMCS\Module\Addon\Affili\Models\Affili\Conversion;
use WHMCS\Module\Addon\Affili\Exceptions\SilentException;
use WHMCS\Module\Addon\Affili\Services\ConversionService;

class InvoiceRefundedHook extends AbstractHook
{
    public function handle()
    {
        try {
            $invoiceId = $this->getVar('invoiceid');
            $conversion = Conversion::query()->where('invoice_id', $invoiceId)->first();

            if ($conversion) {
                ConversionService::make()->updateStatus($conversion, Conversion::STATUS_REFUNDED);
            }
        } catch (SilentException $e) {
            // Nothing to do;
        } catch (Throwable $e) {
            error_log($e->getMessage());
        }
    }
}