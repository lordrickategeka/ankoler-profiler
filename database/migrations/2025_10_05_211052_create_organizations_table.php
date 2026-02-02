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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();

            // COMMON FIELDS - Basic Information
            $table->string('category');
            $table->string('legal_name');
            $table->string('code', 20)->unique();
            $table->enum('organization_type', ['super', 'branch', 'HOLDING', 'SUBSIDIARY', 'STANDALONE'])->default('branch');
            $table->boolean('is_super')->default(false);
            $table->string('registration_number')->nullable();
            $table->string('country'); // ISO country code
            $table->date('date_established')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->boolean('is_active')->default(true);


            // Primary Address
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();


            // Contact Persons
            $table->string('primary_contact_name')->nullable();
            $table->string('primary_contact_email')->nullable();
            $table->string('primary_contact_phone')->nullable();


            // Add timestamps for created_at and updated_at
            $table->timestamps();
        });

        // Create organization_sites table for multi-site support
        Schema::create('organization_sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained('organizations')->onDelete('cascade');
            $table->string('site_name');
            $table->string('site_code', 20)->unique();
            $table->enum('site_type', ['branch', 'campus', 'ward', 'department', 'clinic', 'office']);
            $table->text('address_line_1');
            $table->text('address_line_2')->nullable();
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country', 3)->default('UGA');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('site_contact_name')->nullable();
            $table->string('site_contact_phone')->nullable();
            $table->string('site_contact_email')->nullable();
            $table->time('operating_hours_start')->nullable();
            $table->time('operating_hours_end')->nullable();
            $table->json('services_available')->nullable(); // What services are available at this site
            $table->json('site_specific_details')->nullable(); // Site-specific data
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['organization_id', 'is_active']);
            $table->index(['site_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organization_sites');
        Schema::dropIfExists('organizations');
    }
};
