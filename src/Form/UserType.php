<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', TextType::class, [
                'disabled' => true
            ])
            ->add('name', null, [
                'label' => 'Nombre'
            ])
            ->add('position', null, [
                'label' => 'Cargo o puesto'
            ])
            // Asigna el rol ADMIN si es marcado
            ->add('isAdministrator', CheckboxType::class,[
                'mapped' => true
            ])
            // ->add('password')
            // We dont suppport multiple select so this is disabled for now.
            // ->add('institutions')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
