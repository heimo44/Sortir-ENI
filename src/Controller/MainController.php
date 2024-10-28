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
    #[Route('/accueil', name: 'main_accueil', methods: ['GET'])]
    public function accueil(Request $request, SortieRepository $sortieRepository): Response
    {
        // Création du formulaire AccueilType
        $accueilForm = $this->createForm(AccueilType::class);
        $accueilForm->handleRequest($request);

        // Récupérer la date du jour
        $currentDate = new \DateTime();

        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Initialiser les critères de filtre
//        $campus = null;
//        $isOrganizer = false;
//        $inscrit = false;
//        $nonInscrit = false;
//        $passe = false;
//        $nomSortie = null;
//        $dateDebut = null;
//        $dateFin = null;

//        // Vérifier si le formulaire a été soumis et est valide
//        if ($accueilForm->isSubmitted() && $accueilForm->isValid()) {
//            $data = $accueilForm->getData();
//            $campus = $data['campus'] ?? null;
//            $isOrganizer = $data['organisateur'] ?? false;
//        }
//
//        // Récupérer les sorties en fonction des critères
//        $sorties = $sortieRepository->findWithFilters($campus, $isOrganizer, $user);
//
//
        return $this->render('main/accueil.html.twig', [
            'accueilForm' => $accueilForm,
            'currentDate' => $currentDate,
            'user' => $user,
//            'sorties' => $sorties,
        ]);
    }
}