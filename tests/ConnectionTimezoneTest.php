<?php

use Illuminate\Support\Carbon;
use Omatech\Editora\Connector\ConnectorServiceProvider;
use Omatech\Editora\Extractor\EditoraData;
use Omatech\Editora\Utils\Editora;
use Orchestra\Testbench\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class ConnectionTimezoneTest extends TestCase
{
    protected function getPackageProviders($app): array { return []; }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql', 'host' => 'fixture.invalid', 'port' => 3307,
            'database' => 'synthetic', 'username' => 'fixture', 'password' => 'fixture-only',
        ]);
        // Testbench applies its environment after RegisterProviders; configure this fixture before registering the package.
        $app->register(ConnectorServiceProvider::class);
    }

    protected function tearDown(): void { Carbon::setTestNow(); parent::tearDown(); }

    public static function instants(): array
    {
        return ['winter' => ['2026-11-17 10:00:00', '+01:00'], 'summer' => ['2026-07-17 10:00:00', '+02:00']];
    }

    #[DataProvider('instants')]
    public function test_all_connections_use_the_editors_current_offset(string $instant, string $offset): void
    {
        Carbon::setTestNow(Carbon::parse($instant, 'UTC'));
        $this->refreshApplication();
        self::assertSame($offset, config('database.connections.mysql.timezone'));
        foreach (['Extractor' => EditoraData::class, 'Utils' => Editora::class] as $binding => $class) {
            app($binding);
            $db = (new ReflectionClass($class))->getStaticPropertyValue('conn');
            self::assertInstanceOf(Doctrine\DBAL\Connection::class, $db);
            self::assertSame("SET time_zone = '$offset'", $db->getParams()['driverOptions'][1002] ?? null);
            self::assertFalse($db->isConnected(), 'Resolving a binding must not query application content');
        }
    }

    public function test_an_explicit_empty_zone_preserves_the_server_default(): void
    {
        config(['editora.database_timezone' => '']);
        app('Extractor');
        $db = (new ReflectionClass(EditoraData::class))->getStaticPropertyValue('conn');
        self::assertArrayNotHasKey('driverOptions', $db->getParams());
    }
}
