<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use mysqli_sql_exception;

final class User
{
    public function findByEmail(string $email): ?array
    {
        $statement = Database::connection()->prepare(
            'SELECT id, full_name, email, password FROM users WHERE email = ?'
        );
        $statement->bind_param('s', $email);
        $statement->execute();

        $user = $statement->get_result()->fetch_assoc();
        return $user ?: null;
    }

    public function findById(int $id): ?array
    {
        $database = Database::connection();
        try {
            $statement = $database->prepare('SELECT id, full_name, email, role FROM users WHERE id = ?');
        } catch (mysqli_sql_exception $exception) {
            if ((int) $exception->getCode() !== 1054) throw $exception;
            $database->query("ALTER TABLE users ADD COLUMN role VARCHAR(24) NOT NULL DEFAULT 'Administrator'");
            $statement = $database->prepare('SELECT id, full_name, email, role FROM users WHERE id = ?');
        }
        $statement->bind_param('i', $id);
        $statement->execute();

        $user = $statement->get_result()->fetch_assoc();
        return $user ?: null;
    }

    public function create(string $name, string $email, string $passwordHash): void
    {
        $database = Database::connection();
        $role = (int) $database->query('SELECT COUNT(*) AS total FROM users')->fetch_assoc()['total'] === 0
            ? 'Administrator' : 'Farm Staff';
        try {
            $statement = $database->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)');
        } catch (mysqli_sql_exception $exception) {
            if ((int) $exception->getCode() !== 1054) throw $exception;
            $database->query("ALTER TABLE users ADD COLUMN role VARCHAR(24) NOT NULL DEFAULT 'Administrator'");
            $statement = $database->prepare('INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)');
        }
        $statement->bind_param('ssss', $name, $email, $passwordHash, $role);
        $statement->execute();
    }
}
