<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\SortieType;
use App\Form\VilleType;
use App\Service\CityApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/cities', name: 'cities_list', methods: ['GET'])]
    public function list(Request $request): Response
    {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);
        $cities = $this->cityApiService->getPostalCodeByCityName("Nantes");
        return $this->render('city/villes.html.twig', ['cities' => $cities, 'form' => $form->createView()]);
    }
}

