<?php

namespace WHMCS\Module\Addon\Affili\Services;

use Throwable;
use WHMCS\Module\Addon\Affili\Http;
use WHMCS\Module\Addon\Affili\Helper;
use GuzzleHttp\Exception\ConnectException;
use Composer\Downloader\TransportException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use WHMCS\Module\Addon\Affili\Models\Affili\Customer;
use WHMCS\Module\Addon\Affili\Models\Affili\Conversion;
use WHMCS\Module\Addon\Affili\Exceptions\SyncException;
use WHMCS\Module\Addon\Affili\Exceptions\ServiceException;

class ConversionService extends AbstractService
{
    public function sale(array $invoice, Customer $customer, ?string $referrer): Conversion
    {
        try {
            throw_if($customer->is_not_synced, SyncException::class, 'Customer is not synced');

            $url = rtrim($this->baseUrl) . '/services/conversions';

            $response = Http::make()->timeout(15)->withToken( $this->helper->getAffiliToken() )->post($url, [
                'type' => 'sale',
                'order_id' => $invoice['id'],
                'amount' => $invoice['amount'],
                'coupon' => $invoice['promocode'],
                'products' => $invoice['products'],
                'referrer' => $referrer,
                'customer_id' => $customer->srv_customer_id,
            ]);

            throw_if($response->serverError(), ConnectionException::class, 'affili server error');

            throw_if($response->clientError(), ServiceException::class, 'invalid client data');

            $data = $response->collect()->get('data');

            $conversion = Conversion::query()->create([
                'type' => 'sale',
                'invoice_id' => $invoice['id'],
                'customer_id' => $customer->id,
                'referrer' => $referrer,
                'srv_conversion_id' => $data['id'],
                'status' => Conversion::STATUS_APPROVED,
                'status_synced' => true,
            ]);
        } catch (ServiceException $e) {
            throw $e;
        } catch (SyncException | RequestException | ConnectException | ConnectionException | TransportException $e) {
            $conversion = Conversion::query()->create([
                'type' => 'sale',
                'invoice_id' => $invoice['id'],
                'customer_id' => $customer->id,
                'referrer' => $referrer,
                'status' => Conversion::STATUS_APPROVED,
                'status_synced' => true,
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            throw new ServiceException('internal error', 500, $e);
        }

        return $conversion;
    }

    public function updateStatus(Conversion $conversion, $status): void
    {
        try {
            throw_if($conversion->is_not_synced, SyncException::class, 'Conversion is not synced');

            $url = rtrim($this->baseUrl) . '/services/conversions/' . $conversion->srv_conversion_id;

            $response = Http::make()->timeout(15)->withToken( Helper::make()->getAffiliToken() )->post($url, [
                'status' => $status
            ]);

            throw_if($response->serverError(), ConnectionException::class, 'affili server error');

            throw_if($response->clientError(), ServiceException::class, 'invalid client data');

            Conversion::where('id', $conversion->id)->update([
                'status' => $status,
                'status_synced' => true,
            ]);
        } catch (ServiceException $e) {
            throw $e;
        } catch (SyncException | RequestException | ConnectException | ConnectionException | TransportException $e) {
            Conversion::where('id', $conversion->id)->update([
                'status' => $status,
                'status_synced' => false,
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            throw new ServiceException('internal error', 500, $e);
        }
    }

    public function sync(Conversion $conversion)
    {
        try {
            $conversion->load('customer');
            $customer = $conversion->customer;

            if ($conversion->is_synced || $customer->is_not_synced) {
                return;
            }

            $invoice = $this->helper->getInvoice($conversion->invoice_id);

            $url = rtrim($this->baseUrl) . '/services/conversions';

            $response = Http::make()->timeout(15)->withToken( $this->helper->getAffiliToken() )->post($url, [
                'type' => 'sale',
                'order_id' => $invoice['invoice_number'],
                'amount' => $invoice['amount'],
                'coupon' => $invoice['promocode'],
                'products' => $invoice['products'],
                'referrer' => $conversion->referrer,
                'customer_id' => $customer->srv_customer_id,
            ]);

            throw_if(
                $response->serverError(),
                ConnectionException::class, 'affili server error'
            );

            throw_if(
                $response->clientError(),
                ServiceException::class, 'invalid client data'
            );

            $data = $response->collect()->get('data');

            Conversion::where('id', $conversion->id)->update([
                'srv_conversion_id' => $data['id'],
            ]);
        } catch (ServiceException $e) {
            throw $e;
        } catch (RequestException | ConnectException | ConnectionException | TransportException $e) {
            //
        } catch (Throwable $e) {
            error_log($e->getMessage());

            throw new ServiceException('internal error', 500, $e);
        }
    }
}