<?php

namespace App\Handler;

use App\Entity\Category as EntityCategory;
use App\Entity\SubCategory;
use App\Form\Category\BaseType;
use App\Form\SubCategory\BaseType as SubCategoryBaseType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\UnitOfWork;
use LogicException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class Category
{
  /**
   * @var EntityManagerInterface
   */
  private $em;

  /**
   * @var FormFactoryInterface
   */
  private $formFactory;

  /**
   * @var FlashBagInterface
   */
  private $flashBag;

  public function __construct(EntityManagerInterface $em, FormFactoryInterface $formFactory, FlashBagInterface $flashBag) {
    $this->em = $em;
    $this->formFactory = $formFactory;
    $this->flashBag = $flashBag;
  }

  public function processRowsAndCreate($data)
  {
    // In the case they copy from the public services csv.
    $data = array_unique($data, SORT_REGULAR);

    $resources = [];
    $subResources = [];

    // Validate data has columns
    $columns = [
      'nombre' => 'name',
      'descripcion' => 'description'
    ];

    $subcategoryColumns = [
      'subcategoria_nombre' => 'name',
      'subcategoria_descripcion' => 'description'
    ];

    $propertyColumns = array_flip($columns);

    foreach ($data as $row) {
      // CLEAN DATA

      $trimAndEncodeFunction = function ($str) {
        return trim($str);
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

      /* Process Category */

      $resourceData = $this->transformDataToProperties($row, $columns);

      $resource = null;

      // Check if already exists in cache
      if (isset($resources[$resourceData['name']])) {
        $resource = $resources[$resourceData['name']];
      }

      // Search if exist already in the database
      if (!$resource) {
        $resource = $this->em->getRepository(EntityCategory::class)->findOneBy([
          'name' => $resourceData['name']
        ]);
      }

      if (!$resource) {
        $resource = new EntityCategory();
      }

      // Set data in Form
      $form = $this->formFactory->create(BaseType::class, $resource, [
        'csrf_protection' => false
      ]);

      $form->submit($resourceData, false);

      if ($form->isValid()) {
        if (
          ($this->em->getUnitOfWork()->getEntityState($resource) == UnitOfWork::STATE_NEW // its new
          && !$this->em->getUnitOfWork()->isScheduledForInsert($resource) // it's no already persisted
          )
          || $this->em->getUnitOfWork()->getEntityChangeSet($resource) != []) // has changes
        {
          $this->em->persist($form->getData());
        }

        $resources[$resourceData['name']] = $form->getData();
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

        throw new LogicException(sprintf('Error al procesar registro %s', $resourceData['name']));
      }

      /* Process SubCategory */

      $subResourceData = $this->transformDataToProperties($row, $subcategoryColumns);

      $subResource = null;

      // Check if already exists in cache
      if (isset($subResources[$subResourceData['name']])) {
        $subResource = $subResources[$subResourceData['name']];
      }

      // Search if exist already in the database
      if (!$subResource) {
        $subResource = $this->em->getRepository(SubCategory::class)->findOneBy([
          'name' => $subResourceData['name']
        ]);
      }

      if (!$subResource) {
        $subResource = new SubCategory();
      }

      $subResource->setCategory($resource);

      // Set data in Form
      $form = $this->formFactory->create(SubCategoryBaseType::class, $subResource, [
        'csrf_protection' => false
      ]);

      $form->submit($subResourceData, false);

      if ($form->isValid()) {
        if (
          $this->em->getUnitOfWork()->getEntityState($subResource) == UnitOfWork::STATE_NEW // its new
          || $this->em->getUnitOfWork()->getEntityChangeSet($subResource) != []
        ) // has changes
        {
          $this->em->persist($subResource);
        }

        $subResources[$subResourceData['name']] = $subResource;
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

        throw new LogicException(sprintf('Error al procesar registro %s', $subResourceData['name']));
      }
    }

    $this->em->flush();

    return array_merge($resources, $subResources);
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
