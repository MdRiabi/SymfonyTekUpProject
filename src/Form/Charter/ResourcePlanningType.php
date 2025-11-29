<?php

namespace App\Form\Charter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class ResourcePlanningType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('teamMembers', TextareaType::class, [
                'label' => 'Équipe Projet & Rôles',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez définir l\'équipe projet.']),
                ],
                'attr' => [
                    'placeholder' => "Chef de Projet : Jean Dupont\nDéveloppeur Backend : ...\nDesigner : ...",
                    'rows' => 5,
                ],
                'help' => 'Listez les membres clés et leurs rôles.',
            ])
            ->add('budgetEstimate', MoneyType::class, [
                'label' => 'Estimation Budgétaire',
                'currency' => 'EUR',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez estimer le budget.']),
                    new PositiveOrZero(['message' => 'Le budget doit être positif.']),
                ],
                'attr' => [
                    'placeholder' => '0.00',
                ],
            ])
            ->add('timelineEstimate', TextareaType::class, [
                'label' => 'Planning & Jalons',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez définir le planning.']),
                ],
                'attr' => [
                    'placeholder' => "Mois 1 : Conception\nMois 2 : Développement MVP\n...",
                    'rows' => 4,
                ],
            ])
            ->add('resourceRisks', TextareaType::class, [
                'label' => 'Risques Ressources',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Ex: Disponibilité limitée du designer en Juillet...',
                    'rows' => 3,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
