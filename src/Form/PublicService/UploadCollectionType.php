<?php

namespace App\Form\PublicService;

use App\Entity\PublicService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UploadCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class)
            ->add('csv_delimiter', ChoiceType::class, [
                'label' => 'Delimitador',
                'choices' => [
                    ',' => ',',
                    ';' => ';'
                ]
            ]);
        ;
    }
}
