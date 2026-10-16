<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ShopTimezone;
use App\Models\Shop;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Shop>
 */
final class ShopFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'phone' => null,
            'address' => null,
            'timezone' => ShopTimezone::DEFAULT,
            'receipt_footer' => null,
        ];
    }
}
