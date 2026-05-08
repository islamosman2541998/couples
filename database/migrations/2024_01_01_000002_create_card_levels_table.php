<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('card_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // سهل، متوسط، صعب
            $table->string('slug'); // easy, medium, hard
            $table->string('color')->default('#6366f1');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('card_levels');
    }
};
