<?php
namespace App\Form;

use App\Entity\Chapitre;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ChapitreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du chapitre',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le titre du chapitre'
                ]
            ])
            ->add('duree_estimee', IntegerType::class, [
                'label' => 'Durée estimée (en minutes)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 45'
                ]
            ])
            ->add('chapOrder', IntegerType::class, [
                'label' => 'Ordre du chapitre',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 1'
                ]
            ])
        
            ->add('brochure', FileType::class, [
                'label' => 'Fichier PDF',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '10M', // 10 Mo
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf'
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader un fichier PDF valide'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Chapitre::class,
        ]);
    }
}