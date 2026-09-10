<?php

namespace App\Support;

use App\Models\SchemaColumn;

/**
 * Central source of truth for column-type → option rules.
 *
 * Used by:
 *  - SchemaColumn::booted() saving observer   (model-level guard)
 *  - UpdateColumnRequest                       (API validation)
 *  - DatabaseImportService::buildColumnAttrs() (import sanitisation)
 *  - DatabaseExportService (sanitise before render)
 *  - Blade views (serialised via ::asJson())   (frontend)
 */
class ColumnTypeRules
{
    /**
     * Returns true when $option is allowed for $type.
     * null in $allowedOptions means "allowed for ALL types".
     */
    public static function allows(string $type, string $option): bool
    {
        $map = SchemaColumn::$allowedOptions;

        if (!array_key_exists($option, $map)) {
            // Unknown option — allow by default so we don't break things
            return true;
        }

        $allowed = $map[$option];

        if ($allowed === null) {
            return true;
        }

        return in_array(strtolower($type), array_map('strtolower', $allowed), true);
    }

    /**
     * Strip / reset option values that are illegal for the given type.
     * Also enforces cross-field rules (PK can't be nullable, etc.).
     *
     * @param  array $attrs  raw column attribute array
     * @return array         sanitised attribute array
     */
    public static function sanitise(array $attrs): array
    {
        $type = $attrs['type'] ?? 'string';

        // ── Type-level option restrictions ─────────────────────────────
        if (!self::allows($type, 'length')) {
            $attrs['length'] = null;
        }

        if (!self::allows($type, 'auto_increment')) {
            $attrs['auto_increment'] = false;
        }

        // ── Cross-field rules ───────────────────────────────────────────
        // Primary key cannot be nullable
        if (!empty($attrs['is_primary']) && !empty($attrs['is_nullable'])) {
            $attrs['is_nullable'] = false;
        }

        // AUTO_INCREMENT columns are implicitly NOT NULL
        if (!empty($attrs['auto_increment']) && !empty($attrs['is_nullable'])) {
            $attrs['is_nullable'] = false;
        }

        // is_unique is redundant on a primary key (strip it silently on save)
        if (!empty($attrs['is_primary']) && !empty($attrs['is_unique'])) {
            $attrs['is_unique'] = false;
        }

        return $attrs;
    }

    /**
     * Return the rule map serialised for frontend injection.
     *
     * Usage in Blade:
     *   const COLUMN_TYPE_RULES = @json(\App\Support\ColumnTypeRules::asJson());
     */
    public static function asJson(): array
    {
        return SchemaColumn::$allowedOptions;
    }
}
