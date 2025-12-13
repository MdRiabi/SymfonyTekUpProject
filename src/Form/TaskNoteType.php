<?php

namespace App\Form;

use App\Entity\TaskNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', TextareaType::class, [
                'label' => 'Note de travail',
                'attr' => [
                    'class' => 'trix-content',
                    'rows' => 8,
                    'placeholder' => 'Décrivez le travail effectué, les problèmes rencontrés, les solutions apportées...'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir une note'
                    ])
                ],
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskNote::class,
        ]);
    }
}
