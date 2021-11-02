<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Package;
use App\Models\Subscription;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $packages = Package::all();

        Customer::factory()->count(5)->hasIps(3)->create()->each(function (Customer $customer) use ($packages) {
            $customer->packages()->sync(
                $packages->random(rand(1, 3))->pluck('id')->toArray()
            );
            $customer->subscriptions()->each(function (Subscription $subscription) {
                $subscription->internal_name = $subscription->package->name . ' subscription';
                $subscription->expiry = now()->addDays(mt_rand(1, 30))->toDateTime();
                $subscription->save();
            });
        });
    }
}
