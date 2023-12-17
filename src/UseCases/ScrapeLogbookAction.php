<?php

declare(strict_types=1);

namespace App\UseCases;

use App\Models\HoldSetup;
use App\Models\HoldSetups;
use App\Services\Authenticator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class ScrapeLogbookAction
{
    public function __construct(
        private readonly Client $client,
        private readonly Authenticator $authenticator,
        private readonly HoldSetups $holdSetups,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(): void
    {
        $this->authenticator->authenticate($this->client);

        foreach ($this->holdSetups as $holdSetup) {
            // Skip the 25 degree MoonBoard logbook as it is not available on the website.
            if ($holdSetup->getBoardAngle()->isDegree25()) {
                continue;
            }

            $logbookData = $this->scrapeLogbookData($holdSetup);

            $this->saveLogbookData($logbookData, $holdSetup);
        }
    }

    /**
     * @return \stdClass[]
     */
    private function scrapeLogbookData(HoldSetup $holdSetup): array
    {
        $this->logger->info('Scraping logbook data...', [
            'boardType' => $holdSetup->getBoardType()->getLabel(),
            'boardAngle' => $holdSetup->getBoardAngle()->getLabel(),
        ]);

        $this->client->request('GET', '/Logbook/Index');
        $this->client->executeScript("$('#Holdsetup').val('".$holdSetup->getBoardType()->value."').change()");

        // From the DOM, it is impossible to determine that the hold setup has changed,
        // so we have to wait a certain amount of time.
        sleep(3);

        $crawler = $this->client->getCrawler();

        $logbook = [];
        $page = 1;

        $logbookExpandButtons = $crawler->filter('.k-i-expand');

        if (0 === $logbookExpandButtons->count()) {
            return $logbook;
        }

        while (true) {
            $this->client->executeScript("$('.k-i-expand').click()");

            // From the DOM, it is impossible to determine that the hold setup has changed,
            // so we have to wait a certain amount of time.
            sleep(5);

            $masterRows = $crawler->filter('.k-master-row');
            $masterRows->each(static function (Crawler $masterRow) use (&$logbook): void {
                $dateAdded = $masterRow->filter('.logbook-grid-header')->text();
                $dateAdded = explode(PHP_EOL, $dateAdded)[0];

                $detailRow = $masterRow->nextAll()->eq(0);

                $logbookEntries = $detailRow->filter('.logbookentry');
                $logbookEntries->each(static function (Crawler $logbookEntry) use (&$logbook, $dateAdded): void {
                    $id = $logbookEntry->filter('h3 a')->attr('href');
                    \assert(null !== $id);
                    $id = (int) explode('/', $id)[3];

                    $name = $logbookEntry->filter('h3 a')->text();

                    $setBy = $logbookEntry->filter('p')->eq(0)->text();

                    $grade = $logbookEntry->filter('p')->eq(1)->text();
                    $grade = preg_match('/(.+)\. You graded this problem (.+)\./', $grade, $matches);
                    $grade = $matches[1];

                    $yourGrade = $logbookEntry->filter('p')->eq(1)->text();
                    $yourGrade = preg_match('/(.+)\. You graded this problem (.+)\./', $yourGrade, $matches);
                    $yourGrade = $matches[2];

                    $method = $logbookEntry->filter('p')->eq(2)->text();

                    $rating = $logbookEntry->filter('ul')->eq(0)->filter('img[src="/Content/images/star.png"]')->count();

                    $yourRating = $logbookEntry->filter('ul')->eq(1)->filter('img[src="/Content/images/star.png"]')->count();

                    $numberOfTries = $logbookEntry->filter('p')->eq(3)->text();

                    $comment = null;
                    if ($logbookEntry->filter('p')->count() > 5) {
                        $comment = $logbookEntry->filter('p')->eq(5)->text();
                    }

                    $isBenchmark = 0 !== $logbookEntry->filter('.benchmark')->count();

                    $logbook[] = (object) [
                        'Id' => $id,
                        'Name' => $name,
                        'SetBy' => $setBy,
                        'Grade' => $grade,
                        'YourGrade' => $yourGrade,
                        'Method' => $method,
                        'Rating' => $rating,
                        'YourRating' => $yourRating,
                        'NumberOfTries' => $numberOfTries,
                        'Comment' => $comment,
                        'IsBenchmark' => $isBenchmark,
                        'DateAdded' => $dateAdded,
                    ];
                });
            });

            ++$page;

            $pageElement = $crawler->filter('[data-page="'.$page.'"]');

            if (0 === $pageElement->count()) {
                break;
            }

            $this->client->executeScript("$('[data-page=\"{$page}\"]').click()");

            // From the DOM, it is impossible to determine that the page has changed,
            // so we have to wait a certain amount of time.
            sleep(5);
        }

        return $logbook;
    }

    /**
     * @param \stdClass[] $logbookData
     */
    private function saveLogbookData(array $logbookData, HoldSetup $holdSetup): void
    {
        $this->logger->info('Saving logbook data...', [
            'boardType' => $holdSetup->getBoardType()->getLabel(),
            'boardAngle' => $holdSetup->getBoardAngle()->getLabel(),
        ]);

        file_put_contents(
            sprintf('logbook %s %s.json', $holdSetup->getBoardType()->getLabel(), $holdSetup->getBoardAngle()->getLabel()),
            json_encode($logbookData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );
    }
}
