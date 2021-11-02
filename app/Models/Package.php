<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    public function services()
    {
        return $this->belongsToMany(Service::class, 'service_package');
    }

    public function customers()
    {
        return $this->hasManyThrough(Customer::class, Subscription::class, 'package_id', 'id', 'id', 'customer_id');
    }

    public function customerIps()
    {
        return $this->hasManyThrough(CustomerIp::class, Subscription::class, 'package_id', 'customer_id', 'id', 'customer_id');
    }
}
