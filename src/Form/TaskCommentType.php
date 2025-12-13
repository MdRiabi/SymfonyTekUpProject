<?php

namespace App\Form;

use App\Entity\TaskComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskCommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('contenu', TextareaType::class, [
                'label' => 'Commentaire',
                'attr' => [
                    'rows' => 3,
                    'placeholder' => 'Ajoutez un commentaire...',
                    'class' => 'comment-input'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un commentaire'
                    ])
                ],
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TaskComment::class,
        ]);
    }
}
