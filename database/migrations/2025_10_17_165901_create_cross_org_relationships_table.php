<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cross_org_relationships', function (Blueprint $table) {
            $table->id();
            $table->string('cross_relationship_id')->unique();
            $table->unsignedBigInteger('person_id');
            $table->unsignedBigInteger('primary_affiliation_id');
            $table->unsignedBigInteger('secondary_affiliation_id');

            $table->string('relationship_context')->nullable()
                ->comment('e.g., parent_at_school_patient_at_hospital');

            $table->enum('relationship_strength', ['weak', 'moderate', 'strong'])
                ->default('moderate');

            $table->timestamp('discovered_date')->useCurrent()->nullable();

            $table->enum('discovery_method', ['automatic', 'manual', 'import', 'temporal_analysis'])
                ->default('automatic');

            $table->boolean('verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();

            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');

            $table->decimal('impact_score', 3, 2)->default(0.50)
                ->comment('Business impact score 0.00-1.00');

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Unique Constraints
            $table->unique(
                ['person_id', 'primary_affiliation_id', 'secondary_affiliation_id'],
                'cross_org_relationships_unique_pair'
            );

            // Foreign Keys
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('cascade');
            $table->foreign('primary_affiliation_id')->references('id')->on('person_affiliations')->onDelete('cascade');
            $table->foreign('secondary_affiliation_id')->references('id')->on('person_affiliations')->onDelete('cascade');
            $table->foreign('verified_by')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index(['person_id', 'primary_affiliation_id', 'status'], 'idx_cross_org_person_primary');
            $table->index(['discovery_method', 'verified', 'status'], 'idx_cross_org_discovery');
            $table->index(['relationship_strength', 'impact_score'], 'idx_cross_org_strength');
        });

        // Check Constraints (MySQL 8+ only)
        DB::statement('ALTER TABLE cross_org_relationships ADD CONSTRAINT chk_cross_org_different_affiliations CHECK (primary_affiliation_id <> secondary_affiliation_id)');
        DB::statement('ALTER TABLE cross_org_relationships ADD CONSTRAINT chk_cross_org_impact_score CHECK (impact_score >= 0.00 AND impact_score <= 1.00)');
    }

    public function down(): void
    {
        Schema::dropIfExists('cross_org_relationships');
    }
};
