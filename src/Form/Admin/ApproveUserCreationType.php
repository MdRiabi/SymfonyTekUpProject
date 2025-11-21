<?php

namespace App\Form\Admin;

use App\Entity\Competence;
use App\Entity\Role;
use App\Entity\Utilisateur;
use App\Repository\UtilisateurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Formulaire pour approuver une demande de compte
 * L'email et le rôle sont déjà définis par la demande (AccountRequest)
 * et ne doivent pas être modifiables
 */
class ApproveUserCreationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('adresse', TextType::class, [
                'label' => 'Adresse',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('titrePoste', TextType::class, [
                'label' => 'Titre du Poste',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('departement', TextType::class, [
                'label' => 'Département',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('equipe', TextType::class, [
                'label' => 'Équipe',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'required' => false,
            ])
            ->add('matricule', TextType::class, [
                'label' => 'Matricule',
                'required' => false,
                'constraints' => [
                    new Assert\Length(['max' => 255]),
                ],
            ])
            ->add('estActif', CheckboxType::class, [
                'label' => 'Est Actif',
                'required' => false,
                'data' => true,
            ])
            ->add('capaciteHebdoH', NumberType::class, [
                'label' => 'Capacité Hebdo (en Heures)',
                'required' => false,
                'constraints' => [
                    new Assert\GreaterThan(0),
                ],
                'scale' => 2,
            ])
            ->add('competences', ChoiceType::class, [
                'choices' => [
                    'Dév' => 'dev',
                    'Design' => 'design',
                    'DevOps' => 'DevOps',
                    'DevSecOps' => 'DevSecOps',
                    'Cloud' => 'Cloud',
                    'Réseaux' => 'Reseaux',
                    'Système' => 'Systeme',
                    'AI' => 'Ai',
                ],
                'label' => 'Compétences',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
            ])
            ->add('photoFile', FileType::class, [
                'label' => 'Photo de Profil',
                'required' => false,
            ])
            ->add('manager', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => function ($utilisateur) {
                    return $utilisateur->getNom() . ' ' . $utilisateur->getPrenom();
                },
                'label' => 'Manager / N+1',
                'required' => false,
                'placeholder' => 'Pas de N+1',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'Mot de passe',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 6]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
