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
        Schema::create('Organization_jobs', function (Blueprint $table) {
           $table->id();
            $table->unsignedBigInteger('organization_id')->nullable()->index();
            $table->unsignedBigInteger('category_id')->nullable()->index();
            $table->unsignedBigInteger('posted_by')->nullable()->index();

            $table->string('title');
            $table->longText('description')->nullable();
            $table->enum('job_type', ['Full-time', 'Part-time', 'Contract', 'Internship', 'Volunteer'])->default('Full-time');
            $table->string('location')->nullable();
            $table->string('salary_range')->nullable();
            $table->enum('experience_level', ['Entry', 'Mid', 'Senior'])->default('Entry');
            $table->text('qualifications')->nullable();
            $table->dateTime('application_deadline')->nullable();
            $table->enum('status', ['Open', 'Closed', 'Archived'])->default('Open');

            $table->timestamps();

            // Foreign keys
            $table->foreign('organization_id')->references('id')->on('Organizations')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('job_categories')->onDelete('set null');
            $table->foreign('posted_by')->references('id')->on('users')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Organization_jobs');
    }
};
