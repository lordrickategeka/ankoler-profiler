<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('organization_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('organization_id');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('parent_unit_id')->nullable();
            $table->boolean('is_active')->default(true);

            // Step 1: Basic Info
            $table->string('unit_type')->nullable();
            $table->string('department')->nullable();
            $table->string('community')->nullable();
            $table->string('ministry_committee')->nullable();
            $table->string('administrative_office')->nullable();

            // Step 2: Leadership & Governance
            $table->unsignedBigInteger('unit_head')->nullable();
            $table->unsignedBigInteger('assistant_leader')->nullable();
            $table->json('leadership_committee')->nullable();
            $table->string('appointment_dates')->nullable();
            $table->string('reporting_line')->nullable();

            // Step 3: Purpose & Mission
            $table->text('mission')->nullable();
            $table->text('objectives')->nullable();
            $table->text('activities')->nullable();
            $table->string('target_audience')->nullable();

            // Step 4: Contact Information
            $table->string('official_email')->nullable();
            $table->string('phone_contact')->nullable();
            $table->string('physical_location')->nullable();
            $table->string('website')->nullable();
            $table->string('social_links')->nullable();

            // Step 5: Operational Details
            $table->string('unit_category')->nullable();
            $table->boolean('faith_based')->default(false);
            $table->boolean('socio_economic')->default(false);
            $table->boolean('support_services')->default(false);
            $table->enum('operational_status', ['active', 'inactive', 'pending'])->default('active');
            $table->date('date_established')->nullable();

            // Step 6: Membership Metadata
            $table->string('membership_type')->nullable();
            $table->string('membership_eligibility')->nullable();
            $table->integer('membership_capacity')->nullable();
            $table->boolean('join_requests_enabled')->default(false);

            // Step 7: Events & Programs Metadata
            $table->string('recurring_programs')->nullable();
            $table->string('event_schedule')->nullable();
            $table->string('promotion_permissions')->nullable();
            $table->string('resource_access_requirements')->nullable();

            // Step 8: Showcase & Marketplace Support
            $table->string('showcase_permissions')->nullable();
            $table->string('product_categories_allowed')->nullable();
            $table->string('approval_workflow')->nullable();
            $table->string('commission_structure')->nullable();

            // Step 9: Roles & Permissions for Unit Users
            $table->json('unit_roles')->nullable();

            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('Organizations')->onDelete('cascade');
            $table->foreign('parent_unit_id')->references('id')->on('organization_units')->onDelete('set null');
            $table->foreign('unit_head')->references('id')->on('persons')->onDelete('set null');
            $table->foreign('assistant_leader')->references('id')->on('persons')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organization_units');
    }
};
