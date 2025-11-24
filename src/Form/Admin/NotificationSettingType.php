<?php

namespace App\Form\Admin;

use App\Entity\UserNotificationSetting;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotificationSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // PARTICIPANT
            ->add('notifyMentioned', CheckboxType::class, [
                'label' => 'Mentionné',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
            ->add('notifyWatcher', CheckboxType::class, [
                'label' => 'Observateur',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
            ->add('notifyAssigned', CheckboxType::class, [
                'label' => 'Assigné à',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
            ->add('notifyResponsible', CheckboxType::class, [
                'label' => 'Responsable',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
            ->add('notifyShared', CheckboxType::class, [
                'label' => 'Partagé',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])

            // ALERTES DE DATE
            ->add('notifyStartDate', CheckboxType::class, [
                'label' => 'Date de début',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
            ->add('startDateDelay', ChoiceType::class, [
                'choices' => [
                    '1 jour avant' => '1_day',
                    '3 jours avant' => '3_days',
                    '1 semaine avant' => '1_week',
                ],
                'attr' => ['class' => 'mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white'],
                'required' => false,
            ])
            ->add('notifyEndDate', CheckboxType::class, [
                'label' => 'Date de fin',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
            ->add('endDateDelay', ChoiceType::class, [
                'choices' => [
                    '1 jour avant' => '1_day',
                    '3 jours avant' => '3_days',
                    '1 semaine avant' => '1_week',
                ],
                'attr' => ['class' => 'mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white'],
                'required' => false,
            ])
            ->add('notifyOverdue', CheckboxType::class, [
                'label' => 'En cas de retard',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
            ->add('overdueFrequency', ChoiceType::class, [
                'choices' => [
                    'tous les jours' => 'daily',
                    'toutes les semaines' => 'weekly',
                ],
                'attr' => ['class' => 'mt-1 block w-full rounded-md border-gray-300 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white'],
                'required' => false,
            ])

            // NON PARTICIPANT
            ->add('notifyNewWorkPackage', CheckboxType::class, [
                'label' => 'Nouveaux lots de travaux',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
            ->add('notifyStatusChanges', CheckboxType::class, [
                'label' => 'Tous les changements de statut',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
            ->add('notifyDateChanges', CheckboxType::class, [
                'label' => 'Tous les changements de date',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
            ->add('notifyPriorityChanges', CheckboxType::class, [
                'label' => 'Tous les changements de priorité',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
            ->add('notifyNewComments', CheckboxType::class, [
                'label' => 'Tous les nouveaux commentaires',
                'required' => false,
                'attr' => ['class' => 'rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50'],
                'label_attr' => ['class' => 'ml-2 text-sm text-gray-700 dark:text-gray-300']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserNotificationSetting::class,
        ]);
    }
}
