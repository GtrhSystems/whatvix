<?php

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->longText('modules')->nullable();
            $table->timestamps();
        });

        Schema::create('workspace_users', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Workspace::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
        });

        Schema::create("user_team_members", function (Blueprint $table) {
            $table->foreignId("user_id")->constrained('users')->cascadeOnDelete();
            $table->foreignId("member_id")->constrained('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspaces');
        Schema::dropIfExists('workspace_users');
        Schema::dropIfExists("user_team_members");
    }
};
