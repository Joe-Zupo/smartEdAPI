<?php

namespace App\Support;

final class GradeOfferings
{
    private const ELEMENTARY = ['Kinder', 'Grade 1', 'Grade 2', 'Grade 3', 'Grade 4', 'Grade 5', 'Grade 6'];
    private const JHS = ['Grade 7', 'Grade 8', 'Grade 9', 'Grade 10'];
    private const SHS = ['Grade 11', 'Grade 12'];

    public static function forType(?string $type): array
    {
        return match ($type) {
            'Elementary' => self::ELEMENTARY,
            'Junior High School' => self::JHS,
            'Standalone SHS' => self::SHS,
            'Integrated School',
            'Science High School',
            'ALS',
            'Junior High School with SHS' => [...self::ELEMENTARY, ...self::JHS, ...self::SHS],
            default => [],
        };
    }

    public static function all(): array
    {
        return [...self::ELEMENTARY, ...self::JHS, ...self::SHS];
    }
}
