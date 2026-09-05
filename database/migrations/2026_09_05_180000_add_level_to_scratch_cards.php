<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('scratch_cards', fn (Blueprint $table) => $table->unsignedTinyInteger('level')->default(1));
    }

    public function down(): void
    {
        Schema::table('scratch_cards', fn (Blueprint $table) => $table->dropColumn('level'));
    }
};
