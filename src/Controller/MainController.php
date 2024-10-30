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

        // Initialiser les critères de recherche
        $criteria = $accueilForm->getData() ?? [];
        $criteria['campus'] = $criteria['campus'] ?? $campus; // Définit le campus par défaut si absent

        // Ajouter l'utilisateur pour le filtre d'organisateur si nécessaire
        $isOrganizer = $criteria['organisateur'] ?? false;

        // Récupération du critère d'inscription
        $isInscrit = $criteria['sortie_inscrit'] ?? false;

        // Récupération du critère sortie passée
        $isPassed = $criteria['sortie_passée'] ?? false;

        // Récupérer les sorties en fonction des critères
        $sorties = $sortieRepository->findByFilters($criteria['campus'], $isOrganizer, $user, $isInscrit, $isPassed);

        return $this->render('main/accueil.html.twig', [
            'accueilForm' => $accueilForm->createView(),
            'currentDate' => $currentDate,
            'user' => $user,
            'sorties' => $sorties,
        ]);
    }
}