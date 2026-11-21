<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\BookPhoto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BookPhoto>
 */
class BookPhotoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'path' => 'books/'.fake()->uuid().'.jpg',
            'position' => 0,
        ];
    }
}
