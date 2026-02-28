<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Modules\QAReply\App\Models\AutoResponse;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auto_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->timestamps();
        });

        Schema::create('auto_response_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(AutoResponse::class, 'auto_response_id')->constrained('auto_responses')->cascadeOnDelete();
            $table->longText('key');
            $table->text('value');
            $table->timestamps();

            $table->fullText(['key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auto_response_items', function (Blueprint $table) {
            $table->dropFullText(['key']);
        });
        Schema::dropIfExists('auto_response');
        Schema::dropIfExists('auto_response_items');
    }
};
