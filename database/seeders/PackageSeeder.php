<?php

namespace Database\Seeders;

use App\Models\Package;
use App\Models\Service;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $services = Service::all();
        Package::factory()->count(5)->create()->each(function ($package) use ($services) {
            $package->services()->attach(
                $services->random(rand(1, 5))->pluck('id')->toArray()
            );
        });
    }
}
