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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->nullable()->after('id');
            $table->unsignedBigInteger('organisation_id')->nullable()->after('person_id');

            // Add foreign key constraints
            $table->foreign('person_id')->references('id')->on('persons')->onDelete('set null');
            $table->foreign('organisation_id')->references('id')->on('organisations')->onDelete('set null');

            // Add indexes for better performance
            $table->index('person_id');
            $table->index('organisation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['person_id']);
            $table->dropForeign(['organisation_id']);
            $table->dropColumn(['person_id', 'organisation_id']);
        });
    }
};
