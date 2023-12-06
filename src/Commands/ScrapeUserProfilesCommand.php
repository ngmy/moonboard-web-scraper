<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\Authenticator;
use App\Services\FileUserProfileUrlsGenerator;
use App\Services\ScrapingUserProfileUrlsGenerator;
use App\UseCases\ScrapeUserProfilesAction;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Panther\Client;

class ScrapeUserProfilesCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'scrape-user-profiles';

    /** @var string */
    protected static $defaultDescription = 'Scrape user profiles';

    public function __construct(
        private readonly Client $client,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            name: 'user-ids-file',
            shortcut: 'f',
            mode: InputOption::VALUE_REQUIRED,
            description: 'Path to file containing user ids',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($input->hasOption('user-ids-file')) {
            /** @var string $userIdsFilePath */
            $userIdsFilePath = $input->getOption('user-ids-file');
            $userProfileUrlGenerator = new FileUserProfileUrlsGenerator($userIdsFilePath);
        } else {
            $userProfileUrlGenerator = new ScrapingUserProfileUrlsGenerator($this->client);
        }

        /** @var string $username */
        $username = getenv('MOONBOARD_USERNAME');

        /** @var string $password */
        $password = getenv('MOONBOARD_PASSWORD');

        $action = new ScrapeUserProfilesAction(
            client: $this->client,
            authenticator: new Authenticator(
                username: $username,
                password: $password,
                logger: $this->logger,
            ),
            userProfileUrlsGenerator: $userProfileUrlGenerator,
            logger: $this->logger,
        );

        $action();

        return Command::SUCCESS;
    }
}
