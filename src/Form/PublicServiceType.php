<?php

namespace App\Form;

use App\Entity\PublicService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PublicServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('description')
            ->add('instructions')
            ->add('requirements')
            ->add('cost')
            ->add('timeResponse')
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
