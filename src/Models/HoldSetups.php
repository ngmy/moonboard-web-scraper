<?php

declare(strict_types=1);

namespace App\Models;

/**
 * @implements \IteratorAggregate<HoldSetup>
 */
class HoldSetups implements \IteratorAggregate
{
    /**
     * @param HoldSetup[] $holdSetups
     */
    private function __construct(
        private readonly array $holdSetups,
    ) {}

    public static function all(): self
    {
        return new self(
            holdSetups: [
                HoldSetup::createMoonBoard2016(),
                HoldSetup::createMoonBoardMasters2017Degree40(),
                HoldSetup::createMoonBoardMasters2017Degree25(),
                HoldSetup::createMoonBoardMasters2019Degree40(),
                HoldSetup::createMoonBoardMasters2019Degree25(),
                HoldSetup::createMiniMoonBoard2020(),
            ],
        );
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->holdSetups);
    }
}
