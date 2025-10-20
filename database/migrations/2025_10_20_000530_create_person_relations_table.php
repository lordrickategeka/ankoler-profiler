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
        Schema::create('person_relations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('person_id')->constrained('persons')->onDelete('cascade');
    $table->foreignId('related_person_id')->constrained('persons')->onDelete('cascade');
    $table->enum('relationship_type', [
        'parent', 'child', 'sibling', 'spouse', 'guardian',
        'doctor', 'teacher', 'emergency_contact', 'friend',
        'colleague', 'caregiver', 'next_of_kin'
    ]);
    $table->boolean('is_primary')->default(false);
    $table->boolean('is_emergency_contact')->default(false);
    $table->text('notes')->nullable();
    $table->string('status')->default('active');
    $table->timestamps();

    // Indexes
    $table->index(['person_id', 'relationship_type']);
    $table->index(['related_person_id', 'relationship_type']);
    $table->index('is_primary');
    $table->index('status');

    // Prevent duplicate relationships
    $table->unique(
        ['person_id', 'related_person_id', 'relationship_type'],
        'unique_person_relation'
    );
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('person_relations');
    }
};
