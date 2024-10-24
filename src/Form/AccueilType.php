<?php

namespace App\Form;

use App\Entity\Campus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class AccueilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('campus', EntityType::class, [
                'label' => 'Campus',
                'class' => Campus::class,
                'choice_label' => 'nom',  // Assure-toi que 'nom' est bien l'attribut dans ton entité Campus
                'placeholder' => 'Veuillez choisir votre campus',
            ])
            ->add('nom_sortie', TextType::class, [
                'label' => 'Le nom de la sortie contient',
                'attr' => [
                    'class' => 'form-control', // Pour le style Bootstrap
                    'placeholder' => 'Rechercher une sortie...' // Texte d'indication
                ]
            ])
            // Champ de sélection de la première date (date de début)
            ->add('date_debut', DateType::class, [
                'widget' => 'single_text',
                'label' => 'Entre',
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            // Champ de sélection de la deuxième date (date de fin)
            ->add('date_fin', DateType::class, [
                'widget' => 'single_text',
                'label' => 'et',
                'attr' => [
                    'class' => 'form-control'
                ],
            ])
            ->add('sortie_organisateur', CheckboxType::class, [
                'label' => 'Sorties dont je suis l\'organisateur/trice',
                'required' => false
            ])
            ->add('sortie_inscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je suis inscrit/e',
                'required' => false
            ])
            ->add('sortie_non_inscrit', CheckboxType::class, [
                'label' => 'Sorties auxquelles je ne suis pas inscrit/e',
                'required' => false
            ])
            ->add('sortie_passee', CheckboxType::class, [
                'label' => 'Sorties passées',
                'required' => false
            ])
            ->add('rechercher', SubmitType::class, [
                'label' => 'Rechercher'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }

}