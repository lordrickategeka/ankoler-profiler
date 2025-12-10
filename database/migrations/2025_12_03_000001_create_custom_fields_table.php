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
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('model_type'); // e.g., 'Organization', 'person'
            $table->unsignedBigInteger('model_id');
            $table->string('field_name');
            $table->string('field_label')->nullable();
            $table->string('field_type')->default('string'); // string, number, date, boolean, etc.
            $table->text('field_value')->nullable();
            $table->json('field_options')->nullable(); // for select/dropdown fields
            $table->boolean('is_required')->default(false);
            $table->string('validation_rules')->nullable(); // string or json
            $table->string('group')->nullable();
            $table->integer('order')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_fields');
    }
};
