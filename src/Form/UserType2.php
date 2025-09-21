<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
class UserType2 extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('prenom')
            ->add('date_naissance', null, [
            'widget' => 'single_text',
            ])
            ->add('email') 
            ->add('localisation')
            ->add('image', FileType::class, [
                'label' => 'Choisir une image', 
                'mapped' => false,  
                'required' => false,
                
            ])
          
           
           
           
           
            
            ->add('save', SubmitType::class ,[
                'label' => 'Modifier'   
               ])
          ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
