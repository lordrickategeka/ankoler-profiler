<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    { Schema::create('person_relationships', function (Blueprint $table) {
            $table->id();
            $table->string('relationship_id')->unique();
            $table->unsignedBigInteger('person_a_id');
            $table->unsignedBigInteger('person_b_id');

            $table->enum('relationship_type', [
                'parent_child', 'spouse', 'sibling', 'guardian_ward',
                'emergency_contact', 'next_of_kin', 'dependent',
                'colleague', 'business_partner'
            ]);

            $table->enum('direction', [
                'bidirectional', 'a_to_b', 'b_to_a'
            ])->default('bidirectional');

            $table->boolean('is_primary')->default(false);
            $table->decimal('confidence_score', 3, 2)->default(1.00)
                ->comment('Confidence level 0.00-1.00');

            $table->enum('discovery_method', [
                'manual', 'address_match', 'contact_match',
                'name_pattern', 'temporal_pattern', 'user_import'
            ])->nullable();

            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->enum('status', [
                'active', 'inactive', 'pending_verification', 'disputed'
            ])->default('active');

            $table->enum('verification_status', [
                'unverified', 'verified', 'rejected'
            ])->default('unverified');

            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();

            $table->json('notes')->nullable();
            $table->json('metadata')->nullable()->comment('Additional relationship context');

            $table->char('created_by', 36)->nullable();
            $table->char('updated_by', 36)->nullable();

            $table->timestamps();

            // Constraints
            $table->unique(['person_a_id', 'person_b_id', 'relationship_type'], 'unique_pair');

            $table->foreign('person_a_id')->references('id')->on('persons')->onDelete('cascade');
            $table->foreign('person_b_id')->references('id')->on('persons')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['person_a_id', 'relationship_type', 'status'], 'idx_lookup');
            $table->index(['person_b_id', 'relationship_type', 'status'], 'idx_reverse');
            $table->index(['verification_status', 'confidence_score'], 'idx_verification');
        });

        // Add Check Constraints (MySQL 8+)
        DB::statement('ALTER TABLE person_relationships ADD CONSTRAINT chk_different_persons CHECK (person_a_id <> person_b_id)');
        DB::statement('ALTER TABLE person_relationships ADD CONSTRAINT chk_confidence CHECK (confidence_score >= 0.00 AND confidence_score <= 1.00)');
    }

    public function down(): void
    {
        Schema::dropIfExists('person_relationships');
    }
};
