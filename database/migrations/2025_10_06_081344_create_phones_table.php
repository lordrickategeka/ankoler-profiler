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
        Schema::create('phones', function (Blueprint $table) {
            $table->id();
            $table->string('phone_id')->unique(); // PHN-000001

            // Link to person
            $table->foreignId('person_id')->constrained('persons')->onDelete('cascade');

            // Phone details
            $table->string('number')->nullable(); // +256700123456
            $table->string('type')->default('mobile'); // mobile, landline, fax
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);

            // Privacy settings
            $table->boolean('is_public')->default(false); // Can other orgs see this?
            $table->enum('visibility', ['private', 'organization', 'public'])->default('private');

            // Status
            $table->enum('status', ['active', 'inactive'])->default('active');

            // Metadata
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();

            // Critical index for deduplication
            $table->index('number');
            $table->index(['person_id', 'is_primary']);
            $table->index('person_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phones');
    }
};
