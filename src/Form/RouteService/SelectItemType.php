<?php

namespace App\Form\RouteService;

use App\Entity\PublicService;
use App\Entity\RouteServiceItem;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class SelectItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('publicService', EntityType::class, [
                'class' => PublicService::class,
                'autocomplete' => true,
                'placeholder' => 'Sin seleccionar',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RouteServiceItem::class
        ]);
    }
}
