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
        .total-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        .total-label {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 8px;
        }
        .total-value {
            font-size: 36px;
            font-weight: bold;
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
        .info-box {
            background: #e7f5ff;
            border-left: 4px solid #0066cc;
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
        <p>{{ $fecha_formateada }}</p>
    </div>

    <div class="content">
        <!-- Total general -->
        <div class="section">
            <h2>Resumen de Cierre</h2>
            <div class="total-box">
                <div class="total-label">Total Neto del Día</div>
                <div class="total-value">${{ number_format($total_neto, 2) }}</div>
            </div>
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-label">Total Transacciones</div>
                    <div class="kpi-value">{{ $total_transacciones }}</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Métodos Registrados</div>
                    <div class="kpi-value">{{ $pagos_por_tipo->count() }}</div>
                </div>
            </div>
        </div>

        <!-- Detalle por método -->
        <div class="section">
            <h2>Detalle por Método de Pago</h2>
            <table>
                <thead>
                    <tr>
                        <th>Método de Pago</th>
                        <th>Transacciones</th>
                        <th>Monto Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagos_por_tipo as $metodo => $datos)
                        <tr>
                            <td>{{ $metodo }}</td>
                            <td>{{ $datos['transacciones'] }}</td>
                            <td>${{ number_format($datos['total_monto'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align: center; color: #999;">No hay transacciones registradas</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Información del cierre -->
        <div class="section">
            <h2>Información del Cierre</h2>
            <div class="info-box">
                <strong>Responsable del Cierre:</strong> {{ $usuario }}<br>
                <strong>Fecha de Generación:</strong> {{ $fecha_generacion }}<br>
                <strong>Período:</strong> {{ $fecha }}
            </div>
        </div>
    </div>

    <div class="footer">
        <p><strong>Este documento es confidencial y está destinado solo para uso interno.</strong></p>
        <p>Generado automáticamente por el sistema Pet Spa</p>
        <p style="margin-top: 15px; font-size: 11px;">Fecha y hora de generación: {{ $fecha_generacion }}</p>
    </div>
</body>
</html>
