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
        Schema::create('communication_filter_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // User-friendly name for the filter profile
            $table->text('description')->nullable(); // Optional description

            // Ownership and organization context
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('organisation_id')->constrained('organisations')->onDelete('cascade');

            // Filter criteria (stored as JSON)
            $table->json('filter_criteria'); // All filter parameters

            // Metadata
            $table->boolean('is_active')->default(true);
            $table->boolean('is_shared')->default(false); // Can other users in org use this profile?
            $table->integer('usage_count')->default(0); // Track how often it's used
            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'organisation_id']);
            $table->index(['organisation_id', 'is_shared']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_filter_profiles');
    }
};
