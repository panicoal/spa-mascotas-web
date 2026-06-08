<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $titulo }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            color: #333;
            line-height: 1.6;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 20px;
        }
        .section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            background: #f9f9f9;
        }
        .section h2 {
            color: #667eea;
            font-size: 18px;
            margin-bottom: 15px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .kpi-row {
            display: flex;
            justify-content: space-around;
            margin: 15px 0;
            flex-wrap: wrap;
        }
        .kpi-card {
            background: white;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            flex: 1;
            min-width: 200px;
            margin: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .kpi-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .kpi-value {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 13px;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
            font-size: 12px;
        }
        tr:nth-child(even) {
            background: #f5f5f5;
        }
        tr.bajo-stock {
            background: #fff3cd;
        }
        .footer {
            margin-top: 40px;
            padding: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #999;
            text-align: center;
        }
        .footer p {
            margin: 5px 0;
        }
        .alert {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 13px;
        }
        .info-box {
            background: #e7f5ff;
            border-left: 4px solid #0066cc;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 13px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            color: white;
        }
        .badge.warning {
            background: #ffc107;
            color: #333;
        }
        .badge.success {
            background: #28a745;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>{{ $tipo }}</p>
    </div>

    <div class="content">
        <!-- KPIs -->
        <div class="section">
            <h2>Resumen del Inventario</h2>
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-label">Total de Productos</div>
                    <div class="kpi-value">{{ $total_productos }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Valor Total</div>
                    <div class="kpi-value">${{ number_format($total_valor, 2) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Productos en Alerta</div>
                    <div class="kpi-value" style="color: #ff6b6b;">{{ $bajo_stock_count }}</div>
                </div>
            </div>
        </div>

        <!-- Información general -->
        <div class="section">
            <h2>Información del Reporte</h2>
            <div class="info-box">
                <strong>Generado por:</strong> {{ $usuario }}<br>
                <strong>Fecha y hora:</strong> {{ $fecha_generacion }}<br>
                <strong>Tipo de reporte:</strong> {{ $tipo }}
            </div>
        </div>

        <!-- Listado de productos -->
        <div class="section">
            <h2>Detalle de Productos</h2>
            @if($productos->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Unidad</th>
                            <th>Precio Venta</th>
                            <th>Valor Total</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos as $p)
                            @php
                                $bajo_stock = $p->stock_actual <= $p->stock_minimo;
                                $valor_total = $p->stock_actual * $p->precio_venta;
                            @endphp
                            <tr @if($bajo_stock) class="bajo-stock" @endif>
                                <td><strong>{{ $p->nombre }}</strong></td>
                                <td>{{ $p->categoria ?? '-' }}</td>
                                <td>{{ $p->stock_actual }}</td>
                                <td>{{ $p->stock_minimo ?? '-' }}</td>
                                <td>{{ $p->unidad_medida ?? 'UNID' }}</td>
                                <td>${{ number_format($p->precio_venta, 2) }}</td>
                                <td>${{ number_format($valor_total, 2) }}</td>
                                <td>
                                    @if($bajo_stock)
                                        <span class="badge warning">⚠️ BAJO STOCK</span>
                                    @else
                                        <span class="badge success">OK</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p style="text-align: center; color: #999; padding: 20px;">No hay productos para mostrar</p>
            @endif
        </div>

        <!-- Leyenda -->
        <div class="section">
            <h2>Leyenda</h2>
            <div class="alert">
                <strong>⚠️ BAJO STOCK:</strong> Productos cuyo stock actual es menor o igual al stock mínimo recomendado. Se recomienda realizar pedidos de reabastecimiento.
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Este documento es confidencial y está destinado solo para uso interno.</strong></p>
        <p>Generado automáticamente por el sistema Pet Spa</p>
    </div>
</body>
</html>
