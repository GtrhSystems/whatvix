<?php

use App\Models\User;
use App\Models\Platform;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\Whatsapp\App\Models\CloudApp;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('whatsapp_cloud_app_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Platform::class, 'platform_id')->constrained('platforms')->cascadeOnDelete();
            $table->foreignIdFor(CloudApp::class, 'app_id')->constrained('cloud_apps')->cascadeOnDelete();
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
        Schema::dropIfExists('cloud_app_logs');
    }
};
