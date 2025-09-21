<?php
// src/Form/QuizResponseType.php
namespace App\Form;

use App\Entity\QuizResponse;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class QuizResponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('texte', TextType::class, [
                'label' => 'Response Text',
            ])
            ->add('estCorrecte', CheckboxType::class, [
                'label' => 'Is Correct',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => QuizResponse::class,
        ]);
    }
}
