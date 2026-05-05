<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Etiquetas de Códigos de Barra</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 10px; }

        table.grilla {
            width: 100%;
            border-collapse: collapse;
        }

        td.etiqueta {
            width: 33.33%;
            text-align: center;
            vertical-align: middle;
            padding: 6px 4px;
            border: 1px dashed #bbb;
            height: 85px;
        }

        .barcode-svg {
            display: block;
            text-align: center;
            overflow: hidden;
        }

        .barcode-svg svg {
            max-width: 96%;
        }

        .prod-nombre {
            font-size: 8.5px;
            font-weight: bold;
            margin-top: 3px;
            line-height: 1.3;
        }

        .prod-codigo {
            font-size: 8px;
            color: #444;
            margin-top: 1px;
        }
    </style>
</head>
<body>
    <table class="grilla">
        @foreach(array_chunk($items, 3) as $fila)
        <tr>
            @foreach($fila as $item)
            <td class="etiqueta">
                <div class="barcode-svg"><img src="data:image/svg+xml;base64,{{ base64_encode($item['barcode_svg']) }}" style="display:block;margin:0 auto;max-width:100%;height:auto;" alt="barcode"></div>
                <div class="prod-nombre">{{ \Illuminate\Support\Str::limit($item['descripcion'], 30) }}</div>
                <div class="prod-codigo">{{ $item['codigo'] }}</div>
            </td>
            @endforeach
            @for($i = count($fila); $i < 3; $i++)
            <td class="etiqueta"></td>
            @endfor
        </tr>
        @endforeach
    </table>
</body>
</html>
