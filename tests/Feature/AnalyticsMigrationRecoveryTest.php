<?php

namespace Tests\Feature;

use App\Models\AnalyticsVisitor;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class AnalyticsMigrationRecoveryTest extends TestCase
{
    use RefreshDatabase;

    public function test_retry_restores_constraints_ignored_by_myisam_without_removing_data(): void
    {
        $visitor = AnalyticsVisitor::create(['id' => (string) Str::uuid()]);
        foreach ([
            ['analytics_visitors', 'user_id'],
            ['analytics_visits', 'visitor_id'],
            ['analytics_visits', 'user_id'],
            ['analytics_events', 'visit_id'],
        ] as [$table, $column]) {
            Schema::table($table, fn (Blueprint $t) => $t->dropForeign([$column]));
        }
        $migration = require database_path('migrations/2026_09_23_000001_create_visitor_analytics.php');
        $migration->up();
        $migration->up();
        $this->assertCount(1, Schema::getForeignKeys('analytics_visitors'));
        $this->assertCount(2, Schema::getForeignKeys('analytics_visits'));
        $this->assertCount(1, Schema::getForeignKeys('analytics_events'));
        $this->assertDatabaseHas('analytics_visitors', ['id' => $visitor->id]);
    }

    public function test_retry_keeps_existing_data_and_restores_missing_foreign_key(): void
    {
        $visitor = AnalyticsVisitor::create(['id' => (string) Str::uuid()]);
        Schema::table('subscriptions', fn (Blueprint $t) => $t->dropForeign(['analytics_visit_id']));
        $migration = require database_path('migrations/2026_09_23_000001_create_visitor_analytics.php');
        $migration->up();
        $migration->up();
        $this->assertDatabaseHas('analytics_visitors', ['id' => $visitor->id]);
        $keys = collect(Schema::getForeignKeys('subscriptions'))->filter(fn ($key) => $key['columns'] === ['analytics_visit_id']);
        $this->assertCount(1, $keys);
        $this->assertSame('set null', $keys->first()['on_delete']);
    }

    public function test_retry_after_tables_created_but_column_missing(): void
    {
        $visitor = AnalyticsVisitor::create(['id' => (string) Str::uuid()]);
        Schema::table('subscriptions', fn (Blueprint $t) => $t->dropConstrainedForeignId('analytics_visit_id'));
        $migration = require database_path('migrations/2026_09_23_000001_create_visitor_analytics.php');
        $migration->up();
        $this->assertTrue(Schema::hasColumn('subscriptions', 'analytics_visit_id'));
        $this->assertDatabaseHas('analytics_visitors', ['id' => $visitor->id]);
    }
}
