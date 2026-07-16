<?php

declare(strict_types=1);

namespace App\Core;

class Model
{
    public Database $db;

    protected string $table = '';

    protected string $primaryKey = 'id';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected string $createdAtColumn = 'created_at';

    protected string $updatedAtColumn = 'updated_at';

    protected string $deletedAtColumn = 'deleted_at';

    protected array $fillable = [];

    protected array $hidden = [];

    protected array $casts = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    protected function getTable(): string
    {
        if ($this->table !== '') {
            return $this->table;
        }

        $className = (new \ReflectionClass($this))->getShortName();
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
    }

    public function find(int|string $id): ?array
    {
        $query = $this->db->table($this->getTable())
            ->where($this->primaryKey, $id);

        if ($this->softDeletes) {
            $query = $query->withSoftDeletes();
        }

        $result = $query->first();

        return $result ? $this->applyCasts($this->hideAttributes($result)) : null;
    }

    public function findOrFail(int|string $id): array
    {
        $result = $this->find($id);

        if ($result === null) {
            throw new \RuntimeException(static::class . " with ID [{$id}] not found.");
        }

        return $result;
    }

    public function where(string $column, mixed $operatorOrValue, mixed $value = null): self
    {
        $clone = clone $this;
        if (!empty($clone->db->getTable())) {
            $clone->db = $clone->db->where($column, $operatorOrValue, $value);
        } else {
            $clone->db = $this->db->table($this->getTable())
                ->where($column, $operatorOrValue, $value);
        }

        if ($this->softDeletes) {
            $clone->db = $clone->db->withSoftDeletes();
        }

        return $clone;
    }

    public function whereIn(string $column, array $values): self
    {
        $clone = clone $this;
        if (!empty($clone->db->getTable())) {
            $clone->db = $clone->db->whereIn($column, $values);
        } else {
            $clone->db = $this->db->table($this->getTable())
                ->whereIn($column, $values);
        }

        if ($this->softDeletes) {
            $clone->db = $clone->db->withSoftDeletes();
        }

        return $clone;
    }

    public function whereNull(string $column): self
    {
        $clone = clone $this;
        if (!empty($clone->db->getTable())) {
            $clone->db = $clone->db->whereNull($column);
        } else {
            $clone->db = $this->db->table($this->getTable())
                ->whereNull($column);
        }

        if ($this->softDeletes) {
            $clone->db = $clone->db->withSoftDeletes();
        }

        return $clone;
    }

    public function whereNotNull(string $column): self
    {
        $clone = clone $this;
        if (!empty($clone->db->getTable())) {
            $clone->db = $clone->db->whereNotNull($column);
        } else {
            $clone->db = $this->db->table($this->getTable())
                ->whereNotNull($column);
        }

        if ($this->softDeletes) {
            $clone->db = $clone->db->withSoftDeletes();
        }

        return $clone;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $clone = clone $this;

        if (!empty($clone->db->getTable())) {
            $clone->db = $clone->db->orderBy($column, $direction);
        } else {
            $clone->db = $clone->db->table($this->getTable())->orderBy($column, $direction);
        }

        return $clone;
    }

    public function limit(int $limit): self
    {
        $clone = clone $this;

        if (!empty($clone->db->getTable())) {
            $clone->db = $clone->db->limit($limit);
        } else {
            $clone->db = $clone->db->table($this->getTable())->limit($limit);
        }

        return $clone;
    }

    public function get(): array
    {
        if (!empty($this->db->getTable())) {
            $results = $this->db->get();
        } else {
            $results = $this->db->table($this->getTable())->get();
        }

        return array_map(fn(array $row) => $this->applyCasts($this->hideAttributes($row)), $results);
    }

    public function first(): ?array
    {
        $result = $this->db->first();

        return $result ? $this->applyCasts($this->hideAttributes($result)) : null;
    }

    public function count(): int
    {
        if (!empty($this->db->getTable())) {
            return $this->db->count();
        }

        return $this->db->table($this->getTable())->count();
    }

    public function exists(): bool
    {
        return $this->first() !== null;
    }

    public function create(array $data): array
    {
        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $data[$this->createdAtColumn] = $now;
            $data[$this->updatedAtColumn] = $now;
        }

