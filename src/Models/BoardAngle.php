<?php

declare(strict_types=1);

namespace App\Models;

enum BoardAngle: int
{
    case Degree40 = 1;
    case Degree25 = 2;
    case Degree40MoonBoard2016 = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::Degree40 => '40',
            self::Degree25 => '25',
            self::Degree40MoonBoard2016 => '',
        };
    }
}
