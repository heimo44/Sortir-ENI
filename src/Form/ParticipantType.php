<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Participant;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
                'label' => 'Nom <span class="red-text">*</span>',
                'label_html' => true,
            ])
            ->add('Firstname', TextType::class, [
                'label' => 'Prénom <span class="red-text">*</span>',
                'label_html' => true,
            ])
            ->add('telephone', TextType::class, [
                'label' => 'Numéro de téléphone <span class="red-text">*</span>',
                'label_html' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email <span class="red-text">*</span>',
                'label_html' => true,
            ])
            ->add('newPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'required' => false,
                'empty_data' => '',
                'mapped' => false,
            ])
        ;

        if ($options['user_creation']) {
            $builder
                ->add('roles', CheckboxType::class, [
                    'label' => "L'utilisateur est un administrateur",
                    'required' => false,
                    'mapped' => false,
                    'attr' => [
                        'value' => 'ROLE_ADMIN',
                    ],
                ])
                ->add('campus', EntityType::class, [
                    'class' => Campus::class,
                    'choice_label' => 'nom',
                ]);
        }

        if ($options['user_edition']) {
            $builder
                ->add('pseudo', TextType::class, [
                    'label' => 'Pseudo',
                    'required' => false,
                ])
                ->add('newPasswordConfirmation', PasswordType::class, [
                    'label' => 'Confirmer le mot de passe <span class="red-text">(si le champ précédent est rempli)</span>',
                    'label_html' => true,
                    'required' => false,
                    'empty_data' => '',
                    'mapped' => false,
                ])
                ->add('profileImageFilename', FileType::class, [
                    'label' => 'Image de profil (format JPEG ou PNG)',
                    'mapped' => false,
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