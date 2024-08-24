<?php

namespace WHMCS\Module\Addon\Affili\Models\Affili;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Customer extends Model
{
    protected $table = 'tblaffili_customers';

    protected $fillable = [
        'user_id',
        'referrer',
        'srv_customer_id',
        'srv_publisher_id',
    ];

    public function scopeWhereNotSynced()
    {
        return $this->query()->whereNull('srv_customer_id');
    }

    public function scopeWhereSynced(Builder $query)
    {
        return $query->whereNotNull('srv_customer_id');
    }

    public function getIsSyncedAttribute()
    {
        return ! is_null($this->srv_customer_id);
    }

    public function getIsNotSyncedAttribute()
    {
        return is_null($this->srv_customer_id);
    }
}