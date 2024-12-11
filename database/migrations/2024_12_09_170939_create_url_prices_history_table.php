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
        Schema::create('url_prices_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('url_price_id')->constrained('url_prices')->onDelete('cascade');
            $table->boolean('is_valid');
            $table->timestamp('parsed_at')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->json('ad_data')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_prices_history');
    }
};
