<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\EtatRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sortie')]
final class SortieController extends AbstractController
{

    public function __construct(ParticipantRepository $participantRepository)
    {
        $this->participantRepository = $participantRepository;
    }

    #[Route(name: 'app_sortie_index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository): Response
    {
        return $this->render('main/accueil.html.twig', [
            'sorties' => $sortieRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $currentUser = $this->participantRepository->findOneBy(['email' => $this->getUser()->getUserIdentifier()]);
        $campus = $currentUser->getCampus();
        $sortie = new Sortie();
        $sortie->setOrganisateur($currentUser);
        $sortie->setCampus($campus);
        $etat = new Etat();
        $etat->setLibelle('Créée');
        if ($request->isMethod('POST')) {
            dump($request->getContent());
            if ($request->request->get('action') === 'sauvegarder') {
                $etat->setLibelle('Créée');
                $entityManager->flush();
            }
            if ($request->request->get('action') === 'publier') {
                $etat->setLibelle('Ouverte');
                $entityManager->flush();
            }
        }
        $entityManager->persist($etat);
        $entityManager->flush();

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sortie->setEtat($etat);
            $entityManager->persist($sortie);
            $entityManager->flush();

            return $this->redirectToRoute('main_accueil');
        }

        return $this->render('sortie/new.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sortie_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function show(Sortie $sortie): Response
    {
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sortie_edit', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($request->request->get('annulerSortie')) {
            $etatCancel = $etatRepository->findOneBy(['libelle' => 'Annulée']);
            $sortie->setEtat($etatCancel);
            $entityManager->flush();
            return $this->redirectToRoute('main_accueil');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $action = $request->request->get('action');
            if ($action === 'sauvegarder') {
                    $etat = $etatRepository->findOneBy(['libelle' => 'Créée']);
                    $sortie->setEtat($etat);
                }elseif ($action === 'publier') {
                    $etat = $etatRepository->findOneBy(['libelle' => 'Ouverte']);
                    $sortie->setEtat($etat);
            }
            $entityManager->persist($sortie);
            $entityManager->flush();
            return $this->redirectToRoute('main_accueil', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/cancel', name: 'app_sortie_cancel', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function cancel(Request $request, Sortie $sortie, EntityManagerInterface $entityManager, EtatRepository $etatRepository, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->findOneBy(['id' => $sortie->getId()]);

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        $currentDateTime = new \DateTime();

        if ($sortie->getDateHeureDebut() < $currentDateTime) {
            $this->addFlash('error', 'Vous ne pouvez pas annuler une sortie dont la date est déjà passée.');
        }

        if ($request->isMethod('POST')) {
            $motif = $request->request->get('motif');

            if ($request->request->get('action') === 'save') {
                $sortie->setMotif($motif);
                $entityManager->flush();

                return $this->redirectToRoute('app_sortie_cancel', ['id' => $sortie->getId()], Response::HTTP_SEE_OTHER);
            } elseif ($request->request->get('action') === 'cancel') {
                $etatCancel = $etatRepository->findOneBy(['libelle' => 'Annulée']);
                $sortie->setEtat($etatCancel);
                $sortie->setMotif($motif);
                $entityManager->flush();

                return $this->redirectToRoute('main_accueil', ['id' => $sortie->getId()], Response::HTTP_SEE_OTHER);
            }
        }
        return $this->render('sortie/cancel.html.twig', [
            'sortie' => $sortie,
            'form' => $form,
        ]);
    }
}
