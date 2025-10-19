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
        Schema::create('sms_delivery_reports', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->unique(); // Africa's Talking message ID
            $table->string('phone_number');
            $table->string('status'); // Success, Failed, Sent, Queued, etc.
            $table->string('network_code')->nullable();
            $table->string('failure_reason')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamp('delivered_at')->nullable();
            $table->json('webhook_payload')->nullable(); // Store full webhook data
            $table->timestamps();

            $table->index(['message_id', 'status']);
            $table->index('phone_number');
            $table->index('delivered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_delivery_reports');
    }
};
