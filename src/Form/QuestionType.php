<?php

namespace App\Form;

use App\Entity\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints as Assert;

class QuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre de la question',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le titre de la question'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le titre est obligatoire'
                    ]),
                    new Assert\Length([
                        'min' => 4,
                        'max' => 255,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('enonce', TextareaType::class, [
                'label' => 'Énoncé de la question',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Entrez l\'énoncé de la question'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'énoncé est obligatoire'
                    ]),
                    new Assert\Length([
                        'min' => 10,
                        'minMessage' => 'L\'énoncé doit contenir au moins {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('point', IntegerType::class, [
                'label' => 'Points',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 100,
                    'placeholder' => 'Points (entre 0 et 100)'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Le nombre de points est obligatoire'
                    ]),
                    new Assert\Range([
                        'min' => 0,
                        'max' => 100,
                        'notInRangeMessage' => 'Le nombre de points doit être entre {{ min }} et {{ max }}'
                    ])
                ]
            ])
            ->add('ordreQuestion', IntegerType::class, [
                'label' => 'Ordre de la question',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 1,
                    'placeholder' => 'Position de la question'
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'L\'ordre de la question est obligatoire'
                    ]),
                    new Assert\Positive([
                        'message' => 'L\'ordre doit être un nombre positif'
                    ])
                ]
            ])
            ->add('programmingLanguage', ChoiceType::class, [
                'label' => 'Langage de programmation',
                'required' => false,
                'choices' => [
                    'Sélectionnez un langage' => '',
                    'Java' => 'java',
                    'Python' => 'python',
                    'JavaScript' => 'javascript',
                    'PHP' => 'php',
                    'C' => 'c',
                    'SQL' => 'sql',
                    'HTML' => 'html',
                    'CSS' => 'css'
                ],
                'attr' => [
                    'class' => 'form-control'
                ]
            ])
            ->add('codeSnippet', TextareaType::class, [
                'label' => 'Extrait de code',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 6,
                    'placeholder' => 'Collez votre code ici (optionnel)'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Question::class,
        ]);
    }
}