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
        Schema::create('patient_records', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('affiliation_id')->unique();
            $table->string('patient_number')->nullable();
            $table->string('medical_record_number')->nullable();
            $table->unsignedBigInteger('primary_physician_id')->nullable();
            $table->unsignedBigInteger('primary_care_unit_id')->nullable();
            $table->text('allergies')->nullable();
            $table->text('chronic_conditions')->nullable();
            $table->timestamp('last_visit')->nullable();
            $table->json('clinical_notes')->nullable();
            $table->timestamps();

            $table->foreign('affiliation_id')
                  ->references('id')
                  ->on('person_affiliations')
                  ->onDelete('cascade');

            $table->foreign('primary_physician_id')
                  ->references('id')
                  ->on('persons')
                  ->onDelete('set null');

            $table->foreign('primary_care_unit_id')
                  ->references('id')
                  ->on('Organizations')
                  ->onDelete('set null');

            $table->index('patient_number');
            $table->index('medical_record_number');
            $table->index('primary_physician_id');
            $table->index('primary_care_unit_id');
            $table->index('last_visit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_records');
    }
};
