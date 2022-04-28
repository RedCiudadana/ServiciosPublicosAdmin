<?php

namespace App\Form\PublicService;

use App\Entity\PublicService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('instructions')
            ->add('requirements')
            ->add('cost', NumberType::class)
            ->add('timeResponse', DateIntervalType::class, [
                'widget' => 'integer',
                'with_years'  => false,
                'with_months' => false,
                'with_days'   => true,
                'with_hours'  => true
            ])
            ->add('typeOfDocumentObtainable')
            ->add('url')
            ->add('institution')
            ->add('category')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PublicService::class,
        ]);
    }
}
