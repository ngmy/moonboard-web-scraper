<?php

declare(strict_types=1);

namespace App\Services;

interface UserProfileUrlsGeneratorInterface
{
    /**
     * @return string[]
     */
    public function generate(): array;
}
