<?php
declare(strict_types=1);

namespace App\Core;

use mysqli;

final class Database
{
    private static ?mysqli $connection = null;

    public static function connection(): mysqli
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $config = require dirname(__DIR__, 2) . '/config/database.php';
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        self::$connection = new mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database']
        );
        self::$connection->set_charset('utf8mb4');

        return self::$connection;
    }
}
