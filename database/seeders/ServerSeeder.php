<?php

namespace Database\Seeders;

use App\Models\Server;
use App\Models\Service;
use Illuminate\Database\Seeder;

class ServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Server::factory()->count(5)->hasServices(5)->create()->each(function (Server $server) {
            $server->services()->each(function (Service $service) use ($server) {
                $service->name = $server->name . ' ' . $service->name;
                $service->save();
            });
        });
    }
}
