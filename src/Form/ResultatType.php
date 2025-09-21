<?php

namespace App\Form;

use App\Entity\Reponse;
use App\Entity\Resultat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResultatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('score', null, [
                'required' => false, // Rend le champ score non obligatoire
            ])
            ->add('submitted_at', null, [
                'widget' => 'single_text',
                'required' => false, // Rend le champ submitted_at non obligatoire
            ])
            ->add('feedback', null, [
                'required' => false, // Rend le champ feedback non obligatoire
            ])
            ->add('reponse', EntityType::class, [
                'class' => Reponse::class,
                'choice_label' => 'id',
                'required' => false, // Rend le champ reponse non obligatoire
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Resultat::class,
        ]);
    }
}