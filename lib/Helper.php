<?php

namespace WHMCS\Module\Addon\Affili;

use Exception;
use WHMCS\Billing\Invoice;
use Illuminate\Support\Arr;
use WHMCS\Module\Addon\Setting;

class Helper
{
    public static function make()
    {
        return new static;
    }

    public function printAndDie(array ...$vars)
    {
        echo "<pre>";
        foreach ($vars as $var) {
            print_r($var);
        }
        echo "</pre>";
        die();
    }

    /**
     * @return mixed
     */
    public function getAffiliCookiesData(?string $key = null, $default = null)
    {
        try {
            if (!isset($_COOKIE['AFFILI_DATA'])) {
                $data = [];
            }

            $data = json_decode(
                base64_decode($_COOKIE['AFFILI_DATA']),
                true
            );

            $data = [
                'deleteCookie' => isset($data['deleteCookie']) ? $data['deleteCookie'] : null,
                'referrer' => isset($data['referrer']) ? $data['referrer'] : null,
                'affId' => isset($data['affId']) ? $data['affId'] : null,
            ];

            return isset($data[$key]) && $data[$key] !== null ? $data[$key] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    public function deleteAffiliCookieData()
    {
        setcookie('AFFILI_DATA', '', time() - 3600);
    }

    /**
     * @return mixed
     */
    public function getAffiliAddonConfig(?string $key = null, $default = null)
    {
        try {
            $config = Setting::module('affili')->get()->pluck('value', 'setting')->toArray();

            $data = [
                'version' => $config['version'] ?? null,
                'token' => $config['token'] ?? null,
                'register_lead' => isset($config['register_lead']) ? ( $config['register_lead'] === 'on' ) : null,
                'access' => isset($config['access']) ? explode(',', $config['access']) : null,
            ];

            return isset($data[$key]) && $data[$key] !== null ? $data[$key] : $default;
        } catch (Exception $e) {
            return $default;
        }
    }

    public function getAffiliToken()
    {
        return $this->getAffiliAddonConfig('token');
    }

    public function getInvoice($invoiceId)
    {
        $invoice = Invoice::with(['order.services.product', 'items'])->find($invoiceId);

        $availableBilingCycles = [
            'Free Account' => ['qty' => 1, 'method' => 'free'],
            'One Time' => ['qty' => 1, 'method' => 'onetime'],
            'Monthly' => ['qty' => 1, 'method' => 'monthly'],
            'Quarterly' => ['qty' => 3, 'method' => 'quarterly'],
            'Semi-Annually' => ['qty' => 6, 'method' => 'semiannually'],
            'Annually' => ['qty' => 12, 'method' => 'annually'],
            'Biennially' => ['qty' => 24, 'method' => 'biennially'],
            'Triennially' => ['qty' => 36, 'method' => 'triennially'],
        ];

        $data = [
            'id' => $invoiceId,
            'invoice_number' => $invoice->invoicenum,
            'order_id' => $invoice->order->id,
            'user_id' => $invoice->order->clientId, // userId
            'amount' => $invoice->subtotal,
            'promocode' => $invoice->order->promocode,
            'products' => $invoice->order->services
                ->map(function ($service) use ($availableBilingCycles) {
                    $billingCycle = $service->billingcycle;
                    $billing = $availableBilingCycles[$billingCycle] ?? ['qty' => 1, 'method' => 'free'];

                    $product = $service->product;

                    $quantity = $billing['qty'] ?? 1;
                    $unitPrice = $product->pricing()->{$billing['method']}()->price()->getValue();

                    return [
                        'id' => $product->id,
                        'name' => $product->name,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'total_price' => $quantity * $unitPrice,
                    ];
                })
                ->groupBy(function ($product) { return $product['id']; })
                ->map(function ($products) {
                    $product = Arr::first($products);

                    $qty = count($products);

                    return [
                        'pid' => $product['id'],
                        'name' => $product['name'],
                        'quantity' => $product['quantity'] * $qty,
                        'unit_price' => $product['unit_price'] * $qty,
                        'total_price' => $product['total_price'] * $qty,
                    ];
                })
                ->values()
                ->toArray()
            ,
        ];

        return $data;
    }
}