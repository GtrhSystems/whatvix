<?php

use App\Models\User;
use App\Models\Platform;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Modules\WhatsappWeb\App\Models\WhatsappWebApp;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_web_app_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Platform::class, 'platform_id')->constrained('platforms')->cascadeOnDelete();
            $table->foreignIdFor(WhatsappWebApp::class, 'app_id')->constrained('whatsapp_web_apps')->cascadeOnDelete();
            $table->string('to');
            $table->string('status_code'); // 200
            $table->text('request');
            $table->text('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('whatsapp_web_app_logs');
    }
};
