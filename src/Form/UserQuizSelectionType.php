<?php

namespace App\Form;

use App\Entity\QuizQuestion;
use App\Entity\QuizResponse;
use App\Entity\UserQuizSelection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserQuizSelectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('estCorrecte')
            ->add('responseSelectionnee', EntityType::class, [
                'class' => QuizResponse::class,
                'choice_label' => 'id',
            ])
            ->add('question', EntityType::class, [
                'class' => QuizQuestion::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserQuizSelection::class,
        ]);
    }
}
