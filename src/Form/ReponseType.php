<?php
namespace App\Form;

use App\Entity\Question;
use App\Entity\Reponse;
use App\Entity\Resultat;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\OptionsResolver\OptionsResolver as SymfonyOptionsResolver; // Utiliser un alias

class ReponseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Champ pour le texte de la réponse, avec l'option 'required' à false
            ->add('text', null, [
                'required' => false, // Rend le champ non obligatoire
            ]);
    }

    public function configureOptions(SymfonyOptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reponse::class,
        ]);
    }
}