<?php

declare(strict_types=1);

namespace App\UseCases;

use App\Services\Authenticator;
use App\Services\UserProfileUrlsGeneratorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Panther\Client;

class ScrapeUserProfilesAction
{
    public function __construct(
        private readonly Client $client,
        private readonly Authenticator $authenticator,
        private readonly UserProfileUrlsGeneratorInterface $userProfileUrlsGenerator,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(): void
    {
        $this->authenticator->authenticate($this->client);

        $userProfileUrls = $this->scrapeUserProfileUrls();

        $userProfileData = $this->scrapeUserProfileData($userProfileUrls);

        $this->saveUserProfileData($userProfileData);
    }

    /**
     * @return string[]
     */
    private function scrapeUserProfileUrls(): array
    {
        $this->logger->info('Scraping user profile URLs...');

        return $this->userProfileUrlsGenerator->generate();
    }

    /**
     * @param string[] $userProfileUrls
     *
     * @return \stdClass[]
     */
    private function scrapeUserProfileData(array $userProfileUrls): array
    {
        $userProfileData = [];
        $userProfileUrlCount = \count($userProfileUrls);

        foreach ($userProfileUrls as $i => $userProfileUrl) {
            $this->logger->info('Scraping user profile data... ('.($i + 1).'/'.$userProfileUrlCount.')', ['userProfileUrl' => $userProfileUrl]);

            $this->client->request('GET', $userProfileUrl);

            $crawler = $this->client->getCrawler();

            $profileImageUrl = $crawler->filter('#imgProfile')->attr('style');
            \assert(null !== $profileImageUrl);
            $profileImageUrl = preg_match('/background-image: url\("(.*)"\)/', $profileImageUrl, $matches);
            $profileImageUrl = $matches[1];

            $firstName = $crawler->filter('#main-section-header h1')->text();
            $firstName = explode(',', $firstName)[0];
            $firstName = trim($firstName);

            $lastName = $crawler->filter('#main-section-header h1')->text();
            $lastName = explode(',', $lastName)[1];
            $lastName = trim($lastName);

            $city = $crawler->filter('#main-section-header h2')->text();
            $city = explode(',', $city)[0];
            $city = trim($city);

            $country = $crawler->filter('#main-section-header h2')->text();
            $country = explode(',', $country)[1];
            $country = trim($country);

            \assert(null !== $crawler->filterXPath('//label[text()="Bio:"]')->closest('div'));
            $biography = $crawler->filterXPath('//label[text()="Bio:"]')->closest('div')->filter('span')->text();

            \assert(null !== $crawler->filter('label[for=User_Height]')->closest('div'));
            $height = $crawler->filter('label[for=User_Height]')->closest('div')->filter('span')->text();

            \assert(null !== $crawler->filter('label[for=User_Weight]')->closest('div'));
            $weight = $crawler->filter('label[for=User_Weight]')->closest('div')->filter('span')->text();

            $globalMoonBoardRanking = $crawler->filter('#spnRank')->text();

            $highestGradeClimbed = $crawler->filter('#hhighgrade')->text();
            $highestGradeClimbed = preg_match('/Highest grade climbed: (.*)/', $highestGradeClimbed, $matches);
            $highestGradeClimbed = $matches[1];

            $totalProblemsClimbed = $crawler->filter('#htotalclimbed')->text();
            $totalProblemsClimbed = preg_match('/Total problems climbed: (.*)/', $totalProblemsClimbed, $matches);
            $totalProblemsClimbed = $matches[1];

            $userProfileData[] = (object) [
                'ProfileImageUrl' => $profileImageUrl,
                'FirstName' => $firstName,
                'LastName' => $lastName,
                'City' => $city,
                'Country' => $country,
                'Biography' => $biography,
                'Height' => $height,
                'Weight' => $weight,
                'GlobalMoonBoardRanking' => $globalMoonBoardRanking,
                'HighestGradeClimbed' => $highestGradeClimbed,
                'TotalProblemsClimbed' => $totalProblemsClimbed,
            ];

            // To avoid overloading the server.
            sleep(1);
        }

        return $userProfileData;
    }

    /**
     * @param \stdClass[] $userProfileData
     */
    private function saveUserProfileData(array $userProfileData): void
    {
        $this->logger->info('Saving user profile data...');

        file_put_contents(
            'user-profiles.json',
            json_encode($userProfileData, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        );
    }
}
