<table>
    <thead>
        <tr>
            <th>Fecha movimiento</th>
            <th>Producto</th>
            <th>Tipo movimiento</th>
            <th>Cantidad</th>
            <th>Stock</th>
            <th>Observación</th>
        </tr>
    </thead>
    <tbody>
        @foreach($movimientos as $mov)
            @php
                switch($mov->tipo_mov) {
                    case 'VENTA':
                    case 'VENTA (RECETA)':
                    case 'VENTA (PROMO)': $signo = " (-)"; break;
                    case 'ENTRADA':
                    case 'FACTURA COMPRA':
                    case 'BOLETA COMPRA': $signo = " (+)"; break;
                    default: $signo = "";
                }
                $obs = in_array($mov->tipo_mov, ['ENTRADA', 'SALIDA', 'MERMA']) ? $mov->obs : ($mov->tipo_mov . ' ' . $mov->num_doc);
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($mov->fecha)->format('d-m-Y H:i:s') }}</td>
                <td>{{ $mov->descripcion }}</td>
                <td>{{ $mov->tipo_mov . $signo }}</td>
                <td style="text-align:center">{{ $mov->cantidad }}</td>
                <td style="text-align:center">{{ $mov->stock }}</td>
                <td>{{ $obs }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
