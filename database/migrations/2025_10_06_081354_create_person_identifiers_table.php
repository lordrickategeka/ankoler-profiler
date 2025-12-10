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
        Schema::create('person_identifiers', function (Blueprint $table) {
            $table->id();
            $table->string('identifier_id')->unique(); // ID-000001

            // Link to person
            $table->foreignId('person_id')->constrained('persons')->onDelete('cascade');

            // Identifier details
            $table->string('type')->nullable(); // national_id, passport, driving_license, etc.
            $table->string('identifier')->nullable(); // CM950320123456XYZ
            $table->string('issuing_authority')->nullable(); // NIRA, Immigration, etc.
            $table->date('issued_date')->nullable();
            $table->date('expiry_date')->nullable();

            // Verification
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->string('verified_by')->nullable();

            // Privacy settings
            $table->boolean('is_public')->default(false);
            $table->enum('visibility', ['private', 'organization', 'public'])->default('private');

            // Status
            $table->enum('status', ['active', 'expired', 'revoked'])->default('active');

            // Metadata
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            // Critical index for deduplication
            $table->index(['type', 'identifier']);
            $table->unique(['type', 'identifier']); // One person per ID type per number
            $table->index('person_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_identifiers');
    }
};
