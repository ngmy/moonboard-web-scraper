<?php

declare(strict_types=1);

namespace App\Services;

class FileUserProfileUrlsGenerator implements UserProfileUrlsGeneratorInterface
{
    public function __construct(
        private readonly string $filePath,
    ) {}

    public function generate(): array
    {
        /** @var string[] $userIds */
        $userIds = file($this->filePath);
        $userIds = array_map('trim', $userIds);

        return array_map(static fn (string $userId): string => sprintf('/Account/Profile/%s', $userId), $userIds);
    }
}
