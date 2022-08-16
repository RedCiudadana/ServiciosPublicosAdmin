<?php

namespace App\Handler;

use App\Entity\Institution as EntityInstitution;
use App\Form\Institution\BaseType;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class Institution
{
  public function __construct(EntityManagerInterface $em, FormFactoryInterface $formFactory, FlashBagInterface $flashBag) {
    $this->em = $em;
    $this->formFactory = $formFactory;
    $this->flashBag = $flashBag;
  }

  public function processRowsAndCreate($data)
  {
    $resources = [];
    // Validate data has columns
    $columns = [
      'nombre' => 'name',
      'descripcion' => 'description',
      'direccion' => 'address',
      'horario' => 'schedule',
      'pagina_web' => 'webpage',
      'correo_electronico' => 'email',
      'facebook' => 'facebookURL',
      'twitter' => 'twitterURL',
      'tipo' => 'type'
    ];

    $propertyColumns = array_flip($columns);

    foreach ($data as $row) {
      // CLEAN DATA

      $trimAndEncodeFunction = function ($str) {
        return \ForceUTF8\Encoding::toUTF8(trim($str));
      };

      $row = array_combine(
        array_map($trimAndEncodeFunction, array_keys($row)),
        array_map($trimAndEncodeFunction, array_values($row))
      );

      foreach (array_keys($columns) as $column) {
        if (!isset($row[$column])) {
          throw new LogicException(sprintf('El archivo no cuenta con la columna "%s".', $column));
        }
      }

      // Search if exist already record
      $resource = $this->em->getRepository(EntityInstitution::class)->findOneBy([
        'name' => $row['nombre']
      ]);

      if (!$resource) {
        $resource = new EntityInstitution();
      }

      // Set data in Form
      $form = $this->formFactory->create(BaseType::class, $resource, [
        'csrf_protection' => false
      ]);

      // $row['facebook'] = null;

      $form->submit($this->transformDataToProperties($row, $columns), false);

      if ($form->isValid()) {
        $this->em->persist($form->getData());
        $resources[] = $form->getData();
      } else {
        /**
         * @var FormError
         */
        $errors = $form->getErrors(true);

        foreach ($errors as $error) {
          $message = $error->getMessage();
          $property = $error->getOrigin()->getName();
          $propertyName = $propertyColumns[$property] ?? $property;

          $this->flashBag->add('warning', sprintf('Error en %s: %s', $propertyName, $message));
        }

        throw new LogicException(sprintf('Error al procesar registro %s', $row['nombre']));
      }
    }

    $this->em->flush();

    return $resources;
  }

  private function transformDataToProperties(array $data, array $columns): array
  {
    $propierties = [];

    foreach ($columns as $key => $value) {
      $propierties[$value] = $data[$key];
    }

    return $propierties;
  }
}
