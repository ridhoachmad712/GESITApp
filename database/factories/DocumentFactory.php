<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Document;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);
        $slug = Str::slug($title);

        return [
            'title' => $title,
            'slug' => $slug,
            'description' => fake()->paragraph(),
            'category_id' => Category::factory(),
            'file_path' => "uji-coba/2025/{$slug}.pdf",
            'file_name' => "{$slug}.pdf",
            'file_size' => fake()->numberBetween(10_000, 5_000_000),
            'mime_type' => 'application/pdf',
            'visibility' => Document::VISIBILITY_PUBLIC,
            'academic_year' => '2025/2026',
            'semester' => 'ganjil',
            'uploaded_by' => null,
            'is_featured' => false,
            'status' => Document::STATUS_PUBLISHED,
        ];
    }

    public function visibility(string $visibility): static
    {
        return $this->state(fn (array $attributes) => ['visibility' => $visibility]);
    }

    public function external(string $url = 'https://drive.google.com/file/d/1AbCdEfGhIjKlMnOpQrStUv/view'): static
    {
        return $this->state(fn (array $attributes) => [
            'external_url' => $url,
            'file_path' => null,
            'file_name' => null,
            'file_size' => null,
            'mime_type' => null,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => ['status' => Document::STATUS_DRAFT]);
    }
}
