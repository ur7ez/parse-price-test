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
        Schema::create('price_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscriber_id')->constrained('subscribers')->cascadeOnDelete();
            $table->text('data'); // JSON or raw HTML contents for the email
            $table->timestamp('queued_at')->nullable();
            $table->string('message_id')->nullable();
            $table->timestamp('sent_at')->nullable();
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('last_price_notification_id')->nullable()->constrained('price_notifications')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key from subscriptions table
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_price_notification_id');
        });
        // Drop the price_notifications table
        Schema::dropIfExists('price_notifications');
    }
};
