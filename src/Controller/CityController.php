<?php

namespace App\Controller;

use App\Service\CityApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CityController extends AbstractController
{
    private CityApiService $cityApiService;

    // Constructeur sans annotations de route ni de sécurité
    public function __construct(CityApiService $cityApiService)
    {
        $this->cityApiService = $cityApiService;
    }

    // Annotation pour le rôle et la route sur la méthode d'action
    #[IsGranted('ROLE_ADMIN')]
    #[Route('/cities', name: 'cities_list', methods: ['GET'])]
    public function list(): Response
    {
        $cities = $this->cityApiService->getCities();

        return $this->redirectToRoute('cities_list');
    }
}

