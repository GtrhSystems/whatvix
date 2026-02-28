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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->nullableMorphs('assetable');
            $table->string('type')->default('uploaded');
            $table->string('name');
            $table->string('original_name')->nullable();
            $table->string('mime_type');
            $table->string('file_type');
            $table->string('path');
            $table->text('file_size')->nullable()->comment('megabytes');
            $table->string('for')->nullable(); // reason for what based this asset
            $table->foreignId('original_asset_id')->nullable()->constrained('assets')->nullOnDelete();
            $table->string('filesystem_driver')->default('local');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
