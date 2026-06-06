<?php

namespace Database\Factories;

use App\Enums\RolePengguna;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Factory untuk membuat data dummy pengguna sistem.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * Kata sandi yang di-hash dan di-cache agar tidak di-hash berulang kali.
     */
    protected static ?string $password;

    /**
     * Definisi state default: pengguna publik (pasien) dengan data acak.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => fake()->name(),
            "email" => fake()->unique()->safeEmail(),
            "email_verified_at" => now(),
            "password" => (static::$password ??= Hash::make("password")),
            "remember_token" => Str::random(10),
            "role" => RolePengguna::Pasien, // Peran default adalah pasien
        ];
    }

    /**
     * State: email belum diverifikasi (untuk pengujian alur verifikasi).
     */
    public function unverified(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "email_verified_at" => null,
            ],
        );
    }

    /**
     * State: pengguna dengan peran Administrator.
     */
    public function admin(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "role" => RolePengguna::Admin,
            ],
        );
    }

    /**
     * State: pengguna dengan peran Apoteker.
     */
    public function apoteker(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "role" => RolePengguna::Apoteker,
            ],
        );
    }

    /**
     * State: pengguna dengan peran Kasir.
     */
    public function kasir(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "role" => RolePengguna::Kasir,
            ],
        );
    }

    /**
     * State: pengguna dengan peran Pasien.
     */
    public function pasien(): static
    {
        return $this->state(
            fn(array $attributes) => [
                "role" => RolePengguna::Pasien,
            ],
        );
    }
}
