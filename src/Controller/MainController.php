<?php

namespace App\Controller;

use App\Form\AccueilType;
use App\Repository\SortieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route('/accueil', name: 'main_accueil', methods: ['GET', 'POST'])]
    public function accueil(Request $request, SortieRepository $sortieRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        $campus = $user->getCampus();

        // Création du formulaire AccueilType
        $accueilForm = $this->createForm(AccueilType::class, null, [
            'campus_default' => $campus
        ]);
        $accueilForm->handleRequest($request);

        // Récupérer la date du jour
        $currentDate = new \DateTime();

//        // Initialiser les critères de filtre
//        $campus = null;
//        $isOrganizer = false;
        // Initialiser les critères de recherche
        $criteria = $accueilForm->getData() ?? [];
        $criteria['campus'] = $criteria['campus'] ?? $campus; // Définit le campus par défaut si absent

        // Vérifier si le formulaire est soumis et ajouter le campus par défaut si nécessaire
//        if ($accueilForm->isSubmitted() && !isset($criteria['campus'])) {
//            $criteria['campus'] = $campus;
//        }

        // Ajouter l'utilisateur pour le filtre d'organisateur si nécessaire
        $isOrganizer = $criteria['organisateur'] ?? false;

//        // Vérifier si le formulaire a été soumis et est valide
//        if ($accueilForm->isSubmitted() && $accueilForm->isValid()) {
//            $data = $accueilForm->getData();
//            $campus = $data['campus'] ?? null;
//            $isOrganizer = $data['organisateur'] ?? false;
//        }

        // Récupérer les sorties en fonction des critères
        //dump($campus, $isOrganizer, $user);
        $sorties = $sortieRepository->findByFilters($criteria['campus'], $isOrganizer, $user);

        //dump($sorties);

        return $this->render('main/accueil.html.twig', [
            'accueilForm' => $accueilForm->createView(),
            'currentDate' => $currentDate,
            'user' => $user,
            'sorties' => $sorties,
        ]);
    }
}