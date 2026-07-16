<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;

/**
 * URL slug generation service with uniqueness validation.
 */
class SlugService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate a unique slug for a given text in a table column.
     */
    public function generate(string $text, string $table, string $column = 'slug'): string
    {
        $slug = $this->makeSlug($text);
        $original = $slug;
        $counter  = 1;

        while (!$this->isUnique($slug, $table, $column)) {
            $slug = $original . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if a slug is unique in the given table.
     */
    public function isUnique(string $slug, string $table, string $column = 'slug', ?int $excludeId = null): bool
    {
        $query = $this->db->table($table)
            ->where($column, $slug);

        if ($excludeId !== null) {
            $query = $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Convert text to a URL-friendly slug.
     */
    private function makeSlug(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9\-]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug !== '' ? $slug : 'untitled';
    }
}
