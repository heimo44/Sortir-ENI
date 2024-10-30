<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Repository\ParticipantRepository;
use App\Repository\VilleRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type;


class SortieType extends AbstractType
{
    private Security $security;
    private ParticipantRepository $participantRepository;
    private VilleRepository $villeRepository;

    public function __construct(Security $security, ParticipantRepository $participantRepository, VilleRepository $villeRepository)
    {
        $this->security = $security;
        $this->participantRepository = $participantRepository;
        $this->villeRepository = $villeRepository;
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentUser = $this->participantRepository->findOneBy(['email' => $this->security->getUser()->getUserIdentifier()]);
        $campus = $currentUser->getCampus();
        $postalCode = $this->villeRepository->findOneBy(['nom' => $campus->getNom()]);
        $postalPrefix = substr($postalCode->getCodePostal(), 0, 2);

        $builder
            ->add('nom', Type\TextType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est obligatoire.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 50,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères.',
                    ]),
                ],
            ])
            ->add('dateHeureDebut', null, [
                'widget' => 'single_text',
                'attr' => [
                    'min' => (new \DateTime('+1 day'))->format('Y-m-d\TH:i'), // Default min to one day from now
                ],
                'constraints' => [
                    new Assert\NotNull(['message' => 'La date et l\'heure de début sont obligatoires.']),
                ],
            ])
            ->add('dateLimiteInscription', null, [
                'widget' => 'single_text',
                'attr' => [
                    'min' => (new \DateTime('+0 day'))->format('Y-m-d\TH:i'), // Minimum date set to today
                ],
                'constraints' => [
                    new Assert\NotNull(['message' => 'Le nombre maximum d\'inscriptions est obligatoire.']),
                ],
            ])
            ->add('duree', Type\IntegerType::class, [
                'label' => 'Durée en minute',
                'constraints' => [
                    new Assert\NotNull(['message' => 'La durée est obligatoire.']),
                ],
            ])
            ->add('nbInscriptionsMax', Type\IntegerType::class, [
                'constraints' => [
                    new Assert\NotNull(['message' => 'Le nombre maximum d\'inscriptions est obligatoire.']),
                    new Assert\Positive(['message' => 'Le nombre maximum d\'inscriptions doit être un nombre positif.']),
                ],
            ])
            ->add('infosSortie', TextareaType::class, [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La description sur la sortie est obligatoire.']),
                ],
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
                'choices' => [$currentUser->getCampus()],
                'data' => $currentUser->getCampus(),
                'disabled' => true,
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'query_builder' => function (EntityRepository $er) use ($postalPrefix) {
                    return $er->createQueryBuilder('l')
                        ->join('l.ville', 'v')
                        ->where('SUBSTRING(v.codePostal, 1, 2) = :prefix')
                        ->setParameter('prefix', $postalPrefix)
                        ->orderBy('l.nom', 'ASC');
                },
                'placeholder' => 'Sélectionnez un lieu',
                'constraints' => [
                    new Assert\NotNull(['message' => 'Veuillez sélectionner un lieu.']),
                ],
            ])
            ->add('rue', Type\TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Rue',
                'attr' => ['readonly' => true],
            ])
            ->add('codePostal', Type\TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Code postal',
                'attr' => ['readonly' => true],
            ])
            ->add('longitude', Type\TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Longitude',
                'attr' => ['readonly' => true],
            ])
            ->add('latitude', Type\TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Latitude',
                'attr' => ['readonly' => true],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
