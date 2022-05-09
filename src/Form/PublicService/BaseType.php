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
            ->add('institution')
            ->add('subcategory')
            ->add('instructions')
            ->add('requirements')
            ->add('cost', NumberType::class)
            ->add('timeResponse')
            ->add('typeOfDocumentObtainable')
            ->add('normative')
            ->add('url')
            ->add('highlight')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PublicService::class,
        ]);
    }
}
