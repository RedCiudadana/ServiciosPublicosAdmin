<?php

namespace App\Form\Institution;

use App\Entity\Institution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',null,[
                'label' => 'Nombre: Ingresa el nombre de la institución correspondiente.',
            ])
            ->add('description',null,[
                'label' => 'Descripción: Ingresa la descripción de la institución correspondiente.',
            ])
            ->add('address',null,[
                'label' => 'Dirección: Ingresa la dirección de la institución correspondiente.',
            ])
            ->add('schedule',null,[
                'label' => 'Horario: Ingresa el horario de atención de la institución correspondiente.',
            ])
            ->add('daysOpen',null,[
                'label' => 'Días abiertos: Ingresa los días de atención de la institución correspondiente.',
            ])
            ->add('webpage',null,[
                'label' => 'Página web: Ingresa el enlace de la página web de la institución correspondiente.',
            ])
            ->add('email',null,[
                'label' => 'Correo electrónico: Ingresa el correo institucional de la institución correspondiente.',
            ])
            ->add('type',null,[
                'label' => 'Tipo de Institución: Ingresa el tipo de institución correspondiente.',
            ])
            ->add('facebookURL',null,[
                'label' => 'Facebook: Ingresa el enlace de la página de Facebook de la institución correspondiente.',
            ])
            ->add('twitterURL',null,[
                'label' => 'Twitter: Ingresa el enlace de la página de Twitter de la institución correspondiente.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Institution::class,
        ]);
    }
}
