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
        Schema::create('url_prices', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->boolean('is_valid')->default(0);
            $table->timestamp('parsed_at')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->json('ad_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('url_prices');
    }
};
