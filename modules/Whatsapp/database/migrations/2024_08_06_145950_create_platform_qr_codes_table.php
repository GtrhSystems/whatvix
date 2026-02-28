<?php

use App\Models\Platform;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Platform::class)->nullable()->constrained()->nullOnDelete();
            $table->string('code');
            $table->string('prefilled_message');
            $table->string('deep_link_url');
            $table->text('qr_image_url');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_qr_codes');
    }
};
