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
        Schema::create('sacco_member_records', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->unsignedBigInteger('affiliation_id')->unique();
            $table->string('membership_number')->nullable();
            $table->date('join_date')->nullable();
            $table->decimal('share_capital', 18, 2)->default(0);
            $table->string('savings_account_ref')->nullable();
            $table->json('sacco_notes')->nullable();
            $table->timestamps();

            $table->foreign('affiliation_id')
                  ->references('id')
                  ->on('person_affiliations')
                  ->onDelete('cascade');

            $table->index('membership_number');
            $table->index('join_date');
            $table->index('share_capital');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sacco_member_records');
    }
};
