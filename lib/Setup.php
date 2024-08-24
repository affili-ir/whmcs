<?php

namespace WHMCS\Module\Addon\Affili;

use Exception;
use WHMCS\Database\Capsule;
use Illuminate\Database\Schema\Blueprint;
use WHMCS\Module\Addon\Affili\Models\Affili\Conversion;

class Setup
{
    public static function make()
    {
        return (new static);
    }

    public function config()
    {
        return [
            'name' => 'Affili',
            'description' => 'Affili Tracking Addon',
            'author' => 'Affili Group',
            'language' => 'english',
            'version' => '1.0.0',
            'fields' => [
                'token' => [
                    'FriendlyName' => 'Token',
                    'Type' => 'text',
                    'Size' => '100',
                    'Default' => '',
                ],
                'register_lead' => [
                    'FriendlyName' => 'Register Lead',
                    'Type' => 'yesno',
                    'Description' => 'Enable it, if you want to pay for registered users.',
                ],
            ]
        ];
    }
    public function activate()
    {
        try {
            $this->migrateCustomers();
            $this->migrateConversions();

            return [
                'status' => 'success',
                'description' => 'All required tables created.'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'description' => 'Unable to create tblaffili_customers: ' . $e->getMessage(),
            ];
        }
    }

    public function deactivate()
    {
        try {
            // $this->migrateRollbackConversions();
            // $this->migrateRollbackCustomers();

            return [
                'status' => 'success',
                'description' => 'All tables droped!'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'description' => 'Unable to drop tblaffili_customers: ' . $e->getMessage(),
            ];
        }
    }

    protected function migrateCustomers()
    {
        if (Capsule::schema()->hasTable('tblaffili_customers')) {
            return;
        }

        Capsule::schema()->create('tblaffili_customers', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->string('referrer');
            $table->string('srv_customer_id')->nullable();
            $table->string('srv_publisher_id')->nullable();
            $table->timestamps();
        });
    }

    protected function migrateRollbackCustomers()
    {
        Capsule::schema()->dropIfExists('tblaffili_customers');
    }

    protected function migrateConversions()
    {
        if (Capsule::schema()->hasTable('tblaffili_conversions')) {
            return;
        }

        Capsule::schema()->create('tblaffili_conversions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('referrer');
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('invoice_id');
            $table->string('srv_conversion_id')->nullable();
            $table->tinyInteger('status')->default(Conversion::STATUS_APPROVED);
            $table->boolean('status_synced')->default(false);
            $table->timestamps();
        });
    }

    protected function migrateRollbackConversions()
    {
        Capsule::schema()->dropIfExists('tblaffili_conversions');
    }
}