<?php

namespace App\Form;

use App\Entity\Events;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Validator\Constraints as Assert;

class EventsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('description',TextType::class, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('start_date', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => false,
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Start date cannot be null.',
                    ]),
                ],
            ])
            ->add('end_date', DateTimeType::class, [
                'widget' => 'single_text',
                'required' => false,
                'constraints' => [
                    new Assert\NotNull([
                        'message' => 'Start date cannot be null.',
                    ]),
                ],
            ])
            
            ->add('type', ChoiceType::class, [
                'label' => 'Event Type',
                'required' => false,
                'choices' => [
                    'Online' => 'Online',
                    'On-Site' => 'On-Site',
                ],
                'placeholder' => 'Select event type', // Adds a default option
                'expanded' => false, // Ensures it renders as a <select>
                'multiple' => false, // Ensures only one option is selectable
                'attr' => ['class' => 'form-control'], // Bootstrap styling (optional)
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ])
            ->add('max_participants', null, [
                'required' => false,
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Positive()
                ]
            ])
            ->add('location', null, [
                'required' => false,
                'constraints' => [
                    new Assert\Callback(function ($value, $context) {
                        $form = $context->getRoot();
                        $type = $form->get('type')->getData();
                        if ($type === 'On-Site' && empty($value)) {
                            $context->buildViolation('Location is required for On-Site events.')
                                ->addViolation();
                        }
                    })
                ]
            ])
            
            ->add('imageFile', FileType::class, [
                'mapped' => false,  // Do not map directly to the database
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, GIF)',
                    ])
                ]
            ])
            

            ->add('category', ChoiceType::class, [
                'choices' => [
                    'Conference' => 'conference',
                    'Workshop' => 'workshop',
                    'Webinar' => 'webinar',
                    'Hackathon' => 'hackathon',
                    'Fundraising Event' => 'fundraising',
                    'Cultural Event' => 'cultural',
                    'Sports Event' => 'sports',
                    'Networking Event' => 'networking',
                ],
                'label' => 'Categeory',
                'multiple' => false, // Enables multiple selection
                'expanded' => false, // Keeps it as a dropdown instead of checkboxes
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank()
                ]
            ]);

            
            
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class,
        ]);
    }
}
