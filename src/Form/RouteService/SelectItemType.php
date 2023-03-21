<?php

namespace App\Form\RouteService;

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
      ->add('route_service_item', EntityType::class, [
        'class' => RouteServiceItem::class,
        'required' => true
      ]);
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([]);
  }
}
