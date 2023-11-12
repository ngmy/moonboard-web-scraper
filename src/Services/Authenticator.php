<?php

declare(strict_types=1);

namespace App\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\Panther\Client;

class Authenticator
{
    public function __construct(
        private readonly string $username,
        private readonly string $password,
        private readonly LoggerInterface $logger,
    ) {}

    public function authenticate(Client $client): void
    {
        $this->logger->info('Authenticating...');

        $client->request('GET', '/');
        $client->executeScript("$('#loginDropdown').click()");

        $crawler = $client->getCrawler();

        $form = $crawler->selectButton('Login')->form();
        $form->setValues([
            'Login.Username' => $this->username,
            'Login.Password' => $this->password,
        ]);

        $client->submit($form);
        $client->wait()->until(static fn (): bool => 'https://www.moonboard.com/Dashboard/Index' === $client->getCurrentURL());
    }
}
