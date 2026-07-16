<?php

declare(strict_types=1);

namespace App\Core;

class Database
{
    private static ?self $instance = null;

    private ?\PDO $pdo = null;

    private string $table = '';

    private array $where = [];

    private array $bindings = [];

    private array $selectColumns = ['*'];

    private ?string $orderBy = null;

    private ?int $limit = null;

    private ?int $offset = null;

    private array $joins = [];

    private array $groupBy = [];

    private ?string $having = null;

    private bool $softDeletes = false;

    private string $deletedAtColumn = 'deleted_at';

    private function __construct()
    {
        $this->connect();
    }

    private function __clone() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    private function connect(): void
    {
        $config = require ROOT_PATH . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
        $db     = $config['database'];

        $dsn = sprintf(
            '%s:host=%s;port=%s;dbname=%s;charset=%s',
            $db['driver'],
            $db['host'],
            $db['port'],
            $db['database'],
            $db['charset']
        );

        $this->pdo = new \PDO($dsn, $db['username'], $db['password'], $db['options']);
    }

    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function table(string $table): self
    {
        $clone           = clone $this;
        $clone->table    = $table;
        $clone->where    = [];
        $clone->bindings = [];
        $clone->selectColumns = ['*'];
        $clone->orderBy  = null;
        $clone->limit    = null;
        $clone->offset   = null;
        $clone->joins    = [];
        $clone->groupBy  = [];
        $clone->having   = null;
        $clone->softDeletes = false;
        return $clone;
    }

    public function select(string ...$columns): self
    {
        $clone              = clone $this;
        $clone->selectColumns = $columns;
        return $clone;
    }

    public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        $clone = clone $this;

        if ($value === null) {
            $value     = $operatorOrValue;
            $operator  = '=';
        } else {
            $operator = $operatorOrValue;
        }

        $paramName    = ':where_' . str_replace('.', '_', $column) . '_' . count($clone->bindings);
        $clone->where[] = "{$column} {$operator} {$paramName}";
        $clone->bindings[$paramName] = $value;

