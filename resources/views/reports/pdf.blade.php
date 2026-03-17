<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caso {{ $report->ip }} - Informe</title>
    <style>
        /* Márgenes de página */
        @page {
            margin: 60px 50px 80px 50px;
        }

        /* Reset y base */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #1f2937;
            background: #fff;
        }

        /* Cabecera del documento */
        .header {
            background-color: #166534;
            color: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 0 0 8px 8px;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 70%;
        }

        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 30%;
        }

        .header h1 {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 12px;
            opacity: 0.9;
        }

        .header .ip-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: bold;
        }

        /* Contenedor principal */
        .container {
            padding: 0 20px;
        }

        /* Secciones */
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
            padding-top: 5px;
        }

        .section-title {
            background: #f3f4f6;
            border-left: 4px solid #166534;
            padding: 10px 15px;
            font-size: 13px;
            font-weight: bold;
            color: #166534;
            margin-bottom: 12px;
            margin-top: 10px;
            border-radius: 0 4px 4px 0;
        }

        .section-content {
            padding: 0 10px;
        }

        /* Tablas de información */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .info-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .info-table .label {
            font-weight: 600;
            color: #111827;
            width: 35%;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .info-table .value {
            color: #1f2937;
        }

        /* Grid de 2 columnas */
        .two-columns {
            display: table;
            width: 100%;
            table-layout: fixed;
        }

        .two-columns .column {
            display: table-cell;
            width: 50%;
            padding-right: 15px;
            vertical-align: top;
        }

        .two-columns .column:last-child {
            padding-right: 0;
            padding-left: 15px;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-green {
            background: #dcfce7;
            color: #166534;
        }

        .badge-yellow {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-red {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-blue {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-gray {
            background: #f3f4f6;
            color: #374151;
        }

        /* Texto de antecedentes */
        .background-text {
            background: #fafafa;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            white-space: pre-line;
            text-align: justify;
        }

        /* Card para detalles */
        .detail-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            margin-bottom: 15px;
            overflow: hidden;
            page-break-inside: avoid;
        }

        .detail-card-header {
            background: #f9fafb;
            padding: 12px 15px;
            border-bottom: 1px solid #e5e7eb;
        }

        .detail-card-header h4 {
            font-size: 12px;
            color: #374151;
            margin: 0;
        }

        .detail-card-body {
            padding: 15px;
        }

        .detail-card-body table {
            width: 100%;
            border-collapse: collapse;
        }

        .detail-card-body table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f3f4f6;
        }

        .detail-card-body table tr:last-child td {
            border-bottom: none;
        }

        .field-name {
            color: #6b7280;
            font-size: 10px;
            width: 40%;
        }

        .field-value {
            color: #111827;
        }

        /* Alerta de especie protegida */
        .protected-alert {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            border: 1px solid #6ee7b7;
            border-left: 4px solid #059669;
            padding: 12px 15px;
            border-radius: 0 6px 6px 0;
            margin-bottom: 10px;
        }

        .protected-alert strong {
            color: #047857;
        }

        /* Tabla de costes */
        .cost-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .cost-table th {
            background: #166534;
            color: white;
            padding: 10px 12px;
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
        }

        .cost-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .cost-table tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        .cost-table .text-right {
            text-align: right;
        }

        .cost-table tfoot td {
            background: #f3f4f6;
            font-weight: bold;
        }

        /* Resumen de costes */
        .cost-summary {
            display: table;
            width: 100%;
            margin-top: 15px;
        }

        .cost-summary-item {
            display: table-cell;
            width: 25%;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
        }

        .cost-summary-item.vr {
            background: #dbeafe;
        }

        .cost-summary-item.ve {
            background: #dcfce7;
        }

        .cost-summary-item.vs {
            background: #fef9c3;
        }

        .cost-summary-item.total {
            background: #166534;
            color: white;
        }

        .cost-summary-item .label {
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            opacity: 0.8;
            margin-bottom: 5px;
        }

        .cost-summary-item .value {
            font-size: 16px;
            font-weight: bold;
        }

        .cost-summary-item.total .label {
            color: rgba(255,255,255,0.8);
        }

        /* Pie de página */
        .footer {
            position: fixed;
            bottom: -60px;
            left: 0px;
            right: 0px;
            padding: 10px 20px;
            font-size: 9px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
            background: white;
            height: 40px;
        }

        .footer-content {
            display: table;
            width: 100%;
        }

        .footer-left {
            display: table-cell;
            text-align: left;
        }

        .footer-right {
            display: table-cell;
            text-align: right;
        }

        /* Salto de página */
        .page-break {
            page-break-after: always;
        }

        /* No imprimir en vacío */
        .no-print-empty:empty {
            display: none;
        }
    </style>
</head>
<body>
    {{-- Cabecera --}}
    <div class="header">
        <div class="header-content">
            <div class="header-left">
                <h1 style="color: white;">INFORME DE VALORACIÓN DEL DAÑO</h1>
                <p class="subtitle" style="font-size: 16px; font-weight: bold; margin-top: 8px; color: white;">Caso {{ $report->ip }}</p>
                <p class="subtitle" style="margin-top: 5px; color: rgba(255,255,255,0.9);">{{ $report->category->name }} - {{ $report->subcategory->name }}</p>
            </div>
            <div class="header-right">
                <div class="ip-badge" style="color: white;">{{ $report->ip }}</div>
            </div>
        </div>
    </div>

    <div class="container">
        {{-- Información General --}}
        <div class="section">
            <div class="section-title">📋 Información General</div>
            <div class="section-content">
                <div class="two-columns">
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label">Título</td>
                                <td class="value">{{ $report->title }}</td>
                            </tr>
                            <tr>
                                <td class="label">Categoría</td>
                                <td class="value">{{ $report->category->name }}</td>
                            </tr>
                            <tr>
                                <td class="label">Subcategoría</td>
                                <td class="value">{{ $report->subcategory->name }}</td>
                            </tr>
                            <tr>
                                <td class="label">Creado por</td>
                                <td class="value">{{ $report->user->name }} ({{ $report->user->agent_num }})</td>
                            </tr>
                        </table>
                    </div>
                    <div class="column">
                        <table class="info-table">
                            <tr>
                                <td class="label">Estado</td>
                                <td class="value">
                                    <span class="badge badge-{{ $report->status === 'completado' ? 'green' : ($report->status === 'en_proceso' ? 'blue' : 'yellow') }}">
                                        {{ $report->getStatusLabel() }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Urgencia</td>
                                <td class="value">
                                    <span class="badge badge-{{ $report->urgency === 'urgente' ? 'red' : ($report->urgency === 'alta' ? 'yellow' : 'gray') }}">
                                        {{ $report->getUrgencyLabel() }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Peticionario</td>
                                <td class="value">
                                    {{ $report->petitioner->name }}
                                    @if($report->petitioner->name === 'Otro' && $report->petitioner_other)
                                        - {{ $report->petitioner_other }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="label">Asignado a</td>
                                <td class="value">
                                    @if($report->assigned && $report->assignedTo)
                                        {{ $report->assignedTo->name }} ({{ $report->assignedTo->agent_num }})
                                    @else
                                        <span style="color: #9ca3af;">Sin asignar</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ubicación --}}
        <div class="section">
            <div class="section-title">📍 Ubicación</div>
            <div class="section-content">
                <table class="info-table">
                    <tr>
                        <td class="label" style="width: 20%;">Comunidad Autónoma</td>
                        <td class="value" style="width: 30%;">{{ $report->community }}</td>
                        <td class="label" style="width: 20%;">Provincia</td>
                        <td class="value" style="width: 30%;">{{ $report->province }}</td>
                    </tr>
                    <tr>
                        <td class="label">Localidad</td>
                        <td class="value">{{ $report->locality }}</td>
                        <td class="label">Coordenadas GPS</td>
                        <td class="value">{{ $report->coordinates ?? 'No especificadas' }}</td>
                    </tr>
                </table>

                @if($protectedAreas && $protectedAreas->isNotEmpty())
                    <div class="protected-alert">
                        <strong>🌿 UBICACIÓN EN ÁREA PROTEGIDA</strong><br>
                        @foreach($protectedAreas as $area)
                            <span>{{ $area->name }} ({{ $area->protection_type }})</span>
                            @if(!$loop->last), @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Fechas --}}
        <div class="section">
            <div class="section-title">📅 Fechas</div>
            <div class="section-content">
                <table class="info-table">
                    <tr>
                        <td class="label" style="width: 20%;">Fecha de Petición</td>
                        <td class="value" style="width: 30%;">{{ \Carbon\Carbon::parse($report->date_petition)->format('d/m/Y') }}</td>
                        <td class="label" style="width: 20%;">Fecha del Daño</td>
                        <td class="value" style="width: 30%;">{{ \Carbon\Carbon::parse($report->date_damage)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label">Fecha de Creación</td>
                        <td class="value">{{ $report->created_at->format('d/m/Y H:i') }}</td>
                        <td class="label">Última Actualización</td>
                        <td class="value">{{ $report->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Antecedentes --}}
        <div class="section">
            <div class="section-title">📄 Antecedentes</div>
            <div class="section-content">
                <div class="background-text">{{ $report->background }}</div>
            </div>
        </div>

        {{-- Información Administrativa --}}
        @if($report->office || $report->diligency)
        <div class="section">
            <div class="section-title">🏛️ Información Administrativa</div>
            <div class="section-content">
                <table class="info-table">
                    @if($report->office)
                    <tr>
                        <td class="label" style="width: 20%;">Despacho/Oficina</td>
                        <td class="value">{{ $report->office }}</td>
                    </tr>
                    @endif
                    @if($report->diligency)
                    <tr>
                        <td class="label" style="width: 20%;">Diligencias</td>
                        <td class="value">{{ $report->diligency }}</td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
        @endif

        {{-- Detalles del Caso --}}
        @if($groupedDetails && $groupedDetails->count() > 0)
        <div class="section">
            <div class="section-title">📋 Detalles del Caso ({{ $groupedDetails->count() }} grupo{{ $groupedDetails->count() > 1 ? 's' : '' }})</div>
            <div class="section-content">
                @foreach($groupedDetails as $groupKey => $details)
                    @php
                        $speciesDetail = $details->first(fn($d) => $d->species_id !== null);
                        $species = $speciesDetail?->species;
                        $areaDetail = $details->first(fn($d) => $d->protected_area_id !== null);
                        $protectedArea = $areaDetail?->protectedArea;
                    @endphp
                    
                    <div class="detail-card">
                        <div class="detail-card-header">
                            <h4>{{ ucfirst(str_replace('_', ' ', $groupKey)) }}</h4>
                        </div>
                        <div class="detail-card-body">
                            @if($species)
                                <div class="protected-alert" style="margin-bottom: 10px;">
                                    <strong>🐾 Especie:</strong> {{ $species->common_name ?? $species->scientific_name }}
                                    @if($species->is_protected)
                                        <span class="badge badge-red" style="margin-left: 5px;">PROTEGIDA</span>
                                    @endif
                                    @if($species->scientific_name)
                                        <br><small style="color: #6b7280; font-style: italic;">{{ $species->scientific_name }}</small>
                                    @endif
                                </div>
                            @endif

                            @if($protectedArea)
                                <div class="protected-alert" style="margin-bottom: 10px;">
                                    <strong>🌿 Área Protegida:</strong> {{ $protectedArea->name }}
                                    <span class="badge badge-green" style="margin-left: 5px;">{{ $protectedArea->protection_type }}</span>
                                </div>
                            @endif

                            <table>
                                @foreach($details as $detail)
                                    @php
                                        $field = $detail->field;
                                        $fieldLabel = $field?->display_name ?? ucfirst(str_replace('_', ' ', $detail->field_key));
                                    @endphp
                                    <tr>
                                        <td class="field-name">{{ $fieldLabel }}</td>
                                        <td class="field-value">
                                            @if($field && $field->type === 'boolean')
                                                {{ $detail->value ? 'Sí' : 'No' }}
                                            @elseif($field && $field->type === 'number')
                                                {{ number_format((float)$detail->value, 2, ',', '.') }}
                                            @else
                                                {{ $detail->value ?? '-' }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Costes --}}
        @if($costItems && $costItems->count() > 0)
        <div class="section">
            <div class="section-title">💰 Valoración Económica</div>
            <div class="section-content">
                {{-- Resumen de Costes --}}
                <table style="width: 100%; border-collapse: separate; border-spacing: 10px 0; margin-bottom: 20px;">
                    <tr>
                        <td class="cost-summary-item vr" style="border-radius: 8px;">
                            <div class="label">Valor de Reposición (VR)</div>
                            <div class="value" style="color: #1e40af;">{{ number_format($report->vr_total ?? 0, 2, ',', '.') }} €</div>
                        </td>
                        <td class="cost-summary-item ve" style="border-radius: 8px;">
                            <div class="label">Valor Extraido (VE)</div>
                            <div class="value" style="color: #166534;">{{ number_format($report->ve_total ?? 0, 2, ',', '.') }} €</div>
                        </td>
                        <td class="cost-summary-item vs" style="border-radius: 8px;">
                            <div class="label">Valor Ecosistémico (VS)</div>
                            <div class="value" style="color: #854d0e;">{{ number_format($report->vs_total ?? 0, 2, ',', '.') }} €</div>
                        </td>
                        <td class="cost-summary-item total" style="border-radius: 8px;">
                            <div class="label">COSTE TOTAL</div>
                            <div class="value">{{ number_format($report->total_cost ?? 0, 2, ',', '.') }} €</div>
                        </td>
                    </tr>
                </table>

                {{-- Tabla detallada de costes agrupada por grupo --}}
                <table class="cost-table">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Grupo</th>
                            <th style="width: 15%;">Tipo</th>
                            <th style="width: 25%;">Concepto</th>
                            <th style="width: 12%;" class="text-right">Base</th>
                            <th style="width: 8%;" class="text-right">CR</th>
                            <th style="width: 8%;" class="text-right">IG</th>
                            <th style="width: 12%;" class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($costItems->groupBy('group_key') as $groupKey => $items)
                            @php $isFirstInGroup = true; @endphp
                            @foreach($items as $item)
                            <tr>
                                @if($isFirstInGroup)
                                <td rowspan="{{ $items->count() }}" style="vertical-align: middle; font-weight: bold; background: #f9fafb;">
                                    {{ ucfirst(str_replace('_', ' ', $groupKey)) }}
                                </td>
                                @php $isFirstInGroup = false; @endphp
                                @endif
                                <td>
                                    <span class="badge badge-{{ $item->cost_type === 'VR' ? 'blue' : ($item->cost_type === 'VE' ? 'green' : 'yellow') }}">
                                        {{ $item->cost_type }}
                                    </span>
                                </td>
                                <td>{{ $item->concept_name }}</td>
                                <td class="text-right">{{ number_format($item->base_value, 2, ',', '.') }} €</td>
                                <td class="text-right">{{ number_format($item->cr_value, 4) }}</td>
                                <td class="text-right">{{ number_format($item->gi_value, 4) }}</td>
                                <td class="text-right"><strong>{{ number_format($item->total_cost, 2, ',', '.') }} €</strong></td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="6" class="text-right"><strong>TOTAL GENERAL:</strong></td>
                            <td class="text-right"><strong>{{ number_format($report->total_cost ?? 0, 2, ',', '.') }} €</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @elseif($report->total_cost)
        <div class="section">
            <div class="section-title">💰 Valoración Económica</div>
            <div class="section-content">
                <table style="width: 100%; border-collapse: separate; border-spacing: 10px 0;">
                    <tr>
                        @if($report->vr_total)
                        <td class="cost-summary-item vr" style="border-radius: 8px;">
                            <div class="label">Valor de Reposición (VR)</div>
                            <div class="value" style="color: #1e40af;">{{ number_format($report->vr_total, 2, ',', '.') }} €</div>
                        </td>
                        @endif
                        @if($report->ve_total)
                        <td class="cost-summary-item ve" style="border-radius: 8px;">
                            <div class="label">Valor Extraido (VE)</div>
                            <div class="value" style="color: #166534;">{{ number_format($report->ve_total, 2, ',', '.') }} €</div>
                        </td>
                        @endif
                        @if($report->vs_total)
                        <td class="cost-summary-item vs" style="border-radius: 8px;">
                            <div class="label">Valor Ecosistémico (VS)</div>
                            <div class="value" style="color: #854d0e;">{{ number_format($report->vs_total, 2, ',', '.') }} €</div>
                        </td>
                        @endif
                        <td class="cost-summary-item total" style="border-radius: 8px;">
                            <div class="label">COSTE TOTAL</div>
                            <div class="value">{{ number_format($report->total_cost, 2, ',', '.') }} €</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- Pie de página --}}
    <div class="footer">
        <div class="footer-content">
            <div class="footer-left">
                Generado el {{ now()->format('d/m/Y H:i') }} | Sistema de Valoración Ambiental
            </div>
            <div class="footer-right">
                Caso {{ $report->ip }}
            </div>
        </div>
    </div>
</body>
</html>
