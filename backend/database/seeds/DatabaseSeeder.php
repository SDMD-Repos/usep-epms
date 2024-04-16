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
            AccessRightsSeeder::class,
            CascadingLevelSeeder::class,
            FormFieldSeeder::class,
            FormSeeder::class,
            OfficeTypesSeeder::class,
            OtherConfigSeeder::class,
            SignatoryTypeSeeder::class,



        ]);
    }
}
