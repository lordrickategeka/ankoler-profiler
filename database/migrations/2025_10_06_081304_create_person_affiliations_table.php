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
        Schema::create('person_affiliations', function (Blueprint $table) {
            $table->id();
            $table->string('affiliation_id')->unique(); // AFF-000001

            // Link to person and organization
            $table->foreignId('person_id')->constrained('persons')->onDelete('cascade');
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');

            // Site within organization (optional)
            $table->string('site')->nullable(); // Branch, Department, etc.

            // Role information
            $table->string('role_type'); // STAFF, MEMBER, PATIENT, STUDENT, etc.
            $table->string('role_title')->nullable(); // Nurse, Manager, etc.

            // Affiliation dates
            $table->date('start_date');
            $table->date('end_date')->nullable();

            // Status
            $table->enum('status', ['active', 'inactive', 'suspended', 'terminated'])->default('active');

            // Role-specific data pointer
            $table->string('domain_record_type')->nullable(); // staff_record, sacco_member_record, etc.
            $table->unsignedBigInteger('domain_record_id')->nullable(); // ID in the specific table

            // Permissions and access
            $table->json('permissions')->nullable();
            $table->boolean('can_view_cross_org_data')->default(false);

            // Metadata
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            // Ensure unique affiliation per person per organization
            $table->unique(['person_id', 'organization_id', 'role_type']);

            // Indexes
            $table->index(['person_id', 'status']);
            $table->index(['organization_id', 'status']);
            $table->index(['role_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_affiliations');
    }
};
