<?php

namespace App\Enums;

/**
 * Enum representing task status.
 */
enum TaskStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Completed = 'completed';

    case Blocked = 'blocked';

    /**
     * Get all status values.
     *
     * @return array The array of status values.
     */
    public static function values(): array
    {
        return [
            self::Open,
            self::InProgress,
            self::Completed,
            self::Blocked,

        ];
    }
}
