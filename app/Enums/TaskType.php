<?php

namespace App\Enums;

/**
 * Enum representing task types.
 */
enum TaskType: string
{
    case Bug = 'bug';
    case Feature = 'feature';
    case Improvement = 'improvement';
    /**
     * Get all type values.
     *
     * @return array The array of type values.
     */
    public static function values(): array
    {
        return [
            self::Bug,
            self::Feature,
            self::Improvement,
        ];
    }
}
