<?php

namespace App\Handler;

class PublicServiceCollectionLoader
{
  public function createEntity($data)
  {
  }

  private function requiredValues()
  {
    return [
      'Institución',
      'Dirección / Unidad Ejecutora / Departamento',
      'Trámite',
      'Descripción',
      'Pasos',
      'Requisitos',
      'Costo',
      'Tiempo de respuesta',
      'Categoría',
      'Subcategoría'
    ];
  }
}
