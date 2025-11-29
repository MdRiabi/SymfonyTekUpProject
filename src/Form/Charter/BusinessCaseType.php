<?php

namespace App\Form\Charter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BusinessCaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('justification', TextareaType::class, [
                'label' => 'Justification Business (Pourquoi ce projet ?)',
                'attr' => [
                    'rows' => 4,
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500',
                    'placeholder' => 'Expliquez le problème à résoudre ou l\'opportunité à saisir...'
                ],
                'constraints' => [new Assert\NotBlank()]
            ])
            ->add('roi', TextareaType::class, [
                'label' => 'Analyse de Valeur & ROI',
                'attr' => [
                    'rows' => 3,
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500',
                    'placeholder' => 'Estimation des bénéfices financiers ou qualitatifs...'
                ],
                'constraints' => [new Assert\NotBlank()]
            ])
            ->add('alignementStrategique', ChoiceType::class, [
                'label' => 'Alignement Stratégique',
                'choices' => [
                    'Critique / Stratégique' => 'critical',
                    'Important / Tactique' => 'important',
                    'Support / Opérationnel' => 'support',
                    'Expérimental / Innovation' => 'innovation'
                ],
                'expanded' => true,
                'attr' => ['class' => 'flex flex-col space-y-2'],
                'label_attr' => ['class' => 'font-medium text-gray-700 dark:text-gray-300'],
                'constraints' => [new Assert\NotBlank()]
            ])
            ->add('metriquesSucces', TextareaType::class, [
                'label' => 'Métriques de Succès (KPIs)',
                'attr' => [
                    'rows' => 3,
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500',
                    'placeholder' => 'Ex: Augmentation du CA de 10%, Réduction du temps de traitement de 20%...'
                ],
                'constraints' => [new Assert\NotBlank()]
            ])
            ->add('risquesPrincipaux', TextareaType::class, [
                'label' => 'Risques Principaux Identifiés',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500',
                    'placeholder' => 'Quels sont les obstacles potentiels ?'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
