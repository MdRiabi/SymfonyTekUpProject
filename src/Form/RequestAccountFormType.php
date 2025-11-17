<?php

namespace App\Form;

use App\Entity\AccountRequest;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RequestAccountFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Adresse e-mail professionnelle',
                'attr' => ['placeholder' => 'votre.nom@entreprise.com'],
            ])
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label' => 'nomRole',
                'label' => 'Rôle souhaité',
                'placeholder' => 'Sélectionnez un rôle',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Justification de la demande',
                'help' => 'Expliquez brièvement pourquoi vous avez besoin d’un accès et à quels projets.',
                'attr' => ['rows' => 4, 'placeholder' => 'Ex: Je suis chef du projet Marketing Digital...'],
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 20, 'max' => 500]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Pas de data_class : ce n’est pas lié à une entité (pour l’instant)
            'data_class' => AccountRequest::class,
        ]);
    }
}