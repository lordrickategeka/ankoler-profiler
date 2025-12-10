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
        Schema::create('communication_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Template name
            $table->text('description')->nullable(); // Template description

            // Ownership and organization context
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');

            // Template content
            $table->string('subject')->nullable(); // For email
            $table->text('content'); // Template body with variables like {{given_name}}

            // Template metadata
            $table->enum('category', ['general', 'appointment', 'reminder', 'marketing', 'emergency', 'survey', 'welcome'])->default('general');
            $table->json('supported_channels'); // ['sms', 'whatsapp', 'email']
            $table->json('variables')->nullable(); // Available variables for substitution

            // Sharing and usage
            $table->boolean('is_active')->default(true);
            $table->boolean('is_shared')->default(false); // Can other users in org use this template?
            $table->integer('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'organization_id']);
            $table->index(['organization_id', 'category']);
            $table->index(['organization_id', 'is_shared']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_templates');
    }
};
