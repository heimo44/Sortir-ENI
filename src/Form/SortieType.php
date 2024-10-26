<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\User\UserInterface;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut', null, [
                'widget' => 'single_text',
            ])
            ->add('duree')
            ->add('dateLimiteInscription', null, [
                'widget' => 'single_text',
            ])
            ->add('nbInscriptionsMax')
            ->add('infosSortie')
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
            ->add('lieu', EntityType::class, [
                'class' => Lieu::class,
                'choice_label' => function ($lieu) {
                return $lieu->getRue() . ' ' . $lieu->getCode();
                },
            ])
            ->add('participants', EntityType::class, [
                'class' => Participant::class,
                'choice_label' => function (Participant $participant) {
                    return $participant->getFirstname(). ' ' . $participant->getLastName();
                },
                'multiple' => true,
            ])
            ->add('participant', EntityType::class, [
                'class' => Participant::class,
                'label' => 'Organisateur',
                'choice_label' => function (UserInterface $participant) {
                    return $participant->getUserIdentifier();
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
