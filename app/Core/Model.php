<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Small active-record-ish base model.
 *
 * Column names are validated against a strict identifier pattern and quoted,
 * so even a caller that builds an array from user input cannot inject SQL
 * through a key. Values are always bound.
 */
abstract class Model
{
    protected string $table = '';
    protected string $primaryKey = 'id';

    /** Columns a caller may mass-assign. Subclasses override. */
    protected array $fillable = [];

    /** @return array<string, mixed>|null */
    public function find(int|string $id): ?array
    {
        return Database::one(
            'SELECT * FROM `' . $this->table . '` WHERE `' . $this->primaryKey . '` = ? LIMIT 1',
            [$id]
        );
    }

    /** @return array<string, mixed>|null */
    public function findBy(string $column, mixed $value): ?array
    {
        $column = $this->column($column);

        return Database::one(
            'SELECT * FROM `' . $this->table . '` WHERE `' . $column . '` = ? LIMIT 1',
            [$value]
        );
    }

    /**
     * @param array<string, mixed> $where
     * @return array<int, array<string, mixed>>
     */
    public function all(array $where = [], string $orderBy = 'id DESC', int $limit = 100, int $offset = 0): array
    {
        [$clause, $params] = $this->where($where);
        $limit  = max(1, min(500, $limit));
        $offset = max(0, $offset);

        return Database::all(
            'SELECT * FROM `' . $this->table . '`' . $clause
            . ' ORDER BY ' . $this->orderBy($orderBy)
            . ' LIMIT ' . $limit . ' OFFSET ' . $offset,
            $params
        );
    }

    /** @param array<string, mixed> $where */
    public function count(array $where = []): int
    {
        [$clause, $params] = $this->where($where);

        return (int) Database::scalar('SELECT COUNT(*) FROM `' . $this->table . '`' . $clause, $params);
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): int
    {
        $data = $this->filter($data);
        if ($data === []) {
            throw new \InvalidArgumentException('No fillable columns given.');
        }

        $columns = array_keys($data);
        $sql = 'INSERT INTO `' . $this->table . '` (`' . implode('`, `', $columns) . '`) VALUES ('
            . implode(', ', array_fill(0, count($columns), '?')) . ')';

        return Database::insert($sql, array_values($data));
    }

    /** @param array<string, mixed> $data */
    public function update(int|string $id, array $data): bool
    {
        $data = $this->filter($data);
        if ($data === []) {
            return false;
        }

        $sets = [];
        foreach (array_keys($data) as $column) {
            $sets[] = '`' . $column . '` = ?';
        }
        $params = array_values($data);
        $params[] = $id;

        Database::run(
            'UPDATE `' . $this->table . '` SET ' . implode(', ', $sets)
            . ' WHERE `' . $this->primaryKey . '` = ? LIMIT 1',
            $params
        );

        return true;
    }

    public function delete(int|string $id): bool
    {
        Database::run(
            'DELETE FROM `' . $this->table . '` WHERE `' . $this->primaryKey . '` = ? LIMIT 1',
            [$id]
        );

        return true;
    }

    /**
     * @param array<string, mixed> $where
     * @return array{0:string, 1:array<int, mixed>}
     */
    protected function where(array $where): array
    {
        if ($where === []) {
            return ['', []];
        }

        $parts  = [];
        $params = [];
        foreach ($where as $column => $value) {
            $column = $this->column($column);
            if ($value === null) {
                $parts[] = '`' . $column . '` IS NULL';
                continue;
            }
            if (is_array($value)) {
                $value = array_values($value);
                if ($value === []) {
                    $parts[] = '1 = 0';
                    continue;
                }
                $parts[] = '`' . $column . '` IN (' . implode(', ', array_fill(0, count($value), '?')) . ')';
                $params = [...$params, ...$value];
                continue;
            }
            $parts[] = '`' . $column . '` = ?';
            $params[] = $value;
        }

        return [' WHERE ' . implode(' AND ', $parts), $params];
    }

    /** Only fillable columns survive, and their names must be safe identifiers. */
    protected function filter(array $data): array
    {
        $out = [];
        foreach ($data as $key => $value) {
            if ($this->fillable !== [] && !in_array($key, $this->fillable, true)) {
                continue;
            }
            $out[$this->column($key)] = $value;
        }

        return $out;
    }

    protected function column(string $name): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]{0,63}$/', $name)) {
            throw new \InvalidArgumentException('Invalid column name.');
        }

        return $name;
    }

    protected function orderBy(string $orderBy): string
    {
        if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]{0,63}( (ASC|DESC))?(, ?[a-zA-Z_][a-zA-Z0-9_]{0,63}( (ASC|DESC))?)*$/i', $orderBy)) {
            return '`' . $this->primaryKey . '` DESC';
        }

        $parts = array_map(static function (string $part): string {
            $part = trim($part);
            $bits = preg_split('/\s+/', $part) ?: [];
            $column = '`' . $bits[0] . '`';
            $direction = isset($bits[1]) && strtoupper($bits[1]) === 'ASC' ? 'ASC' : 'DESC';

            return $column . ' ' . $direction;
        }, explode(',', $orderBy));

        return implode(', ', $parts);
    }
}
