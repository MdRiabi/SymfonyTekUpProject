<?php

namespace App\Form;

use App\Entity\Projet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ProjectEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Titre du Projet',
                'required' => false, // Optional in edit mode
                'attr' => [
                    'placeholder' => 'Ex: Développement d\'un site e-commerce',
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de Projet',
                'required' => false,
                'choices' => [
                    'Sélectionnez un type' => '',
                    'Site Web / Application Web' => 'web',
                    'Application Mobile' => 'mobile',
                    'Application Desktop' => 'desktop',
                    'API / Backend' => 'api',
                    'Design / UI/UX' => 'design',
                    'Autre' => 'other',
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('categorie', ChoiceType::class, [
                'label' => 'Catégorie',
                'required' => false,
                'choices' => [
                    'Sélectionnez une catégorie' => '',
                    'E-commerce' => 'ecommerce',
                    'Site Corporate' => 'corporate',
                    'SaaS' => 'saas',
                    'Éducation' => 'education',
                    'Santé' => 'health',
                    'Finance' => 'finance',
                    'Autre' => 'other',
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description Détaillée',
                'required' => false,
                'attr' => [
                    'rows' => 6,
                    'placeholder' => 'Décrivez votre projet en détail: objectifs, fonctionnalités souhaitées, public cible...',
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('objectifs', TextareaType::class, [
                'label' => 'Objectifs Principaux',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'Listez les objectifs principaux de ce projet...',
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de Début Souhaitée',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de Fin Souhaitée',
                'widget' => 'single_text',
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('budget', ChoiceType::class, [
                'label' => 'Budget Estimé',
                'required' => false,
                'choices' => [
                    'Sélectionnez une fourchette' => '',
                    'Moins de 5 000 €' => '5000',
                    '5 000 € - 10 000 €' => '10000',
                    '10 000 € - 25 000 €' => '25000',
                    '25 000 € - 50 000 €' => '50000',
                    '50 000 € - 100 000 €' => '100000',
                    'Plus de 100 000 €' => '100001',
                ],
                'attr' => [
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('priorite', ChoiceType::class, [
                'label' => 'Priorité',
                'required' => false,
                'choices' => [
                    'Basse' => 'low',
                    'Moyenne' => 'medium',
                    'Haute' => 'high',
                    'Urgente' => 'urgent',
                ],
                'expanded' => true,
                'label_attr' => ['class' => 'block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2']
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes Additionnelles',
                'required' => false,
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Toute information supplémentaire que vous souhaitez partager...',
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500'
                ]
            ])
            ->add('fichierJoint', FileType::class, [
                'label' => 'Fichier Joint',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'w-full px-4 py-2 border dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500',
                    'accept' => '.pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg'
                ],
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.ms-excel',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'image/png',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier valide (PDF, DOC, DOCX, XLS, XLSX, PNG, JPG)',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximale autorisée est {{ limit }} {{ suffix }}.'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
