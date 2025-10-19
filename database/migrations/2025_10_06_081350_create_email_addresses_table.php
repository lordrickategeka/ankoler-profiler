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
        Schema::create('email_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('email_id')->unique(); // EML-000001
            
            // Link to person
            $table->foreignId('person_id')->constrained('persons')->onDelete('cascade');
            
            // Optional link to organization (for work emails)
            $table->foreignId('organisation_id')->nullable()->constrained('organisations')->onDelete('cascade');
            
            // Email details
            $table->string('email')->lowercase(); // jane.doe@email.com
            $table->string('type')->default('personal'); // personal, work, other
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            
            // Privacy settings
            $table->boolean('is_public')->default(false);
            $table->enum('visibility', ['private', 'organization', 'public'])->default('private');
            
            // Status
            $table->enum('status', ['active', 'inactive', 'bounced'])->default('active');
            
            // Metadata
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            
            // Critical index for deduplication
            $table->index('email');
            $table->index(['person_id', 'is_primary']);
            $table->index(['person_id', 'organisation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_addresses');
    }
};
