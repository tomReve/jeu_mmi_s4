<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;

class InscriptionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            //->add('roles')
            ->add('password', PasswordType::class, [
                'empty_data'=> ''
            ])
            ->add('nom')
            ->add('prenom')
            ->add('pseudo')
            ->add('recaptcha', EWZRecaptchaType::class)
            //->add('creationLe')
            //->add('connexionLe')
            //->add('avatar')
            //->add('token')
            //->add('online')
            //->add('avertissement')
            //->add('blocage')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
