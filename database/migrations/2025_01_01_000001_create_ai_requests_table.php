<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Request details
            $table->string('provider'); // openai, claude, etc.
            $table->string('model');
            $table->string('type'); // text, chat, embed, json

            // Prompt and response
            $table->text('prompt')->nullable();
            $table->json('messages')->nullable(); // For chat requests
            $table->longText('response')->nullable();

            // Token and cost tracking
            $table->integer('tokens_used')->default(0);
            $table->integer('input_tokens')->default(0);
            $table->integer('output_tokens')->default(0);
            $table->decimal('cost', 10, 6)->default(0);

            // Options and metadata
            $table->json('options')->nullable();
            $table->json('metadata')->nullable();

            // Response details
            $table->string('status')->default('success'); // success, error
            $table->text('error_message')->nullable();
            $table->integer('response_time_ms')->nullable();

            // Tracking
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['provider', 'model']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_requests');
    }
};
