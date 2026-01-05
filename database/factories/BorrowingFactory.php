<?php

namespace Database\Factories;

use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BorrowingFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'library_item_id' => LibraryItem::factory(),
            'date' => fake()->date(),
            'status' => fake()->randomElement(["borrowed","returned",'reserved']),
        ];
    }
}
