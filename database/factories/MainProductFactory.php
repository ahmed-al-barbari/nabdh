<?php

namespace Database\Factories;

use App\Models\MainProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

class MainProductFactory extends Factory
{
    protected $model = MainProduct::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'price' => $this->faker->numberBetween(10, 200),
            'category_id' => 1
        ];
    }
}
