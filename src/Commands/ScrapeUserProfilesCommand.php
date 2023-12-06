<?php

declare(strict_types=1);

namespace App\Commands;

use App\UseCases\ScrapeUserProfilesAction;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeUserProfilesCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'scrape-user-profiles';

    /** @var string */
    protected static $defaultDescription = 'Scrape user profiles';

    public function __construct(
        private readonly ScrapeUserProfilesAction $action,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ($this->action)();

        return Command::SUCCESS;
    }
}
