<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create();

        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+1 (555) 000-0001',
            'address' => '123 Admin Street, Admin City, AC 12345',
            'status' => 'active',
            'last_login_at' => now(),
        ]);

        // Create regular customers
        for ($i = 1; $i <= 50; $i++) {
            $createdAt = $faker->dateTimeBetween('-6 months', 'now');
            $lastLogin = $faker->optional(0.8)->dateTimeBetween($createdAt, 'now');
            
            User::create([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'email_verified_at' => $faker->optional(0.9)->dateTimeBetween($createdAt, 'now'),
                'password' => Hash::make('password'),
                'role' => $faker->randomElement(['customer', 'customer', 'customer', 'customer', 'admin']), // 80% customers, 20% admins
                'phone' => $faker->optional(0.7)->phoneNumber(),
                'address' => $faker->optional(0.6)->address(),
                'status' => $faker->randomElement(['active', 'active', 'active', 'active', 'inactive', 'banned']), // 80% active
                'last_login_at' => $lastLogin,
                'created_at' => $createdAt,
                'updated_at' => $faker->dateTimeBetween($createdAt, 'now'),
            ]);
        }

        // Create some specific test users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+1 (555) 123-4567',
            'address' => '123 Main St, City, State 12345',
            'status' => 'active',
            'last_login_at' => now()->subHours(2),
            'created_at' => now()->subMonths(3),
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+1 (555) 987-6543',
            'address' => '456 Oak Ave, City, State 12345',
            'status' => 'active',
            'last_login_at' => now()->subDays(1),
            'created_at' => now()->subMonths(2),
        ]);

        User::create([
            'name' => 'Bob Johnson',
            'email' => 'bob@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+1 (555) 456-7890',
            'address' => '789 Pine Rd, City, State 12345',
            'status' => 'active',
            'last_login_at' => now()->subDays(2),
            'created_at' => now()->subMonths(4),
        ]);

        User::create([
            'name' => 'Alice Brown',
            'email' => 'alice@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+1 (555) 321-0987',
            'address' => '321 Elm St, City, State 12345',
            'status' => 'inactive',
            'last_login_at' => now()->subWeeks(2),
            'created_at' => now()->subMonths(1),
        ]);

        User::create([
            'name' => 'Charlie Wilson',
            'email' => 'charlie@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'customer',
            'phone' => '+1 (555) 654-3210',
            'address' => '654 Maple Dr, City, State 12345',
            'status' => 'banned',
            'last_login_at' => now()->subMonths(1),
            'created_at' => now()->subMonths(5),
        ]);
    }
}