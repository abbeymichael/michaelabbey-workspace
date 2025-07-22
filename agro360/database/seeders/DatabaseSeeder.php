<?php

namespace Database\Seeders;

use App\Models\SpecificCropOrAnimal;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Database\Seeders\FarmTypesSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
     
        $this->call([
        //FarmTypeSeeder::class,
       // SubFarmTypeSeeder::class,
        //SpecificCropOrAnimalSeeder::class,
         FarmingMethodSeeder::class,
       
        // UserSeeder::class, // Uncomment if you have a UserSeeder to create dummy users
            // Add other seeders here as needed
        ]);
        
  
    }
}
