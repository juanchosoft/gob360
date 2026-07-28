<?php

require_once __DIR__ . '/PermissionCatalog.php';

/**
 * Puente entre IDs numéricos legacy y KEYs del nuevo sistema.
 */
final class PermissionLegacyMap
{
    /** @var array<int, string>|null */
    private static ?array $idToKey = null;

    /** @var array<string, int>|null */
    private static ?array $keyToId = null;

    public static function idToKey(int $legacyId): ?string
    {
        self::boot();
        return self::$idToKey[$legacyId] ?? null;
    }

    public static function keyToId(string $key): ?int
    {
        self::boot();
        return self::$keyToId[$key] ?? null;
    }

    /** @return int[] */
    public static function keysToLegacyIds(array $keys): array
    {
        self::boot();
        $ids = [];
        foreach ($keys as $key) {
            if (isset(self::$keyToId[$key])) {
                $ids[] = self::$keyToId[$key];
            }
        }
        return array_values(array_unique($ids));
    }

    /** @return string[] */
    public static function legacyIdsToKeys(array $legacyIds): array
    {
        self::boot();
        $keys = [];
        foreach ($legacyIds as $id) {
            $id = (int) $id;
            if (isset(self::$idToKey[$id])) {
                $keys[] = self::$idToKey[$id];
            }
        }
        return array_values(array_unique($keys));
    }

    private static function boot(): void
    {
        if (self::$idToKey === null) {
            self::$idToKey = PermissionCatalog::legacyIdToKey();
            self::$keyToId = PermissionCatalog::legacyKeyToId();
        }
    }
}
