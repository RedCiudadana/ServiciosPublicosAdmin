<?php

namespace App\Form\PublicService;

use App\Entity\Institution;
use App\Entity\PublicService;
use App\Repository\InstitutionRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DownloadReportType extends AbstractType
{
    public function __construct(TokenStorageInterface $session)
    {
        $this->session = $session;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->session->getToken()->getUser();

        $builder
            ->add('institution', EntityType::class, [
                'label' => 'Institución',
                'class' => Institution::class,
                'required' => !$user->isAdmin(),
                'help' => 'Vacío para generar reporte de todos los trámites',
                'query_builder' => function (InstitutionRepository $er) use ($user) {
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
        ;
    }
}
