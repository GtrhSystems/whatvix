<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WAServerTablesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sqlFile = database_path('whatsapp-server-db.sql');

        if (!file_exists($sqlFile)) {
            Log::error("SQL file not found: {$sqlFile}");
            return;
        }

        try {
            $sqlContent = file_get_contents($sqlFile);

            // Execute the raw SQL content
            DB::unprepared($sqlContent);

            Log::info("SQL file imported successfully from {$sqlFile}");
        } catch (\Throwable $th) {
            Log::error('SQL Import Error: ' . $th->getMessage());
        }
    }
}
