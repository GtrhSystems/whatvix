<?php

use App\Models\Platform;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_web_apps', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->uuid('key');
            $table->foreignIdFor(Platform::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->nullOnDelete();
            $table->string('name');
            $table->string('site_link');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_web_apps');
    }
};
