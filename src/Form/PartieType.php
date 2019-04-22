<?php

namespace App\Form;

use App\Entity\Partie;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PartieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('joueur1', EntityType::class, [
                    'class'=>User::class,
                    'choice_label'=>'pseudo'
                ])
            ->add('joueur2', EntityType::class, [
                'class'=>User::class,
                'choice_label'=>'pseudo'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Partie::class,
        ]);
    }
}
