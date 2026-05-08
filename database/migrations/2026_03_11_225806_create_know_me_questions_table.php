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
        Schema::create('know_me_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');           // السؤال عن الشخص
            $table->string('category')->default('daily'); // daily,future,family,personality,values,fun
            $table->string('answer_type')->default('open'); // open, choice
            $table->json('choices')->nullable(); // ["خيار 1","خيار 2",...] للأسئلة الاختيارية
            $table->text('hint')->nullable();    // تلميح للإجابة
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('know_me_questions');
    }
};
