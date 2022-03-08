<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CascadingLevelSeeder::class,
            FormSeeder::class,
            OfficeTypesSeeder::class,
            SignatoryTypeSeeder::class,
            FormFieldSeeder::class,
            AccessRightsSeeder::class,
        ]);
    }
}
