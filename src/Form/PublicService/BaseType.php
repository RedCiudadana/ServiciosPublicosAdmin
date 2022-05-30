<?php

namespace App\Form\PublicService;

use App\Config\Roles;
use App\Entity\Institution;
use App\Entity\PublicService;
use App\Entity\SubCategory;
use App\Repository\InstitutionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BaseType extends AbstractType
{
    public function __construct(TokenStorageInterface $session) {
        $this->session = $session;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->session->getToken()->getUser();

        $builder
            ->add('name')
            ->add('description')
            ->add('institution', EntityType::class, [
                'class' => Institution::class,
                'query_builder' => function(InstitutionRepository $er) use ($user) {
                    $qb = $er->createQueryBuilder('i');

                    if ($user->isAdmin()) {
                        return $qb;
                    }

                    $qb->innerJoin('i.members', 'm')
                        ->where('m = :user')
                        ->setParameter('user', $user);

                    return $qb;
                }
            ])
            ->add('subcategory', EntityType::class, [
                'class' => SubCategory::class,
                'choice_label' => 'getNameAndCategory'
            ])
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
