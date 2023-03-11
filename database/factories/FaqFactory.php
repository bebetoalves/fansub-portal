<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    public function definition(): array
    {
        return [
            'question' => fake()->sentence(nbWords: 3),
            'answer' => fake()->realText(),
        ];
    }
}
