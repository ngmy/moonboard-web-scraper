<?php

declare(strict_types=1);

namespace App\Models;

enum BoardType: int
{
    case MoonBoard2016 = 1;
    case MoonBoardMasters2017 = 15;
    case MoonBoardMasters2019 = 17;
    case MiniMoonBoard2020 = 19;

    public function getLabel(): string
    {
        return match ($this) {
            self::MoonBoard2016 => 'MoonBoard 2016',
            self::MoonBoardMasters2017 => 'MoonBoard Masters 2017',
            self::MoonBoardMasters2019 => 'MoonBoard Masters 2019',
            self::MiniMoonBoard2020 => 'Mini MoonBoard 2020',
        };
    }
}
