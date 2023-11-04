<?php

declare(strict_types=1);

namespace App\Commands;

use App\UseCases\ScrapeBenchmarksAction;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ScrapeBenchmarksCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'scrape-benchmarks';

    /** @var string */
    protected static $defaultDescription = 'Scrape benchmarks';

    public function __construct(
        private readonly ScrapeBenchmarksAction $action,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ($this->action)();

        return Command::SUCCESS;
    }
}
