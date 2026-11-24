<?php

namespace App\Enums;

enum ExperienceType: string
{
    case Work = 'work';
    case Education = 'education';

    public function label(): string
    {
        return match ($this) {
            self::Work => 'Work',
            self::Education => 'Education',
        };
    }
}
