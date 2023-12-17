<?php

declare(strict_types=1);

namespace App\Commands;

use App\UseCases\ScrapeLogbookAction;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeLogbookCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'scrape-logbook';

    /** @var string */
    protected static $defaultDescription = 'Scrape logbook';

    public function __construct(
        private readonly ScrapeLogbookAction $action,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ($this->action)();

        return Command::SUCCESS;
    }
}
