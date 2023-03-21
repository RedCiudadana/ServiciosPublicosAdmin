<?php

namespace App\Form\RouteService;

use App\Entity\RouteService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RouteServiceBaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Nombre: Ingresa el nombre de la ruta de servicios correspondiente.',
            ])
            ->add('description', null, [
                'label' => 'Descripción: Ingresa la descripción de la ruta de servicios correspondiente.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RouteService::class,
        ]);
    }
}
