<?php

namespace Omatech\Editora\Connector;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

/** Connect lazily and preserve the same wall clock as the editor and Laravel. */
final class DatabaseConnection
{
    public static function offset(): ?string
    {
        $zone = config('editora.database_timezone', 'Europe/Madrid');
        return $zone === '' ? null : now(new \DateTimeZone($zone))->format('P');
    }

    public static function open(): Connection
    {
        $c = config('database.connections.'.config('database.default'));
        $params = [
            'dbname' => $c['database'], 'user' => $c['username'], 'password' => $c['password'],
            'host' => $c['host'], 'port' => $c['port'] ?? 3306, 'driver' => 'pdo_mysql',
            'charset' => $c['charset'] ?? 'utf8mb4',
        ];
        $options = $c['options'] ?? [];
        $offset = self::offset();
        if ($offset !== null) {
            $init = \defined('Pdo\\Mysql::ATTR_INIT_COMMAND') ? \constant('Pdo\\Mysql::ATTR_INIT_COMMAND') : \PDO::MYSQL_ATTR_INIT_COMMAND;
            $options[$init] = "SET time_zone = '$offset'";
        }
        if ($options !== []) $params['driverOptions'] = $options;
        return DriverManager::getConnection($params);
    }
}
