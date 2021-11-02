<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $casts = [
        'attributes' => 'array',
    ];

    public function ips()
    {
        return $this->hasMany(CustomerIp::class);
    }


    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function packages()
    {
        return $this->belongsToMany(Package::class, 'subscriptions')->using(Subscription::class)->withPivot(['status', 'expiry', 'note', 'customer_data']);
    }
}
