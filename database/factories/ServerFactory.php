<?php

namespace Database\Factories;

use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;
use PragmaRX\Countries\Package\Countries;

class ServerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Server::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => 'Server ' . $this->faker->firstNameFemale(),
            'ip' => $this->faker->unique()->ipv4(),
            'hostname' => $this->faker->domainName(),
            'status' => $this->faker->boolean(85),
            'country' => (new Countries())->all()->random(1)->pluck('name.common')->first(),
            'isp' =>$this->faker->company(),
        ];
    }
}
