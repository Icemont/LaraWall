<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Subscription extends Pivot
{

    protected $table = 'subscriptions';

    public $incrementing = true;

    public function package()
    {
        return $this->hasOne(Package::class, 'id', 'package_id');
    }

    public function customer()
    {
        return $this->hasOne(Customer::class, 'id', 'customer_id');
    }
}
