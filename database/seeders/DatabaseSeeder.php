<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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
            UsersTableSeeder::class,
            PlanSeeder::class,
            OptionsTableSeeder::class,
            GatewaysTableSeeder::class,
            CategoriesTableSeeder::class,
            IntegrationTableSeeder::class,
          
            ServiceSeeder::class,
            ProjectSeeder::class,
            MenusTableSeeder::class,
            PostsTableSeeder::class,
            PostCategoryTableSeeder::class,
            PostMetaTableSeeder::class,
            AiModelSeeder::class,
            WAServerTablesSeeder::class
        ]);

    }
}
