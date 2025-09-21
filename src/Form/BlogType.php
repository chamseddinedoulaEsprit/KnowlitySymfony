<?php
namespace App\Form;

use App\Entity\Blog;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du blog',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le titre ne peut pas être vide'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères',
                        'max' => 255,
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez un titre pour votre blog'
                ]
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu du blog',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le contenu ne peut pas être vide'
                    ]),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'Le contenu doit contenir au moins {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Écrivez le contenu de votre blog ici...',
                    'rows' => 10
                ]
            ])
            ->add('creatorName', TextType::class, [
                'label' => 'Nom du créateur',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le nom du créateur ne peut pas être vide'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'max' => 50,
                        'maxMessage' => 'Le nom ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez votre nom'
                ]
            ])
            ->add('blogImage', FileType::class, [
                'label' => 'Image du blog',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG).',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('userImage', TextType::class, [
                'label' => 'Photo de profil (URL)',
                'required' => false,
                'constraints' => [
                    new Url([
                        'message' => 'Veuillez entrer une URL valide pour la photo de profil'
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://exemple.com/photo.jpg'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}