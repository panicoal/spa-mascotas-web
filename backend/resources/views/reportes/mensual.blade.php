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
            font-size: 13px;
        }
        tr:nth-child(even) {
            background: #f5f5f5;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $titulo }}</h1>
        <p>{{ $mes }} de {{ $anio }}</p>
    </div>

    <div class="content">
        <!-- KPIs -->
        <div class="section">
            <h2>Indicadores Clave</h2>
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-label">Ventas Totales</div>
                    <div class="kpi-value">${{ number_format($ventas_totales, 2) }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Citas Completadas</div>
                    <div class="kpi-value">{{ $citas_completadas }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Transacciones</div>
                    <div class="kpi-value">{{ $transacciones->sum('cantidad') }}</div>
                </div>
            </div>
        </div>

        <!-- Transacciones por método -->
        <div class="section">
            <h2>Transacciones por Método de Pago</h2>
            <table>
                <thead>
                    <tr>
                        <th>Método de Pago</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transacciones as $t)
                        <tr>
                            <td>{{ $t->metodo_pago }}</td>
                            <td>{{ $t->cantidad }}</td>
                            <td>${{ number_format($t->total, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999;">No hay transacciones registradas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Productos bajo stock -->
        @if($productos_bajo_stock->count() > 0)
            <div class="section">
                <h2>⚠️ Productos con Stock Bajo</h2>
                <div class="alert">
                    Se encontraron {{ $productos_bajo_stock->count() }} productos con stock inferior al mínimo recomendado.
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Stock Actual</th>
                            <th>Stock Mínimo</th>
                            <th>Categoría</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($productos_bajo_stock as $p)
                            <tr>
                                <td>{{ $p->nombre }}</td>
                                <td>{{ $p->stock_actual }} {{ $p->unidad_medida ?? 'UNID' }}</td>
                                <td>{{ $p->stock_minimo }} {{ $p->unidad_medida ?? 'UNID' }}</td>
                                <td>{{ $p->categoria }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="footer">
        <p><strong>Reporte generado por:</strong> {{ $usuario }}</p>
        <p><strong>Fecha y hora:</strong> {{ $fecha_generacion }}</p>
        <p style="margin-top: 15px; font-style: italic;">Este documento es confidencial y está destinado solo para uso interno.</p>
    </div>
</body>
</html>
