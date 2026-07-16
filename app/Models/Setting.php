<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class Setting extends Model
{
    protected string $table = 'settings';

    protected bool $timestamps = true;

    protected bool $softDeletes = false;

    protected array $fillable = [
        'setting_group',
        'setting_key',
        'value',
        'type',
        'description',
        'created_at',
        'updated_at',
    ];

    protected array $hidden = [];

    protected array $casts = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = (new self())->where('setting_key', $key)->first();

        if ($setting === null) {
            return $default;
        }

        return self::castValue($setting['value'], $setting['type'] ?? 'string');
    }

    public static function set(string $key, mixed $value, string $group = 'general'): bool
    {
        $existing = (new self())->where('setting_key', $key)->first();

        $data = [
            'setting_group' => $group,
            'setting_key'   => $key,
            'value' => is_array($value) ? json_encode($value) : (string) $value,
            'type'  => get_debug_type($value),
        ];

        if ($existing !== null) {
            return (new self())->updateById((int) $existing['id'], $data);
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        return (bool) (new self())->create($data);
    }

    public static function getGroup(string $group): array
    {
        $settings = (new self())->where('setting_group', $group)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting['setting_key']] = self::castValue($setting['value'], $setting['type'] ?? 'string');
        }

        return $result;
    }

    public static function getMany(array $keys): array
    {
        $result = [];
        $model = new self();

        foreach ($keys as $key => $default) {
            $result[$key] = self::get($key, $default);
        }

        return $result;
    }

    public static function forget(string $key): bool
    {
        return $model = (new self())->where('setting_key', $key)->delete() > 0;
    }

    public static function allSettings(): array
    {
        return (new self())->orderBy('setting_group', 'ASC')
            ->orderBy('setting_key', 'ASC')
            ->get();
    }

    private static function castValue(string $value, string $type): mixed
    {
        return match ($type) {
            'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'boolean' => $value === '1' || $value === 'true',
            'json', 'array' => json_decode($value, true) ?? [],
            default => $value,
        };
    }
}
