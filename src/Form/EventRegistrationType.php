<?php

namespace App\Form;

use App\Entity\EventRegistration;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;


class EventRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => [
                    'class' => 'input-text js-input',
                    'placeholder' => 'Enter Name',
                ],
            ])
            ->add('coming_from', TextType::class, [
                'label' => 'City',
                'attr' => [
                    'class' => 'input-text js-input',
                    'placeholder' => 'City',
                ],
            ])

            ->add('places_reserved', IntegerType::class, [
                'label' => 'Number of Places (between 1->5)',
                'attr' => [
                    'class' => 'input-text js-input',
                    'placeholder' => 'Number of places',
                    'min' => 1,  // Optional: Prevents negative numbers
                    'step' => 1, // Ensures only whole numbers can be entered
                    'max' => 5,
                ],
            ])
            
            ->add('disabled_parking', ChoiceType::class, [
                'label' => 'Do you require a parking spot for special needs?',
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'expanded' => true, // Render as radio buttons
                'multiple' => false,
                'attr' => [
                    'class'=>'form-check-input',
                ] 
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You must agree to our terms.'
                    ])
                ],
                'label' => false, // Hide automatic label
                'attr' => [
                    'class' => 'form-check-input',
                    'required' => 'required'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EventRegistration::class,
        ]);
    }
}
