<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            // Existing games keep the free trial they have today.
            $table->boolean('trial_enabled')->default(true)->after('is_free');
        });

        // The free trial shrinks from 5 items to 3 (owner's decision, 2026-11-23).
        \App\Models\Setting::set('trial_items', '3');
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('trial_enabled');
        });
    }
};
