<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;
use Throwable;

final class PasswordReset
{
    public function create(int $userId, string $codeHash): void
    {
        $connection = Database::connection();
        $connection->begin_transaction();

        try {
            $delete = $connection->prepare('DELETE FROM password_resets WHERE user_id = ?');
            $delete->bind_param('i', $userId);
            $delete->execute();

            $insert = $connection->prepare(
                'INSERT INTO password_resets (user_id, token_hash, expires_at, attempt_count, verified_at) '
                . 'VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), 0, NULL)'
            );
            $insert->bind_param('is', $userId, $codeHash);
            $insert->execute();
            $connection->commit();
        } catch (Throwable $exception) {
            $connection->rollback();
            throw $exception;
        }
    }

    public function verifyCode(int $userId, string $codeHash): bool
    {
        $connection = Database::connection();
        $connection->begin_transaction();

        try {
            $lookup = $connection->prepare(
                'SELECT id, token_hash, attempt_count FROM password_resets '
                . 'WHERE user_id = ? AND expires_at > NOW() AND verified_at IS NULL '
                . 'ORDER BY created_at DESC LIMIT 1 FOR UPDATE'
            );
            $lookup->bind_param('i', $userId);
            $lookup->execute();
            $reset = $lookup->get_result()->fetch_assoc();

            if (!$reset || (int) $reset['attempt_count'] >= 5) {
                $connection->rollback();
                return false;
            }

            if (hash_equals($reset['token_hash'], $codeHash)) {
                $update = $connection->prepare(
                    'UPDATE password_resets SET verified_at = NOW() WHERE id = ?'
                );
                $resetId = (int) $reset['id'];
                $update->bind_param('i', $resetId);
                $update->execute();
                $connection->commit();
                return true;
            }

            $update = $connection->prepare(
                'UPDATE password_resets SET attempt_count = attempt_count + 1 WHERE id = ?'
            );
            $resetId = (int) $reset['id'];
            $update->bind_param('i', $resetId);
            $update->execute();
            $connection->commit();
            return false;
        } catch (Throwable $exception) {
            $connection->rollback();
            throw $exception;
        }
    }

    public function deleteForUser(int $userId): void
    {
        $statement = Database::connection()->prepare(
            'DELETE FROM password_resets WHERE user_id = ?'
        );
        $statement->bind_param('i', $userId);
        $statement->execute();
    }

    public function consumeVerified(int $userId, string $passwordHash): bool
    {
        $connection = Database::connection();
        $connection->begin_transaction();

        try {
            $lookup = $connection->prepare(
                'SELECT id FROM password_resets '
                . 'WHERE user_id = ? AND expires_at > NOW() AND verified_at IS NOT NULL '
                . 'ORDER BY verified_at DESC LIMIT 1 FOR UPDATE'
            );
            $lookup->bind_param('i', $userId);
            $lookup->execute();
            $reset = $lookup->get_result()->fetch_assoc();

            if (!$reset) {
                $connection->rollback();
                return false;
            }

            $update = $connection->prepare('UPDATE users SET password = ? WHERE id = ?');
            $update->bind_param('si', $passwordHash, $userId);
            $update->execute();

            $resetId = (int) $reset['id'];
            $delete = $connection->prepare('DELETE FROM password_resets WHERE id = ?');
            $delete->bind_param('i', $resetId);
            $delete->execute();
            $connection->commit();

            return true;
        } catch (Throwable $exception) {
            $connection->rollback();
            throw $exception;
        }
    }
}
