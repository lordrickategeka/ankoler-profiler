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
        Schema::create('student_records', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('affiliation_id')->unique();
            $table->string('student_number')->nullable();
            $table->date('enrollment_date')->nullable();
            $table->date('graduation_date')->nullable();
            $table->string('current_class')->nullable();
            $table->json('guardian_contact')->nullable();
            $table->json('academic_notes')->nullable();
            $table->timestamps();

            $table->foreign('affiliation_id')
                  ->references('id')
                  ->on('person_affiliations')
                  ->onDelete('cascade');

            $table->index('student_number');
            $table->index('enrollment_date');
            $table->index('current_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_records');
    }
};
