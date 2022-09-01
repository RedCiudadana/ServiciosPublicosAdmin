<?php

namespace App\Reporter;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicServiceReporter {
    public function getReport($data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'id',
            'institución',
            'nombre',
            'categoria',
            'subcategoria',
            'descripcion',
            'instrucciones',
            'requisitos',
            'costo',
            'codigo_moneda',
            'tiempo_de_respuesta',
            'documento_obtenible',
            'enlace',
            'respaldo_legal',
            'fecha_actualizado'
        ];

        $rows = array_merge([$headers], $data);

        $sheet
            ->fromArray(
                $rows
            );

        $writer = new Xlsx($spreadsheet);

        $response = new StreamedResponse(function() use ($writer) {
                $writer->save('php://output');
            },
            200,
            []
        );

        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');

        $response->headers->set(
            'Content-Disposition',
            sprintf(
                'attachment;filename=%s_%s.xlsx',
                'tramites',
                (new \DateTime())->format('Y-m-d')
            )
        );

        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}