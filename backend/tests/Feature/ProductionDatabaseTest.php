<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\DB;
use LogicException;
use Tests\TestCase;

class ProductionDatabaseTest extends TestCase
{
    public function test_sqlite_is_rejected_in_production(): void
    {
        $this->app->instance('env', 'production');
        config(['database.default' => 'sqlite']);
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Production requires PostgreSQL');

        (new AppServiceProvider($this->app))->boot();
    }

    public function test_a_sqlite_url_cannot_bypass_the_production_restriction(): void
    {
        $this->app->instance('env', 'production');
        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql.url' => 'sqlite:///:memory:',
        ]);
        DB::purge('pgsql');
        $this->expectException(LogicException::class);

        (new AppServiceProvider($this->app))->boot();
    }

    public function test_postgresql_url_configuration_does_not_require_a_connection_to_boot(): void
    {
        $this->app->instance('env', 'production');
        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql.url' => 'postgresql://postgres@127.0.0.1/daily_wins?sslmode=require',
        ]);
        DB::purge('pgsql');

        (new AppServiceProvider($this->app))->boot();

        $connection = DB::connection();
        $this->assertSame('pgsql', $connection->getDriverName());
        $this->assertSame('daily_wins', $connection->getDatabaseName());
        $this->assertSame('require', $connection->getConfig('sslmode'));
        $this->getJson('/api/health')->assertOk()->assertExactJson(['status' => 'ok']);
    }
}
