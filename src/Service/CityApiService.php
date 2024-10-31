<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class CityApiService
{
    private HttpClientInterface $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    public function getCities(): array
    {
        try {
            $response = $this->client->request('GET', 'https://geo.api.gouv.fr/communes?nom=Paris', [
                'verify_peer' => false,  // Désactive la vérification SSL pour l'environnement de dev
            ]);

            return $response->toArray();
        } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
            throw new \Exception('Erreur lors de la récupération des données : ' . $e->getMessage());
        }
    }
}

