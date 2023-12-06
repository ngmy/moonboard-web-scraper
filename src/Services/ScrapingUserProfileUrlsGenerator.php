<?php

declare(strict_types=1);

namespace App\Services;

use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\DomCrawler\Crawler;

class ScrapingUserProfileUrlsGenerator implements UserProfileUrlsGeneratorInterface
{
    public function __construct(
        private readonly Client $client,
    ) {}

    public function generate(): array
    {
        $this->client->request('GET', '/Account/UserProfiles');

        // From the DOM, it is impossible to determine that the hold setup has changed,
        // so we have to wait a certain amount of time.
        sleep(3);

        $crawler = $this->client->getCrawler();

        $userProfileUrls = [];
        $page = 1;

        while (true) {
            $tables = $crawler->filter('table');
            $tables->each(static function (Crawler $table, int $i) use (&$userProfileUrls): void {
                if (1 !== $i) {
                    return;
                }
                $table->filter('tr')->each(static function (Crawler $tr) use (&$userProfileUrls): void {
                    $tr->filter('td')->each(static function (Crawler $td, int $i) use (&$userProfileUrls): void {
                        if (0 !== $i) {
                            return;
                        }
                        $td->filter('a')->each(static function (Crawler $a) use (&$userProfileUrls): void {
                            $url = $a->attr('href');
                            if (null === $url) {
                                return;
                            }
                            $userProfileUrls[] = $url;
                        });
                    });
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
            sleep(3);
        }

        return $userProfileUrls;
    }
}
