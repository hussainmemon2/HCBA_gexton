<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class LibraryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'author_name' => fake()->name(),
            'type' => fake()->randomElement(["book","e-journal"]),
            'return_date'=>now(),
            // 'status' => fake()->randomElement(["available","reserved"]),
            'rfid_tag' => fake()->regexify('[A-Za-z0-9]{50}'),
        ];
    }
}
