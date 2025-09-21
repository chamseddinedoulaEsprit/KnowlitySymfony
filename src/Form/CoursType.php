<?php
namespace App\Form;

use App\Entity\Cours;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Matiere;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;

class CoursType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du cours',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le titre du cours'
                ],
            ])
            ->add('matiere', EntityType::class, [
                'class' => Matiere::class,
                'choice_label' => 'titre',
                'required' => false,
            ])
            ->add('lienDePaiment', TextType::class, [
                'label' => 'lien de paiement du cours',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le lien de paiement du cours'
                ],
            ])
            ->add('langue', ChoiceType::class, [
                'label' => 'Langue du cours',
                'choices' => [
                    'Français' => 'fr',
                    'Anglais' => 'en',
                    'Espagnol' => 'es',
                    'Arabe' => 'ar'
                ],
                'attr' => [
                    'class' => 'form-select',
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner une langue'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description du cours',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez la description du cours',
                    'rows' => 4
                ],
            ])
            ->add('prix', MoneyType::class, [
                'label' => 'Prix du cours (TND)',
                'currency' => 'TND',
                'scale' => 3, // Permet les millimes
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'Le prix ne peut pas être négatif'
                    ]),
                    new Range([
                        'min' => 0,
                        'minMessage' => 'Le prix minimum est {{ limit }} TND'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 50.500',
                    'min' => 0,
                    'step' => '0.001'
                ],
                'html5' => true
            ])
            ->add('brochure', FileType::class, [
                'label' => "Image du cours",
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Formats autorisés : JPEG, PNG, WEBP',
                        'maxSizeMessage' => 'La taille maximale autorisée est {{ limit }}'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cours::class,
        ]);
    }
}