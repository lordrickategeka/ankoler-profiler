<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPasswordToPersonsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->string('password')->nullable()->after('created_by'); // Adjust 'email' to the correct column if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('persons', function (Blueprint $table) {
            $table->dropColumn('password');
        });
    }
}
