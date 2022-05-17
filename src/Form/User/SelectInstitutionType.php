<?php

namespace App\Form\User;

use App\Entity\Institution;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @IsGranted("ROLE_ADMIN")
 */
class SelectInstitutionType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('institution', EntityType::class, [
        'class' => Institution::class,
        'required' => true
      ]);
    ;
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([]);
  }
}
