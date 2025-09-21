<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints as Assert;
class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('nom', TextType::class, [
            'required' => false,
        ])
        
        ->add('prenom', TextType::class, [
            'required' => false,
        ])
        ->add('date_naissance', DateType::class, [
            'widget' => 'single_text',
            'required' => false,
            
        ])
           
        ->add('email', TextType::class, [
            'required' => false,
        ])
           
            ->add('num_telephone', IntegerType::class, [
                'required' => false,
            ])  
            ->add('password', PasswordType::class,[
              'attr' =>[
                   'class'  => 'form-control',
                   'placeholder' => 'mot de pass ',

              ],
              'label' => false,

              'required' => false,

            ])
            ->add('image', FileType::class, [
                'label' => 'Choisir une image', 
                 'required' => false,
              
                
                
            ])
            ->add('genre', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'Homme',
                    'Femme' => 'Femme',
                    
                ],
                'placeholder' => 'Selectionner Genre', 
                'expanded' => false,
                'multiple' => false, 
                'required' => false,
                'attr' => [
                    'class' => 'form-control', 
                ]
              
            ])
            ->add('localisation', TextType::class, [
                'required' => false,
            ])
         
                
            ->add('confirm_password', PasswordType::class,[
                'attr' =>[
                     'class'  => 'form-control',
                     'placeholder' => 'mot de pass ',
  
                ],
                'label' => false,
                'required' => false,
  
  
              ])    
            
            ->add('save', SubmitType::class, [
                'label' => 'Login',
            ]);
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
