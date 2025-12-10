<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Orden de Compra</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #0066cc;
            padding-bottom: 15px;
        }

        .header h1 {
            color: #0066cc;
            font-size: 28px;
            margin: 0 0 10px 0;
        }

        .header p {
            color: #666;
            font-size: 12px;
            margin: 3px 0;
        }

        .info-section {
            margin: 20px 0;
            background-color: #f5f5f5;
            padding: 15px;
            border-left: 4px solid #0066cc;
        }

        .info-section h3 {
            color: #0066cc;
            font-size: 14px;
            margin: 0 0 10px 0;
        }

        .info-row {
            margin: 8px 0;
        }

        .info-label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 120px;
        }

        .info-value {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            background-color: #0066cc;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
        }

        td {
            padding: 10px 8px;
            border-bottom: 1px solid #ddd;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center {
            text-align: center;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #888;
            font-size: 10px;
        }

        .footer p {
            margin: 5px 0;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>ORDEN DE COMPRA</h1>
        <p><strong>Número:</strong> OC-<?php echo str_pad($oc['id'], 6, '0', STR_PAD_LEFT); ?></p>
        <p><strong>Fecha de emisión:</strong> <?php echo date('d/m/Y'); ?></p>
    </div>

    <div class="info-section">
        <h3>Información del Proveedor</h3>
        <div class="info-row">
            <span class="info-label">Proveedor:</span>
            <span class="info-value"><?php echo htmlspecialchars($oc['prov'], ENT_QUOTES, 'UTF-8'); ?></span>
        </div>
    </div>

    <h3 style="color: #0066cc; margin-top: 30px; margin-bottom: 10px;">Detalle de la Orden</h3>
    <table>
        <thead>
            <tr>
                <th>Material</th>
                <th class="text-center">Cantidad</th>
                <th class="text-center">Unidad</th>
                <th class="text-center">Fecha Estimada de Entrega</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?php echo htmlspecialchars($oc['mat'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-center"><?php echo number_format($oc['cant'], 2, '.', ','); ?></td>
                <td class="text-center"><?php echo htmlspecialchars($oc['u'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="text-center"><?php echo date('d/m/Y', strtotime($oc['eta'])); ?></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Documento generado automáticamente por el Sistema MRP</strong></p>
        <p>Fecha y hora de generación: <?php echo date('d/m/Y H:i:s'); ?></p>
    </div>
</body>

</html>