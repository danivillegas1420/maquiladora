<?php

namespace App\Controllers;

class Modals extends BaseController
{
    /**
     * JSON para detalle de pedido usado en modales del Módulo 1.
     */
    public function pedido_json($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }

        try {
            $pedidoModel = new \App\Models\PedidoModel();
            $detalle = $pedidoModel->getPedidoDetalle($id);

            if (!$detalle) {
                $basic = $pedidoModel->getPedidoPorId($id);
                if (!$basic) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'Pedido no encontrado']);
                }
                $detalle = [
                    'id' => (int) ($basic['id'] ?? $id),
                    'folio' => $basic['folio'] ?? '',
                    'fecha' => $basic['fecha'] ?? null,
                    'estatus' => $basic['estatus'] ?? '',
                    'moneda' => $basic['moneda'] ?? '',
                    'total' => $basic['total'] ?? 0,
                    'cliente' => [
                        'nombre' => $basic['empresa'] ?? '',
                    ],
                    'items' => [],
                ];
            }

            if (empty($detalle['diseno'])) {
                $db = \Config\Database::connect();
                $row = null;
                try {
                    $row = $db->query(
                        "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion
                         FROM orden_produccion op
                         LEFT JOIN diseno_version dv ON dv.id = op.disenoVersionId
                         LEFT JOIN diseno d ON d.id = dv.disenoId
                         WHERE op.ordenCompraId = ?
                         ORDER BY op.id DESC
                         LIMIT 1",
                        [$id]
                    )->getRowArray();
                } catch (\Throwable $e) {
                    $row = null;
                }
                if (!$row) {
                    try {
                        $row = $db->query(
                            "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion
                             FROM OrdenProduccion op
                             LEFT JOIN DisenoVersion dv ON dv.id = op.disenoVersionId
                             LEFT JOIN Diseno d ON d.id = dv.disenoId
                             WHERE op.ordenCompraId = ?
                             ORDER BY op.id DESC
                             LIMIT 1",
                            [$id]
                        )->getRowArray();
                    } catch (\Throwable $e2) {
                        $row = null;
                    }
                }
                if ($row && isset($row['d_id'])) {
                    $detalle['diseno'] = [
                        'id' => $row['d_id'],
                        'codigo' => $row['d_codigo'] ?? '',
                        'nombre' => $row['d_nombre'] ?? '',
                        'descripcion' => $row['d_descripcion'] ?? '',
                        'version' => [
                            'id' => $row['id'] ?? null,
                            'version' => $row['version'] ?? null,
                            'fecha' => $row['fecha'] ?? null,
                            'aprobado' => $row['aprobado'] ?? null,
                            'notas' => $row['notas'] ?? null,
                            'archivoCadUrl' => $row['archivoCadUrl'] ?? null,
                            'archivoPatronUrl' => $row['archivoPatronUrl'] ?? null,
                        ],
                        'archivoCadUrl' => $row['archivoCadUrl'] ?? null,
                        'archivoPatronUrl' => $row['archivoPatronUrl'] ?? null,
                    ];
                }
            }

            // Obtener las tallas de la orden de producción si existe
            $tallas = [];
            if (!empty($detalle['op_id'])) {
                $pedidoModel = new \App\Models\PedidoModel();
                $tallas = $pedidoModel->getTallasPorOP($detalle['op_id']);
                log_message('debug', 'Tallas cargadas para OP ' . $detalle['op_id'] . ': ' . json_encode($tallas));
            }

            $out = [
                'id' => (int) ($detalle['id'] ?? $id),
                'folio' => $detalle['folio'] ?? '',
                'fecha' => isset($detalle['fecha']) ? date('Y-m-d', strtotime($detalle['fecha'])) : '',
                'estatus' => $detalle['estatus'] ?? '',
                'moneda' => $detalle['moneda'] ?? '',
                'total' => isset($detalle['total']) ? number_format((float) $detalle['total'], 2) : '0.00',
                'empresa' => $detalle['cliente']['nombre'] ?? ($detalle['empresa'] ?? ''),
                'cliente' => $detalle['cliente'] ?? null,
                'items' => $detalle['items'] ?? [],
                'diseno' => $detalle['diseno'] ?? null,
                'disenos' => $detalle['disenos'] ?? [],
                'documento_url' => $detalle['documento_url'] ?? '',
                'op_id' => $detalle['op_id'] ?? null,
                'op_folio' => $detalle['op_folio'] ?? null,
                'op_disenoVersionId' => $detalle['op_disenoVersionId'] ?? null,
                'op_cantidadPlan' => $detalle['op_cantidadPlan'] ?? null,
                'op_cantidadBultos' => $detalle['op_cantidadBultos'] ?? null,
                'op_fechaInicioPlan' => $detalle['op_fechaInicioPlan'] ?? null,
                'op_fechaFinPlan' => $detalle['op_fechaFinPlan'] ?? null,
                'op_status' => $detalle['op_status'] ?? null,
                'tallas' => $tallas,
            ];

            if (isset($out['diseno']) && is_array($out['diseno'])) {
                if (isset($out['diseno']['foto']) && !empty($out['diseno']['foto'])) {
                    $out['diseno']['foto'] = base64_encode($out['diseno']['foto']);
                }
                if (isset($out['diseno']['patron']) && !empty($out['diseno']['patron'])) {
                    $out['diseno']['patron'] = base64_encode($out['diseno']['patron']);
                }
                if (isset($out['diseno']['version']) && is_array($out['diseno']['version'])) {
                    if (isset($out['diseno']['version']['foto']) && !empty($out['diseno']['version']['foto'])) {
                        $out['diseno']['version']['foto'] = base64_encode($out['diseno']['version']['foto']);
                    }
                    if (isset($out['diseno']['version']['patron']) && !empty($out['diseno']['version']['patron'])) {
                        $out['diseno']['version']['patron'] = base64_encode($out['diseno']['version']['patron']);
                    }
                }
            }

            return $this->response->setJSON($out);
        } catch (\Throwable $e) {
            log_message('error', 'Error en Modals::pedido_json: ' . $e->getMessage() . ' | ' . $e->getFile() . ':' . $e->getLine());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error interno al cargar el pedido',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }
}
