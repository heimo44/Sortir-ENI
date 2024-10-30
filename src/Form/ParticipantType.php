<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('Lastname', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('Firstname', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Numéro de téléphone'
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email'
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => false,
                'empty_data' => '',
                'attr' => [
                    'mapped' => false,
                ]
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom',
            ])
        ;

        if ($options['user_creation']) {
            $builder
                ->add('roles', ChoiceType::class, [
                    'label' => false,
                    'choices' => [
                        "L'utilisateur est un administrateur" => 'ROLE_ADMIN',
                    ],
                    'multiple' => true,
                    'expanded' => true,
                ]);
        }

        if ($options['user_edition']) {
            $builder
                ->add('pseudo', TextType::class, [
                    'label' => 'Pseudo',
                    'required' => false,
                ])
                ->add('newPasswordConfirmation', PasswordType::class, [
                    'label' => 'Confirmer le mot de passe',
                    'required' => false,
                    'empty_data' => '',
                    'attr' => [
                        'mapped' => false,
                    ]
                ])
                ->add('profileImageFilename', FileType::class, [
                    'label' => '(JPEG, PNG)',
                    'mapped' => false, // We'll handle the file upload manually
                    'required' => false,
                ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'attr' => ['novalidate' => 'novalidate'],
            'user_creation' => false,
            'user_edition' => false,
        ]);
    }
}