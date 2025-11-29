<?php

namespace App\Form\Charter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ScopeDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('scopeIn', TextareaType::class, [
                'label' => 'Périmètre Inclus (In-Scope)',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez définir ce qui est inclus dans le projet.']),
                ],
                'attr' => [
                    'placeholder' => 'Listez les fonctionnalités et éléments inclus...',
                    'rows' => 5,
                ],
            ])
            ->add('scopeOut', TextareaType::class, [
                'label' => 'Périmètre Exclu (Out-of-Scope)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Listez ce qui est explicitement exclu pour éviter les dérives...',
                    'rows' => 3,
                ],
            ])
            ->add('functionalRequirements', CollectionType::class, [
                'label' => 'Exigences Fonctionnelles',
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'required' => false,
                'attr' => ['class' => 'space-y-2'],
                'entry_options' => [
                    'label' => false,
                    'attr' => ['placeholder' => 'Ex: Le système doit permettre...'],
                ],
            ])
            ->add('technicalRequirements', CollectionType::class, [
                'label' => 'Contraintes Techniques',
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'required' => false,
                'attr' => ['class' => 'space-y-2'],
                'entry_options' => [
                    'label' => false,
                    'attr' => ['placeholder' => 'Ex: Hébergement sur AWS, PHP 8.2...'],
                ],
            ])
            ->add('mvpFeatures', TextareaType::class, [
                'label' => 'Définition du MVP (Minimum Viable Product)',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez définir le MVP.']),
                ],
                'attr' => [
                    'placeholder' => 'Quelles sont les fonctionnalités indispensables pour la première version ?',
                    'rows' => 4,
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
