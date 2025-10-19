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
        Schema::create('communication_messages', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->unique(); // Our internal message ID
            $table->string('provider_message_id')->nullable(); // Provider's message ID

            // Sender information
            $table->foreignId('sent_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('organisation_id')->constrained('organisations')->onDelete('cascade');

            // Recipient information
            $table->foreignId('recipient_person_id')->nullable()->constrained('persons')->onDelete('set null');
            $table->string('recipient_identifier'); // email, phone number, etc.
            $table->string('recipient_type'); // email, sms, whatsapp

            // Message content
            $table->string('channel'); // email, sms, whatsapp
            $table->string('subject')->nullable(); // For emails
            $table->text('content');
            $table->string('message_type')->default('text'); // text, template, media, etc.
            $table->json('template_data')->nullable(); // For template messages
            $table->json('attachments')->nullable(); // File attachments

            // Status tracking
            $table->string('status')->default('pending'); // pending, sent, delivered, read, failed, etc.
            $table->text('error_message')->nullable();
            $table->json('delivery_details')->nullable(); // Provider-specific delivery info

            // Scheduling
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();

            // Metadata
            $table->json('metadata')->nullable(); // Additional data
            $table->integer('priority')->default(5); // 1-10, lower = higher priority
            $table->boolean('is_bulk_message')->default(false);
            $table->string('bulk_message_id')->nullable(); // Group bulk messages

            // Provider information
            $table->string('provider')->nullable(); // twilio, africas_talking, etc.
            $table->json('provider_response')->nullable();

            // Audit fields
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['channel', 'status']);
            $table->index(['recipient_person_id']);
            $table->index(['organisation_id']);
            $table->index(['sent_by_user_id']);
            $table->index(['bulk_message_id']);
            $table->index(['scheduled_at']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('communication_messages');
    }
};
