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
        Schema::create('persons', function (Blueprint $table) {
            $table->id();
            $table->string('person_id')->unique(); // PRS-000001
            $table->uuid('global_identifier')->unique(); // Global UUID across all orgs
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');

            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Personal Information
            $table->string('given_name');
            $table->string('middle_name')->nullable();
            $table->string('family_name');
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();

            // Classification - array of roles this person has
            $table->json('classification')->nullable(); // ['STAFF', 'MEMBER', 'PATIENT', etc.]

            // Address Information
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('country')->default('Uganda');

            // Status
            $table->enum('status', ['active', 'inactive', 'deceased'])->default('active');

            // Metadata
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            // Indexes for deduplication
            $table->index(['given_name', 'family_name', 'date_of_birth']);
            $table->index('date_of_birth');
            $table->index('global_identifier');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persons');
    }
};
