<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use PragmaRX\Countries\Package\Countries;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'nickname' => Str::slug($this->faker->unique()->name()),
            'email' => $this->faker->unique()->companyEmail(),
            'status' => $this->faker->boolean(85),
            'phone' => $this->faker->e164PhoneNumber(),
            'country' => (new Countries())->all()->random(1)->pluck('name.common')->first(),
            'note' => $this->faker->realText(150),
        ];
    }
}
