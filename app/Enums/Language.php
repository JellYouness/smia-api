<?php

namespace App\Enums;

enum Language: string
{
    case ENGLISH = 'ENGLISH';
    case FRENCH = 'FRENCH';
    case ARABIC = 'ARABIC';
    case SPANISH = 'SPANISH';

    /**
     * Convert frontend language code to enum value
     */
    public static function fromCode(string $code): ?self
    {
        return match (strtolower($code)) {
            'en', 'english' => self::ENGLISH,
            'fr', 'french' => self::FRENCH,
            'ar', 'arabic' => self::ARABIC,
            'es', 'spanish' => self::SPANISH,
            default => null,
        };
    }

    /**
     * Get all available language codes
     */
    public static function getCodes(): array
    {
        return [
            'en' => self::ENGLISH->value,
            'fr' => self::FRENCH->value,
            'ar' => self::ARABIC->value,
            'es' => self::SPANISH->value,
        ];
    }
}