        $data = $this->filterFillable($data);

        $id = $this->db->table($this->getTable())->insert($data);

        return $this->find((int) $id) ?? $data;
    }

    public function updateById(int|string $id, array $data): bool
    {
        if ($this->timestamps) {
            $data[$this->updatedAtColumn] = date('Y-m-d H:i:s');
        }

        $data = $this->filterFillable($data);

        $affected = $this->db->table($this->getTable())
            ->where($this->primaryKey, $id)
            ->update($data);

        return $affected > 0;
    }

    public function save(array $data): bool
    {
        $id = $data[$this->primaryKey] ?? null;

        if ($id !== null) {
            unset($data[$this->primaryKey]);
            return $this->updateById($id, $data);
        }

        $this->create($data);
        return true;
    }

    public function deleteById(int|string $id): bool
    {
        if ($this->softDeletes) {
            $affected = $this->db->table($this->getTable())
                ->where($this->primaryKey, $id)
                ->withSoftDeletes($this->deletedAtColumn)
                ->update([$this->deletedAtColumn => date('Y-m-d H:i:s')]);
        } else {
            $affected = $this->db->table($this->getTable())
                ->where($this->primaryKey, $id)
                ->delete();
        }

        return $affected > 0;
    }

    public function forceDeleteById(int|string $id): bool
    {
        $affected = $this->db->table($this->getTable())
            ->where($this->primaryKey, $id)
            ->forceDelete();

        return $affected > 0;
    }

    public function restoreById(int|string $id): bool
    {
        $affected = $this->db->table($this->getTable())
            ->where($this->primaryKey, $id)
            ->withSoftDeletes($this->deletedAtColumn)
            ->restore();

        return $affected > 0;
    }

    public function paginate(int $perPage = 20, int $page = 1): array
    {
        if (!empty($this->db->getTable())) {
            $query = $this->db;
        } else {
            $query = $this->db->table($this->getTable());
        }

        if ($this->softDeletes) {
            $query = $query->withSoftDeletes();
        }

        $result = $query->paginate($perPage, $page);

        $result['data'] = array_map(
            fn(array $row) => $this->applyCasts($this->hideAttributes($row)),
            $result['data']
        );

        return $result;
    }

    public function pluck(string $column, ?string $key = null): array
    {
        $results = $this->get();
        $plucked = [];

        foreach ($results as $row) {
            $value = $row[$column] ?? null;
            if ($key !== null) {
                $plucked[$row[$key] ?? $value] = $value;
            } else {
                $plucked[] = $value;
            }
        }

        return $plucked;
    }

    public function chunk(int $chunkSize, callable $callback): void
    {
        $page = 1;

        do {
            $result   = $this->paginate($chunkSize, $page);
            $continue = $callback($result['data'], $page);
            $page++;

            if ($continue === false) {
                break;
            }
        } while ($result['has_more_pages']);
    }

    public function boot(): void
    {
    }

    private function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }

        return array_intersect_key($data, array_flip($this->fillable));
    }

    private function hideAttributes(array $data): array
    {
        if (empty($this->hidden)) {
            return $data;
        }

        return array_diff_key($data, array_flip($this->hidden));
    }

    private function applyCasts(array $data): array
    {
        foreach ($this->casts as $field => $type) {
            if (!isset($data[$field])) {
                continue;
            }

            switch ($type) {
                case 'integer':
                    $data[$field] = (int) $data[$field];
                    break;
                case 'float':
                    $data[$field] = (float) $data[$field];
                    break;
                case 'boolean':
                    $data[$field] = (bool) $data[$field];
                    break;
                case 'array':
                    if (is_string($data[$field])) {
                        $data[$field] = json_decode($data[$field], true) ?? [];
                    }
                    break;
                case 'json':
                    if (is_string($data[$field])) {
                        $data[$field] = json_decode($data[$field], false);
                    }
                    break;
                case 'datetime':
                    $data[$field] = $data[$field] !== null ? new \DateTime($data[$field]) : null;
                    break;
                case 'date':
                    $data[$field] = $data[$field] !== null ? new \DateTime($data[$field]) : null;
                    break;
            }
        }

        return $data;
    }
}
