<?php

namespace App\Form;

use App\Entity\Quiz;
use App\Entity\UserQuizResult;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserQuizResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('score')
            ->add('soumisLe', null, [
                'widget' => 'single_text',
            ])
            ->add('feedback')
            ->add('user')
            ->add('quiz', EntityType::class, [
                'class' => Quiz::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserQuizResult::class,
        ]);
    }
}
