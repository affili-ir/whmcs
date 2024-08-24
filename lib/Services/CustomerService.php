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
use WHMCS\Module\Addon\Affili\Exceptions\ServiceException;

class CustomerService extends AbstractService
{
    public function create($userId, string $referrer): Customer
    {
        try {
            $url = rtrim($this->baseUrl) . '/services/customers';

            $response = Http::make()->timeout(15)->withToken( Helper::make()->getAffiliToken() )->post($url, [
                'referrer' => $referrer,
                'mcs_id' => $userId,
            ]);

            throw_if($response->serverError(), ConnectionException::class, 'affili server error');

            throw_if($response->clientError(), ServiceException::class, 'invalid client data');

            $data = $response->collect()->get('data');

            $customer = Customer::query()->create([
                'user_id' => $userId,
                'referrer' => $referrer,
                'srv_customer_id' => $data['id'],
                'srv_publisher_id' => $data['publisher']['id'],
            ]);
        } catch (ServiceException $e) {
            throw $e;
        } catch(RequestException | ConnectException | ConnectionException | TransportException $e) {
            $customer = Customer::query()->create([
                'user_id' => $userId,
                'referrer' => $referrer,
            ]);
        } catch (Throwable $e) {
            error_log($e->getMessage());

            throw new ServiceException('internal error', 500, $e);
        }

        return $customer;
    }

    public function sync(Customer $customer)
    {
        try {
            if ($customer->is_synced) {
                return;
            }

            $url = rtrim($this->baseUrl) . '/services/customers';

            $response = Http::make()->timeout(15)->withToken( Helper::make()->getAffiliToken() )->post($url, [
                'referrer' => $customer->referrer,
                'mcs_id' => $customer->user_id,
            ]);

            throw_if($response->serverError(), ConnectionException::class, 'affili server error');

            throw_if($response->clientError(), ServiceException::class, 'invalid client data');

            $data = $response->collect()->get('data');

            $customer = Customer::query()->where('id', $customer->id)->update([
                'srv_customer_id' => $data['id'],
                'srv_publisher_id' => $data['publisher']['id'],
            ]);
        } catch (ServiceException $e) {
            throw $e;
        } catch(RequestException | ConnectException | ConnectionException | TransportException $e) {
            //
        } catch (Throwable $e) {
            error_log($e->getMessage());

            throw new ServiceException('internal error', 500, $e);
        }
    }
}