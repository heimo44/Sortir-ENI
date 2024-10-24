<?php

namespace App\Controller;

use App\Form\AccueilType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



class MainController extends AbstractController
{
    #[Route('/accueil', name: 'main_accueil', methods: ['GET'])]
    public function accueil(Request $request): Response
    {
        // Création du formulaire AccueilType
        $accueilForm = $this->createForm(AccueilType::class);
        $accueilForm->handleRequest($request);

        return $this->render('main/accueil.html.twig', [
            'accueilForm' => $accueilForm
        ]);
    }
}