<?php

declare(strict_types=1);

namespace App\UseCases;

use App\Models\HoldSetup;
use App\Models\HoldSetups;
use App\Services\Authenticator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class ScrapeBenchmarksAction
{
    public function __construct(
        private readonly Client $client,
        private readonly Authenticator $authenticator,
        private readonly HoldSetups $holdSetups,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(): void
    {
        $this->authenticator->authenticate($this->client);

        foreach ($this->holdSetups as $holdSetup) {
            $problemUrls = $this->getProblemUrls($holdSetup);

            $problemData = $this->getProblemData($problemUrls);

            $this->saveProblemData($problemData, $holdSetup);
        }
    }

    /**
     * @return string[]
     */
    private function getProblemUrls(HoldSetup $holdSetup): array
    {
        $this->client->request('GET', '/Dashboard/Index');
        $this->client->executeScript("$('#Holdsetup').val('".$holdSetup->getBoardType()->value."').change()");
        $this->client->executeScript("$('[data-id=\"".$holdSetup->getBoardAngle()->value."\"]').click()");

        // From the DOM, it is impossible to determine that the hold setup has changed,
        // so we have to wait a certain amount of time.
        sleep(3);

        $crawler = $this->client->getCrawler();

        $problemUrls = [];
        $page = 1;

        while (true) {
            $tables = $crawler->filter('#grdBenchmarks table');
            $tables->each(static function (Crawler $table, int $i) use (&$problemUrls): void {
                if (1 !== $i) {
                    return;
                }
                $table->filter('tr')->each(static function (Crawler $tr) use (&$problemUrls): void {
                    $tr->filter('td')->each(static function (Crawler $td, int $i) use (&$problemUrls): void {
                        if (0 !== $i) {
                            return;
                        }
                        $td->filter('a')->each(static function (Crawler $a) use (&$problemUrls): void {
                            $url = $a->attr('href');
                            if (null === $url) {
                                return;
                            }
                            $problemUrls[] = $url;
                        });
                    });
                });
            });

            ++$page;

            $pageElement = $crawler->filter('#grdBenchmarks [data-page="'.$page.'"]');

            if (0 === $pageElement->count()) {
                break;
            }

            $this->client->executeScript("$('#grdBenchmarks [data-page=\"{$page}\"]').click()");

            // From the DOM, it is impossible to determine that the page has changed,
            // so we have to wait a certain amount of time.
            sleep(3);
        }

        return $problemUrls;
    }

    /**
     * @param string[] $problemUrls
     *
     * @return \stdClass[]
     */
    private function getProblemData(array $problemUrls): array
    {
        $problemData = [];

        foreach ($problemUrls as $problemUrl) {
            $this->client->request('GET', $problemUrl);

            $crawler = $this->client->getCrawler();

            preg_match('/var problem = JSON\.parse\(\'(.*)\'\);/', $crawler->html(), $matches);

            try {
                /** @var \stdClass $data */
                $data = json_decode($matches[1], false, 512, JSON_THROW_ON_ERROR);
            } catch (\JsonException $e) {
                $this->logger->warning('Skip scraping problem data because it is invalid.', ['problemUrl' => $problemUrl]);

                continue;
            }

            if (null === $data) {
                $this->logger->warning('Skip scraping problem data because it is null.', ['problemUrl' => $problemUrl]);

                continue;
            }

            $problemData[] = $data;

            // To avoid overloading the server.
            sleep(1);
        }

        return $problemData;
    }

    /**
     * @param \stdClass[] $problemData
     */
    private function saveProblemData(array $problemData, HoldSetup $holdSetup): void
    {
        file_put_contents(
            sprintf('problems %s %s.json', $holdSetup->getBoardType()->getLabel(), $holdSetup->getBoardAngle()->getLabel()),
            json_encode($problemData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );
    }
}
