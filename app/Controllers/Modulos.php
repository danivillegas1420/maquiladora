<?php

namespace App\Controllers;

use App\Services\NotificationService;
use App\Models\RolNotificacionModel;

/** Controlador principal: vistas y endpoints JSON de módulos. */
class Modulos extends BaseController
{
    /** Datos base (notif/usuario) + datos de vista. */
    private function payload(array $data = []): array
    {
        $base = [
            'notifCount' => $data['notifCount'] ?? 0,
            'userEmail' => session()->get('email') ?: 'admin@fabrica.com',
        ];
        return array_merge($base, $data);
    }

    /** Eliminar un pedido (orden_compra) y en cascada su OP y dependencias */
    public function m1_pedido_eliminar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }
        $ocId = (int) ($this->request->getPost('id') ?? $this->request->getVar('id') ?? $this->request->getPost('ocId') ?? 0);
        if ($ocId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }
        $db = \Config\Database::connect();
        // Maquiladora del usuario autenticado
        $maquiladoraId = session()->get('maquiladora_id');
        try {
            $db->transStart();

            // Buscar OPs relacionadas con esta OC
            $opIds = [];
            try {
                $rows = $db->query('SELECT id FROM orden_produccion WHERE ordenCompraId = ?', [$ocId])->getResultArray();
                foreach ($rows as $r) {
                    if (isset($r['id']))
                        $opIds[] = (int) $r['id'];
                }
            } catch (\Throwable $e) {
                try {
                    $rows = $db->query('SELECT id FROM OrdenProduccion WHERE ordenCompraId = ?', [$ocId])->getResultArray();
                    foreach ($rows as $r) {
                        if (isset($r['id']))
                            $opIds[] = (int) $r['id'];
                    }
                } catch (\Throwable $e2) { /* ignore */
                }
            }

            foreach ($opIds as $opId) {
                // Borrar Reproceso -> Inspeccion -> Asignaciones por OP
                $insIds = [];
                try {
                    $rins = $db->query('SELECT id FROM inspeccion WHERE ordenProduccionId = ?', [$opId])->getResultArray();
                    foreach ($rins as $ri) {
                        if (isset($ri['id']))
                            $insIds[] = (int) $ri['id'];
                    }
                } catch (\Throwable $e) {
                    try {
                        $rins = $db->query('SELECT id FROM Inspeccion WHERE ordenProduccionId = ?', [$opId])->getResultArray();
                        foreach ($rins as $ri) {
                            if (isset($ri['id']))
                                $insIds[] = (int) $ri['id'];
                        }
                    } catch (\Throwable $e2) { /* ignore */
                    }
                }
                if (!empty($insIds)) {
                    try {
                        $db->table('reproceso')->whereIn('inspeccionId', $insIds)->delete();
                    } catch (\Throwable $e) {
                        try {
                            $db->table('Reproceso')->whereIn('inspeccionId', $insIds)->delete();
                        } catch (\Throwable $e2) { /* ignore */
                        }
                    }
                }
                try {
                    $db->table('inspeccion')->where('ordenProduccionId', $opId)->delete();
                } catch (\Throwable $e) {
                    try {
                        $db->table('Inspeccion')->where('ordenProduccionId', $opId)->delete();
                    } catch (\Throwable $e2) { /* ignore */
                    }
                }
                try {
                    $db->table('asignacion_tarea')->where('ordenProduccionId', $opId)->delete();
                } catch (\Throwable $e) { /* ignore */
                }
                // Borrar OP
                $okDelOp = false;
                try {
                    $okDelOp = (bool) $db->table('orden_produccion')->where('id', $opId)->delete();
                } catch (\Throwable $e) {
                    $okDelOp = false;
                }
                if (!$okDelOp) {
                    try {
                        $okDelOp = (bool) $db->table('OrdenProduccion')->where('id', $opId)->delete();
                    } catch (\Throwable $e2) {
                        $okDelOp = false;
                    }
                }
            }

            // Finalmente, borrar la Orden de Compra
            $okOc = false;
            try {
                $okOc = (bool) $db->table('orden_compra')->where('id', $ocId)->delete();
            } catch (\Throwable $e) {
                $okOc = false;
            }
            if (!$okOc) {
                try {
                    $okOc = (bool) $db->table('OrdenCompra')->where('id', $ocId)->delete();
                } catch (\Throwable $e2) {
                    $okOc = false;
                }
            }
            if (!$okOc) {
                throw new \Exception('No se pudo eliminar el pedido (Orden de Compra)');
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }
            return $this->response->setJSON(['ok' => true, 'id' => $ocId]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $e2) {
            }
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al eliminar pedido: ' . $e->getMessage()]);
        }
    }

    /**
     * Obtener datos de un usuario (JSON) + catálogos (roles, maquiladoras)
     */
    public function m11_obtener_usuario($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID inválido',
            ]);
        }

        // Conexión a base de datos
        $db = \Config\Database::connect();
        try {
            // Usuario base
            $user = $db->table('users')->where('id', $id)->get()->getRowArray();
            if (!$user) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Usuario no encontrado',
                ]);
            }

            // Rol actual y listado de roles
            $rolActual = null;
            try {
                $map = $db->table('usuario_rol')->where('usuarioIdFK', $id)->get()->getRowArray();
                $rolActual = $map['rolIdFK'] ?? null;
            } catch (\Throwable $e) {
                $rolActual = null;
            }

            // Alias a 'name' para coincidir con el JS de la vista, filtrando por maquiladora cuando aplique
            $rolesBuilder = $db->table('rol')->select('id, nombre as name');
            $maquiladoraId = session()->get('maquiladora_id');
            if ($maquiladoraId) {
                try {
                    $fields = $db->getFieldNames('rol');
                    if (in_array('maquiladoraID', $fields, true)) {
                        $rolesBuilder->where('maquiladoraID', (int) $maquiladoraId);
                    }
                } catch (\Throwable $e) {
                }
            }
            $roles = $rolesBuilder->orderBy('nombre', 'ASC')->get()->getResultArray();

            // Maquiladoras
            $maqs = $db->table('maquiladora')->select('idmaquiladora as id, Nombre_Maquila as nombre')
                ->orderBy('Nombre_Maquila', 'ASC')->get()->getResultArray();

            $out = [
                'id' => (int) $user['id'],
                'username' => $user['username'] ?? '',
                'email' => $user['correo'] ?? '',
                'maquiladoraIdFK' => $user['maquiladoraIdFK'] ?? null,
                'activo' => (int) ($user['active'] ?? 1),
                'rol_id' => $rolActual,
                'roles' => $roles,
                'maquiladoras' => $maqs,
            ];

            return $this->response->setJSON(['success' => true, 'data' => $out]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al obtener usuario: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Actualizar datos de usuario: nombre, correo, rol, maquiladora, activo y password (opcional)
     * Entrada POST: id, nombre, email, rol, idmaquiladora, activo, password?
     */
    public function m11_actualizar_usuario()
    {
        // Aceptar la petición sin forzar método (la ruta es POST, pero algunos entornos envían como AJAX genérico)
        $id = (int) ($this->request->getPost('id') ?? $this->request->getVar('id'));
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'ID inválido']);
        }

        $nombre = trim((string) ($this->request->getPost('nombre') ?? $this->request->getVar('nombre') ?? ''));
        $email = trim((string) ($this->request->getPost('email') ?? $this->request->getVar('email') ?? ''));
        $rolId = $this->request->getPost('rol') ?? $this->request->getVar('rol');
        $maqId = $this->request->getPost('idmaquiladora') ?? $this->request->getVar('idmaquiladora');
        $activo = (int) ($this->request->getPost('activo') ?? $this->request->getVar('activo') ?? 1);
        $pwd = (string) ($this->request->getPost('password') ?? $this->request->getVar('password') ?? '');

        $db = \Config\Database::connect();
        try {
            $db->transStart();
            $upd = [
                'username' => $nombre,
                'correo' => $email,
                'active' => $activo,
            ];
            if ($maqId !== null && $maqId !== '') {
                $upd['maquiladoraIdFK'] = (int) $maqId;
            }
            if ($pwd !== '') {
                $upd['password'] = password_hash($pwd, PASSWORD_BCRYPT, ['cost' => 10]);
            }
            $db->table('users')->where('id', $id)->update($upd);

            // Actualizar rol: borrar asignaciones previas del usuario y dejar solo una
            if ($rolId !== null && $rolId !== '' && (int) $rolId > 0) {
                $db->table('usuario_rol')->where('usuarioIdFK', $id)->delete();

                // Como la PK incluye 'id' y no es autoincrement, generamos uno único (MAX(id)+1)
                $nextId = 1;
                try {
                    $rowNext = $db->query('SELECT COALESCE(MAX(id),0)+1 AS nextId FROM usuario_rol')->getRowArray();
                    if ($rowNext && isset($rowNext['nextId'])) {
                        $nextId = (int) $rowNext['nextId'];
                    }
                } catch (\Throwable $e) {
                    $nextId = time();
                }

                $okRole = $db->table('usuario_rol')->insert([
                    'id' => $nextId,
                    'usuarioIdFK' => $id,
                    'rolIdFK' => (int) $rolId,
                ]);
                if (!$okRole) {
                    $dbErr = $db->error();
                    $dbMsg = isset($dbErr['message']) && $dbErr['message'] ? $dbErr['message'] : 'No se pudo asignar el rol';
                    throw new \Exception($dbMsg);
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                $dbErr = $db->error();
                $dbMsg = isset($dbErr['message']) && $dbErr['message'] ? $dbMsg : 'Error en la transacción';
                try {
                    log_message('error', 'm11_actualizar_usuario transStatus=false: ' . $dbMsg);
                } catch (\Throwable $e) {
                }
                throw new \Exception($dbMsg);
            }

            // Crear notificación para el usuario actualizado
            try {
                $notificationService = new NotificationService();
                $maquiladoraId = session()->get('maquiladoraID') ?? session()->get('maquiladora_id') ?? 1;

                $notificationService->notifyUsuarioActualizado(
                    $maquiladoraId,
                    $nombre,
                    $email
                );
            } catch (\Throwable $e) {
                // No fallar la actualización del usuario si hay error en la notificación
                log_message('warning', 'Error al crear notificación de usuario actualizado: ' . $e->getMessage());
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Usuario y rol actualizados correctamente']);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $e3) {
            }
            $errMsg = $e->getMessage();
            try {
                log_message('error', 'm11_actualizar_usuario exception: ' . $errMsg);
            } catch (\Throwable $e2) {
            }
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al actualizar: ' . $errMsg,
            ]);
        }
    }

    /**
     * Eliminar usuario (lógica): marca deleted_at o, si no existe, active=0
     * Entrada: POST id
     * Salida: JSON { success, message }
     */
    public function m11_eliminar_usuario()
    {
        // Aceptar la petición sin forzar método, la ruta ya limita a POST
        $id = (int) ($this->request->getPost('id') ?? $this->request->getVar('id') ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID inválido'
            ]);
        }

        $db = \Config\Database::connect();
        try {
            // Intentar soft delete con deleted_at
            $ok = false;
            try {
                $ok = $db->table('users')->where('id', $id)
                    ->update(['deleted_at' => date('Y-m-d H:i:s')]);
            } catch (\Throwable $e) {
                $ok = false;
            }
            if (!$ok) {
                // Fallback: desactivar
                $db->table('users')->where('id', $id)->update(['active' => 0]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Usuario eliminado correctamente'
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage(),
            ]);
        }
    }

    public function m1_pedido_pdf($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }

        try {
            $pedidoModel = new \App\Models\PedidoModel();
            $detalle = $pedidoModel->getPedidoDetalle($id);

            if (!$detalle) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Pedido no encontrado']);
            }

            $pedido = [
                'id' => (int) ($detalle['id'] ?? $id),
                'folio' => $detalle['folio'] ?? '',
                'fecha' => isset($detalle['fecha']) && $detalle['fecha'] ? date('d/m/Y', strtotime($detalle['fecha'])) : date('d/m/Y'),
                'estatus' => $detalle['estatus'] ?? '',
                'moneda' => $detalle['moneda'] ?? 'MXN',
                'total' => isset($detalle['total']) ? number_format((float) $detalle['total'], 2) : '0.00',
            ];

            $cliente = $detalle['cliente'] ?? [];
            $diseno = $detalle['diseno'] ?? null;
            $tallas = $detalle['tallas'] ?? [];
            $op_cantidadPlan = $detalle['op_cantidadPlan'] ?? null;
            $op_fechaInicioPlan = isset($detalle['op_fechaInicioPlan']) && $detalle['op_fechaInicioPlan'] ? date('d/m/Y', strtotime($detalle['op_fechaInicioPlan'])) : null;
            $op_fechaFinPlan = isset($detalle['op_fechaFinPlan']) && $detalle['op_fechaFinPlan'] ? date('d/m/Y', strtotime($detalle['op_fechaFinPlan'])) : null;

            $maquiladoraId = session()->get('maquiladora_id');
            $maquiladoraRaw = [];
            if ($maquiladoraId) {
                $db = \Config\Database::connect();
                try {
                    $maquiladoraRaw = $db->table('maquiladora')
                        ->where('idmaquiladora', $maquiladoraId)
                        ->get()
                        ->getRowArray() ?: [];
                } catch (\Throwable $e) {
                    $maquiladoraRaw = [];
                }
                if (!$maquiladoraRaw) {
                    try {
                        $maquiladoraRaw = $db->table('Maquiladora')
                            ->where('idmaquiladora', $maquiladoraId)
                            ->get()
                            ->getRowArray() ?: [];
                    } catch (\Throwable $e2) {
                        $maquiladoraRaw = [];
                    }
                }
            }

            $maquiladora = [
                'nombre' => $maquiladoraRaw['nombre'] ?? ($maquiladoraRaw['Nombre_Maquila'] ?? ($maquiladoraRaw['Nombre'] ?? '')),
                'domicilio' => $maquiladoraRaw['domicilio'] ?? ($maquiladoraRaw['Domicilio'] ?? ''),
                'telefono' => $maquiladoraRaw['telefono'] ?? ($maquiladoraRaw['Telefono'] ?? ''),
                'correo' => $maquiladoraRaw['correo'] ?? ($maquiladoraRaw['Correo'] ?? ''),
                'dueno' => $maquiladoraRaw['dueno'] ?? ($maquiladoraRaw['Dueno'] ?? ''),
                'logo_base64' => null,
                'logo_mime' => null,
            ];
            if (!empty($maquiladoraRaw['logo'])) {
                $maquiladora['logo_base64'] = base64_encode($maquiladoraRaw['logo']);
                $mime = null;
                try {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime = $finfo->buffer($maquiladoraRaw['logo']);
                } catch (\Throwable $e) {
                    $mime = null;
                }
                $maquiladora['logo_mime'] = $mime ?: 'image/jpeg';
            }

            $html = view('modulos/pedido_pdf', [
                'pedido' => $pedido,
                'cliente' => $cliente,
                'diseno' => $diseno,
                'tallas' => $tallas,
                'op_cantidadPlan' => $op_cantidadPlan,
                'op_fechaInicioPlan' => $op_fechaInicioPlan,
                'op_fechaFinPlan' => $op_fechaFinPlan,
                'maquiladora' => $maquiladora,
            ]);

            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('letter', 'portrait');
            $dompdf->render();

            $pdfOutput = $dompdf->output();
            return $this->response
                ->setHeader('Content-Disposition', 'inline; filename="Pedido_' . (($detalle['folio'] ?? '') !== '' ? $detalle['folio'] : (string) $id) . '.pdf"')
                ->setContentType('application/pdf')
                ->setBody($pdfOutput);
        } catch (\Throwable $e) {
            log_message('error', 'Error al generar PDF del pedido: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al generar PDF']);
        }
    }

    /**
     * Generar Excel del pedido
     */
    public function m1_pedido_excel($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }

        try {
            $pedidoModel = new \App\Models\PedidoModel();
            // Obtener detalle completo del pedido
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
                    'op_cantidadPlan' => $basic['op_cantidadPlan'] ?? null,
                ];
            }

            // Rellenar diseño si está vacío (mismo código que m1_pedido_json)
            if (empty($detalle['diseno'])) {
                $db = \Config\Database::connect();
                $row = null;
                try {
                    $row = $db->query(
                        "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion,
                                COALESCE(dv.precio_unidad, dv.precioUnidad, d.precio_unidad, d.precioUnidad) AS precio_unidad
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
                            "SELECT dv.*, d.id AS d_id, d.codigo AS d_codigo, d.nombre AS d_nombre, d.descripcion AS d_descripcion,
                                    COALESCE(dv.precio_unidad, dv.precioUnidad, d.precio_unidad, d.precioUnidad) AS precio_unidad
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
                        'precio_unidad' => $row['precio_unidad'] ?? $row['precioUnidad'] ?? null,
                        'version' => [
                            'id' => $row['id'] ?? null,
                            'version' => $row['version'] ?? null,
                            'fecha' => $row['fecha'] ?? null,
                            'aprobado' => $row['aprobado'] ?? null,
                        ],
                    ];
                }
            }

            // Preparar datos
            $pedido = [
                'id' => (int) ($detalle['id'] ?? $id),
                'folio' => $detalle['folio'] ?? '',
                'fecha' => isset($detalle['fecha']) ? date('d/m/Y', strtotime($detalle['fecha'])) : date('d/m/Y'),
                'estatus' => $detalle['estatus'] ?? '',
                'moneda' => $detalle['moneda'] ?? 'MXN',
                'total' => isset($detalle['total']) ? number_format((float) $detalle['total'], 2) : '0.00',
            ];
            $cliente = $detalle['cliente'] ?? [];
            $diseno = $detalle['diseno'] ?? null;
            $op_cantidadPlan = $detalle['op_cantidadPlan'] ?? null;
            $op_fechaInicioPlan = isset($detalle['op_fechaInicioPlan']) ? date('d/m/Y', strtotime($detalle['op_fechaInicioPlan'])) : null;
            $op_fechaFinPlan = isset($detalle['op_fechaFinPlan']) ? date('d/m/Y', strtotime($detalle['op_fechaFinPlan'])) : null;

            // Generar Excel usando formato XML SpreadsheetML
            $xml = '<?xml version="1.0"?>' . "\n";
            $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
            $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            $xml .= ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n";
            $xml .= ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n";
            $xml .= ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n";
            $xml .= ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
            $xml .= '<Worksheet ss:Name="Pedido">' . "\n";
            $xml .= '<Table>' . "\n";

            // Función helper para agregar fila
            $addRow = function ($cells) use (&$xml) {
                $xml .= '<Row>' . "\n";
                foreach ($cells as $cell) {
                    $type = is_numeric($cell) ? 'Number' : 'String';
                    $xml .= '<Cell><Data ss:Type="' . $type . '">' . htmlspecialchars($cell, ENT_XML1) . '</Data></Cell>' . "\n";
                }
                $xml .= '</Row>' . "\n";
            };

            // Encabezado
            $addRow(['ORDEN DE COMPRA']);
            $addRow(['Folio:', $pedido['folio']]);
            $addRow([]);

            // Información General
            $addRow(['INFORMACIÓN GENERAL']);
            $addRow(['Folio:', $pedido['folio']]);
            $addRow(['Fecha:', $pedido['fecha']]);
            $addRow(['Estatus:', $pedido['estatus']]);
            $addRow(['Moneda:', $pedido['moneda']]);
            $addRow([]);

            // Datos del Cliente
            $addRow(['DATOS DEL CLIENTE']);
            if (!empty($cliente['nombre'])) {
                $addRow(['Nombre:', $cliente['nombre']]);
            }
            if (!empty($cliente['email'])) {
                $addRow(['Email:', $cliente['email']]);
            }
            if (!empty($cliente['telefono'])) {
                $addRow(['Teléfono:', $cliente['telefono']]);
            }
            if (!empty($cliente['direccion_detalle'])) {
                $dir = $cliente['direccion_detalle'];
                $direccion = ($dir['calle'] ?? '');
                if (!empty($dir['numExt']))
                    $direccion .= ' #' . $dir['numExt'];
                if (!empty($dir['numInt']))
                    $direccion .= ' Int. ' . $dir['numInt'];
                if (!empty($dir['ciudad']))
                    $direccion .= ', ' . $dir['ciudad'];
                if (!empty($dir['estado']))
                    $direccion .= ', ' . $dir['estado'];
                if (!empty($dir['cp']))
                    $direccion .= ' CP ' . $dir['cp'];
                if (!empty($dir['pais']))
                    $direccion .= ', ' . $dir['pais'];
                if ($direccion) {
                    $addRow(['Dirección:', $direccion]);
                }
            }
            $addRow([]);

            // Diseño / Modelo
            if (!empty($diseno)) {
                $addRow(['DISEÑO / MODELO']);
                if (!empty($diseno['codigo'])) {
                    $addRow(['Código:', $diseno['codigo']]);
                }
                if (!empty($diseno['nombre'])) {
                    $addRow(['Nombre:', $diseno['nombre']]);
                }
                if (!empty($diseno['descripcion'])) {
                    $addRow(['Descripción:', $diseno['descripcion']]);
                }
                if (!empty($diseno['precio_unidad'])) {
                    $addRow(['Precio Unitario:', $pedido['moneda'] . ' ' . number_format((float) $diseno['precio_unidad'], 2)]);
                }
                if (!empty($diseno['version'])) {
                    $ver = is_array($diseno['version']) ? $diseno['version'] : ['version' => $diseno['version']];
                    if (!empty($ver['version'])) {
                        $addRow(['Versión:', $ver['version']]);
                    }
                    if (!empty($ver['fecha'])) {
                        $addRow(['Fecha Versión:', date('d/m/Y', strtotime($ver['fecha']))]);
                    }
                }
                $addRow([]);
            }

            // Plan
            $addRow(['PLAN']);
            $addRow(['Concepto', 'Valor']);
            if (!empty($op_cantidadPlan)) {
                $addRow(['Cantidad Plan', number_format((float) $op_cantidadPlan, 0) . ' unidades']);
            }
            if (!empty($diseno['precio_unidad']) && !empty($op_cantidadPlan)) {
                $addRow(['Precio Unitario', $pedido['moneda'] . ' ' . number_format((float) $diseno['precio_unidad'], 2)]);
                $addRow(['Subtotal', $pedido['moneda'] . ' ' . number_format((float) $diseno['precio_unidad'] * (float) $op_cantidadPlan, 2)]);
            }
            if (!empty($op_fechaInicioPlan)) {
                $addRow(['Fecha Inicio Plan', $op_fechaInicioPlan]);
            }
            if (!empty($op_fechaFinPlan)) {
                $addRow(['Fecha Fin Plan', $op_fechaFinPlan]);
            }
            $addRow([]);
            $addRow(['TOTAL', $pedido['moneda'] . ' ' . $pedido['total']]);

            $xml .= '</Table>' . "\n";
            $xml .= '</Worksheet>' . "\n";
            $xml .= '</Workbook>';

            // Limpiar cualquier output buffer
            while (ob_get_level() > 0) {
                @ob_end_clean();
            }

            // Responder con el Excel
            $filename = 'pedido_' . ($pedido['folio'] ?: $id) . '.xls';

            return $this->response
                ->setStatusCode(200)
                ->setHeader('Content-Type', 'application/vnd.ms-excel')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setHeader('Cache-Control', 'private, max-age=0, must-revalidate')
                ->setHeader('Pragma', 'public')
                ->setBody($xml);
        } catch (\Throwable $e) {
            log_message('error', 'Error al generar Excel del pedido: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al generar el Excel',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Eliminar un diseño: borra sus versiones y materiales asociados.
     * Acepta POST/OPTIONS
     */
    public function m2_eliminar_diseno($id = null)
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        }
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID inválido']);
        }

        $db = \Config\Database::connect();
        try {
            $db->transStart();

            // Obtener IDs de versiones
            $dvIds = array_map(function ($r) {
                return (int) $r['id'];
            }, $db->query('SELECT id FROM diseno_version WHERE disenoId = ?', [$id])->getResultArray() ?? []);
            if (!$dvIds) {
                $dvIds = array_map(function ($r) {
                    return (int) $r['id'];
                }, $db->query('SELECT id FROM disenoversion WHERE disenoId = ?', [$id])->getResultArray() ?? []);
            }

            if (!empty($dvIds)) {
                $in = implode(',', array_fill(0, count($dvIds), '?'));
                // Borrar materiales por versiones
                foreach (['lista_materiales', 'listamateriales', 'ListaMateriales'] as $t) {
                    try {
                        $db->query("DELETE FROM $t WHERE disenoVersionId IN ($in)", $dvIds);
                        break;
                    } catch (\Throwable $e) { /* try next */
                    }
                }
                // Borrar versiones
                foreach (['diseno_version', 'disenoversion'] as $t) {
                    try {
                        $db->query("DELETE FROM $t WHERE id IN ($in)", $dvIds);
                        break;
                    } catch (\Throwable $e) { /* try next */
                    }
                }
            }

            // Borrar diseño
            $deleted = false;
            foreach (['diseno', 'Diseno'] as $t) {
                try {
                    $db->table($t)->where('id', $id)->delete();
                    $deleted = true;
                    break;
                } catch (\Throwable $e) { /* next */
                }
            }

            $db->transComplete();
            if ($db->transStatus() === false || !$deleted) {
                throw new \Exception('No se pudo eliminar el diseño');
            }

            return $this->response->setJSON(['ok' => true, 'message' => 'Diseño eliminado']);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $ee) {
            }
            return $this->response->setStatusCode(500)->setJSON(['ok' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }

    /** JSON detalle normalizado de diseño. */
    public function m2_diseno_json($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }

        $disenoModel = new \App\Models\DisenoModel();
        $detalle = $disenoModel->getDisenoDetalle($id);
        if (!$detalle) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Diseño no encontrado']);
        }

        // Normalizar salida y agregar campos convenientes
        $out = [
            'id' => (int) $detalle['id'],
            'codigo' => $detalle['codigo'] ?? '',
            'nombre' => $detalle['nombre'] ?? '',
            'descripcion' => $detalle['descripcion'] ?? '',
            'clienteId' => $detalle['clienteId'] ?? null,
            'version' => $detalle['version'] ?? '',
            'fecha' => $detalle['fecha'] ?? '',
            'notas' => $detalle['notas'] ?? '',
            'materiales' => $detalle['materiales'] ?? [],
            'archivoCadUrl' => $detalle['archivoCadUrl'] ?? '',
            'archivoPatronUrl' => $detalle['archivoPatronUrl'] ?? '',
            'aprobado' => $detalle['aprobado'] ?? null,
            'precio_unidad' => $detalle['precio_unidad'] ?? null,
            'idSexoFK' => $detalle['idSexoFK'] ?? null,
            'idTallasFK' => $detalle['idTallasFK'] ?? null,  // Fixed typo: was IdTallasFK
            'idTipoCorteFK' => $detalle['idTipoCorteFK'] ?? null,
            'idTipoRopaFK' => $detalle['idTipoRopaFK'] ?? null,
            // Foto y patrón en base64
            'foto' => $detalle['foto'] ?? null,
            'patron' => $detalle['patron'] ?? null,
            // Nuevos: listas de archivos/imágenes (compatibles hacia atrás)
            'archivosCad' => $detalle['archivosCad'] ?? [],
            'archivosPatron' => $detalle['archivosPatron'] ?? [],
            'imagenes' => $detalle['imagenes'] ?? [],
            // Si más adelante tienes una imagen, puedes exponerla aquí
            'imagenUrl' => $detalle['imagenUrl'] ?? '',
        ];

        return $this->response->setJSON($out);
    }

    /** Lista de diseños filtrados por maquiladora del usuario autenticado (JSON) */
    public function m2_disenos_json()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');

        $disenos = [];
        try {
            // Intentar con diferentes nombres de tabla
            $queries = [
                "SELECT d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad, 
                        (SELECT dv.version FROM diseno_version dv WHERE dv.disenoId = d.id ORDER BY dv.fecha DESC, dv.id DESC LIMIT 1) as version
                 FROM diseno d 
                 WHERE d.maquiladoraID = ? 
                 ORDER BY d.nombre",
                "SELECT d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad,
                        (SELECT dv.version FROM disenoversion dv WHERE dv.disenoId = d.id ORDER BY dv.fecha DESC, dv.id DESC LIMIT 1) as version
                 FROM Diseno d 
                 WHERE d.maquiladoraID = ? 
                 ORDER BY d.nombre",
            ];

            foreach ($queries as $query) {
                try {
                    $disenos = $db->query($query, [$maquiladoraId])->getResultArray();
                    if ($disenos !== null)
                        break;
                } catch (\Throwable $e) {
                    // Intentar siguiente query
                }
            }

            // Si no se encontraron diseños con maquiladoraID, intentar sin filtro (fallback)
            if (empty($disenos)) {
                $fallbackQueries = [
                    "SELECT d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad,
                            (SELECT dv.version FROM diseno_version dv WHERE dv.disenoId = d.id ORDER BY dv.fecha DESC, dv.id DESC LIMIT 1) as version
                     FROM diseno d 
                     ORDER BY d.nombre",
                    "SELECT d.id, d.codigo, d.nombre, d.descripcion, d.precio_unidad,
                            (SELECT dv.version FROM disenoversion dv WHERE dv.disenoId = d.id ORDER BY dv.fecha DESC, dv.id DESC LIMIT 1) as version
                     FROM Diseno d 
                     ORDER BY d.nombre",
                ];

                foreach ($fallbackQueries as $query) {
                    try {
                        $disenos = $db->query($query)->getResultArray();
                        if ($disenos !== null)
                            break;
                    } catch (\Throwable $e) {
                        // Intentar siguiente query
                    }
                }
            }
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al obtener diseños: ' . $e->getMessage()
            ]);
        }

        return $this->response->setJSON($disenos);
    }

    public function ordenes()
    {
        $ordenes = [
            ['op' => 'OP-0001', 'cliente' => 'Textiles MX', 'responsable' => 'Juan Pérez', 'ini' => '2025-09-20', 'fin' => '2025-09-25', 'estatus' => 'En proceso'],
            ['op' => 'OP-0002', 'cliente' => 'Fábrica Sur', 'responsable' => 'María López', 'ini' => '2025-09-21', 'fin' => '2025-09-27', 'estatus' => 'Planificada'],
            ['op' => 'OP-0003', 'cliente' => 'Industrias PZ', 'responsable' => 'Carlos Ruiz', 'ini' => '2025-09-19', 'fin' => '2025-09-24', 'estatus' => 'En proceso'],
        ];

        return view('modulos/m1_ordenes', $this->payload([
            'title' => 'Órdenes',
            'ordenes' => $ordenes,
            'notifCount' => 0,
        ]));
    }



    public function wip()
    {
        $etapas = [
            ['etapa' => 'Corte', 'resp' => 'Juan Pérez', 'ini' => '2025-09-20', 'fin' => '2025-09-22', 'prog' => 80],
            ['etapa' => 'Confección', 'resp' => 'María López', 'ini' => '2025-09-22', 'fin' => '2025-09-25', 'prog' => 45],
            ['etapa' => 'Acabado', 'resp' => 'Carlos Ruiz', 'ini' => '2025-09-25', 'fin' => '2025-09-27', 'prog' => 10],
        ];

        return view('modulos/wip', $this->payload([
            'title' => 'WIP',
            'etapas' => $etapas,
            'notifCount' => 0,
        ]));
    }

    public function incidencias()
    {
        $lista = [
            ['fecha' => '2025-09-21', 'op' => 'OP-0001', 'tipo' => 'Paro de máquina', 'desc' => 'Mantenimiento no programado'],
            ['fecha' => '2025-09-22', 'op' => 'OP-0003', 'tipo' => 'Falta de material', 'desc' => 'Faltan rollos de tela'],
        ];

        return view('modulos/incidencias', $this->payload([
            'title' => 'Incidencias',
            'lista' => $lista,
            'notifCount' => count($lista),
        ]));
    }

    public function reportes()
    {
        $maquiladora = [];
        $maquiladoraId = session()->get('maquiladora_id');

        // Depuración
        log_message('debug', 'ID de maquiladora en sesión: ' . print_r($maquiladoraId, true));

        if ($maquiladoraId) {
            try {
                $db = \Config\Database::connect();

                // Verificar si la tabla existe
                $tables = $db->listTables();
                log_message('debug', 'Tablas en la base de datos: ' . print_r($tables, true));

                // Obtener los campos de la tabla maquiladora
                $fields = $db->getFieldData('maquiladora');
                log_message('debug', 'Campos de la tabla maquiladora: ' . print_r(array_column($fields, 'name'), true));

                // Intentar con minúsculas primero
                $maquiladora = $db->table('maquiladora')
                    ->select('*')
                    ->where('idmaquiladora', $maquiladoraId)
                    ->get()
                    ->getRowArray();

                log_message('debug', 'Consulta 1 (minúsculas): ' . $db->getLastQuery());

                // Si no se encontró, intentar con mayúsculas
                if (empty($maquiladora)) {
                    $maquiladora = $db->table('Maquiladora')
                        ->select('*')
                        ->where('idmaquiladora', $maquiladoraId)
                        ->get()
                        ->getRowArray();

                    log_message('debug', 'Consulta 2 (mayúsculas): ' . $db->getLastQuery());
                }

                log_message('debug', 'Datos de maquiladora: ' . print_r($maquiladora, true));

            } catch (\Exception $e) {
                log_message('error', 'Error al obtener datos de la maquiladora: ' . $e->getMessage());
            }
        }

        return view('modulos/reportes', $this->payload([
            'title' => 'Reportes',
            'maquiladora' => $maquiladora,
            'notifCount' => 0,
            'debug_info' => [
                'maquiladora_id' => $maquiladoraId,
                'maquiladora_data' => $maquiladora
            ]
        ]));
    }

    public function notificaciones()
    {
        // Obtener ID de maquiladora y usuario de la sesión (usando el mismo patrón que otros controladores)
        $maquiladoraId = session()->get('maquiladora_id') ?? session()->get('maquiladoraID');
        $userId = session()->get('user_id');

        // Debug: Log para verificar los IDs de sesión
        log_message('debug', 'Notificaciones - Session IDs: maquiladora_id=' . session()->get('maquiladora_id') .
            ', maquiladoraID=' . session()->get('maquiladoraID') .
            ', user_id=' . session()->get('user_id'));

        // Validar que tengamos IDs válidos
        if (!$maquiladoraId || !$userId) {
            log_message('error', 'Notificaciones - Faltan datos de sesión: maquiladoraId=' . $maquiladoraId . ', userId=' . $userId);
            return view('modulos/notificaciones', $this->payload([
                'title' => 'Notificaciones',
                'items' => [],
                'notifCount' => 0,
                'error' => 'No se pudo identificar tu sesión. Por favor inicia sesión nuevamente.'
            ]));
        }

        // Cargar modelo de notificaciones
        $notificationModel = new \App\Models\NotificacionModel();

        try {
            // Debug: Log para verificar la consulta
            log_message('debug', 'Notificaciones - Consultando para maquiladoraId: ' . (int) $maquiladoraId . ', userId: ' . (int) $userId);

            // Obtener notificaciones con estado de lectura para el usuario actual
            $notifications = $notificationModel->getWithReadStatus(
                (int) $maquiladoraId,
                (int) $userId,
                50 // Límite de notificaciones a mostrar
            );

            // Debug: Log resultados
            log_message('debug', 'Notificaciones - Encontradas: ' . count($notifications) . ' notificaciones');

            // Formatear las notificaciones para la vista
            $items = [];
            foreach ($notifications as $notif) {
                // Determinar color según nivel
                $color = '#4dabf7'; // Default azul
                $nivel = 'Media';

                switch (strtolower($notif['nivel'] ?? '')) {
                    case 'critical':
                    case 'crítica':
                        $color = '#e03131';
                        $nivel = 'Crítica';
                        break;
                    case 'high':
                    case 'alta':
                        $color = '#ffd43b';
                        $nivel = 'Alta';
                        break;
                    case 'warning':
                    case 'advertencia':
                        $color = '#ff8787';
                        $nivel = 'Advertencia';
                        break;
                    case 'info':
                    case 'información':
                        $color = '#4dabf7';
                        $nivel = 'Media';
                        break;
                }

                // Formatear tiempo
                $timeAgo = $this->timeAgo($notif['created_at']);

                $items[] = [
                    'id' => $notif['id'],
                    'nivel' => $nivel,
                    'color' => $color,
                    'titulo' => $notif['titulo'] ?? 'Notificación',
                    'sub' => ($notif['sub'] ?? '') . ' • ' . $timeAgo,
                    'is_leida' => (int) ($notif['is_leida'] ?? 0),
                    'created_at' => $notif['created_at']
                ];
            }

        } catch (\Throwable $e) {
            // En caso de error, mostrar array vacío
            log_message('error', 'Error al cargar notificaciones: ' . $e->getMessage());
            $items = [];
        }

        return view('modulos/notificaciones', $this->payload([
            'title' => 'Notificaciones',
            'items' => $items,
            'notifCount' => count($items),
            'debug_maquiladora_id' => $maquiladoraId // Para depuración en la vista
        ]));
    }

    /**
     * Helper: Convert timestamp to "time ago" format
     */
    protected function timeAgo($datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Justo ahora';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return "Hace {$mins} " . ($mins == 1 ? 'minuto' : 'minutos');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "Hace {$hours} " . ($hours == 1 ? 'hora' : 'horas');
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return "Hace {$days} " . ($days == 1 ? 'día' : 'días');
        } else {
            return date('d/m/Y', $timestamp);
        }
    }

    public function mrp()
    {
        $reqModel = new \App\Models\MrpRequerimientoModel();
        $ocModel = new \App\Models\MrpOcModel();

        // Obtener todos los requerimientos y OCs de la base de datos
        $reqs = $reqModel->findAll();
        $ocs = $ocModel->findAll();

        return view('modulos/mrp', $this->payload([
            'title' => 'MRP',
            'reqs' => $reqs,
            'ocs' => $ocs,
            'notifCount' => 0,
        ]));
    }

    public function descargarOCPdf($id)
    {
        $ocModel = new \App\Models\MrpOcModel();
        $oc = $ocModel->find($id);

        if (!$oc || !$oc['pdf_path']) {
            return redirect()->to('/modulo3/mrp')->with('error', 'PDF no encontrado');
        }

        $filepath = WRITEPATH . $oc['pdf_path'];

        if (!file_exists($filepath)) {
            return redirect()->to('/modulo3/mrp')->with('error', 'Archivo PDF no existe');
        }

        return $this->response->download($filepath, null)->setFileName('OC_' . str_pad($id, 6, '0', STR_PAD_LEFT) . '.pdf');
    }

    /**
     * Generar OC desde un requerimiento de material
     * POST /modulo3/mrp/requerimientos/{id}/generar-oc
     */
    public function generarOCDesdeRequerimiento($id)
    {
        $id = (int) $id;

        if ($id <= 0) {
            return redirect()->to('/modulo3/mrp')->with('error', 'ID de requerimiento inválido');
        }

        $reqModel = new \App\Models\MrpRequerimientoModel();
        $ocModel = new \App\Models\MrpOcModel();

        try {
            // Obtener el requerimiento
            $req = $reqModel->find($id);

            if (!$req) {
                return redirect()->to('/modulo3/mrp')->with('error', 'Requerimiento no encontrado');
            }

            // Validar que haya cantidad a comprar
            $cantidadComprar = (float) ($req['comprar'] ?? 0);
            if ($cantidadComprar <= 0) {
                return redirect()->to('/modulo3/mrp')->with('error', 'No hay cantidad a comprar para este requerimiento');
            }

            // Crear la OC con los datos del requerimiento
            $ocData = [
                'prov' => 'Proveedor por definir', // Valor por defecto, puede mejorarse
                'mat' => $req['mat'],
                'cant' => $cantidadComprar,
                'u' => $req['u'],
                'eta' => date('Y-m-d', strtotime('+7 days')), // ETA por defecto: 7 días
                'pdf_path' => null
            ];

            // Insertar la OC primero para obtener el ID
            $ocId = $ocModel->insert($ocData);

            if (!$ocId) {
                throw new \Exception('No se pudo crear la orden de compra');
            }

            // Obtener la OC recién creada
            $oc = $ocModel->find($ocId);

            // Generar el PDF
            $html = view('pdf/orden_compra', ['oc' => $oc]);

            // Configurar Dompdf
            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $options->set('isHtml5ParserEnabled', true);

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('letter', 'portrait');
            $dompdf->render();

            // Guardar el PDF
            $pdfOutput = $dompdf->output();

            // Crear directorio si no existe
            $uploadPath = WRITEPATH . 'uploads/mrp_oc/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Nombre del archivo
            $filename = 'OC_' . str_pad($ocId, 6, '0', STR_PAD_LEFT) . '_' . date('Ymd_His') . '.pdf';
            $filepath = $uploadPath . $filename;

            // Guardar el archivo
            file_put_contents($filepath, $pdfOutput);

            // Actualizar la OC con la ruta del PDF
            $relativePath = 'uploads/mrp_oc/' . $filename;
            $ocModel->update($ocId, ['pdf_path' => $relativePath]);

            return redirect()->to('/modulo3/mrp')->with('success', 'Orden de compra generada correctamente con PDF');

        } catch (\Throwable $e) {
            log_message('error', 'Error al generar OC desde requerimiento: ' . $e->getMessage());
            return redirect()->to('/modulo3/mrp')->with('error', 'Error al generar la orden de compra: ' . $e->getMessage());
        }
    }


    /**
     * Crear un nuevo requerimiento de material
     * POST /modulo3/mrp/requerimientos/crear
     */
    public function crearRequerimiento()
    {
        $reqModel = new \App\Models\MrpRequerimientoModel();

        try {
            $data = [
                'mat' => $this->request->getPost('mat'),
                'u' => $this->request->getPost('u'),
                'necesidad' => (float) $this->request->getPost('necesidad'),
                'stock' => (float) $this->request->getPost('stock'),
                'comprar' => (float) $this->request->getPost('comprar'),
            ];

            $reqModel->insert($data);

            return redirect()->to('/modulo3/mrp')->with('success', 'Requerimiento creado correctamente');
        } catch (\Throwable $e) {
            log_message('error', 'Error al crear requerimiento: ' . $e->getMessage());
            return redirect()->to('/modulo3/mrp')->with('error', 'Error al crear el requerimiento');
        }
    }

    /**
     * Editar un requerimiento existente
     * POST /modulo3/mrp/requerimientos/{id}/editar
     */
    public function editarRequerimiento($id)
    {
        $id = (int) $id;
        $reqModel = new \App\Models\MrpRequerimientoModel();

        try {
            $data = [
                'mat' => $this->request->getPost('mat'),
                'u' => $this->request->getPost('u'),
                'necesidad' => (float) $this->request->getPost('necesidad'),
                'stock' => (float) $this->request->getPost('stock'),
                'comprar' => (float) $this->request->getPost('comprar'),
            ];

            $reqModel->update($id, $data);

            return redirect()->to('/modulo3/mrp')->with('success', 'Requerimiento actualizado correctamente');
        } catch (\Throwable $e) {
            log_message('error', 'Error al editar requerimiento: ' . $e->getMessage());
            return redirect()->to('/modulo3/mrp')->with('error', 'Error al actualizar el requerimiento');
        }
    }

    /**
     * Eliminar un requerimiento
     * POST /modulo3/mrp/requerimientos/{id}/eliminar
     */
    public function eliminarRequerimiento($id)
    {
        $id = (int) $id;
        $reqModel = new \App\Models\MrpRequerimientoModel();

        try {
            $reqModel->delete($id);

            return redirect()->to('/modulo3/mrp')->with('success', 'Requerimiento eliminado correctamente');
        } catch (\Throwable $e) {
            log_message('error', 'Error al eliminar requerimiento: ' . $e->getMessage());
            return redirect()->to('/modulo3/mrp')->with('error', 'Error al eliminar el requerimiento');
        }
    }

    /**
     * Obtener un requerimiento en formato JSON
     * GET /modulo3/mrp/requerimientos/{id}/json
     */
    public function requerimientoJson($id)
    {
        $id = (int) $id;
        $reqModel = new \App\Models\MrpRequerimientoModel();

        try {
            $req = $reqModel->find($id);

            if (!$req) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Requerimiento no encontrado']);
            }

            return $this->response->setJSON($req);
        } catch (\Throwable $e) {
            log_message('error', 'Error al obtener requerimiento: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al obtener el requerimiento']);
        }
    }

    /**
     * Crear una nueva orden de compra
     * POST /modulo3/mrp/oc/crear
     */
    public function crearOC()
    {
        $ocModel = new \App\Models\MrpOcModel();

        try {
            $data = [
                'prov' => $this->request->getPost('prov'),
                'mat' => $this->request->getPost('mat'),
                'cant' => (float) $this->request->getPost('cant'),
                'u' => $this->request->getPost('u'),
                'eta' => $this->request->getPost('eta'),
                'pdf_path' => null
            ];

            $ocModel->insert($data);

            return redirect()->to('/modulo3/mrp')->with('success', 'Orden de compra creada correctamente');
        } catch (\Throwable $e) {
            log_message('error', 'Error al crear OC: ' . $e->getMessage());
            return redirect()->to('/modulo3/mrp')->with('error', 'Error al crear la orden de compra');
        }
    }

    /**
     * Editar una orden de compra existente
     * POST /modulo3/mrp/oc/{id}/editar
     */
    public function editarOC($id)
    {
        $id = (int) $id;
        $ocModel = new \App\Models\MrpOcModel();

        try {
            $data = [
                'prov' => $this->request->getPost('prov'),
                'mat' => $this->request->getPost('mat'),
                'cant' => (float) $this->request->getPost('cant'),
                'u' => $this->request->getPost('u'),
                'eta' => $this->request->getPost('eta'),
            ];

            $ocModel->update($id, $data);

            return redirect()->to('/modulo3/mrp')->with('success', 'Orden de compra actualizada correctamente');
        } catch (\Throwable $e) {
            log_message('error', 'Error al editar OC: ' . $e->getMessage());
            return redirect()->to('/modulo3/mrp')->with('error', 'Error al actualizar la orden de compra');
        }
    }

    /**
     * Eliminar una orden de compra
     * POST /modulo3/mrp/oc/{id}/eliminar
     */
    public function eliminarOC($id)
    {
        $id = (int) $id;
        $ocModel = new \App\Models\MrpOcModel();

        try {
            // Obtener la OC para eliminar el PDF si existe
            $oc = $ocModel->find($id);

            if ($oc && !empty($oc['pdf_path'])) {
                $pdfPath = WRITEPATH . $oc['pdf_path'];
                if (file_exists($pdfPath)) {
                    @unlink($pdfPath);
                }
            }

            $ocModel->delete($id);

            return redirect()->to('/modulo3/mrp')->with('success', 'Orden de compra eliminada correctamente');
        } catch (\Throwable $e) {
            log_message('error', 'Error al eliminar OC: ' . $e->getMessage());
            return redirect()->to('/modulo3/mrp')->with('error', 'Error al eliminar la orden de compra');
        }
    }

    /**
     * Obtener una orden de compra en formato JSON
     * GET /modulo3/mrp/oc/{id}/json
     */
    public function ocJson($id)
    {
        $id = (int) $id;
        $ocModel = new \App\Models\MrpOcModel();

        try {
            $oc = $ocModel->find($id);

            if (!$oc) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Orden de compra no encontrada']);
            }

            return $this->response->setJSON($oc);
        } catch (\Throwable $e) {
            log_message('error', 'Error al obtener OC: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al obtener la orden de compra']);
        }
    }

    public function desperdicios()
    {
        $desp = [
            ['fecha' => '2025-09-20', 'op' => 'OP-0012', 'mat' => 'Tela', 'cant' => '15 m', 'motivo' => 'Manchas'],
            ['fecha' => '2025-09-21', 'op' => 'OP-0010', 'mat' => 'Piezas', 'cant' => '8 pz', 'motivo' => 'Corte chueco'],
        ];
        $rep = [
            ['op' => 'OP-0014', 'tarea' => 'Costura lateral', 'pend' => 25, 'resp' => 'María', 'eta' => '2025-09-24'],
            ['op' => 'OP-0011', 'tarea' => 'Rebasteado', 'pend' => 10, 'resp' => 'Luis', 'eta' => '2025-09-23'],
        ];

        return view('modulos/desperdicios', $this->payload([
            'title' => 'Desperdicios y Reprocesos',
            'desp' => $desp,
            'rep' => $rep,
            'notifCount' => 2,
        ]));
    }

    /* =========================================================
     *                   MÓDULO 3 · Mantenimiento
     * ========================================================= */

    public function mantenimientoInventario()
    {
        $maq = [
            ['cod' => 'MC-0001', 'modelo' => 'Juki DDL-8700', 'compra' => '2022-01-10', 'ubic' => 'Línea 1', 'estado' => 'Operativa'],
            ['cod' => 'MC-0002', 'modelo' => 'Brother 8450', 'compra' => '2021-07-05', 'ubic' => 'Línea 3', 'estado' => 'En reparación'],
        ];

        return view('modulos/mantenimiento_inventario', $this->payload([
            'title' => 'Mantenimiento · Inventario',
            'maq' => $maq,
            'notifCount' => 0,
        ]));
    }

    public function mantenimientoPreventivo()
    {
        $prox = [
            ['fecha' => '2025-09-25', 'maq' => 'MC-0001', 'tarea' => 'Lubricación', 'resp' => 'Carlos', 'estado' => 'Próximo'],
            ['fecha' => '2025-09-28', 'maq' => 'MC-0002', 'tarea' => 'Ajuste correa', 'resp' => 'Ana', 'estado' => 'Programado'],
        ];

        return view('modulos/dashboard', $this->payload([
            'title' => 'Mantenimiento · Preventivo',
            'prox' => $prox,
            'notifCount' => 0,
        ]));
    }

    public function mantenimientoCorrectivo()
    {
        $hist = [
            ['fecha' => '2025-09-20', 'maq' => 'MC-0002', 'falla' => 'Correa rota', 'accion' => 'Reemplazo', 'estado' => 'Cerrada'],
            ['fecha' => '2025-09-22', 'maq' => 'MC-0003', 'falla' => 'Vibración', 'accion' => 'Ajuste base', 'estado' => 'En reparación'],
        ];

        return view('modulos/mantenimiento_correctivo', $this->payload([
            'title' => 'Mantenimiento · Correctivo',
            'hist' => $hist,
            'notifCount' => 0,
        ]));
    }

    /* =========================================================
     *                    MÓDULO 3 · Logística
     * ========================================================= */

    public function logisticaPreparacion()
    {
        $cons = [
            ['pedido' => 'PED-0041', 'op' => 'OP-0011', 'cajas' => 3, 'peso' => 25, 'dest' => 'Cliente B'],
            ['pedido' => 'PED-0042', 'op' => 'OP-0012', 'cajas' => 6, 'peso' => 54, 'dest' => 'Cliente C'],
        ];

        return view('modulos/logistica_preparacion', $this->payload([
            'title' => 'Logística · Preparación de Envíos',
            'cons' => $cons,
            'notifCount' => 0,
        ]));
    }

    public function logisticaGestion()
    {
        $env = [
            ['fecha' => '2025-09-21', 'empresa' => 'DHL', 'guia' => 'JD0148899001', 'estado' => 'En tránsito'],
            ['fecha' => '2025-09-22', 'empresa' => 'FedEx', 'guia' => 'FE99223311', 'estado' => 'Entregado'],
        ];

        return view('modulos/logistica_gestion', $this->payload([
            'title' => 'Logística · Gestión de Envíos',
            'env' => $env,
            'notifCount' => 0,
        ]));
    }

    public function logisticaDocumentos()
    {
        $docs = [
            ['tipo' => 'Factura', 'num' => 'FAC-2025-001', 'fecha' => '2025-09-21', 'estado' => 'Emitida'],
            ['tipo' => 'Lista de empaque', 'num' => 'PL-2025-009', 'fecha' => '2025-09-21', 'estado' => 'Emitida'],
        ];

        return view('modulos/logistica_documentos', $this->payload([
            'title' => 'Logística · Documentos de Embarque',
            'docs' => $docs,
            'notifCount' => 0,
        ]));
    }

    /**
     * Vista de inspección (listado para iniciar evaluaciones)
     */
    public function inspeccion()
    {
        return view('modulos/inspeccion', $this->payload([
            'title' => 'Inspección de Producción',
            'notifCount' => 0,
        ]));
    }

    /* =========================================================
     *                       MÓDULO 1
     * ========================================================= */

    public function m1_index()
    {
        return $this->m1_pedidos();
    }

    /** Vista listado de pedidos (Módulo 1). */
    public function m1_pedidos()
    {
        $pedidoModel = new \App\Models\PedidoModel();
        // Filtrar pedidos por la maquiladora del usuario autenticado
        $maquiladoraId = session()->get('maquiladora_id');
        $pedidos = $pedidoModel->getListadoPedidos($maquiladoraId);

        return view('modulos/pedidos', $this->payload([
            'title' => 'Módulo 1 · Pedidos',
            'pedidos' => $pedidos,
            'notifCount' => 0,
        ]));
    }

    public function m1_ordenes()
    {
        // Usar el modelo centralizado para evitar diferencias de esquema
        $ordenes = [];
        $maquiladoras = [];
        $maquiladoraId = session()->get('maquiladora_id');

        try {
            $opModel = new \App\Models\OrdenProduccionModel();
            // Filtrar OP por maquiladora del usuario autenticado
            $ordenes = $opModel->getListado($maquiladoraId);

            // Obtener lista de otras maquiladoras para compartir
            $db = \Config\Database::connect();
            // Intentar minúsculas
            // Intentar minúsculas
            try {
                $maquiladoras = $db->table('maquiladora')
                    ->select('idmaquiladora as id, Nombre_Maquila as nombre')
                    ->where('idmaquiladora !=', $maquiladoraId)
                    ->where('status', 1) // Solo activas
                    ->where('tipo', 'empresa externa') // Solo Empresa Externa
                    ->orderBy('Nombre_Maquila', 'ASC')
                    ->get()
                    ->getResultArray();
            } catch (\Throwable $e) {
                // Intentar con mayúsculas
                $maquiladoras = $db->table('Maquiladora')
                    ->select('idmaquiladora as id, Nombre_Maquila as nombre')
                    ->where('idmaquiladora !=', $maquiladoraId)
                    ->where('status', 1)
                    ->where('tipo', 'Empresa Externa') // Solo Empresa Externa
                    ->orderBy('Nombre_Maquila', 'ASC')
                    ->get()
                    ->getResultArray();
            }

        } catch (\Throwable $e) {
            $ordenes = [];
        }

        return view('modulos/m1_ordenes', $this->payload([
            'title' => 'Módulo 1 · Órdenes',
            'ordenes' => $ordenes,
            'maquiladoras' => $maquiladoras,
            'currentMaquiladoraId' => $maquiladoraId,
            'notifCount' => 0,
        ]));
    }

    /**
     * Compartir una orden de producción con otra maquiladora
     */
    public function m1_ordenes_compartir()
    {
        $opId = (int) $this->request->getPost('opId');
        $maquiladoraId = (int) $this->request->getPost('maquiladoraId');

        if ($opId <= 0 || $maquiladoraId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'Datos inválidos']);
        }

        $db = \Config\Database::connect();
        try {
            // Verificar que la OP exista y pertenezca a la maquiladora actual (seguridad básica)
            $currentMaquiladoraId = session()->get('maquiladora_id');

            // Actualizar
            $updated = false;
            // Intentar tabla minúsculas
            try {
                $builder = $db->table('orden_produccion');
                if ($currentMaquiladoraId) {
                    $builder->where('maquiladoraID', $currentMaquiladoraId);
                }
                $builder->where('id', $opId)->update(['maquiladoraCompartidaID' => $maquiladoraId]);

                // Verificar si se actualizó algo (o si ya tenía ese valor, affectedRows podría ser 0, pero la query corrió)
                // Asumimos éxito si no lanza excepción, aunque lo ideal es checkear affectedRows si cambia
                $updated = true;
            } catch (\Throwable $e) {
                // Fallback mayúsculas
                try {
                    $builder = $db->table('OrdenProduccion');
                    if ($currentMaquiladoraId) {
                        $builder->where('maquiladoraID', $currentMaquiladoraId);
                    }
                    $builder->where('id', $opId)->update(['maquiladoraCompartidaID' => $maquiladoraId]);
                    $updated = true;
                } catch (\Throwable $e2) {
                    $updated = false;
                    log_message('error', 'Error al compartir OP: ' . $e2->getMessage());
                }
            }

            if ($updated) {
                return $this->response->setJSON(['success' => true, 'message' => 'Orden compartida correctamente']);
            } else {
                return $this->response->setStatusCode(400)->setJSON(['success' => false, 'message' => 'No se pudo actualizar la orden']);
            }

        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
        }
    }

    public function m1_produccion()
    {
        $empleadoId = null;
        try {
            $userId = (int) (session()->get('user_id') ?? 0);
            if ($userId > 0) {
                $emp = (new \App\Models\EmpleadoModel())
                    ->where('idusuario', $userId)
                    ->select('id')
                    ->first();
                if ($emp && isset($emp['id'])) {
                    $empleadoId = (int) $emp['id'];
                }
            }
        } catch (\Throwable $e) {
            $empleadoId = null;
        }

        return view('modulos/produccion', $this->payload([
            'title' => 'Módulo 1 · Producción',
            'notifCount' => 0,
            'empleadoId' => $empleadoId,
        ]));
    }

    public function m1_agregar()
    {
        if ($this->request->getMethod() === 'post') {
            // Procesar formulario
            return redirect()->to('/modulo1/pedidos')->with('success', 'Pedido agregado correctamente');
        }

        return view('modulos/agregar_pedido', $this->payload([
            'title' => 'Módulo 1 · Agregar Pedido',
            'notifCount' => 0,
        ]));
    }

    /** Formulario editar pedido (GET/POST). */
    public function m1_editar($id = null)
    {
        $pedidoModel = new \App\Models\PedidoModel();

        if (strtolower($this->request->getMethod()) === 'post') {
            // Obtener ID desde POST si no viene en la URL
            $idPost = (int) ($this->request->getPost('id') ?? 0);
            if (!$id && $idPost) {
                $id = $idPost;
            }

            $acceptsJson = stripos((string) $this->request->getHeaderLine('accept'), 'application/json') !== false;
            if (!$id) {
                if ($this->request->isAJAX() || $acceptsJson) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'success' => false,
                        'message' => 'ID de pedido ausente',
                    ]);
                }
            }

            // Normalizar total
            $totalPost = $this->request->getPost('total');
            if (is_string($totalPost)) {
                $totalPost = str_replace(',', '', $totalPost);
            }
            $totalPost = ($totalPost === '' || $totalPost === null) ? null : (float) $totalPost;

            $ocData = [
                'folio' => $this->request->getPost('folio') ?? null,
                'fecha' => $this->request->getPost('fecha') ?? null,
                'estatus' => $this->request->getPost('estatus'),
                'moneda' => $this->request->getPost('moneda') ?? null,
                'total' => $totalPost,
            ];

            if (empty($ocData['fecha'])) {
                $ocData['fecha'] = date('Y-m-d');
            }
            if (!empty($ocData['fecha']) && is_string($ocData['fecha'])) {
                $f = trim($ocData['fecha']);
                if (strpos($f, '/') !== false) {
                    $parts = preg_split('/[\/]/', $f);
                    if (count($parts) === 3) {
                        $dd = (int) $parts[0];
                        $mm = (int) $parts[1];
                        $yy = (int) $parts[2];
                        if ($dd > 0 && $mm > 0 && $yy > 0) {
                            $ocData['fecha'] = sprintf('%04d-%02d-%02d', $yy, $mm, $dd);
                        }
                    }
                }
                if ($ocData['fecha'] === '' || strtolower($ocData['fecha']) === 'invalid date') {
                    $ocData['fecha'] = date('Y-m-d');
                }
            }
            foreach (['folio', 'estatus', 'moneda'] as $k) {
                if (!isset($ocData[$k]) || $ocData[$k] === '') {
                    unset($ocData[$k]);
                }
            }
            if ($ocData['total'] === null || $ocData['total'] === '') {
                unset($ocData['total']);
            }

            try {
                $rowsOC = 0;
                $rowsOP = 0;

                if ($id) {
                    $db = \Config\Database::connect();

                    // Actualizar OC
                    if (!empty($ocData)) {
                        $updated = false;
                        try {
                            $updated = $db->table('orden_compra')->where('id', (int) $id)->update($ocData);
                            $rowsOC = $db->affectedRows();
                        } catch (\Throwable $eQB1) {
                            $updated = false;
                        }
                        if (!$updated) {
                            try {
                                $db->table('OrdenCompra')->where('id', (int) $id)->update($ocData);
                                $rowsOC = $db->affectedRows();
                            } catch (\Throwable $eQB2) {
                            }
                        }
                    }

                    // Actualizar/crear OP
                    $opCantidadPlan = $this->request->getPost('op_cantidadPlan');
                    $opCantidadBultos = $this->request->getPost('op_cantidadBultos');
                    $disenoVersionId = $this->request->getPost('disenoVersionId');
                    $disenoId = $this->request->getPost('disenoId');

                    if ((!$disenoVersionId || $disenoVersionId == '') && $disenoId) {
                        try {
                            $verRow = $db->query('SELECT id FROM diseno_version WHERE disenoId = ? ORDER BY id DESC LIMIT 1', [(int) $disenoId])->getRowArray();
                            if (!$verRow) {
                                $verRow = $db->query('SELECT id FROM DisenoVersion WHERE disenoId = ? ORDER BY id DESC LIMIT 1', [(int) $disenoId])->getRowArray();
                            }
                            if ($verRow && isset($verRow['id'])) {
                                $disenoVersionId = $verRow['id'];
                            }
                        } catch (\Throwable $eVer) {
                        }
                    }

                    $opFechaFinPlanRaw = $this->request->getPost('op_fechaFinPlan');
                    $opFechaFinPlan = null;
                    if ($opFechaFinPlanRaw !== null && $opFechaFinPlanRaw !== '') {
                        $ff = trim((string) $opFechaFinPlanRaw);
                        if (strpos($ff, '/') !== false) {
                            $pp = preg_split('/[\/]/', $ff);
                            if (count($pp) === 3) {
                                $dd = (int) $pp[0];
                                $mm = (int) $pp[1];
                                $yy = (int) $pp[2];
                                if ($dd > 0 && $mm > 0 && $yy > 0) {
                                    $opFechaFinPlan = sprintf('%04d-%02d-%02d', $yy, $mm, $dd);
                                }
                            }
                        } else {
                            $opFechaFinPlan = $ff;
                        }
                    }

                    if ($opCantidadPlan !== null || $opCantidadBultos !== null || $disenoVersionId !== null || $opFechaFinPlan !== null) {
                        $op = null;
                        try {
                            $op = $db->query('SELECT * FROM orden_produccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [(int) $id])->getRowArray();
                        } catch (\Throwable $e1) {
                        }
                        if (!$op) {
                            try {
                                $op = $db->query('SELECT * FROM OrdenProduccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [(int) $id])->getRowArray();
                            } catch (\Throwable $e2) {
                            }
                        }

                        if ($op) {
                            $set = [];
                            if ($opCantidadPlan !== null && $opCantidadPlan !== '') {
                                $set['cantidadPlan'] = (int) $opCantidadPlan;
                            }
                            if ($opCantidadBultos !== null && $opCantidadBultos !== '') {
                                $set['cantidadBultos'] = (int) $opCantidadBultos;
                            }
                            if ($disenoVersionId !== null && (int) $disenoVersionId > 0) {
                                $set['disenoVersionId'] = (int) $disenoVersionId;
                            }
                            if ($opFechaFinPlan !== null && $opFechaFinPlan !== '') {
                                $set['fechaFinPlan'] = $opFechaFinPlan;
                            }
                            if (!empty($set)) {
                                $db->table('orden_produccion')->where('id', (int) $op['id'])->update($set);
                                $rowsOP = max($rowsOP, $db->affectedRows());
                            }
                        } else {
                            $newOp = [
                                'ordenCompraId' => (int) $id,
                                'disenoVersionId' => ($disenoVersionId !== null && (int) $disenoVersionId > 0) ? (int) $disenoVersionId : null,
                                'folio' => null,
                                'cantidadPlan' => ($opCantidadPlan !== null && $opCantidadPlan !== '') ? (int) $opCantidadPlan : null,
                                'cantidadBultos' => ($opCantidadBultos !== null && $opCantidadBultos !== '') ? (int) $opCantidadBultos : null,
                                'fechaInicioPlan' => null,
                                'fechaFinPlan' => ($opFechaFinPlan !== null && $opFechaFinPlan !== '') ? $opFechaFinPlan : null,
                                'status' => 'Planeada',
                            ];
                            $db->table('orden_produccion')->insert($newOp);
                            $rowsOP = $db->affectedRows();
                        }

                        // Recalcular total desde diseño
                        try {
                            $opNow = $db->query('SELECT * FROM orden_produccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [(int) $id])->getRowArray();
                            if ($opNow) {
                                $dvId = $opNow['disenoVersionId'] ?? null;
                                $cant = $opNow['cantidadPlan'] ?? null;
                                if ($dvId && $cant) {
                                    $precio = null;
                                    try {
                                        $rowP = $db->query('SELECT d.precio_unidad FROM diseno_version dv LEFT JOIN diseno d ON d.id = dv.disenoId WHERE dv.id = ?', [(int) $dvId])->getRowArray();
                                        $precio = $rowP['precio_unidad'] ?? null;
                                    } catch (\Throwable $eP1) {
                                    }
                                    if ($precio !== null) {
                                        $calcTotal = (float) $precio * (float) $cant;
                                        $db->table('orden_compra')->where('id', (int) $id)->update(['total' => $calcTotal]);
                                        $rowsOC = max($rowsOC, $db->affectedRows());
                                    }
                                }
                            }
                        } catch (\Throwable $eR) {
                        }

                        // ===== Detalle por tallas =====
                        $tallasRaw = $this->request->getPost('tallas');
                        $tallas = null;

                        if (is_array($tallasRaw) && !empty($tallasRaw)) {
                            $tallas = $tallasRaw;
                        } elseif (is_string($tallasRaw)) {
                            $trimmed = trim($tallasRaw);
                            if ($trimmed !== '' && ($trimmed[0] === '[' || $trimmed[0] === '{')) {
                                $decoded = json_decode($tallasRaw, true);
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                                    $tallas = $decoded;
                                }
                            }
                        }

                        if (is_array($tallas) && !empty($tallas)) {
                            $rowsValid = [];
                            foreach ($tallas as $t) {
                                if (!is_array($t)) {
                                    continue;
                                }
                                $idSexo = $t['id_sexo'] ?? $t['sexo_id'] ?? null;
                                $idTalla = $t['id_talla'] ?? $t['talla_id'] ?? null;
                                $cant = $t['cantidad'] ?? $t['cant'] ?? null;
                                $cantInt = (int) $cant;
                                if ($idSexo !== null && $idTalla !== null && $cantInt > 0) {
                                    $rowsValid[] = [
                                        'id_sexo' => (int) $idSexo,
                                        'id_talla' => (int) $idTalla,
                                        'cantidad' => $cantInt,
                                    ];
                                }
                            }

                            if (!empty($rowsValid)) {
                                $opForTallas = $db->query('SELECT id FROM orden_produccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [(int) $id])->getRowArray();
                                if ($opForTallas && isset($opForTallas['id'])) {
                                    $opId = (int) $opForTallas['id'];

                                    // Borrar anteriores
                                    try {
                                        $db->table('pedido_tallas_detalle')->where('ordenProduccionId', $opId)->delete();
                                    } catch (\Throwable $e) {
                                        try {
                                            $db->table('OrdenProduccionTalla')->where('ordenProduccionId', $opId)->delete();
                                        } catch (\Throwable $e2) {
                                        }
                                    }

                                    foreach ($rowsValid as $rv) {
                                        $rowTalla = [
                                            'ordenProduccionId' => $opId,
                                            'id_sexo' => $rv['id_sexo'],
                                            'id_talla' => $rv['id_talla'],
                                            'cantidad' => $rv['cantidad'],
                                        ];
                                        try {
                                            $db->table('pedido_tallas_detalle')->insert($rowTalla);
                                        } catch (\Throwable $eT1) {
                                            try {
                                                $db->table('OrdenProduccionTalla')->insert($rowTalla);
                                            } catch (\Throwable $eT2) {
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if ($this->request->isAJAX() || $acceptsJson) {
                    $db = \Config\Database::connect();
                    $ocRow = $db->query('SELECT id, folio, fecha, estatus, moneda, total FROM orden_compra WHERE id = ?', [(int) $id])->getRowArray();
                    $opRow = $db->query('SELECT * FROM orden_produccion WHERE ordenCompraId = ? ORDER BY id DESC LIMIT 1', [(int) $id])->getRowArray();
                    return $this->response->setJSON([
                        'success' => true,
                        'message' => 'Pedido actualizado correctamente',
                        'rowsOC' => $rowsOC,
                        'rowsOP' => $rowsOP,
                        'oc' => $ocRow,
                        'op' => $opRow,
                    ]);
                }

                return redirect()->to('/modulo1/pedidos')->with('success', 'Pedido actualizado correctamente');
            } catch (\Throwable $e) {
                $acceptsJson = stripos((string) $this->request->getHeaderLine('accept'), 'application/json') !== false;
                if ($this->request->isAJAX() || $acceptsJson) {
                    return $this->response->setStatusCode(500)->setJSON([
                        'success' => false,
                        'message' => $e->getMessage(),
                    ]);
                }
                return redirect()->to('/modulo1/pedidos')->with('error', 'Error al actualizar: ' . $e->getMessage());
            }
        }

        // GET: cargar datos del pedido para la vista de edición
        $pedido = $pedidoModel->getPedidoPorId((int) $id);

        // Cargar tallas existentes
        $tallas = [];
        if (isset($pedido['op_id'])) {
            $tallas = $pedidoModel->getTallasPorOP($pedido['op_id']);
        }
        $pedido['tallas'] = $tallas;

        // Cargar catálogos de tallas y sexos
        $db = \Config\Database::connect();
        $catalogoTallas = $db->table('tallas')->get()->getResultArray();
        $catalogoSexos = $db->table('sexo')->get()->getResultArray();

        return view('modulos/editarpedido', $this->payload([
            'title' => 'Módulo 1 · Editar Pedido',
            'pedido' => $pedido,
            'id' => $id,
            'notifCount' => 0,
            'catalogoTallas' => $catalogoTallas,
            'catalogoSexos' => $catalogoSexos,
        ]));
    }

    /** Vista detalle de pedido (usa PedidoModel::getPedidoDetalle). */
    public function m1_detalles($id = null)
    {
        $pedidoModel = new \App\Models\PedidoModel();

        // Traer detalle completo para incluir cliente, direcciones, y diseño asignado
        $pedido = $pedidoModel->getPedidoDetalle((int) $id);

        // Para depuración: registrar datos del pedido
        log_message('debug', 'Datos del pedido: ' . print_r([
            'id' => $id,
            'op_id' => $pedido['op_id'] ?? 'No definido',
            'tallas' => $pedido['tallas'] ?? 'No definido'
        ], true));

        // Cargar las tallas si existe el ID de la orden de producción
        $pedido['tallas_detalle'] = [];
        if (isset($pedido['op_id']) && $pedido['op_id']) {
            $tallas = $pedidoModel->getTallasPorOP($pedido['op_id']);
            if (!empty($tallas)) {
                $pedido['tallas_detalle'] = $tallas;
            }

            // Si no hay tallas en tallas_detalle pero sí en el array de tallas
            if (empty($pedido['tallas_detalle']) && !empty($pedido['tallas'])) {
                $pedido['tallas_detalle'] = $pedido['tallas'];
            }

            // Para depuración
            log_message('debug', 'Tallas cargadas: ' . print_r($pedido['tallas_detalle'], true));
        }

        // Normalizar campos esperados por la vista
        if (is_array($pedido)) {
            if (!isset($pedido['empresa']) && isset($pedido['cliente']['nombre'])) {
                $pedido['empresa'] = $pedido['cliente']['nombre'];
            }
        }

        // Cargar catálogos para los selectores
        $db = \Config\Database::connect();
        $catalogoTallas = $db->table('tallas')->get()->getResultArray();
        $catalogoSexos = $db->table('sexo')->get()->getResultArray();

        return view('modulos/detalle_pedido', $this->payload([
            'title' => 'Módulo 1 · Detalle del Pedido',
            'pedido' => $pedido,
            'id' => $id,
            'notifCount' => 0,
            'catalogoTallas' => $catalogoTallas,
            'catalogoSexos' => $catalogoSexos,
            'debug_data' => [
                'tallas_detalle' => $pedido['tallas_detalle'] ?? [],
                'op_id' => $pedido['op_id'] ?? null
            ]
        ]));
    }

    /**
     * Formulario de evaluación por orden (desde Inspección)
     */
    public function m1_evaluar($id = null)
    {
        if (!$id) {
            return redirect()->to('/modulo3/inspeccion');
        }

        // Datos de ejemplo; en producción vendrán de la BD
        $ordenData = [
            'folio' => 'OP-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'cantidadPlan' => 100,
        ];

        $defectos = [
            ['id' => 1, 'codigo' => 'D01', 'description' => 'Costura abierta', 'severidad' => 'Media'],
            ['id' => 2, 'codigo' => 'D02', 'description' => 'Mancha', 'severidad' => 'Baja'],
            ['id' => 3, 'codigo' => 'D03', 'description' => 'Corte chueco', 'severidad' => 'Alta'],
        ];

        return view('evaluar', $this->payload([
            'title' => 'Evaluar Orden',
            'orden_id' => $id,
            'orden_data' => $ordenData,
            'defectos' => $defectos,
            'notifCount' => 0,
        ]));
    }

    /**
     * Recepción del formulario de evaluación
     */
    public function m1_guardarEvaluacion()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/modulo3/inspeccion');
        }

        // Aquí se procesaría y guardaría la evaluación
        // $data = $this->request->getPost();

        return redirect()->to('/modulo3/inspeccion')->with('success', 'Evaluación guardada correctamente');
    }

    /* =========================================================
     *                       MUESTRAS
     * ========================================================= */
    public function muestras()
    {
        // Vista de listado de muestras (puede reusar una tabla similar a inspección)
        return view('modulos/muestras', $this->payload([
            'title' => 'Muestras de Prototipos',
            'notifCount' => 0,
        ]));
    }

    public function muestras_evaluar($id = null)
    {
        if (!$id) {
            return redirect()->to('/muestras');
        }

        // Datos de ejemplo; en producción vendrán de la BD
        $muestraData = [
            'prototipo_codigo' => 'PR-' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            'solicitadaPor' => 'Cliente Demo',
            'archivoCadUrl' => '',
            'archivoPatronUrl' => '',
        ];

        $responsables = [
            ['id' => 1, 'nombre' => 'Ana Control Calidad'],
            ['id' => 2, 'nombre' => 'Luis Supervisor'],
        ];

        return view('modulos/evaluar_muestra', $this->payload([
            'title' => 'Evaluar Muestra',
            'muestra_id' => $id,
            'muestra_data' => $muestraData,
            'responsables' => $responsables,
            'notifCount' => 0,
        ]));
    }

    public function muestras_guardarEvaluacion()
    {
        if ($this->request->getMethod() !== 'post') {
            return redirect()->to('/muestras');
        }

        // $data = $this->request->getPost(); // procesar y guardar
        return redirect()->to('/muestras')->with('success', 'Evaluación de muestra guardada correctamente');
    }
    public function m1_ordenesclientes()
    {
        $pedidoModel = new \App\Models\PedidoModel();
        $pedidos = $pedidoModel->getListadoPedidos();

        return view('modulos/ordenesclientes', $this->payload([
            'title' => 'Módulo 1 · Órdenes de Clientes',
            'pedidos' => $pedidos,
            'notifCount' => 0,
        ]));
    }
    public function m1_perfilempleado()
    {
        $uid = (int) (session()->get('user_id') ?? 0);
        $empleado = [];
        try {
            $db = \Config\Database::connect();
            // Query principal (snake case)
            $row = null;
            try {
                $row = $db->query(
                    'SELECT 
                        e.noEmpleado, e.nombre, e.apellido, e.puesto,
                        e.email, e.telefono, e.domicilio,
                        e.fecha_nac, e.curp, e.foto, e.Forma_Pago AS Forma_pago,
                        TIMESTAMPDIFF(YEAR, e.fecha_nac, CURDATE()) AS edad,
                        u.username, u.correo, u.active AS usuario_activo,
                        u.maquiladoraIdFK AS maquiladoraID,
                        m.Nombre_Maquila AS nombre_maquiladora
                     FROM empleado e 
                     INNER JOIN users u ON e.idusuario = u.id
                     LEFT JOIN maquiladora m ON u.maquiladoraIdFK = m.idmaquiladora
                     WHERE u.id = ? LIMIT 1',
                    [$uid]
                )->getRowArray();
            } catch (\Throwable $e1) {
                $row = null;
            }
            // Variantes por mayúsculas/campos alternos
            if (!$row) {
                try {
                    $row = $db->query(
                        'SELECT 
                            e.noEmpleado, e.nombre, e.apellido, e.puesto,
                            e.email, e.telefono, e.domicilio,
                            e.fecha_nac, e.curp, e.foto, e.Forma_Pago AS Forma_pago,
                            TIMESTAMPDIFF(YEAR, e.fecha_nac, CURDATE()) AS edad,
                            u.username, u.correo, u.active AS usuario_activo,
                            u.maquiladoraIdFK AS maquiladoraID,
                            m.Nombre_Maquila AS nombre_maquiladora
                         FROM Empleado e 
                         INNER JOIN Users u ON e.idusuario = u.id
                         LEFT JOIN Maquiladora m ON u.maquiladoraIdFK = m.idmaquiladora
                         WHERE u.id = ? LIMIT 1',
                        [$uid]
                    )->getRowArray();
                } catch (\Throwable $e2) {
                    $row = null;
                }
            }
            if ($row) {
                // Completar faltantes desde sesión (email/puesto)
                if (!isset($row['email']) || $row['email'] === null || $row['email'] === '') {
                    $row['email'] = $row['correo']
                        ?? (string) (session()->get('user_email') ?? '')
                        ?? (string) (session()->get('correo') ?? '');
                }

                // Codificar la imagen en base64 si existe
                if (!empty($row['foto'])) {
                    $row['foto'] = base64_encode($row['foto']);
                }
                if (!isset($row['puesto']) || $row['puesto'] === null || $row['puesto'] === '') {
                    $primary = session()->get('primary_role');
                    $rnames = session()->get('role_names');
                    $row['puesto'] = (string) (
                        ($primary ?: (is_array($rnames) && isset($rnames[0]) ? $rnames[0] : null))
                        ?? session()->get('user_role')
                        ?? session()->get('status')
                        ?? ''
                    );
                }
                $empleado = $row;
            }
        } catch (\Throwable $e) {
            $empleado = [];
        }

        return view('modulos/perfilempleado', $this->payload([
            'title' => 'Módulo 1 · Perfil de Empleado',
            'empleado' => $empleado,
            'notifCount' => 0,
        ]));
    }

    /**
     * Guardar datos de empleado del usuario logueado (insert/update).
     * Entrada POST: nombre, apellido, email, telefono, domicilio, puesto, fecha_nac, curp, noEmpleado
     * Respuesta JSON { success, updated|inserted }
     */
    public function m1_empleado_guardar()
    {
        if (strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Método no permitido']);
        }
        $uid = (int) (session()->get('user_id') ?? 0);
        if ($uid <= 0) {
            return $this->response->setStatusCode(401)->setJSON(['success' => false, 'message' => 'Sesión no válida']);
        }

        $in = static function ($k, $t = 'str') {
            $v = trim((string) service('request')->getPost($k));
            if ($t === 'date') {
                return $v === '' ? null : $v;
            }
            return $v === '' ? null : $v;
        };

        // Procesar la foto si se subió
        $fotoData = null;
        $fotoFile = $this->request->getFile('foto');
        if ($fotoFile && $fotoFile->isValid() && !$fotoFile->hasMoved()) {
            // Validar tipo de archivo
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($fotoFile->getMimeType(), $allowedTypes)) {
                // Leer el contenido del archivo
                $fotoData = file_get_contents($fotoFile->getTempName());
            }
        }

        $row = [
            'noEmpleado' => $in('noEmpleado'),
            'nombre' => $in('nombre'),
            'apellido' => $in('apellido'),
            'email' => $in('email'),
            'telefono' => $in('telefono'),
            'domicilio' => $in('domicilio'),
            'puesto' => $in('puesto'),
            'fecha_nac' => $in('fecha_nac', 'date'),
            'curp' => $in('curp'),
            'Forma_pago' => $in('Forma_pago'),
            'activo' => 1,
        ];

        // Asignar siempre la maquiladora del usuario autenticado (si existe en sesión)
        $maquiladoraId = session()->get('maquiladora_id');
        if ($maquiladoraId) {
            $row['maquiladoraID'] = (int) $maquiladoraId;
        }

        // Solo actualizar la foto si se subió una nueva
        if ($fotoData !== null) {
            $row['foto'] = $fotoData;
        }

        $db = \Config\Database::connect();
        try {
            // Defaults desde sesión si faltan
            if (empty($row['noEmpleado'])) {
                $row['noEmpleado'] = 'EMP0' . $uid;
            }
            if (empty($row['email'])) {
                $row['email'] = (string) (session()->get('user_email') ?? '');
            }
            if (empty($row['puesto'])) {
                $primary = session()->get('primary_role');
                $rnames = session()->get('role_names');
                $row['puesto'] = (string) (
                    ($primary ?: (is_array($rnames) && isset($rnames[0]) ? $rnames[0] : null))
                    ?? session()->get('user_role')
                    ?? session()->get('status')
                    ?? ''
                );
            }
            // ¿Existe empleado ligado a este usuario?
            $emp = null;
            try {
                $emp = $db->table('empleado')
                    ->select('*')
                    ->select('foto') // Asegurarse de obtener la foto actual
                    ->where('idusuario', $uid)
                    ->get()
                    ->getRowArray();
            } catch (\Throwable $e) {
                $emp = null;
            }

            if (!$emp) {
                try {
                    $emp = $db->table('Empleado')
                        ->select('*')
                        ->select('foto') // Asegurarse de obtener la foto actual
                        ->where('idusuario', $uid)
                        ->get()
                        ->getRowArray();
                } catch (\Throwable $e2) {
                    $emp = null;
                }
            }

            if ($emp) {
                // Update
                $ok = false;
                $tables = ['empleado', 'Empleado'];
                foreach ($tables as $t) {
                    try {
                        $ok = $db->table($t)->where('idusuario', $uid)->update($row);
                        if ($ok)
                            break;
                    } catch (\Throwable $e) { /* try next */
                    }
                }
                if (!$ok) {
                    throw new \Exception('No se pudo actualizar');
                }
                return $this->response->setJSON(['success' => true, 'updated' => true]);
            } else {
                // Insert con vínculo
                $row['idusuario'] = $uid;
                $ok = false;
                $tables = ['empleado', 'Empleado'];
                foreach ($tables as $t) {
                    try {
                        $ok = $db->table($t)->insert($row);
                        if ($ok)
                            break;
                    } catch (\Throwable $e) { /* try next */
                    }
                }
                if (!$ok) {
                    throw new \Exception('No se pudo insertar');
                }
                return $this->response->setJSON(['success' => true, 'inserted' => true]);
            }
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /* =========================================================
     *                       MÓDULO 2
     * ========================================================= */

    public function m2_index()
    {
        return $this->m2_perfildisenador();
    }

    public function m2_perfildisenador()
    {
        $disenador = [
            'nombre' => session()->get('user_name') ?? 'Diseñador',
            'email' => session()->get('user_email') ?? 'diseñador@fabrica.com',
            'especialidad' => 'Diseño Textil',
            'experiencia' => '5 años',
            'proyectos_completados' => 45,
            'proyectos_activos' => 3,
        ];

        return view('modulos/perfildisenador', $this->payload([
            'title' => 'Módulo 2 · Perfil del Diseñador',
            'disenador' => $disenador,
            'notifCount' => 0,
        ]));
    }

    public function m2_catalogodisenos()
    {
        // Conectar a BD y traer catálogo real
        $disenoModel = new \App\Models\DisenoModel();
        // Mostrar todas las versiones filtradas por la maquiladora del usuario
        $maquiladoraId = session()->get('maquiladora_id');
        $disenos = $disenoModel->getCatalogoDisenosTodasVersiones($maquiladoraId);

        return view('modulos/catalogodisenos', $this->payload([
            'title' => 'Módulo 2 · Catálogo de Diseños',
            'disenos' => $disenos,
            'notifCount' => 0,
        ]));
    }

    public function m2_agregardiseno()
    {
        if ($this->request->getMethod() === 'post') {
            $db = \Config\Database::connect();

            // Datos del diseño
            $dataDiseno = [
                'nombre' => trim((string) $this->request->getPost('nombre')),
                'descripcion' => trim((string) $this->request->getPost('descripcion')),
            ];
            $maquiladoraId = session()->get('maquiladora_id');
            if ($maquiladoraId) {
                $dataDiseno['maquiladoraID'] = (int) $maquiladoraId;
            }

            // Datos de la versión
            $materialesTxt = trim((string) $this->request->getPost('materiales'));
            $cortesTxt = trim((string) $this->request->getPost('cortes'));
            $notas = "Materiales:\n$materialesTxt\n\nCortes:\n$cortesTxt";

            $dataVersion = [
                'version' => '1.0',
                'fecha' => date('Y-m-d'),
                'notas' => $notas,
                'aprobado' => 0
            ];

            // Archivos (BLOBs)
            try {
                $fotoFile = $this->request->getFile('foto');
                if ($fotoFile && $fotoFile->isValid()) {
                    $dataVersion['foto'] = file_get_contents($fotoFile->getTempName());
                }
            } catch (\Throwable $e) {
            }

            try {
                $patronFile = $this->request->getFile('patron');
                if ($patronFile && $patronFile->isValid()) {
                    $dataVersion['patron'] = file_get_contents($patronFile->getTempName());
                }
            } catch (\Throwable $e) {
            }

            $db->transStart();
            try {
                // Insertar Diseño
                $db->table('diseno')->insert($dataDiseno);
                $idDiseno = $db->insertID();

                // Insertar Versión
                $dataVersion['disenoId'] = $idDiseno;
                $db->table('diseno_version')->insert($dataVersion);
                $idVersion = $db->insertID();

                // Crear Prototipo y Muestra (automático)
                $userName = (string) (session()->get('user_name') ?? '');
                $rowProt = [
                    'disenoVersionId' => $idVersion,
                    'fechainicio' => null,
                    'estado' => null
                ];
                $db->table('prototipo')->insert($rowProt);
                $prototipoId = $db->insertID();

                $rowMuestra = [
                    'prototipoId' => $prototipoId,
                    'solicitadaPor' => $userName ?: null,
                    'fechaSolicitud' => date('Y-m-d'),
                    'estado' => 'Pendiente'
                ];
                $db->table('muestra')->insert($rowMuestra);
                $muestraId = $db->insertID();

                $db->table('aprobacion_muestra')->insert([
                    'muestraId' => $muestraId,
                    'decision' => 'Pendiente'
                ]);

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Error al guardar en base de datos.');
                }

                return redirect()->to('/modulo2/catalogodisenos')->with('success', 'Diseño agregado correctamente');
            } catch (\Throwable $e) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
            }
        }

        return view('modulos/agregardiseno', $this->payload([
            'title' => 'Módulo 2 · Agregar Diseño',
            'notifCount' => 0,
        ]));
    }

    public function m2_editardiseno($id = null)
    {
        // Validar que se proporcione un ID
        if (!$id) {
            return redirect()->to('/modulo2/catalogodisenos')->with('error', 'ID de diseño no válido');
        }

        // Traer detalle desde BD usando DisenoModel
        $disenoModel = new \App\Models\DisenoModel();
        $detalle = $disenoModel->getDisenoDetalle((int) $id);
        if (!$detalle) {
            return redirect()->to('/modulo2/catalogodisenos')->with('error', 'Diseño no encontrado');
        }

        // Adaptar al formato que espera la vista editardiseno
        $diseno = [
            'id' => $detalle['id'],
            'nombre' => $detalle['nombre'],
            'descripcion' => $detalle['descripcion'],
            'materiales' => implode("\n", $detalle['materiales'] ?? []),
            'cortes' => $detalle['notas'] ?? '',
            'archivo' => $detalle['archivoCadUrl'] ?? '',
        ];

        return view('modulos/editardiseno', $this->payload([
            'title' => 'Módulo 2 · Editar Diseño',
            'diseno' => $diseno,
            'id' => $id,
            'notifCount' => 0,
        ]));
    }


    /* =========================================================
     *                       MÓDULO 11 - USUARIOS
     * ========================================================= */
    public function m11_roles()
    {
        if (!can('menu.roles')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $roles = [];
        try {
            $builder = $db->table('rol')->select('id, nombre, descripcion, es_fijo');

            if ($maquiladoraId) {
                try {
                    $fields = $db->getFieldNames('rol');
                    if (in_array('maquiladoraID', $fields, true)) {
                        $builder->where('maquiladoraID', (int) $maquiladoraId);
                    }
                } catch (\Throwable $e) {
                }
            }
            $roles = $builder->orderBy('id', 'ASC')->get()->getResultArray();
        } catch (\Throwable $e) {
            $roles = [];
        }

        // Diagnóstico rápido: si ?debug=1, devolver HTML mínimo
        if ($this->request->getGet('debug')) {
            $html = "<!doctype html><html><head><meta charset='utf-8'><title>Diag Roles</title></head><body><h1>Diag Roles</h1><pre>" .
                htmlspecialchars(print_r($roles, true)) . "</pre></body></html>";
            return $this->response->setHeader('Content-Type', 'text/html; charset=utf-8')->setBody($html);
        }

        return view('modulos/Roles', $this->payload([
            'title' => 'Gestión de Roles',
            'roles' => $roles,
            'notifCount' => 0,
        ]), ['saveData' => true]);

    }
    public function m11_roles_agregar()
    {
        if (!can('menu.roles')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Acceso denegado',
            ]);
        }

        $nom = trim((string) ($this->request->getPost('nombre') ?? $this->request->getVar('nombre') ?? ''));
        $desc = trim((string) ($this->request->getPost('descripcion') ?? $this->request->getVar('descripcion') ?? ''));

        if ($nom === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'El nombre es obligatorio'
            ]);
        }

        $db = \Config\Database::connect();
        try {
            $data = [
                'nombre' => $nom,
                'descripcion' => $desc,
            ];

            // Si la tabla rol tiene maquiladoraID, asignar la maquila actual
            $maquiladoraId = session()->get('maquiladora_id');
            if ($maquiladoraId) {
                try {
                    $fields = $db->getFieldNames('rol');
                    if (in_array('maquiladoraID', $fields, true)) {
                        $data['maquiladoraID'] = (int) $maquiladoraId;
                    }
                } catch (\Throwable $e) {
                }
            }

            $ok = $db->table('rol')->insert($data);
            if (!$ok) {
                $err = $db->error();
                $msg = $err['message'] ?? 'No se pudo insertar';
                throw new \Exception($msg);
            }
            $id = $db->insertID();

            // Crear notificación para el nuevo rol
            try {
                $notificationService = new NotificationService();
                $maquiladoraId = session()->get('maquiladoraID') ?? session()->get('maquiladora_id') ?? 1;
                $descripcionCorta = strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;

                $notificationService->notifyRolCreado(
                    $maquiladoraId,
                    $nom,
                    $descripcionCorta
                );
            } catch (\Throwable $e) {
                // No fallar la creación del rol si hay error en la notificación
                log_message('warning', 'Error al crear notificación de rol creado: ' . $e->getMessage());
            }

            return $this->response->setJSON(['success' => true, 'id' => (int) $id, 'message' => 'Rol agregado correctamente']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Actualiza un rol (POST): id, nombre, descripcion
     * Respuesta: JSON { success, message? }
     */
    public function m11_roles_actualizar()
    {
        if (!can('menu.roles')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Acceso denegado',
            ]);
        }

        // Aceptar tanto POST normal como AJAX
        $id = (int) ($this->request->getPost('id') ?? $this->request->getVar('id') ?? 0);
        $nom = trim((string) ($this->request->getPost('nombre') ?? $this->request->getVar('nombre') ?? ''));
        $desc = trim((string) ($this->request->getPost('descripcion') ?? $this->request->getVar('descripcion') ?? ''));

        if ($id <= 0 || $nom === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Datos inválidos'
            ]);
        }

        $db = \Config\Database::connect();
        try {
            $ok = $db->table('rol')->where('id', $id)->update([
                'nombre' => $nom,
                'descripcion' => $desc,
            ]);
            if (!$ok) {
                $err = $db->error();
                $msg = $err['message'] ?? 'No se pudo actualizar';
                throw new \Exception($msg);
            }

            // Crear notificación para el rol actualizado
            try {
                $notificationService = new NotificationService();
                $maquiladoraId = session()->get('maquiladoraID') ?? session()->get('maquiladora_id') ?? 1;
                $descripcionCorta = strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc;

                $notificationService->notifyRolActualizado(
                    $maquiladoraId,
                    $nom,
                    $descripcionCorta
                );
            } catch (\Throwable $e) {
                // No fallar la actualización del rol si hay error en la notificación
                log_message('warning', 'Error al crear notificación de rol actualizado: ' . $e->getMessage());
            }

            return $this->response->setJSON(['success' => true, 'message' => 'Rol actualizado correctamente']);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Obtener los permisos de un rol (POST): rol_id
     * Respuesta: JSON { success, permisos[] }
     */
    public function m11_roles_permisos()
    {
        if (!can('menu.roles')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Acceso denegado',
            ]);
        }

        $rolId = (int) ($this->request->getPost('rol_id') ?? $this->request->getVar('rol_id') ?? 0);

        if ($rolId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID de rol inválido'
            ]);
        }

        $db = \Config\Database::connect();

        try {
            // Si la tabla de permisos no existe, devolvemos lista vacía
            if (!$db->tableExists('rol_permiso')) {
                $permisoList = [];
            } else {
                // Obtener permisos del rol desde la tabla rol_permiso
                $permisos = $db->table('rol_permiso')
                    ->select('permiso')
                    ->where('rol_id', $rolId)
                    ->get()
                    ->getResultArray();

                $permisoList = array_column($permisos, 'permiso');
            }

            // Obtener notificaciones del rol desde la tabla rol_notificacion
            $notifList = [];
            if ($db->tableExists('rol_notificacion')) {
                $notificaciones = $db->table('rol_notificacion')
                    ->select('tipo_notificacion')
                    ->where('rol_id', $rolId)
                    ->get()
                    ->getResultArray();

                $notifList = array_column($notificaciones, 'tipo_notificacion');
            }

            return $this->response->setJSON([
                'success' => true,
                'permisos' => $permisoList,
                'notificaciones' => $notifList
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al obtener permisos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Guardar los permisos de un rol (POST): rol_id, permisos[]
     * Respuesta: JSON { success, message? }
     */
    /**
     * Guardar los permisos de un rol (POST): rol_id, permisos[]
     * Respuesta: JSON { success, message? }
     */
    public function m11_roles_guardar_permisos()
    {
        if (!can('menu.roles')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Acceso denegado',
            ]);
        }

        $rolId = (int) ($this->request->getPost('rol_id') ?? $this->request->getVar('rol_id') ?? 0);
        $permisos = $this->request->getPost('permisos') ?? [];
        $notificaciones = $this->request->getPost('notificaciones') ?? [];

        log_message('debug', 'Guardando permisos - rol_id: ' . $rolId . ', permisos: ' . json_encode($permisos));
        log_message('debug', 'Guardando notificaciones - rol_id: ' . $rolId . ', notificaciones: ' . json_encode($notificaciones));

        if ($rolId <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID de rol inválido: ' . $rolId
            ]);
        }

        $db = \Config\Database::connect();

        try {
            $db->transStart();

            // Verificar y crear tabla rol_notificacion si no existe
            $this->crearTablaRolNotificacionSiNoExiste($db);

            // Eliminar permisos actuales de ese rol
            $db->table('rol_permiso')->where('rol_id', $rolId)->delete();

            // Obtener maquiladoraID del rol para rellenar rol_permiso.maquiladoraID
            $maquiladoraId = null;
            try {
                $rolRow = $db->table('rol')->select('maquiladoraID')->where('id', $rolId)->get()->getRowArray();
                if ($rolRow && array_key_exists('maquiladoraID', $rolRow)) {
                    $maquiladoraId = $rolRow['maquiladoraID'] !== null ? (int) $rolRow['maquiladoraID'] : null;
                }
            } catch (\Throwable $e) {
                log_message('error', 'Error obteniendo maquiladoraID para rol ' . $rolId . ': ' . $e->getMessage());
            }

            // Insertar nuevos permisos
            if (!empty($permisos)) {
                $data = [];
                foreach ($permisos as $permiso) {
                    $row = [
                        'rol_id' => $rolId,
                        'permiso' => trim($permiso),
                    ];
                    if ($maquiladoraId !== null) {
                        $row['maquiladoraID'] = $maquiladoraId;
                    }
                    $data[] = $row;
                }
                log_message('debug', 'Datos a insertar en rol_permiso: ' . json_encode($data));
                $db->table('rol_permiso')->insertBatch($data);
            }

            // Eliminar notificaciones actuales de ese rol
            if ($db->tableExists('rol_notificacion')) {
                $db->table('rol_notificacion')->where('rol_id', $rolId)->delete();

                // Insertar nuevas notificaciones
                if (!empty($notificaciones)) {
                    $notifData = [];
                    foreach ($notificaciones as $notif) {
                        $row = [
                            'rol_id' => $rolId,
                            'tipo_notificacion' => trim($notif),
                        ];
                        if ($maquiladoraId !== null) {
                            $row['maquiladoraID'] = $maquiladoraId;
                        }
                        $notifData[] = $row;
                    }
                    log_message('debug', 'Datos a insertar en rol_notificacion: ' . json_encode($notifData));
                    $db->table('rol_notificacion')->insertBatch($notifData);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                $error = $db->error();
                throw new \Exception('Error en la transacción: ' . ($error['message'] ?? 'Error desconocido'));
            }

            log_message('debug', 'Permisos guardados exitosamente para rol_id: ' . $rolId);

            // Crear notificación para los permisos actualizados
            try {
                $notificationService = new NotificationService();
                $maquiladoraId = session()->get('maquiladoraID') ?? session()->get('maquiladora_id') ?? 1;

                // Obtener el nombre del rol
                $rolData = $db->table('rol')->select('nombre')->where('id', $rolId)->get()->getRowArray();
                $nombreRol = $rolData['nombre'] ?? 'Rol #' . $rolId;

                $notificationService->notifyPermisosRolActualizados(
                    $maquiladoraId,
                    $nombreRol,
                    count($permisos)
                );
            } catch (\Throwable $e) {
                // No fallar el guardado de permisos si hay error en la notificación
                log_message('warning', 'Error al crear notificación de permisos actualizados: ' . $e->getMessage());
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Permisos guardados correctamente',
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error al guardar permisos para rol_id ' . $rolId . ': ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al guardar permisos: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Eliminar un rol (POST): id
     * Respuesta: JSON { success, message? }
     */
    public function m11_roles_eliminar()
    {
        if (!can('menu.roles')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Acceso denegado',
            ]);
        }

        $id = (int) ($this->request->getPost('id') ?? $this->request->getVar('id') ?? 0);

        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'ID de rol inválido'
            ]);
        }

        $db = \Config\Database::connect();
        try {
            // Verificar si el rol está siendo utilizado por usuarios
            $usuariosConRol = $db->table('usuario_rol')
                ->where('rolIdFK', $id)
                ->countAllResults();

            if ($usuariosConRol > 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'No se puede eliminar el rol porque está asignado a ' . $usuariosConRol . ' usuario(s)'
                ]);
            }

            // Eliminar permisos del rol primero
            $db->table('rol_permiso')->where('rol_id', $id)->delete();

            // Eliminar el rol
            $ok = $db->table('rol')->where('id', $id)->delete();

            if (!$ok) {
                $err = $db->error();
                $msg = $err['message'] ?? 'No se pudo eliminar el rol';
                throw new \Exception($msg);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Rol eliminado correctamente'
            ]);

        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al eliminar el rol: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Inicializar permisos predeterminados (GET)
     * Endpoint: modulo11/roles/inicializar_permisos
     */
    public function m11_roles_inicializar_permisos()
    {
        if (!can('menu.roles')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Acceso denegado',
            ]);
        }

        try {
            $db = \Config\Database::connect();

            // Verificar y crear tabla rol_notificacion si no existe
            $this->crearTablaRolNotificacionSiNoExiste($db);

            // Verificar si la tabla existe
            $this->crearTablaRolPermisoSiNoExiste($db);

            // Limpiar permisos existentes
            $db->table('rol_permiso')->emptyTable();

            // Insertar permisos predeterminados
            $this->insertarPermisosPredeterminados($db);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Permisos inicializados correctamente'
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al inicializar permisos: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Limpiar todas las notificaciones (GET)
     * Endpoint: modulo11/limpiar_notificaciones
     */
    public function limpiar_notificaciones()
    {
        if (!can('menu.roles')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Acceso denegado',
            ]);
        }

        try {
            $db = \Config\Database::connect();

            // Limpiar todas las notificaciones
            $db->table('notificaciones')->emptyTable();
            $db->table('usuarioNotificacion')->emptyTable();

            log_message('info', 'Todas las notificaciones han sido limpiadas manualmente');

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Todas las notificaciones han sido eliminadas'
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al limpiar notificaciones: ' . $e->getMessage()
            ]);
        }
    }

    public function m11_usuarios()
    {
        if (!can('menu.usuarios')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $usuarioModel = new \App\Models\UsuarioModel();
        $maquiladoraId = session()->get('maquiladora_id');

        // Obtener usuarios (excepto eliminados lógicamente), filtrados por maquiladora cuando aplique
        $builder = $usuarioModel->where('deleted_at', null);
        if ($maquiladoraId) {
            $builder = $builder->where('maquiladoraIdFK', (int) $maquiladoraId);
        }
        $usuarios = $builder->findAll();

        // Obtener mapa de roles por usuario
        $rolesPorUsuario = [];
        try {
            $db = \Config\Database::connect();
            $rows = $db->table('usuario_rol ur')
                ->select('ur.usuarioIdFK as uid, r.nombre as rol')
                ->join('rol r', 'r.id = ur.rolIdFK', 'left')
                ->get()->getResultArray();
            foreach ($rows as $r) {
                if (!isset($r['uid'])) {
                    continue;
                }
                $rolesPorUsuario[(int) $r['uid']] = $r['rol'] ?? 'Usuario';
            }
        } catch (\Throwable $e) {
            $rolesPorUsuario = [];
        }

        // Formatear los datos para la vista
        $usuariosFormateados = [];
        foreach ($usuarios as $usuario) {
            $usuariosFormateados[] = [
                'id' => $usuario['id'],
                'noEmpleado' => $usuario['id'], // Usar ID como número de empleado temporal
                'nombre' => $usuario['username'] ?? 'Sin nombre',
                'apellido' => '', // No hay campo apellido en la tabla
                'email' => $usuario['correo'] ?? 'Sin correo',
                // Mostrar el nombre del rol en la columna PUESTO
                'puesto' => $rolesPorUsuario[(int) $usuario['id']] ?? 'Usuario',
                'idmaquiladora' => $usuario['maquiladoraIdFK'] ?? 'Sin asignar',
                'activo' => $usuario['active'] ?? 1,
                'fechaAlta' => $usuario['created_at'] ?? date('Y-m-d H:i:s'),
                'ultimoAcceso' => $usuario['last_active'] ?? 'Nunca'
            ];
        }

        return view('modulos/usuarios', $this->payload([
            'title' => 'Gestión de Usuarios',
            'usuarios' => $usuariosFormateados,
            'notifCount' => 0,
        ]));
    }

    public function m11_agregar_usuario()
    {
        if (!can('menu.usuarios')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        if ($this->request->getMethod() === 'post') {
            // Validar datos
            $validation = \Config\Services::validation();
            $validation->setRules([
                'usuario' => 'required|min_length[3]|max_length[100]|is_unique[usuario.usuario]',
                'password' => 'required|min_length[6]',
                'noEmpleado' => 'required|min_length[3]|max_length[20]|is_unique[empleado.noEmpleado]',
                'nombre' => 'required|min_length[2]|max_length[100]',
                'apellido' => 'required|min_length[2]|max_length[100]',
                'email' => 'required|valid_email|max_length[100]|is_unique[empleado.email]',
                'puesto' => 'required|max_length[100]',
            ]);

            if (!$validation->withRequest($this->request)->run()) {
                return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            }

            // Iniciar transacción
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Crear usuario
                $usuarioData = [
                    'usuario' => $this->request->getPost('usuario'),
                    'password' => $this->request->getPost('password'),
                    'activo' => 1,
                    'fechaAlta' => date('Y-m-d H:i:s'),
                    'ultimoAcceso' => date('Y-m-d H:i:s'),
                    'idmaquiladora' => $this->request->getPost('idMaquiladora') ?: null
                ];

                $usuarioId = $usuarioModel->insert($usuarioData);

                if (!$usuarioId) {
                    throw new \Exception('Error al crear el usuario');
                }

                // Crear empleado
                $empleadoData = [
                    'noEmpleado' => $this->request->getPost('noEmpleado'),
                    'nombre' => $this->request->getPost('nombre'),
                    'email' => $this->request->getPost('email'),
                    'telefono' => $this->request->getPost('telefono'),
                    'domicilio' => $this->request->getPost('domicilio'),
                    'puesto' => $this->request->getPost('puesto'),
                    'activo' => 1,
                    'idusuario' => $usuarioId
                ];

                $empleadoId = $empleadoModel->insert($empleadoData);

                if (!$empleadoId) {
                    throw new \Exception('Error al crear el empleado');
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Error en la transacción');
                }

                return redirect()->to('/modulo11/usuarios')->with('success', 'Usuario agregado correctamente');

            } catch (\Exception $e) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Error al crear el usuario: ' . $e->getMessage());
            }
        }

        return view('modulos/agregar_usuario', $this->payload([
            'title' => 'Módulo 11 · Agregar Usuario',
            'notifCount' => 0,
        ]));
    }

    /**
     * Obtiene los datos de un usuario para edición (llamada AJAX)
     */
    public function obtener_usuario($id = null)
    {
        if (!can('menu.usuarios')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Acceso denegado',
            ]);
        }

        try {
            // Verificar que sea una petición AJAX
            if (!$this->request->isAJAX()) {
                throw new \RuntimeException('Método no permitido');
            }

            // Validar ID
            $id = (int) $id;
            if ($id <= 0) {
                throw new \InvalidArgumentException('ID de usuario inválido');
            }

            // Cargar modelo de usuarios
            $usuarioModel = new \App\Models\UsuarioModel();

            // Buscar usuario
            $usuario = $usuarioModel->find($id);

            if (!$usuario) {
                throw new \RuntimeException('Usuario no encontrado');
            }

            $db = \Config\Database::connect();

            // Datos básicos del usuario
            $usuarioData = [
                'id' => $usuario['id'] ?? null,
                'username' => $usuario['username'] ?? '',
                'email' => $usuario['correo'] ?? '',
                'rol_actual' => 'Usuario',
                'rol_id' => null,
                'maquiladoraIdFK' => $usuario['maquiladoraIdFK'] ?? null,
                'activo' => $usuario['active'] ?? 1,
                'roles' => [],
                'maquiladoras' => []
            ];

            // Configurar roles por defecto
            $usuarioData['roles'] = [
                ['id' => 1, 'name' => 'Administrador'],
                ['id' => 2, 'name' => 'Usuario'],
                ['id' => 3, 'name' => 'Invitado']
            ];

            // Establecer un rol por defecto
            $usuarioData['rol_id'] = 2; // Usuario por defecto
            $usuarioData['rol_actual'] = 'Usuario';

            // Obtener maquiladoras si la tabla existe
            if ($db->tableExists('maquiladoras')) {
                try {
                    $usuarioData['maquiladoras'] = $db->table('maquiladoras')
                        ->select('id, nombre')
                        ->get()
                        ->getResultArray();
                } catch (\Exception $e) {
                    log_message('error', 'Error al obtener maquiladoras: ' . $e->getMessage());
                    // Si hay error, usar un array vacío
                    $usuarioData['maquiladoras'] = [];
                }
            } else {
                $usuarioData['maquiladoras'] = [];
            }

            // Si no hay maquiladoras, agregar una opción por defecto
            if (empty($usuarioData['maquiladoras'])) {
                $usuarioData['maquiladoras'] = [
                    ['id' => 1, 'nombre' => 'Maquiladora Principal']
                ];
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $usuarioData
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en obtener_usuario: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Error al obtener los datos del usuario: ' . $e->getMessage(),
                'trace' => ENVIRONMENT === 'development' ? $e->getTrace() : null
            ]);
        }
    }

    /**
     * Actualiza los datos de un usuario (llamada AJAX)
     */
    public function actualizar_usuario()
    {
        if (!can('menu.usuarios')) {
            return $this->response->setStatusCode(403)->setJSON([
                'success' => false,
                'message' => 'Acceso denegado',
            ]);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'message' => 'Método no permitido']);
        }

        $id = (int) $this->request->getPost('id');
        $nombre = trim($this->request->getPost('nombre'));
        $email = trim($this->request->getPost('email'));
        $rol = $this->request->getPost('rol');
        $maquiladoraId = $this->request->getPost('idmaquiladora');
        $activo = (int) $this->request->getPost('activo');
        $password = $this->request->getPost('password');

        // Validar datos
        if (empty($nombre) || empty($email)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Nombre y correo son obligatorios']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setJSON(['success' => false, 'message' => 'El formato del correo no es válido']);
        }

        // Cargar modelo de usuarios
        $usuarioModel = new \App\Models\UsuarioModel();

        // Verificar si el usuario existe
        $usuario = $usuarioModel->find($id);
        if (!$usuario) {
            return $this->response->setJSON(['success' => false, 'message' => 'Usuario no encontrado']);
        }

        // Verificar si el correo ya existe (excepto para el usuario actual)
        $existeCorreo = $usuarioModel->where('correo', $email)
            ->where('id !=', $id)
            ->first();
        if ($existeCorreo) {
            return $this->response->setJSON(['success' => false, 'message' => 'El correo electrónico ya está en uso']);
        }

        // Actualizar datos del usuario
        $data = [
            'username' => $nombre,
            'correo' => $email,
            'maquiladoraIdFK' => !empty($maquiladoraId) ? (int) $maquiladoraId : null,
            'active' => $activo,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Actualizar contraseña si se proporcionó
        if (!empty($password)) {
            if (strlen($password) < 8) {
                return $this->response->setJSON(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres']);
            }
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        // Iniciar transacción
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Actualizar datos del usuario
            $usuarioModel->update($id, $data);

            // Actualizar rol del usuario si se proporcionó
            if (!empty($rol)) {
                $builder = $db->table('auth_groups_users');

                // Verificar si el rol existe
                $rolExiste = $db->table('auth_groups')
                    ->where('id', $rol)
                    ->countAllResults() > 0;

                if ($rolExiste) {
                    // Eliminar roles existentes
                    $builder->where('user_id', $id)->delete();

                    // Agregar nuevo rol
                    $builder->insert([
                        'group_id' => (int) $rol,
                        'user_id' => $id,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al actualizar el usuario en la base de datos');
            }

            // Obtener datos actualizados del usuario para la respuesta
            $usuarioActualizado = $usuarioModel->find($id);
            $builder = $db->table('auth_groups_users')->where('user_id', $id);
            $roles = $builder->get()->getResultArray();
            $rolNombre = 'Usuario';

            if (!empty($roles)) {
                $rol = $db->table('auth_groups')
                    ->where('id', $roles[0]['group_id'])
                    ->get()
                    ->getRowArray();
                if ($rol) {
                    $rolNombre = $rol['name'];
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => [
                    'id' => $usuarioActualizado['id'],
                    'username' => $usuarioActualizado['username'],
                    'email' => $usuarioActualizado['correo'],
                    'rol' => $rolNombre,
                    'maquiladoraIdFK' => $usuarioActualizado['maquiladoraIdFK'],
                    'activo' => $usuarioActualizado['active']
                ]
            ]);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error al actualizar usuario: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al actualizar el usuario: ' . $e->getMessage()
            ]);
        }
    }

    public function m11_editar_usuario($id = null)
    {
        if (!$id) {
            return redirect()->to('/modulo11/usuarios')->with('error', 'ID de usuario no válido');
        }

        $usuarioModel = new \App\Models\UsuarioModel();
        $empleadoModel = new \App\Models\EmpleadoModel();

        if ($this->request->getMethod() === 'post') {
            // Validar datos
            $validation = \Config\Services::validation();
            $validation->setRules([
                'usuario' => "required|min_length[3]|max_length[100]|is_unique[usuario.usuario,id,{$id}]",
                'noEmpleado' => 'required|min_length[3]|max_length[20]',
                'nombre' => 'required|min_length[2]|max_length[100]',
                'apellido' => 'required|min_length[2]|max_length[100]',
                'email' => 'required|valid_email|max_length[100]',
                'puesto' => 'required|max_length[100]',
            ]);

            if (!$validation->withRequest($this->request)->run()) {
                return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            }

            // Iniciar transacción
            $db = \Config\Database::connect();
            $db->transStart();

            try {
                // Actualizar usuario
                $usuarioData = [
                    'usuario' => $this->request->getPost('usuario'),
                    'activo' => $this->request->getPost('activo_usuario') ?: 1,
                    'idmaquiladora' => $this->request->getPost('idMaquiladora') ?: null
                ];

                // Si se proporcionó una nueva contraseña, la actualizamos
                if ($this->request->getPost('password') && $this->request->getPost('password') !== '') {
                    $usuarioData['password'] = $this->request->getPost('password');
                }

                $usuarioModel->update($id, $usuarioData);

                // Obtener el empleado asociado al usuario
                $empleado = $empleadoModel->where('idusuario', $id)->first();

                if ($empleado) {
                    // Actualizar empleado existente
                    $empleadoData = [
                        'noEmpleado' => $this->request->getPost('noEmpleado'),
                        'nombre' => $this->request->getPost('nombre'),
                        'apellido' => $this->request->getPost('apellido'),
                        'email' => $this->request->getPost('email'),
                        'telefono' => $this->request->getPost('telefono'),
                        'domicilio' => $this->request->getPost('domicilio'),
                        'puesto' => $this->request->getPost('puesto'),
                        'activo' => $this->request->getPost('activo_empleado') ?: 1
                    ];

                    $empleadoModel->update($empleado['id'], $empleadoData);
                }

                $db->transComplete();

                if ($db->transStatus() === false) {
                    throw new \Exception('Error en la transacción');
                }

                return redirect()->to('/modulo11/usuarios')->with('success', 'Usuario actualizado correctamente');

            } catch (\Exception $e) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Error al actualizar el usuario: ' . $e->getMessage());
            }
        }

        // Obtener datos del usuario con empleado desde la base de datos
        $usuario = $usuarioModel->getUsuarioConEmpleado($id);

        if (!$usuario) {
            return redirect()->to('/modulo11/usuarios')->with('error', 'Usuario no encontrado');
        }

        return view('modulos/editar_usuario', $this->payload([
            'title' => 'Módulo 11 · Editar Usuario',
            'usuario' => $usuario,
            'id' => $id,
            'notifCount' => 0,
        ]));
    }

    public function m1_pedidos_crear()
    {
        $method = strtolower($this->request->getMethod());
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['ok' => false, 'message' => 'Método no permitido']);
        }

        // Obtener maquiladoraId desde la sesión
        $maquiladoraId = session()->get('maquiladora_id');

        $clienteId = (int) ($this->request->getPost('oc_clienteId'));
        $ocEstatus = (string) ($this->request->getPost('oc_estatus') ?? 'Pendiente');
        $ocFolio = trim((string) $this->request->getPost('oc_folio'));
        $ocFecha = (string) $this->request->getPost('oc_fecha');
        $ocMoneda = trim((string) $this->request->getPost('oc_moneda')) ?: 'MXN';
        $ocTotal = (float) ($this->request->getPost('oc_total'));

        $opFolio = trim((string) $this->request->getPost('op_folio'));
        $opCantidadPlan = (int) ($this->request->getPost('op_cantidadPlan'));
        $opCantidadBultos = (int) ($this->request->getPost('op_cantidadBultos'));
        $opFechaInicioPlan = (string) ($this->request->getPost('op_fechaInicioPlan'));
        $opFechaFinPlan = (string) ($this->request->getPost('op_fechaFinPlan'));
        $opStatus = (string) ($this->request->getPost('op_status') ?? 'Planeada');

        // Diseño
        $disenoVersionId = (int) ($this->request->getPost('disenoVersionId') ?? $this->request->getPost('pa_dis_version_id') ?? 0);
        $disenoId = (int) ($this->request->getPost('disenoId') ?? 0);

        if ($clienteId <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Cliente requerido']);
        }
        if ($opCantidadPlan <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Cantidad plan debe ser mayor que 0']);
        }
        if ($ocFecha === '') {
            $ocFecha = date('Y-m-d');
        }
        if ($opFechaInicioPlan === '') {
            $opFechaInicioPlan = date('Y-m-d');
        }

        $db = \Config\Database::connect();

        // Si no viene disenoVersionId, intentar resolver última versión por disenoId
        if ($disenoVersionId <= 0 && $disenoId > 0) {
            try {
                $row = $db->query(
                    'SELECT dv.id FROM diseno_version dv WHERE dv.disenoId = ? ORDER BY dv.fecha DESC, dv.id DESC LIMIT 1',
                    [$disenoId]
                )->getRowArray();
                if ($row && isset($row['id'])) {
                    $disenoVersionId = (int) $row['id'];
                }
            } catch (\Throwable $e) {
                try {
                    $row = $db->query(
                        'SELECT dv.id FROM disenoversion dv WHERE dv.disenoId = ? ORDER BY dv.fecha DESC, dv.id DESC LIMIT 1',
                        [$disenoId]
                    )->getRowArray();
                    if ($row && isset($row['id'])) {
                        $disenoVersionId = (int) $row['id'];
                    }
                } catch (\Throwable $e2) {
                    // ignore
                }
            }
        }
        if ($disenoVersionId <= 0) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Versión de diseño requerida']);
        }

        try {
            $db->transStart();

            // Insertar orden_compra (nombres exactos)
            $rowOC = [
                'clienteId' => $clienteId,
                'folio' => $ocFolio !== '' ? $ocFolio : ('OC-' . date('Y') . '-' . $clienteId),
                'fecha' => $ocFecha,
                'estatus' => $ocEstatus ?: 'Pendiente',
                'moneda' => $ocMoneda,
                'total' => $ocTotal,
            ];
            if ($maquiladoraId) {
                $rowOC['maquiladoraID'] = (int) $maquiladoraId;
            }
            $db->table('orden_compra')->insert($rowOC);
            $ocId = (int) $db->insertID();
            if ($ocId === 0) {
                // fallback por mayúsculas
                $db->table('OrdenCompra')->insert($rowOC);
                $ocId = (int) $db->insertID();
            }
            if (!$ocId) {
                throw new \Exception('No se pudo crear la Orden de Compra');
            }

            // Forzar folio único de OC: OC-YYYY-<ocId>
            $nuevoOCFolio = 'OC-' . date('Y') . '-' . $ocId;
            $okUpdOCFolio = false;
            try {
                $okUpdOCFolio = (bool) $db->table('orden_compra')->where('id', $ocId)->update(['folio' => $nuevoOCFolio]);
            } catch (\Throwable $e) {
                $okUpdOCFolio = false;
            }
            if (!$okUpdOCFolio) {
                try {
                    $okUpdOCFolio = (bool) $db->table('OrdenCompra')->where('id', $ocId)->update(['folio' => $nuevoOCFolio]);
                } catch (\Throwable $e2) {
                    // ignore
                }
            }

            // Insertar orden_produccion (nombres exactos)
            $rowOP = [
                'ordenCompraId' => $ocId,
                'disenoVersionId' => $disenoVersionId,
                'folio' => $opFolio !== '' ? $opFolio : ('OP-' . date('Y') . '-' . $clienteId),
                'cantidadPlan' => $opCantidadPlan,
                'cantidadBultos' => ($opCantidadBultos > 0 ? $opCantidadBultos : null),
                'fechaInicioPlan' => $opFechaInicioPlan,
                'fechaFinPlan' => ($opFechaFinPlan ?: null),
                'status' => $opStatus ?: 'Planeada',
            ];
            if ($maquiladoraId) {
                $rowOP['maquiladoraID'] = (int) $maquiladoraId;
            }
            $db->table('orden_produccion')->insert($rowOP);
            $opId = (int) $db->insertID();
            if ($opId === 0) {
                $db->table('OrdenProduccion')->insert($rowOP);
                $opId = (int) $db->insertID();
            }
            if (!$opId) {
                throw new \Exception('No se pudo crear la Orden de Producción');
            }

            // Forzar folio único de OP: OP-YYYY-<opId>
            $nuevoFolio = 'OP-' . date('Y') . '-' . $opId;
            $okUpdFolio = false;
            try {
                $okUpdFolio = (bool) $db->table('orden_produccion')->where('id', $opId)->update(['folio' => $nuevoFolio]);
            } catch (\Throwable $e) {
                $okUpdFolio = false;
            }
            if (!$okUpdFolio) {
                try {
                    $okUpdFolio = (bool) $db->table('OrdenProduccion')->where('id', $opId)->update(['folio' => $nuevoFolio]);
                } catch (\Throwable $e2) {
                    // ignore
                }
            }

            // Crear registro inicial en inspeccion vinculado a la OP (otros campos en NULL)
            // Guardar Tallas (si existen)
            $tallas = $this->request->getPost('tallas');
            if (!empty($tallas) && is_array($tallas)) {
                foreach ($tallas as $t) {
                    if (isset($t['id_sexo'], $t['id_talla'], $t['cantidad']) && $t['cantidad'] > 0) {
                        $rowTalla = [
                            'ordenProduccionId' => $opId,
                            'id_sexo' => (int) $t['id_sexo'],
                            'id_talla' => (int) $t['id_talla'],
                            'cantidad' => (int) $t['cantidad'],
                        ];
                        try {
                            $db->table('pedido_tallas_detalle')->insert($rowTalla);
                        } catch (\Throwable $eT1) {
                            try {
                                $db->table('OrdenProduccionTalla')->insert($rowTalla);
                            } catch (\Throwable $eT2) {
                                // Ignorar error individual de talla
                            }
                        }
                    }
                }
            }

            $rowIns = [
                'ordenProduccionId' => $opId,
                'puntoInspeccionId' => null,
                'inspectorId' => null,
                'fecha' => null,
                'resultado' => null,
                'observaciones' => null,
            ];
            $db->table('inspeccion')->insert($rowIns);
            $insId = (int) $db->insertID();
            if ($insId === 0) {
                // Fallback por mayúsculas
                $db->table('Inspeccion')->insert($rowIns);
                $insId = (int) $db->insertID();
            }
            if (!$insId) {
                throw new \Exception('No se pudo crear la inspeccion inicial');
            }

            // Crear registro inicial en reproceso vinculado a la inspeccion (otros campos en NULL)
            $rowRep = [
                'inspeccionId' => $insId,
                'accion' => null,
                'cantidad' => null,
                'fecha' => null,
            ];
            $db->table('reproceso')->insert($rowRep);

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }

            // Enviar notificación de pedido creado
            try {
                $notificationService = new \App\Services\NotificationService();

                // Obtener nombre del cliente para la notificación
                $cliente = $db->table('cliente')->where('id', $clienteId)->get()->getRowArray();
                $clienteNombre = $cliente['nombre'] ?? 'Cliente desconocido';

                $notificationId = $notificationService->notifyPedidoAgregado(
                    $maquiladoraId ?? 1,
                    $opFolio ?: $opId,
                    $clienteNombre
                );

                log_message('debug', 'Notificación de pedido creada con ID: ' . ($notificationId ?? 'FALSE'));
            } catch (\Exception $e) {
                log_message('error', 'Error al enviar notificación de pedido: ' . $e->getMessage());
            }

            return $this->response->setJSON([
                'ok' => true,
                'ocId' => $ocId,
                'opId' => $opId,
                'message' => 'Pedido creado',
            ]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $e2) {
                // ignorar fallo en rollback
            }

            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Error al crear pedido: ' . $e->getMessage(),
            ]);
        }
    }

    public function m1_pedidos_cargar_pdf()
    {
        $method = strtolower($this->request->getMethod());
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON([
                'ok' => false,
                'message' => 'Método no permitido',
            ]);
        }

        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'Solicitud inválida',
            ]);
        }

        $file = $this->request->getFile('pdf');
        if (!$file || !$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'Archivo PDF no recibido o inválido',
            ]);
        }

        if (strtolower($file->getClientExtension()) !== 'pdf') {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'El archivo debe ser un PDF',
            ]);
        }

        $targetDir = WRITEPATH . 'uploads/pedidos_pdf';
        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0775, true);
        }

        $newName = uniqid('pedido_', true) . '.pdf';
        $filePath = rtrim($targetDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $newName;
        try {
            $file->move($targetDir, $newName, true);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'No se pudo guardar el PDF: ' . $e->getMessage(),
            ]);
        }

        // Intentar generar imágenes de las primeras páginas del PDF (opcional)
        $paginasImg = [];
        if (class_exists('Imagick')) {
            try {
                $imgDir = WRITEPATH . 'uploads/pedidos_pdf_imgs';
                if (!is_dir($imgDir)) {
                    @mkdir($imgDir, 0775, true);
                }

                // Configuración básica
                $imagick = new \Imagick();
                $imagick->setResolution(150, 150);
                // Cargar solo las primeras 2 páginas (si existen)
                $imagick->readImage($filePath . '[0-1]');
                $imagick->setImageFormat('jpeg');

                $index = 1;
                foreach ($imagick as $page) {
                    $page->setImageFormat('jpeg');
                    $page->setImageCompressionQuality(85);
                    $imgName = pathinfo($newName, PATHINFO_FILENAME) . '_p' . $index . '.jpg';
                    $imgPath = rtrim($imgDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $imgName;
                    $page->writeImage($imgPath);

                    // Construir URL relativa para servir desde public (asumiendo symlink o configuración)
                    $paginasImg[] = base_url('writable/uploads/pedidos_pdf_imgs/' . $imgName);
                    $index++;
                }

                $imagick->clear();
                $imagick->destroy();
            } catch (\Throwable $e) {
                // Si falla Imagick, simplemente no devolvemos imágenes
            }
        }

        if (!class_exists(\Smalot\PdfParser\Parser::class)) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'No está instalado el parser de PDF (smalot/pdfparser).',
            ]);
        }

        try {
            $parser = new \Smalot\PdfParser\Parser();
            $pdf = $parser->parseFile($filePath);
            $text = (string) $pdf->getText();
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Error al leer el PDF: ' . $e->getMessage(),
            ]);
        }

        $clienteNombre = null;
        $modelo = null;
        $modeloCliente = null;
        $fecha = null;
        $disenoNombre = null;
        $disenoDescripcion = null;
        $disenoNotas = null;
        $tallasTexto = null;
        $cantidadPlan = null;

        if ($text !== '') {
            // CLIENTE: solo por etiqueta explícita al inicio de línea
            if (preg_match('/^\s*CLIENTE\s*:\s*(.+)$/imu', $text, $m)) {
                $line = trim($m[1]);
                // Si en la misma línea vienen otras columnas (MODELO, MODELO CLIENTE, etc.), cortarlas
                $line = preg_split('/\s{2,}(?=MODELO)/iu', $line)[0] ?? $line;
                $clienteNombre = trim($line);
            }

            // MODELO CLIENTE: línea específica
            if (preg_match('/MODELO\s+CLIENTE\s*:\s*"?([0-9A-Z\-]+)"?/iu', $text, $m)) {
                $modeloCliente = trim($m[1]);
            }

            // MODELO: cabecera MODELO: 40759 (evitar MODELO CLIENTE)
            if (preg_match('/^\s*MODELO\s*:\s*([0-9A-Z\-]+)/imu', $text, $m)) {
                $modelo = trim($m[1]);
            } elseif (!$modelo && $modeloCliente) {
                // fallback: si solo tenemos modelo cliente, usarlo
                $modelo = $modeloCliente;
            }

            // FECHA: primer dd/mm/yyyy que aparezca
            if (preg_match('/([0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4})/u', $text, $m)) {
                $fechaRaw = trim($m[1]);
                $parts = preg_split('/[\/]/', $fechaRaw);

                if (count($parts) === 3) {
                    $d = (int) $parts[0];
                    $m2 = (int) $parts[1];
                    $y = (int) $parts[2];
                    if ($y < 100) {
                        $y += 2000;
                    }
                    if ($d > 0 && $m2 > 0 && $y > 0) {
                        $fecha = sprintf('%04d-%02d-%02d', $y, $m2, $d);
                    }
                }
            }

            // NOMBRE DE DISEÑO: usar línea de DESCRIPCION: si existe
            if (preg_match('/DESCRIPCION\s*:\s*(.+)/iu', $text, $m)) {
                $line = trim($m[1]);
                $disenoNombre = preg_split('/\r?\n/', $line)[0] ?? $line;
            }

            // DESCRIPCION LARGA: tomar varias líneas después del encabezado "DESCRIPCION" de la tabla
            if (preg_match('/DESCRIPCION\s*\n(.+?)(?:\n\s*\*\*\*|$)/isu', $text, $m)) {
                $block = trim($m[1]);
                $lines = preg_split('/\r?\n/', $block);
                if (is_array($lines) && !empty($lines)) {
                    $lines = array_slice($lines, 0, 15);
                    $disenoDescripcion = trim(implode("\n", $lines));
                }
            }

            // NOTAS: recopilar líneas que contengan ETIQUETA
            $notas = [];
            if (preg_match_all('/ETIQUETA[^\n]*/iu', $text, $matches) && !empty($matches[0])) {
                foreach ($matches[0] as $ln) {
                    $ln = trim($ln);
                    if ($ln !== '') {
                        $notas[] = $ln;
                    }
                }
            }
            if (!empty($notas)) {
                $disenoNotas = implode("\n", array_unique($notas));
            }

            // TALLAS: capturar bloque a partir de palabra TALLAS hasta línea que contenga Total
            if (preg_match('/TALLAS[\s\S]+?Total[\s\S]*$/iu', $text, $m)) {
                $tallasBlock = trim($m[0]);
                $tallasTexto = $tallasBlock;
                // Intentar extraer cantidad plan del Total
                if (preg_match('/Total\s+([0-9]{1,4})/iu', $tallasBlock, $mt)) {
                    $cantidadPlan = (int) $mt[1];
                }
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'cliente_nombre' => $clienteNombre,
            'modelo' => $modelo,
            'modelo_cliente' => $modeloCliente,
            'fecha' => $fecha,
            'texto_crudo' => $text,
            'diseno_nombre' => $disenoNombre,
            'diseno_descripcion' => $disenoDescripcion,
            'diseno_notas' => $disenoNotas,
            'tallas_texto' => $tallasTexto,
            'cantidad_plan' => $cantidadPlan,
            'paginas_img' => $paginasImg,
        ]);
    }

    /**
     * Crear tabla rol_notificacion si no existe
     */
    private function crearTablaRolNotificacionSiNoExiste($db)
    {
        if (!$db->tableExists('rol_notificacion')) {
            $sql = "
                CREATE TABLE `rol_notificacion` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `rol_id` int(11) NOT NULL,
                    `tipo_notificacion` varchar(50) NOT NULL,
                    `maquiladoraID` int(11) DEFAULT NULL,
                    `created_at` datetime NOT NULL,
                    `updated_at` datetime NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `rol_tipo_unique` (`rol_id`,`tipo_notificacion`),
                    KEY `rol_notificacion_rol_id_foreign` (`rol_id`),
                    CONSTRAINT `rol_notificacion_rol_id_foreign` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ";
            $db->query($sql);
            log_message('info', 'Tabla rol_notificacion creada exitosamente');
        }
    }

    /**
     * Agregar columna tipo_notificacion a tabla notificaciones si no existe
     */
    private function agregarColumnaTipoNotificacionSiNoExiste($db)
    {
        if ($db->tableExists('notificaciones')) {
            // Verificar si la columna ya existe
            $query = $db->query("SHOW COLUMNS FROM notificaciones LIKE 'tipo_notificacion'");
            if ($query->getNumRows() == 0) {
                $sql = "ALTER TABLE notificaciones ADD COLUMN tipo_notificacion VARCHAR(50) NULL AFTER color";
                $db->query($sql);
                log_message('info', 'Columna tipo_notificacion agregada a tabla notificaciones');
            }
        }
    }

    /**
     * Vista para gestionar notificaciones por rol
     */
    public function gestion_notificaciones_rol()
    {
        $maquiladoraId = session()->get('maquiladora_id');
        $rolNotificacionModel = new \App\Models\RolNotificacionModel();

        $roles = $rolNotificacionModel->getRolesWithPermisos($maquiladoraId);
        $tiposNotificacion = $rolNotificacionModel->getTiposNotificacion();

        return view('modulos/gestion_notificaciones_rol', [
            'roles' => $roles,
            'tiposNotificacion' => $tiposNotificacion,
            'maquiladoraId' => $maquiladoraId
        ]);
    }

    /**
     * Guardar configuración de notificaciones para un rol
     */
    public function guardar_notificaciones_rol()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }

        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }

        $rolId = (int) $this->request->getPost('rol_id');
        $tiposNotificacion = $this->request->getPost('tipos_notificacion') ?? [];
        $maquiladoraId = session()->get('maquiladora_id');

        if ($rolId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID de rol inválido']);
        }

        try {
            $rolNotificacionModel = new \App\Models\RolNotificacionModel();
            $result = $rolNotificacionModel->savePermisosRol($rolId, $maquiladoraId, $tiposNotificacion);

            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Configuración de notificaciones actualizada correctamente'
                ]);
            } else {
                return $this->response->setStatusCode(500)->setJSON([
                    'error' => 'Error al guardar la configuración'
                ]);
            }
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}