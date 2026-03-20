<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductosPlantillaExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private readonly array $headers,
        private readonly array $categories,
        private readonly array $taxes,
    ) {
    }

    public function sheets(): array
    {
        $instructionRows = [
            ['PLANTILLA DE CARGA MASIVA DE PRODUCTOS'],
            [''],
            ['HOJA PRODUCTOS'],
            ['Complete un producto por fila, comenzando debajo del encabezado.'],
            ['No elimine ni modifique los nombres de las columnas.'],
            [''],
            ['REGLAS POR CAMPO'],
            ['codigo', 'Obligatorio, único y no puede existir en productos, recetas ni promociones.'],
            ['descripcion', 'Obligatoria, única y no puede existir en productos, recetas ni promociones.'],
            ['precio_compra_neto', 'Obligatorio. Debe ser numérico entero mayor o igual a 0.'],
            ['impuesto_1', 'Obligatorio. Puede escribir el nombre exacto del impuesto o su ID.'],
            ['impuesto_2', 'Opcional. Puede escribir el nombre exacto del impuesto, su ID o dejarlo vacío.'],
            ['precio_compra_bruto', 'Opcional. Si queda vacío, el sistema lo calcula desde precio_compra_neto e impuestos.'],
            ['precio_venta', 'Obligatorio. Debe ser numérico entero mayor a 0.'],
            ['stock_minimo', 'Opcional. Si no se informa, se guarda en 0.'],
            ['categoria', 'Obligatoria. Debe escribir el nombre exacto de la categoría; el sistema la convierte al ID.'],
            ['unidad_medida', 'Obligatoria. Acepta UN, L, KG, CJ o UNIDAD, LITRO, KILOGRAMO, CAJA.'],
            ['tipo', 'Obligatorio. Acepta P, S, I, PR o PRODUCTO, NO AFECTO A STOCK, INSUMO, PROMOCION.'],
            ['nom_foto', 'Opcional. Debe ser una ruta existente en el sistema, con este formato => /img/fotos_prod/nombre_foto.jpg.'],
            [''],
            ['CATEGORÍAS ACTIVAS'],
        ];

        foreach ($this->categories as $category) {
            $instructionRows[] = [$category];
        }

        $instructionRows[] = [''];
        $instructionRows[] = ['IMPUESTOS DISPONIBLES'];

        foreach ($this->taxes as $tax) {
            $instructionRows[] = [$tax];
        }

        return [
            new ProductosPlantillaSheet('Productos', [$this->headers]),
            new ProductosPlantillaSheet('Instrucciones', $instructionRows),
        ];
    }
}

class ProductosPlantillaSheet implements FromArray, WithTitle, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly string $title,
        private readonly array $rows,
    ) {
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [
            1 => ['font' => ['bold' => true]],
        ];

        if ($this->title === 'Productos') {
            $sheet->freezePane('A2');
            $sheet->setAutoFilter($sheet->calculateWorksheetDimension());
        }

        return $styles;
    }
}