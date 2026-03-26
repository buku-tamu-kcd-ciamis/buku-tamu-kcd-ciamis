<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        if($this->command->confirm('Do You want to Refresh migration before seeding, it will clear all old data ?')) {
            $this->command->call('migrate:fresh');
            $this->command->info('Data cleared, starting from blank database.');
        }

        $this->call([
            DropdownOptionSeeder::class,
            RoleUserSeeder::class,
            StaffRoleSeeder::class,
            PegawaiSeeder::class,
            UserSeeder::class,
        ]);
        $this->command->info('sample user seeded.');

        if($this->command->confirm('Do You want to seed some sample product ?')) {
            $this->call(CategorySeeder::class);
            $this->call(ProductSeeder::class);
            $this->command->info('10 sample products seeded.');
        }
    }
}
