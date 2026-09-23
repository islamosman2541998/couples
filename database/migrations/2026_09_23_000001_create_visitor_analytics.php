<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A host may default to MyISAM, which silently ignores foreign keys.
        // Repair existing analytics tables before creating any dependent table.
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            foreach (['analytics_visitors', 'analytics_visits', 'analytics_events'] as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }
                $metadata = DB::selectOne(
                    'SELECT ENGINE AS storage_engine FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?',
                    [DB::connection()->getDatabaseName(), DB::connection()->getTablePrefix().$table]
                );
                if ($metadata && strcasecmp($metadata->storage_engine, 'InnoDB') !== 0) {
                    $name = DB::connection()->getQueryGrammar()->wrapTable($table);
                    DB::statement('ALTER TABLE '.$name.' ENGINE=InnoDB');
                }
            }
        }

        if (! Schema::hasTable('analytics_visitors')) {
            Schema::create('analytics_visitors', function (Blueprint $t) {
                $t->engine = 'InnoDB';
                $t->uuid('id')->primary();
                $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $t->timestamps();
            });
        }
        if (! Schema::hasTable('analytics_visits')) {
            Schema::create('analytics_visits', function (Blueprint $t) {
                $t->engine = 'InnoDB';
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
        }
        if (! Schema::hasTable('analytics_events')) {
            Schema::create('analytics_events', function (Blueprint $t) {
                $t->engine = 'InnoDB';
                $t->id();
                $t->foreignUuid('visit_id')->constrained('analytics_visits')->cascadeOnDelete();
                $t->string('name', 40);
                $t->string('path', 255);
                $t->string('detail', 150)->nullable();
                $t->timestamp('created_at')->useCurrent();
                $t->index(['created_at', 'name']);
            });
        }
        // MyISAM may have kept the indexes without creating the constraints.
        foreach ([
            ['analytics_visitors', 'user_id', 'users', 'set null'],
            ['analytics_visits', 'visitor_id', 'analytics_visitors', 'cascade'],
            ['analytics_visits', 'user_id', 'users', 'set null'],
            ['analytics_events', 'visit_id', 'analytics_visits', 'cascade'],
        ] as [$table, $column, $parent, $onDelete]) {
            $present = collect(Schema::getForeignKeys($table))
                ->contains(fn ($key) => $key['columns'] === [$column]);
            if (! $present) {
                Schema::table($table, function (Blueprint $t) use ($column, $parent, $onDelete) {
                    $t->foreign($column)->references('id')->on($parent)->onDelete($onDelete);
                });
            }
        }
        // MySQL DDL is not transactional: an earlier attempt may have created
        // all three tables and the column before failing to add the foreign key.
        $foreignKeys = Schema::getForeignKeys('subscriptions');
        foreach ($foreignKeys as $key) {
            if ($key['columns'] === ['analytics_visit_id']) {
                return;
            }
        }

        $exists = Schema::hasColumn('subscriptions', 'analytics_visit_id');
        $charset = $collation = null;
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            $column = DB::selectOne(
                'SELECT CHARACTER_SET_NAME AS charset_name, COLLATION_NAME AS collation_name FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [DB::connection()->getDatabaseName(), DB::connection()->getTablePrefix().'analytics_visits', 'id']
            );
            if (! $column || ! $column->charset_name || ! $column->collation_name) {
                throw new RuntimeException('Cannot read analytics_visits.id charset/collation; no existing data was removed.');
            }
            $charset = $column->charset_name;
            $collation = $column->collation_name;
        }

        Schema::table('subscriptions', function (Blueprint $t) use ($exists, $charset, $collation) {
            $column = $t->uuid('analytics_visit_id')->nullable();
            // Match the referenced column, not the old subscriptions table default.
            if ($charset) {
                $column->charset($charset)->collation($collation);
            }
            if ($exists) {
                $column->change();
            }
        });
        Schema::table('subscriptions', function (Blueprint $t) {
            $t->foreign('analytics_visit_id')->references('id')->on('analytics_visits')->nullOnDelete();
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
