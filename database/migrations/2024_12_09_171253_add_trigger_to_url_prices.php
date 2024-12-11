<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            CREATE TRIGGER url_prices_update_trigger
            AFTER UPDATE ON url_prices
            FOR EACH ROW
            BEGIN
                IF COALESCE(OLD.price, 0) != COALESCE(NEW.price, 0) OR OLD.is_valid != NEW.is_valid THEN
                    INSERT INTO url_prices_history (url_price_id, is_valid, parsed_at, price, ad_data)
                    VALUES (NEW.id, NEW.is_valid, NEW.parsed_at, NEW.price, NEW.ad_data);
                END IF;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP TRIGGER IF EXISTS url_prices_update_trigger");
    }
};