        return $clone;
    }

    public function orWhere(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        $clone = clone $this;

        if ($value === null) {
            $value    = $operatorOrValue;
            $operator = '=';
        } else {
            $operator = $operatorOrValue;
        }

        $paramName = ':where_' . str_replace('.', '_', $column) . '_' . count($clone->bindings);
        $lastWhere = array_key_exists(0, $clone->where)
            ? ' OR '
            : '';

        $clone->where[] = $lastWhere . "{$column} {$operator} {$paramName}";
        $clone->bindings[$paramName] = $value;

        return $clone;
    }

    public function whereIn(string $column, array $values): self
    {
        $clone = clone $this;

        $placeholders = [];
        foreach ($values as $i => $value) {
            $paramName      = ':wherein_' . str_replace('.', '_', $column) . '_' . $i;
            $placeholders[] = $paramName;
            $clone->bindings[$paramName] = $value;
        }

        $paramList    = implode(', ', $placeholders);
        $clone->where[] = "{$column} IN ({$paramList})";

        return $clone;
    }

    public function whereNull(string $column): self
    {
        $clone         = clone $this;
        $clone->where[] = "{$column} IS NULL";
        return $clone;
    }

    public function whereNotNull(string $column): self
    {
        $clone         = clone $this;
        $clone->where[] = "{$column} IS NOT NULL";
        return $clone;
    }

    public function whereBetween(string $column, mixed $start, mixed $end): self
    {
        $clone = clone $this;

        $paramStart = ':wherebetween_start_' . str_replace('.', '_', $column);
        $paramEnd   = ':wherebetween_end_' . str_replace('.', '_', $column);

        $clone->where[]             = "{$column} BETWEEN {$paramStart} AND {$paramEnd}";
        $clone->bindings[$paramStart] = $start;
        $clone->bindings[$paramEnd]   = $end;

        return $clone;
    }

    public function join(string $table, string $first, string $operator, string $second, string $type = 'INNER'): self
    {
        $clone          = clone $this;
        $clone->joins[] = "{$type} JOIN {$table} ON {$first} {$operator} {$second}";
        return $clone;
    }

    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $clone             = clone $this;
        $direction         = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $clone->orderBy    = "{$column} {$direction}";
        return $clone;
    }

    public function limit(int $limit): self
    {
        $clone         = clone $this;
        $clone->limit  = $limit;
        return $clone;
    }

    public function offset(int $offset): self
    {
        $clone          = clone $this;
        $clone->offset  = $offset;
        return $clone;
    }

    public function groupBy(string ...$columns): self
    {
        $clone           = clone $this;
        $clone->groupBy  = array_merge($clone->groupBy, $columns);
        return $clone;
    }

    public function withSoftDeletes(string $column = 'deleted_at'): self
    {
        $clone               = clone $this;
        $clone->softDeletes  = true;
        $clone->deletedAtColumn = $column;
        return $clone;
    }

    public function onlySoftDeleted(): self
    {
        $clone = clone $this;
        $clone->where[]     = "{$clone->deletedAtColumn} IS NOT NULL";
        return $clone;
    }

    private function buildSelectQuery(): string
    {
        $columns = implode(', ', $this->selectColumns);
        $sql     = "SELECT {$columns} FROM {$this->table}";

        $conditions = $this->where;

        if ($this->softDeletes && !empty($this->table)) {
            $conditions[] = "{$this->deletedAtColumn} IS NULL";
        }

        if (!empty($this->joins)) {
            $sql .= ' ' . implode(' ', $this->joins);
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }

        if ($this->having !== null) {
            $sql .= ' HAVING ' . $this->having;
        }

        if ($this->orderBy !== null) {
            $sql .= " ORDER BY {$this->orderBy}";
        }

        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    public function get(): array
    {
        $sql         = $this->buildSelectQuery();
        $stmt        = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);
        return $stmt->fetchAll();
    }

    public function first(): ?array
    {
        $clone         = clone $this;
        $clone->limit  = 1;
        $sql           = $clone->buildSelectQuery();
        $stmt          = $clone->pdo->prepare($sql);
        $stmt->execute($clone->bindings);
        $result        = $stmt->fetch();
        return $result ?: null;
    }

    public function find(int|string $id): ?array
    {
        return $this->where('id', $id)->first();
    }

    public function count(): int
    {
        $clone              = clone $this;
        $clone->selectColumns = ['COUNT(*) as aggregate'];
        $sql                = $clone->buildSelectQuery();
        $stmt               = $clone->pdo->prepare($sql);
        $stmt->execute($clone->bindings);
        $result             = $stmt->fetch();
        return (int) ($result['aggregate'] ?? 0);
    }

    public function sum(string $column): float
    {
        $clone              = clone $this;
        $clone->selectColumns = ["SUM({$column}) as aggregate"];
        $sql                = $clone->buildSelectQuery();
        $stmt               = $clone->pdo->prepare($sql);
        $stmt->execute($clone->bindings);
        $result             = $stmt->fetch();
        return (float) ($result['aggregate'] ?? 0);
    }

    public function exists(): bool
    {
        return $this->first() !== null;
    }

    public function insert(array $data): string
    {
        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), ':'));

        $namedPlaceholders = [];
        $bindings          = [];
        foreach ($data as $key => $value) {
            $paramName                   = ':insert_' . $key;
            $namedPlaceholders[]         = $paramName;
            $bindings[$paramName]        = $value;
        }

        $placeholdersStr = implode(', ', $namedPlaceholders);
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholdersStr})";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);

        return $this->pdo->lastInsertId();
    }

    public function insertBatch(array $rows): int
    {
        if (empty($rows)) {
            return 0;
        }

        $columns = array_keys($rows[0]);
        $columnList = implode(', ', $columns);

        $allPlaceholders = [];
        $allBindings     = [];
        $bindingIndex    = 0;

        foreach ($rows as $row) {
            $rowPlaceholders = [];
            foreach ($columns as $col) {
                $paramName           = ':batch_' . $bindingIndex . '_' . $col;
                $rowPlaceholders[]   = $paramName;
                $allBindings[$paramName] = $row[$col] ?? null;
                $bindingIndex++;
            }
            $allPlaceholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
        }

        $valuesList = implode(', ', $allPlaceholders);
        $sql = "INSERT INTO {$this->table} ({$columnList}) VALUES {$valuesList}";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($allBindings);

        return $stmt->rowCount();
    }

    public function update(array $data): int
    {
        $setClauses  = [];
        $bindings    = [];

        foreach ($data as $key => $value) {
            if ($value instanceof \App\Core\RawExpression) {
                $setClauses[] = "{$key} = {$value->sql}";
                $bindings = array_merge($bindings, $value->bindings);
            } else {
                $paramName          = ':update_' . $key;
                $setClauses[]       = "{$key} = {$paramName}";
                $bindings[$paramName] = $value;
            }
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses);

        $conditions = $this->where;
        if ($this->softDeletes) {
            $conditions[] = "{$this->deletedAtColumn} IS NULL";
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $allBindings = array_merge($bindings, $this->bindings);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($allBindings);

        return $stmt->rowCount();
    }

    public function delete(): int
    {
        if ($this->softDeletes) {
            return $this->update([$this->deletedAtColumn => date('Y-m-d H:i:s')]);
        }

        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->rowCount();
    }

    public function forceDelete(): int
    {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->where)) {
            $sql .= ' WHERE ' . implode(' AND ', $this->where);
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->rowCount();
    }

    public function restore(): int
    {
        if (!$this->softDeletes) {
            return 0;
        }

        $sql  = "UPDATE {$this->table} SET {$this->deletedAtColumn} = NULL";

        $conditions = $this->where;
        $conditions[] = "{$this->deletedAtColumn} IS NOT NULL";

        $sql .= ' WHERE ' . implode(' AND ', $conditions);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($this->bindings);

        return $stmt->rowCount();
    }

    public function paginate(int $perPage = 20, int $page = 1): array
    {
        $clone    = clone $this;
        $offset   = ($page - 1) * $perPage;
        $clone    = $clone->limit($perPage)->offset($offset);

        $totalQuery = clone $this;
        $totalQuery->selectColumns = ['COUNT(*) as aggregate'];
        $totalSql   = $totalQuery->buildSelectQuery();
        $totalStmt  = $clone->pdo->prepare($totalSql);
        $totalStmt->execute($totalQuery->bindings);
        $totalResult = $totalStmt->fetch();
        $total       = (int) ($totalResult['aggregate'] ?? 0);

        $sql  = $clone->buildSelectQuery();
        $stmt = $clone->pdo->prepare($sql);
        $stmt->execute($clone->bindings);
        $data = $stmt->fetchAll();

        $lastPage = (int) ceil($total / $perPage);

        return [
            'data'            => $data,
            'total'           => $total,
            'per_page'        => $perPage,
            'current_page'    => $page,
            'last_page'       => $lastPage,
            'has_more_pages'  => $page < $lastPage,
            'has_prev_page'   => $page > 1,
        ];
    }

    public function raw(string $sql, array $bindings = []): \PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }

    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function getLastQuery(): string
    {
        return $this->buildSelectQuery();
    }
}
