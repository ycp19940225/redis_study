<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CrawlSourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'url' => $this->faker->url,
        ];
    }
}
