<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Menú QR</title>
    <style>
        @page {
            size: letter portrait;
            margin: 10mm;
        }
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #222;
        }

        .page {
            page-break-after: always;
        }

        .page:last-child {
            page-break-after: auto;
        }

        .grid {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .grid td {
            width: 50%;
            vertical-align: top;
            padding: 4mm;
        }

        .slot {
            border: 1px solid #ddd;
            border-radius: 12px;
            padding: 8px;
            text-align: center;
            height: 84mm;
            overflow: hidden;
        }

        .brand {
            margin-bottom: 4px;
        }

        .brand h1 {
            margin: 0;
            font-size: 13px;
            line-height: 1.2;
        }

        .brand p {
            margin: 3px 0 0;
            font-size: 9px;
            color: #666;
            line-height: 1.3;
        }

        .brand .menu-name {
            margin-top: 4px;
            font-size: 10px;
            color: #1f2937;
            font-weight: 700;
        }

        .qr {
            width: 56mm;
            height: 56mm;
            object-fit: contain;
            margin: 3px auto 0;
            display: block;
        }

        .empty {
            border: 1px dashed transparent;
            height: 84mm;
        }

        .row-fixed {
            height: 90mm;
        }
    </style>
</head>
<body>
    @php
        $indices = range(1, $copias);
        $bloques = array_chunk($indices, 4);
        $menuNombre = trim((string) ($configuracion->nombre ?? ''));
        $menuNombre = $menuNombre !== '' ? $menuNombre : 'Menú QR';
    @endphp

    @foreach($bloques as $bloque)
        <div class="page">
            <table class="grid">
                <tr class="row-fixed">
                    @for($i = 0; $i < 2; $i++)
                        <td>
                            @if(isset($bloque[$i]))
                                <div class="slot">
                                    <div class="brand">
                                        <h1>{{ $corporateData['fantasy_name_enterprise'] ?? ($corporateData['name_enterprise'] ?? 'Menú QR') }}</h1>
                                        <p>{{ trim(($corporateData['address_enterprise'] ?? '') . ' ' . ($corporateData['comuna_enterprise'] ?? '')) }}</p>
                                        <p class="menu-name">{{ $menuNombre }}</p>
                                    </div>
                                    <img class="qr" src="{{ $qrDataUri }}" alt="QR menú">
                                </div>
                            @else
                                <div class="empty"></div>
                            @endif
                        </td>
                    @endfor
                </tr>
                <tr class="row-fixed">
                    @for($i = 2; $i < 4; $i++)
                        <td>
                            @if(isset($bloque[$i]))
                                <div class="slot">
                                    <div class="brand">
                                        <h1>{{ $corporateData['fantasy_name_enterprise'] ?? ($corporateData['name_enterprise'] ?? 'Menú QR') }}</h1>
                                        <p>{{ trim(($corporateData['address_enterprise'] ?? '') . ' ' . ($corporateData['comuna_enterprise'] ?? '')) }}</p>
                                        <p class="menu-name">{{ $menuNombre }}</p>
                                    </div>
                                    <img class="qr" src="{{ $qrDataUri }}" alt="QR menú">
                                </div>
                            @else
                                <div class="empty"></div>
                            @endif
                        </td>
                    @endfor
                </tr>
            </table>
        </div>
    @endforeach
</body>
</html>