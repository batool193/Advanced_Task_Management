<?php

namespace App\Enums;

/**
 * Enum representing task priorities.
 */
enum TaskPriorty: string
{
    case Low = 'low';
    case Medium = 'medium';
    case High = 'high';
    /**
     * Get all priority values.
     *
     * @return array The array of priority values.
     */
    public static function values(): array
    {
        return [
            self::Low,
            self::Medium,
            self::High,
        ];
    }
}
