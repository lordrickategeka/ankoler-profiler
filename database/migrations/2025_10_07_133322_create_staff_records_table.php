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
        Schema::create('staff_records', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('affiliation_id')->unique();
            $table->string('staff_number')->nullable();
            $table->string('payroll_id')->nullable();
            $table->string('employment_type')->nullable()->comment('FULL_TIME, PART_TIME, CONTRACT, INTERN, CONSULTANT');
            $table->string('grade')->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->unsignedBigInteger('supervisor_id')->nullable();
            $table->json('work_schedule')->nullable();
            $table->json('hr_notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('affiliation_id')
                  ->references('id')
                  ->on('person_affiliations')
                  ->onDelete('cascade');

            $table->foreign('supervisor_id')
                  ->references('id')
                  ->on('persons')
                  ->onDelete('set null');

            // Indexes
            $table->index('staff_number');
            $table->index('payroll_id');
            $table->index('employment_type');
            $table->index('supervisor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_records');
    }
};
