<?php

namespace WHMCS\Module\Addon\Affili\Models\Affili;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Conversion extends Model
{
    protected $table = 'tblaffili_conversions';

    protected $fillable = [
        'type',
        'invoice_id',
        'customer_id',
        'referrer',
        'srv_conversion_id',
        'status',
        'status_synced',
    ];

    protected $casts = [
        'status_synced' => 'boolean',
    ];

    const STATUS_APPROVED = 1;
    const STATUS_REFUNDED = 2;

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }

    public function scopeWhereHasCustomerSynced()
    {
        return $this->query()->whereHas('customer', fn ($q) => $q->whereSynced());
    }

    public function scopeWhereNotSynced()
    {
        return $this->query()->whereNull('srv_conversion_id');
    }

    public function scopeWhereSynced(Builder $query)
    {
        return $query->whereNotNull('srv_conversion_id');
    }

    public function scopeWhereStatusNotSynced()
    {
        return $this->query()->where('status_synced', '=', false);
    }

    public function scopeWhereStatusSynced(Builder $query)
    {
        return $query->where('status_synced', '=', true);
    }

    public function getIsSyncedAttribute()
    {
        return ! is_null($this->srv_conversion_id);
    }

    public function getIsNotSyncedAttribute()
    {
        return is_null($this->srv_conversion_id);
    }
}