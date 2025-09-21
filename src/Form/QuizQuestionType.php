<?php
// src/Form/QuizQuestionType.php

namespace App\Form;

use App\Entity\QuizQuestion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuizQuestionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', TextType::class, [
                'label' => 'Question Type',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('points', IntegerType::class, [
                'label' => 'Points',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('texte', TextType::class, [
                'label' => 'Question Text',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('ordre', IntegerType::class, [
                'label' => 'Order',
                'attr' => ['class' => 'form-control'],
            ]) // Order validation is handled in the entity

            ->add('reponses', CollectionType::class, [
                'entry_type' => QuizResponseType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'by_reference' => false,
                'label' => 'Responses',
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-primary'],
            ])
            ->add('add_another', SubmitType::class, [
                'label' => 'Add Another Question',
                'attr' => ['class' => 'btn btn-secondary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuizQuestion::class, // Fixed class name reference
        ]);
    }
}
