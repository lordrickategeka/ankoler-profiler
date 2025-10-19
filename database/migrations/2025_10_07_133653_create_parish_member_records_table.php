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
        Schema::create('parish_member_records', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('affiliation_id')->unique();
            $table->string('member_number')->nullable();
            $table->string('communion_status')->nullable();
            $table->date('baptism_date')->nullable();
            $table->json('parish_notes')->nullable();
            $table->timestamps();

            $table->foreign('affiliation_id')
                  ->references('id')
                  ->on('person_affiliations')
                  ->onDelete('cascade');

            $table->index('member_number');
            $table->index('baptism_date');
            $table->index('communion_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parish_member_records');
    }
};
