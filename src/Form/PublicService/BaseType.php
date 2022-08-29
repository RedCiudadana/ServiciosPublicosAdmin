<?php

namespace App\Form\PublicService;

use App\Config\Roles;
use App\Entity\Currency;
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
use Trsteel\CkeditorBundle\Form\Type\CkeditorType;

class BaseType extends AbstractType
{
    public function __construct(TokenStorageInterface $session) {
        $this->session = $session;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $this->session->getToken()->getUser();

        $builder
            ->add('name',null,[
                'label' => 'Nombre: Ingresa el nombre del trámite, recuerda colocar un nombre llamativo, amigable, breve y fácil de recordar.',
            ])
            ->add('description',null,[
                'label' => 'Descripción: Ingresa la descripción del trámite, recuerda colocar la mayor cantidad de detalles posibles.',
            ])
            ->add('institution', EntityType::class, [
                'label' => 'Institución: Selecciona la institución o las instituciones donde se realiza el trámite.',
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
                'label' => 'Subcategoría: Selecciona una de las siguientes subcategorías para encontrar con más facilidad el trámite que buscas.',
                'class' => SubCategory::class,
                'choice_label' => 'getNameAndCategory'
            ])
            ->add('instructions', CkeditorType::class,[
                'label' => 'Pasos: Enumera los pasos para realizar el trámite, recuerda colocar los detalles de cada paso para concluir con éxito tu trámite.',
            ])
            ->add('requirements',null,[
                'label' => 'Requisitos: Coloca todos los requisitos necesarios para realizar el trámite, recuerda colocar el mayor detalle posible.',
            ])
            ->add('cost', NumberType::class,[
                'label' => 'Costo: Indica el costo total del trámite. Si el trámite es gratuito coloca 0.',
            ])
            ->add('currency', EntityType::class, [
                'label' => 'Moneda',
                'class' => Currency::class,
                'choice_label' => 'getCodeAndSymbol'
            ])
            ->add('timeResponse',null,[
                'label' => 'Respuesta de tiempo: Ingresa el tiempo correspondiente de cada trámite hasta estar 100% resuelto. Expresa la temporalidad en días según corresponda.',
            ])
            ->add('typeOfDocumentObtainable',null,[
                'label' => 'Tipo de documento obtenible: Indica el tipo de documento que los ciudadanos obtendrán al finalizar este trámite.',
            ])
            ->add('normative',null,[
                'label' => 'Normativa: Ingresa el marco normativo, ley o reglamento que respalde el trámite correspondiente ',
            ])
            ->add('url',null,[
                'label' => 'URL: Ingrese el enlace al trámite o plataforma según corresponda.',
            ])
            ->add('highlight', null, [
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PublicService::class,
        ]);
    }
}
