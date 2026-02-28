<?php

use App\Models\User;
use App\Models\Badge;
use App\Models\Group;
use App\Models\Message;
use App\Models\Campaign;
use App\Models\Customer;
use App\Models\Platform;
use App\Models\Template;
use App\Models\Conversation;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platforms', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('uuid', 1000)->nullable();
            $table->string('name');
            $table->string('picture', 1000)->nullable();
            $table->text('access_token')->nullable();
            $table->timestamp('access_token_expire_at');
            $table->text('refresh_token')->nullable();
            $table->timestamp('refresh_token_expire_at')->nullable();
            $table->text('meta')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });

        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Platform::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('uuid');
            $table->string('name');
            $table->string('picture')->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('platform_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Platform::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Customer::class)->nullable()->constrained()->cascadeOnDelete();
            $table->enum('direction', ['in', 'out']);
            $table->string('message_type');
            $table->text('message_text')->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignIdFor(Platform::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Badge::class)->nullable()->constrained()->cascadeOnDelete();
            $table->boolean('auto_reply_enabled')->default(false);
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Platform::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Conversation::class)->constrained()->cascadeOnDelete();
            $table->string('uuid', 191)->unique();
            $table->enum('direction', ['in', 'out']);
            $table->string('type');
            $table->text('body')->nullable();
            $table->enum('status', ['pending', 'sent', 'delivered', 'read', 'received', 'failed'])->default('pending');
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('quick_replies', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Platform::class)->nullable()->constrained()->cascadeOnDelete();
            $table->text('message_template');
            $table->string('status')->default('active');
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Platform::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('customer_group', function (Blueprint $table) {
            $table->foreignIdFor(Group::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Platform::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('meta')->nullable();
            $table->string('type')->nullable(); // template or interactive
            $table->string('status', 25)->default('active');
            $table->timestamps();
        });

        Schema::create('auto_replies', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Platform::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Template::class)->nullable()->constrained()->cascadeOnDelete();
            $table->text('keywords');
            $table->string('message_type')->default('text');
            $table->text('message_template')->nullable();
            $table->string('status')->default('active');
            $table->text('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Platform::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Group::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Template::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('message_type');
            $table->enum('send_type', ['draft', 'instant', 'scheduled']);
            $table->timestamp('schedule_at')->nullable();
            $table->string('timezone')->default('UTC');
            $table->text('delay_between')->nullable();
            $table->enum('status', ['draft', 'pending', 'scheduled', 'send', 'failed'])->default('draft');
            $table->text('meta')->nullable();
            $table->timestamps();
        });


        Schema::create('campaign_logs', function (Blueprint $table) {
            $table->id();
            $table->string('module');
            $table->foreignIdFor(User::class, 'owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignIdFor(Campaign::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Message::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Customer::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamp('send_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platforms');
        Schema::dropIfExists('platform_logs');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('quick_replies');
        Schema::dropIfExists('auto_replies');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('customer_group');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('campaign_logs');
    }
};
