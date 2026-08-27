<?php

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nickname' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => RoleEnum::User,
        ];
    }

    public function user(): static
    {
        return $this->state(fn (): array => [
            'role' => RoleEnum::User,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn (): array => [
            'role' => RoleEnum::Admin,
        ]);
    }
}
