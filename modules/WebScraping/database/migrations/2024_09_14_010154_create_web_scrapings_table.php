<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('web_scrapings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->uuid();
            $table->string('module');
            $table->string('title');
            $table->string('type')->default('google_places');
            $table->text('parameters'); // search parameters
            $table->enum('status', ['pending', 'in_progress', 'completed', 'failed'])->default('pending');
            $table->integer('query_count')->default(0);
            $table->timestamps();
        });

        Schema::create('web_scraped_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('web_scraping_id')->constrained()->onDelete('cascade');
            $table->string('unique_id');
            $table->text('data'); // JSON
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('web_scrapings');
    }
};
