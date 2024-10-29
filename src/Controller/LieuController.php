<?php

namespace App\Controller;

use App\Entity\Lieu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LieuController extends AbstractController
{
    #[Route('/lieu', name: 'app_lieu')]
    public function index(): Response
    {
        return $this->render('lieu/index.html.twig', [
            'controller_name' => 'LieuController',
        ]);
    }

    #[Route('/lieu/details', name: 'lieu_details')]
    public function details(EntityManagerInterface $entityManager, Request $request): Response
    {
        $lieuId = $request->query->get('id');

        if (!$lieuId) {
            return $this->json(['error' => 'Lieu id manquant'], Response::HTTP_BAD_REQUEST);
        }

        $lieu = $entityManager->getRepository(Lieu::class)->find($lieuId);

        if (!$lieu) {
            throw $this->createNotFoundException('Lieu non trouvé');
        }

        return $this->json([
            'rue' => $lieu->getRue(),
            'codePostal' => $lieu->getVille()->getCodePostal(),
            'longitude' => $lieu->getLongitude(),
            'latitude' => $lieu->getLatitude(),
        ]);
    }
}
