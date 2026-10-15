<?php

namespace App\Core;

use PDO;
use PDOStatement;

/**
 * Thin PDO wrapper. Every query here is prepared — no string concatenation
 * of user input anywhere in the application.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function connect(array $cfg): void
    {
        $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset={$cfg['charset']}";
        self::$pdo = new PDO($dsn, $cfg['user'], $cfg['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            throw new \RuntimeException('Database not connected.');
        }
        return self::$pdo;
    }

    public static function run(string $sql, array $params = []): PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** @return array<int,array<string,mixed>> */
    public static function all(string $sql, array $params = []): array
    {
        return self::run($sql, $params)->fetchAll();
    }

    /** @return array<string,mixed>|null */
    public static function one(string $sql, array $params = []): ?array
    {
        $row = self::run($sql, $params)->fetch();
        return $row === false ? null : $row;
    }

    public static function value(string $sql, array $params = [], mixed $default = null): mixed
    {
        $v = self::run($sql, $params)->fetchColumn();
        return $v === false ? $default : $v;
    }

    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $sql  = "INSERT INTO `$table` (`" . implode('`,`', $cols) . '`) VALUES ('
              . implode(',', array_fill(0, count($cols), '?')) . ')';
        self::run($sql, array_values($data));
        return (int) self::pdo()->lastInsertId();
    }

    public static function update(string $table, int $id, array $data): void
    {
        $set = implode(', ', array_map(static fn ($c) => "`$c` = ?", array_keys($data)));
        self::run("UPDATE `$table` SET $set WHERE id = ?", [...array_values($data), $id]);
    }

    public static function delete(string $table, int $id): void
    {
        self::run("DELETE FROM `$table` WHERE id = ?", [$id]);
    }

    public static function transaction(callable $fn): mixed
    {
        $pdo = self::pdo();
        $pdo->beginTransaction();
        try {
            $result = $fn();
            $pdo->commit();
            return $result;
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
