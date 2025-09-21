<?php
namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom de la catégorie',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom de la catégorie'
                ]
            ])
            ->add('mots_cles', null, [
                'label' => 'Mots-clés',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Séparez les mots-clés par des virgules'
                ]
            ])
            ->add('public_cible', ChoiceType::class, [
                'label' => 'Public cible',
                'choices' => [
                    'Élèves' => 'élèves',
                    'Étudiants' => 'étudiants',
                    'Adultes' => 'adultes',
                    'Professionnels' => 'professionnels'
                ],
                'placeholder' => 'Sélectionnez un public',
                'required' => false,
                'attr' => [
                    'class' => 'form-select'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez sélectionner un public cible'
                    ])
                ]
            ])
            ->add('descrption', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Décrivez la catégorie'
                ]
            ])
            ->add('brochure', FileType::class, [
                'label' => 'Fichier à uploader',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new File([
                        'mimeTypes' => [ // Allowed MIME types
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, GIF, or WEBP).',
                    ])
                ],
                    
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Categorie::class,
        ]);
    }
}