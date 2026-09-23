<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_visitors', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('analytics_visits', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('visitor_id')->constrained('analytics_visitors')->cascadeOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('source', 150);
            $t->string('referrer_host', 150)->nullable();
            $t->string('campaign', 150)->nullable();
            $t->string('medium', 100)->nullable();
            $t->string('landing_path', 255);
            $t->string('last_path', 255);
            $t->string('device', 30);
            $t->string('browser', 40);
            $t->string('os', 40);
            $t->string('ip_network', 80)->nullable();
            $t->string('language', 35)->nullable();
            $t->string('timezone', 80)->nullable();
            $t->string('screen', 30)->nullable();
            $t->unsignedInteger('active_seconds')->default(0);
            $t->timestamp('last_seen_at')->index();
            $t->timestamps();
            $t->index(['created_at', 'source']);
        });
        Schema::create('analytics_events', function (Blueprint $t) {
            $t->id();
            $t->foreignUuid('visit_id')->constrained('analytics_visits')->cascadeOnDelete();
            $t->string('name', 40);
            $t->string('path', 255);
            $t->string('detail', 150)->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['created_at', 'name']);
        });
        Schema::table('subscriptions', function (Blueprint $t) {
            $t->foreignUuid('analytics_visit_id')->nullable()->constrained('analytics_visits')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', fn (Blueprint $t) => $t->dropConstrainedForeignId('analytics_visit_id'));
        Schema::dropIfExists('analytics_events');
        Schema::dropIfExists('analytics_visits');
        Schema::dropIfExists('analytics_visitors');
    }
};
