<?php

namespace App\Form\SubCategory;

use App\Entity\SubCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

class BaseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',null,[
                'label' => 'Nombre: Ingresa el nombre de la subcategoría, recuerda colocar un nombre llamativo, amigable, breve y fácil de recordar.',
            ])
            ->add('description',null,[
                'label' => 'Descripción: Ingresa la descripción de la subcategoría, recuerda colocar la mayor cantidad de detalles posibles.',
            ])
            ->add('highlight', null, [
                'required' => false
            ])
            ->add('category')
            ->add('imageFile', VichImageType::class, [
                'required' => false,
                'error_bubbling' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SubCategory::class,
        ]);
    }
}
