<?php

declare(strict_types=1);

namespace App\Models;

class HoldSetup
{
    private function __construct(
        private readonly BoardType $boardType,
        private readonly BoardAngle $boardAngle,
    ) {}

    public static function createMoonBoard2016(): self
    {
        return new self(
            boardType: BoardType::MoonBoard2016,
            boardAngle: BoardAngle::Degree40MoonBoard2016,
        );
    }

    public static function createMoonBoardMasters2017Degree40(): self
    {
        return new self(
            boardType: BoardType::MoonBoardMasters2017,
            boardAngle: BoardAngle::Degree40,
        );
    }

    public static function createMoonBoardMasters2017Degree25(): self
    {
        return new self(
            boardType: BoardType::MoonBoardMasters2017,
            boardAngle: BoardAngle::Degree25,
        );
    }

    public static function createMoonBoardMasters2019Degree40(): self
    {
        return new self(
            boardType: BoardType::MoonBoardMasters2019,
            boardAngle: BoardAngle::Degree40,
        );
    }

    public static function createMoonBoardMasters2019Degree25(): self
    {
        return new self(
            boardType: BoardType::MoonBoardMasters2019,
            boardAngle: BoardAngle::Degree25,
        );
    }

    public static function createMiniMoonBoard2020(): self
    {
        return new self(
            boardType: BoardType::MiniMoonBoard2020,
            boardAngle: BoardAngle::Degree40,
        );
    }

    public function getBoardType(): BoardType
    {
        return $this->boardType;
    }

    public function getBoardAngle(): BoardAngle
    {
        return $this->boardAngle;
    }
}
