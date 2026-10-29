<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use mysqli_sql_exception;

final class SensorThreshold
{
    public function all(): array
    {
        $database = Database::connection();
        try {
            $result = $database->query(
                'SELECT sensor_key, display_name, unit, scale_min, scale_max, ideal_min, ideal_max, decimals
                 FROM sensor_thresholds ORDER BY display_order, display_name'
            );
        } catch (mysqli_sql_exception $exception) {
            if ((int) $exception->getCode() !== 1146) {
                throw $exception;
            }
            $this->createDefaults();
            $result = $database->query(
                'SELECT sensor_key, display_name, unit, scale_min, scale_max, ideal_min, ideal_max, decimals
                 FROM sensor_thresholds ORDER BY display_order, display_name'
            );
        }

        $thresholds = [];
        while ($row = $result->fetch_assoc()) {
            $thresholds[] = [
                'key' => $row['sensor_key'],
                'name' => $row['display_name'],
                'unit' => $row['unit'] === 'C' ? "\u{00B0}C" : $row['unit'],
                'min' => (float) $row['scale_min'],
                'max' => (float) $row['scale_max'],
                'low' => (float) $row['ideal_min'],
                'high' => (float) $row['ideal_max'],
                'decimals' => (int) $row['decimals'],
            ];
        }

        return $thresholds;
    }

    public function updateAll(array $values): void
    {
        $database = Database::connection();
        $statement = $database->prepare('UPDATE sensor_thresholds SET ideal_min = ?, ideal_max = ? WHERE sensor_key = ?');
        $database->begin_transaction();
        try {
            foreach ($values as $key => [$low, $high]) {
                $statement->bind_param('dds', $low, $high, $key);
                $statement->execute();
                if ($statement->affected_rows < 0) throw new \RuntimeException('Could not save threshold.');
            }
            $database->commit();
        } catch (\Throwable $exception) {
            $database->rollback();
            throw $exception;
        }
    }

    private function createDefaults(): void
    {
        $database = Database::connection();
        $database->query(
            "CREATE TABLE IF NOT EXISTS sensor_thresholds (
                sensor_key VARCHAR(32) PRIMARY KEY,
                display_name VARCHAR(64) NOT NULL,
                unit VARCHAR(16) NOT NULL DEFAULT '',
                scale_min DECIMAL(10,2) NOT NULL,
                scale_max DECIMAL(10,2) NOT NULL,
                ideal_min DECIMAL(10,2) NOT NULL,
                ideal_max DECIMAL(10,2) NOT NULL,
                decimals TINYINT UNSIGNED NOT NULL DEFAULT 0,
                display_order TINYINT UNSIGNED NOT NULL DEFAULT 0
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
        );
        $database->query(
            "INSERT IGNORE INTO sensor_thresholds
                (sensor_key, display_name, unit, scale_min, scale_max, ideal_min, ideal_max, decimals, display_order)
             VALUES
                ('temperature', 'Temperature', 'C', 10, 40, 18, 24, 1, 1),
                ('humidity', 'Humidity', '%', 30, 100, 50, 70, 0, 2),
                ('ph', 'pH level', '', 4, 9, 5.5, 6.5, 1, 3),
                ('tds', 'TDS', 'ppm', 0, 1500, 560, 840, 0, 4)"
        );
    }
}
