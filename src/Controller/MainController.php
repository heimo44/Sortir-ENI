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

        // Récupérer tous les filtres
        $isOrganizer = $criteria['organisateur'] ?? false;
        $isInscrit = $criteria['sortie_inscrit'] ?? false;
        $isNonInscrit = $criteria['sortie_non_inscrit'] ?? false;
        $isPassed = $criteria['sortie_passee'] ?? false;
        $searchTerm = $criteria['nom_sortie'] ?? null;
        $dateDebut = $criteria['date_debut'] ?? null;
        $dateFin = $criteria['date_fin'] ?? null;

        // Récupérer les sorties avec tous les filtres
        $sorties = $sortieRepository->findByFilters(
            $campus,
            $isOrganizer,
            $isInscrit,
            $isNonInscrit,
            $isPassed,
            $user,
            $searchTerm,
            $dateDebut,
            $dateFin
        );

        return $this->render('main/accueil.html.twig', [
            'accueilForm' => $accueilForm->createView(),
            'currentDate' => $currentDate,
            'user' => $user,
            'sorties' => $sorties,
        ]);
    }
}