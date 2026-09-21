<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('game_id')->nullable()->change();
            $table->boolean('is_bundle')->default(false);
            $table->decimal('amount', 10, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['is_bundle', 'amount']);
        });
    }
};
