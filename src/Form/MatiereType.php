<?php

namespace App\Form;

use App\Entity\Categorie;
use App\Entity\Matiere;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MatiereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('titre', null, [
            'required' => false,
        ])
        ->add('categorie', EntityType::class, [
            'class' => Categorie::class,
            'choice_label' => 'name',
            'required' => false,
        ])
        ->add('prerequis', null, [
            'required' => false,
        ])
        ->add('description', null, [
            'required' => false,
        ])
        ->add('couleur_theme', null, [
            'required' => false,
        ]);
           
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Matiere::class,
        ]);
    }
}
