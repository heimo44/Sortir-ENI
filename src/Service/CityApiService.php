<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class CityApiService
{
    private HttpClientInterface $client;


    public function __construct(HttpClientInterface $client, RouterInterface $router)
    {
        $this->client = $client;
        $this->router = $router;
    }
    public function getCities(): array
    {
        try {
            $response = $this->client->request('GET', 'https://geo.api.gouv.fr/communes', [
                'verify_peer' => false,  // Désactive la vérification SSL pour l'environnement de dev
            ]);

            return $response->toArray();
        } catch (TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e) {
            throw new \Exception('Erreur lors de la récupération des données : ' . $e->getMessage());
        }
    }

    public function getPostalCodeByCityName(string $cityName): array
    {
        return $this->searchByCommune($cityName);
    }

    public function getCityByPostalCode(string $postalCode): array
    {
        try{
            $response = $this->client->request('GET', "https://geo.api.gouv.fr/communes/$postalCode", [
                'verify_peer' => false,
            ]);
            return $response->toArray();
        } catch(TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e){
            $this->redirectToMain();
            return [$e->getMessage()];
        }
    }

    private function searchByCommune(string $commune): array
    {
        $c = ucfirst(strtolower($commune));
        try{
            $response = $this->client->request('GET', "https://geo.api.gouv.fr/communes?nom=$c", [
                'verify_peer' => false,
            ]);
            return $response->toArray();
        } catch(TransportExceptionInterface | ClientExceptionInterface | RedirectionExceptionInterface | ServerExceptionInterface $e){
            $this->redirectToMain();
            return [$e->getMessage()];
        }
    }

    public function redirectToMain(): RedirectResponse
    {
        return new RedirectResponse($this->router->generate('main_accueil'));
    }
}

