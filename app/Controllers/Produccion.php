<?php
namespace App\Controllers;

use App\Models\OrdenProduccionModel;
use App\Models\AsignacionTareaModel;
use App\Models\EmpleadoModel;
use App\Models\TiempoTrabajoModel;
use App\Services\NotificationService;

class Produccion extends BaseController
{
    public function ordenes()
    {
        $model = new OrdenProduccionModel();
        $ordenes = $model->getListado();

        foreach ($ordenes as &$r) {
            $r['ini'] = $r['ini'] ? date('Y-m-d', strtotime($r['ini'])) : '';
            $r['fin'] = $r['fin'] ? date('Y-m-d', strtotime($r['fin'])) : '';
        }

        return view('modulos/m1_ordenes', [
            'title' => 'Órdenes de Producción',
            'ordenes' => $ordenes,
        ]);
    }

    public function actualizarEstatus()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }

        $id = (int) ($this->request->getPost('id') ?? $this->request->getVar('id') ?? $this->request->getPost('opId') ?? $this->request->getVar('opId') ?? 0);
        $estatus = trim((string) ($this->request->getPost('estatus') ?? $this->request->getVar('estatus') ?? $this->request->getPost('status') ?? $this->request->getVar('status') ?? ''));

        if ($id <= 0 || $estatus === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Parámetros inválidos']);
        }

        $service = new \App\Services\OrdenProduccionService();
        $resultado = $service->actualizarEstatus($id, $estatus);

        if (!$resultado['ok']) {
            return $this->response->setStatusCode(500)->setJSON(['error' => $resultado['error']]);
        }

        return $this->response->setJSON($resultado);
    }

    /** Eliminar una OP y dependencias (asignaciones, inspeccion, reproceso) */
    public function orden_eliminar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }
        $id = (int) ($this->request->getPost('id') ?? $this->request->getVar('id') ?? $this->request->getPost('opId') ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }
        $db = \Config\Database::connect();
        try {
            $db->transStart();

            // Obtener ordenCompraId de la OP
            $ocId = null;
            try {
                $rowOP = $db->query('SELECT ordenCompraId FROM orden_produccion WHERE id = ?', [$id])->getRowArray();
                if ($rowOP && isset($rowOP['ordenCompraId'])) {
                    $ocId = (int) $rowOP['ordenCompraId'];
                }
            } catch (\Throwable $e) {
                try {
                    $rowOP = $db->query('SELECT ordenCompraId FROM OrdenProduccion WHERE id = ?', [$id])->getRowArray();
                    if ($rowOP && isset($rowOP['ordenCompraId'])) {
                        $ocId = (int) $rowOP['ordenCompraId'];
                    }
                } catch (\Throwable $e2) {
                }
            }

            // Obtener IDs de inspección ligados a la OP
            $insIds = [];
            try {
                $rows = $db->query('SELECT id FROM inspeccion WHERE ordenProduccionId = ?', [$id])->getResultArray();
                foreach ($rows as $r) {
                    if (isset($r['id']))
                        $insIds[] = (int) $r['id'];
                }
            } catch (\Throwable $e) {
                try {
                    $rows = $db->query('SELECT id FROM Inspeccion WHERE ordenProduccionId = ?', [$id])->getResultArray();
                    foreach ($rows as $r) {
                        if (isset($r['id']))
                            $insIds[] = (int) $r['id'];
                    }
                } catch (\Throwable $e2) { /* ignore */
                }
            }

            if (!empty($insIds)) {
                // Borrar reprocesos por inspeccionId
                try {
                    $db->table('reproceso')->whereIn('inspeccionId', $insIds)->delete();
                } catch (\Throwable $e) {
                    try {
                        $db->table('Reproceso')->whereIn('inspeccionId', $insIds)->delete();
                    } catch (\Throwable $e2) { /* ignore */
                    }
                }
            }
            // Borrar inspecciones por OP
            try {
                $db->table('inspeccion')->where('ordenProduccionId', $id)->delete();
            } catch (\Throwable $e) {
                try {
                    $db->table('Inspeccion')->where('ordenProduccionId', $id)->delete();
                } catch (\Throwable $e2) { /* ignore */
                }
            }

            // Borrar asignaciones de tarea por OP
            try {
                $db->table('asignacion_tarea')->where('ordenProduccionId', $id)->delete();
            } catch (\Throwable $e) { /* ignore si no existe */
            }

            // Borrar la OP
            $okDel = false;
            try {
                $okDel = (bool) $db->table('orden_produccion')->where('id', $id)->delete();
            } catch (\Throwable $e) {
                $okDel = false;
            }
            if (!$okDel) {
                try {
                    $okDel = (bool) $db->table('OrdenProduccion')->where('id', $id)->delete();
                } catch (\Throwable $e2) {
                    $okDel = false;
                }
            }
            if (!$okDel) {
                throw new \Exception('No se pudo eliminar la Orden de Producción');
            }

            // Si había orden_compra ligada, eliminarla también
            if ($ocId && $ocId > 0) {
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
                // No forzar excepción si no existe, pero registrar fallo lógico
            }

            $db->transComplete();
            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción');
            }
            return $this->response->setJSON(['ok' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            try {
                $db->transRollback();
            } catch (\Throwable $e2) {
            }
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }

    public function orden_json($id = null)
    {
        $id = (int) ($id ?? 0);
        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        }
        try {
            $model = new OrdenProduccionModel();
            // Cargar solo datos básicos de orden_produccion primero
            $row = $model->getDetalleBasico($id);
            if (!$row) {
                // Diagnóstico adicional para entender por qué no se encuentra
                $db = \Config\Database::connect();
                $dbName = $db->query('SELECT DATABASE() AS db')->getRowArray()['db'] ?? '';
                $exists = $db->query('SELECT COUNT(*) AS c FROM orden_produccion WHERE id = ?', [$id])->getRowArray();
                $sample = $db->query('SELECT id, folio FROM orden_produccion ORDER BY id ASC LIMIT 10')->getResultArray();
                return $this->response->setStatusCode(404)->setJSON([
                    'error' => 'Orden no encontrada',
                    'db' => $dbName,
                    'countById' => $exists['c'] ?? null,
                    'sample' => $sample,
                ]);
            }
            return $this->response->setJSON($row);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Excepción en orden_json',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function orden_json_folio($folio = null)
    {
        $folio = trim((string) ($folio ?? ''));
        if ($folio === '') {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Folio inválido']);
        }
        try {
            $model = new OrdenProduccionModel();
            // Primero obtener la OP por folio (básico) para conocer el ID
            $basico = $model->getDetalleBasicoPorFolio($folio);
            if (!$basico) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Orden no encontrada por folio', 'folio' => $folio]);
            }
            // Con el ID, obtener el detalle completo con diseño
            $row = $model->getDetalle((int) $basico['id']);
            if (!$row) {
                // Fallback: devolver básico si por alguna razón el join no devuelve
                return $this->response->setJSON($basico);
            }
            return $this->response->setJSON($row);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Excepción en orden_json_folio',
                'message' => $e->getMessage(),
            ]);
        }
    }

    // -------- Asignaciones de tarea --------
    public function asignaciones($opId = null)
    {
        $opId = (int) ($opId ?? 0);
        if ($opId <= 0)
            return $this->response->setStatusCode(400)->setJSON(['error' => 'ID inválido']);
        $asigModel = new AsignacionTareaModel();
        $empModel = new EmpleadoModel();
        $asignadas = $asigModel->listarPorOP($opId);
        // Filtrar empleados por maquiladora del usuario autenticado
        $maquiladoraId = session()->get('maquiladora_id');
        $empleados = $empModel->listarDisponiblesParaOP($opId, $maquiladoraId);
        return $this->response->setJSON([
            'opId' => $opId,
            'asignadas' => $asignadas,
            'empleados' => $empleados,
        ]);
    }

    public function asignaciones_agregar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }
        $opId = (int) ($this->request->getPost('opId') ?? 0);
        $empleadoId = (int) ($this->request->getPost('empleadoId') ?? 0);
        $desde = $this->request->getPost('desde') ?: null;
        $hasta = $this->request->getPost('hasta') ?: null;
        $ruta = $this->request->getPost('rutaOperacionId') ? (int) $this->request->getPost('rutaOperacionId') : null;
        if ($opId <= 0 || $empleadoId <= 0)
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Parámetros inválidos']);
        $asigModel = new AsignacionTareaModel();
        if (!$asigModel->agregar($opId, $empleadoId, $desde, $hasta, $ruta)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'No se pudo asignar']);
        }

        // Crear notificación de asignación
        try {
            $notificationService = new NotificationService();
            $ordenModel = new OrdenProduccionModel();
            $empleadoModel = new EmpleadoModel();
            
            // Obtener datos para la notificación
            $orden = $ordenModel->find($opId);
            $empleado = $empleadoModel->find($empleadoId);
            
            if ($orden && $empleado) {
                $maquiladoraId = session()->get('maquiladoraID') ?? 1;
                $ordenFolio = $orden['folio'] ?? 'OP-' . $opId;
                $empleadoNombre = trim(($empleado['nombre'] ?? '') . ' ' . ($empleado['apellido'] ?? ''));
                $empleadoPuesto = $empleado['puesto'] ?? 'Empleado';
                
                $notificationService->notifyEmpleadoAsignado(
                    $maquiladoraId,
                    $ordenFolio,
                    $empleadoNombre,
                    $empleadoPuesto
                );
            }
        } catch (\Throwable $e) {
            // No fallar la asignación si hay error en la notificación
            log_message('warning', 'Error al crear notificación de asignación: ' . $e->getMessage());
        }

        return $this->response->setJSON(['ok' => true]);
    }

    public function asignaciones_agregar_multiple()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }
        $opId = (int) ($this->request->getPost('opId') ?? 0);
        $empleados = $this->request->getPost('empleados'); // array de IDs
        $desde = $this->request->getPost('desde') ?: null;
        $hasta = $this->request->getPost('hasta') ?: null;
        $ruta = $this->request->getPost('rutaOperacionId') ? (int) $this->request->getPost('rutaOperacionId') : null;
        if ($opId <= 0 || !is_array($empleados) || empty($empleados)) {
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Parámetros inválidos']);
        }
        $asigModel = new AsignacionTareaModel();
        $ok = 0;
        $dup = 0;
        $fail = 0;
        foreach ($empleados as $eid) {
            $eid = (int) $eid;
            if ($eid <= 0) {
                $fail++;
                continue;
            }
            if ($asigModel->existeAsignacion($opId, $eid)) {
                $dup++;
                continue;
            }
            $res = $asigModel->agregar($opId, $eid, $desde, $hasta, $ruta);
            if ($res)
                $ok++;
            else
                $fail++;
        }

        // Crear notificación si se asignaron empleados exitosamente
        if ($ok > 0) {
            try {
                $notificationService = new NotificationService();
                $ordenModel = new OrdenProduccionModel();
                
                // Obtener datos para la notificación
                $orden = $ordenModel->find($opId);
                
                if ($orden) {
                    $maquiladoraId = session()->get('maquiladoraID') ?? 1;
                    $ordenFolio = $orden['folio'] ?? 'OP-' . $opId;
                    
                    // Si solo se asignó un empleado, usar la notificación individual
                    if ($ok === 1 && count($empleados) === 1) {
                        $empleadoModel = new EmpleadoModel();
                        $empleadoId = (int) $empleados[0];
                        $empleado = $empleadoModel->find($empleadoId);
                        
                        if ($empleado) {
                            $empleadoNombre = trim(($empleado['nombre'] ?? '') . ' ' . ($empleado['apellido'] ?? ''));
                            $empleadoPuesto = $empleado['puesto'] ?? 'Empleado';
                            
                            $notificationService->notifyEmpleadoAsignado(
                                $maquiladoraId,
                                $ordenFolio,
                                $empleadoNombre,
                                $empleadoPuesto
                            );
                        }
                    } else {
                        // Usar notificación múltiple
                        $notificationService->notifyEmpleadosAsignadosMultiple(
                            $maquiladoraId,
                            $ordenFolio,
                            $ok
                        );
                    }
                }
            } catch (\Throwable $e) {
                // No fallar la asignación si hay error en la notificación
                log_message('warning', 'Error al crear notificación de asignación múltiple: ' . $e->getMessage());
            }
        }

        return $this->response->setJSON(['ok' => true, 'asignados' => $ok, 'duplicados' => $dup, 'fallidos' => $fail]);
    }

    public function asignaciones_eliminar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }
        $asignacionId = (int) ($this->request->getPost('asignacionId') ?? 0);
        if ($asignacionId <= 0)
            return $this->response->setStatusCode(400)->setJSON(['error' => 'Parámetro inválido']);
        $asigModel = new AsignacionTareaModel();
        if (!$asigModel->eliminar($asignacionId)) {
            return $this->response->setStatusCode(500)->setJSON(['error' => 'No se pudo eliminar la asignación']);
        }
        return $this->response->setJSON(['ok' => true]);
    }

    // Búsqueda remota (Select2) de empleados disponibles para una OP
    public function empleados_buscar_disponibles($opId = null)
    {
        $opId = (int) ($opId ?? 0);
        if ($opId <= 0)
            return $this->response->setStatusCode(400)->setJSON(['results' => []]);
        $term = trim((string) ($this->request->getGet('term') ?? ''));
        $empModel = new EmpleadoModel();
        // Filtrar por maquiladora del usuario autenticado
        $maquiladoraId = session()->get('maquiladora_id');
        $rows = $empModel->buscarDisponiblesParaOP($opId, $term, 20, $maquiladoraId);
        $results = array_map(function ($r) {
            $text = ($r['noEmpleado'] ? ('[' . $r['noEmpleado'] . '] ') : '') . $r['nombre'] . ' ' . ($r['apellido'] ?? '');
            return ['id' => (int) $r['id'], 'text' => $text];
        }, $rows);
        return $this->response->setJSON(['results' => $results]);
    }

    public function tareas_empleado_json($empleadoId = null)
    {
        try {
            // Permitir query param ?empleadoId=8
            $empleadoId = (int) ($this->request->getGet('empleadoId') ?? $empleadoId ?? 0);
            if ($empleadoId <= 0) {
                $empModel = new EmpleadoModel();
                // 1) por idusuario
                $userId = (int) (session()->get('user_id') ?? 0);
                if ($userId > 0 && $empleadoId <= 0) {
                    $emp = $empModel->where('idusuario', $userId)->select('id')->first();
                    if ($emp && isset($emp['id'])) {
                        $empleadoId = (int) $emp['id'];
                    }
                }
                // 2) por email
                $email = (string) (session()->get('email') ?? '');
                if ($empleadoId <= 0 && $email !== '') {
                    $emp = $empModel->where('email', $email)->select('id')->first();
                    if ($emp && isset($emp['id'])) {
                        $empleadoId = (int) $emp['id'];
                    }
                }
                // 3) por user_name contra noEmpleado o nombre
                $uname = (string) (session()->get('user_name') ?? '');
                if ($empleadoId <= 0 && $uname !== '') {
                    $emp = $empModel->groupStart()
                        ->where('noEmpleado', $uname)
                        ->orWhere('nombre', $uname)
                        ->groupEnd()->select('id')->first();
                    if ($emp && isset($emp['id'])) {
                        $empleadoId = (int) $emp['id'];
                    }
                }
            }
            if ($empleadoId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'No se pudo resolver el empleado actual']);
            }

            // Forzar una nueva conexión para evitar caché
            $db = \Config\Database::connect();
            $db->reconnect();

            // Usar consulta directa para evitar caché del modelo
            // Obtener primero solo los datos básicos sin el JOIN del estatus
            $sql = "SELECT at.id,
                           at.ordenProduccionId AS opId,
                           at.rutaOperacionId,
                           at.asignadoDesde,
                           at.asignadoHasta
                    FROM asignacion_tarea at
                    WHERE at.empleadoId = ?
                    ORDER BY at.asignadoDesde IS NULL, at.asignadoDesde ASC, at.id DESC";
            $rows = $db->query($sql, [$empleadoId])->getResultArray();

            // Asegurar que rows sea un array
            if (!is_array($rows)) {
                $rows = [];
            }

            // Obtener el folio y estatus directamente desde la BD para cada orden
            // Usar consultas individuales con conexión fresca para evitar caché
            $tiempoModel = new TiempoTrabajoModel();
            foreach ($rows as &$row) {
                $opId = (int) ($row['opId'] ?? 0);
                if ($opId <= 0) {
                    $row['folio'] = '';
                    $row['status'] = '';
                    $row['tieneFinalizado'] = false;
                    $row['tiempoActivo'] = false;
                    $row['tiempoTrabajoId'] = null;
                    $row['tiempoInicio'] = null;
                    continue;
                }

                try {
                    // Forzar una nueva conexión para cada consulta de estatus
                    $dbStatus = \Config\Database::connect();
                    $dbStatus->reconnect();

                    // Consulta directa SQL para obtener folio y estatus actualizado
                    $sqlOp = "SELECT folio, status FROM orden_produccion WHERE id = ? LIMIT 1";
                    $opRow = $dbStatus->query($sqlOp, [$opId])->getRowArray();

                    if ($opRow) {
                        $row['folio'] = $opRow['folio'] ?? '';
                        $row['status'] = trim($opRow['status'] ?? '');

                        // Verificar si ya hay un tiempo de trabajo finalizado para este empleado y orden
                        try {
                            $row['tieneFinalizado'] = $tiempoModel->tieneFinalizado($empleadoId, $opId);
                        } catch (\Throwable $e) {
                            // Si falla la verificación, asumir que no tiene finalizado
                            $row['tieneFinalizado'] = false;
                            log_message('warning', "Error al verificar tiempo finalizado para OP {$opId}: " . $e->getMessage());
                        }

                        log_message('debug', "OP {$opId} - Estatus obtenido: '{$row['status']}', tieneFinalizado: " . ($row['tieneFinalizado'] ? 'true' : 'false'));

                        // Tiempo activo (sin finalizar) para rehidratar cronómetro en el front
                        try {
                            $activo = $tiempoModel->obtenerActivo($empleadoId, $opId);
                            $row['tiempoActivo'] = !empty($activo);
                            $row['tiempoTrabajoId'] = !empty($activo) && isset($activo['id']) ? (int) $activo['id'] : null;
                            $row['tiempoInicio'] = !empty($activo) ? ($activo['inicio'] ?? null) : null;
                        } catch (\Throwable $e) {
                            $row['tiempoActivo'] = false;
                            $row['tiempoTrabajoId'] = null;
                            $row['tiempoInicio'] = null;
                            log_message('warning', "Error al obtener tiempo activo para OP {$opId}: " . $e->getMessage());
                        }
                    } else {
                        $row['folio'] = '';
                        $row['status'] = '';
                        $row['tieneFinalizado'] = false;
                        $row['tiempoActivo'] = false;
                        $row['tiempoTrabajoId'] = null;
                        $row['tiempoInicio'] = null;
                        log_message('warning', "OP {$opId} - No se encontró en BD");
                    }
                } catch (\Throwable $e) {
                    // Si hay un error, establecer valores por defecto
                    $row['folio'] = '';
                    $row['status'] = '';
                    $row['tieneFinalizado'] = false;
                    $row['tiempoActivo'] = false;
                    $row['tiempoTrabajoId'] = null;
                    $row['tiempoInicio'] = null;
                    log_message('error', "Error al obtener datos de OP {$opId}: " . $e->getMessage());
                }
            }

            // Asegurar que siempre devolvamos un array válido
            if (!is_array($rows)) {
                $rows = [];
            }

            return $this->response->setJSON([
                'empleadoId' => $empleadoId,
                'items' => $rows
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error en tareas_empleado_json: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al obtener tareas',
                'message' => $e->getMessage(),
                'empleadoId' => $empleadoId ?? 0,
                'items' => []
            ]);
        }
    }

    /**
     * Iniciar tiempo de trabajo para una orden de producción
     */
    public function tiempo_trabajo_iniciar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }

        try {
            // Obtener ordenProduccionId
            $ordenProduccionId = (int) ($this->request->getPost('ordenProduccionId') ?? $this->request->getPost('id') ?? 0);
            if ($ordenProduccionId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'ID de orden de producción inválido']);
            }

            // Obtener empleadoId (similar a tareas_empleado_json)
            $empleadoId = (int) ($this->request->getPost('empleadoId') ?? 0);
            if ($empleadoId <= 0) {
                $empModel = new EmpleadoModel();
                // 1) por idusuario
                $userId = (int) (session()->get('user_id') ?? 0);
                if ($userId > 0 && $empleadoId <= 0) {
                    $emp = $empModel->where('idusuario', $userId)->select('id')->first();
                    if ($emp && isset($emp['id'])) {
                        $empleadoId = (int) $emp['id'];
                    }
                }
                // 2) por email
                $email = (string) (session()->get('email') ?? '');
                if ($empleadoId <= 0 && $email !== '') {
                    $emp = $empModel->where('email', $email)->select('id')->first();
                    if ($emp && isset($emp['id'])) {
                        $empleadoId = (int) $emp['id'];
                    }
                }
                // 3) por user_name contra noEmpleado o nombre
                $uname = (string) (session()->get('user_name') ?? '');
                if ($empleadoId <= 0 && $uname !== '') {
                    $emp = $empModel->groupStart()
                        ->where('noEmpleado', $uname)
                        ->orWhere('nombre', $uname)
                        ->groupEnd()->select('id')->first();
                    if ($emp && isset($emp['id'])) {
                        $empleadoId = (int) $emp['id'];
                    }
                }
            }

            if ($empleadoId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'No se pudo resolver el empleado actual']);
            }

            // Verificar el estatus de la orden de producción
            $db = \Config\Database::connect();
            $db->reconnect(); // Forzar conexión fresca
            $sqlStatus = "SELECT status FROM orden_produccion WHERE id = ? LIMIT 1";
            $opRow = $db->query($sqlStatus, [$ordenProduccionId])->getRowArray();

            if (!$opRow || !isset($opRow['status'])) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Orden de producción no encontrada']);
            }

            $estatusOP = trim($opRow['status']);
            $estatusLower = strtolower($estatusOP);

            // Solo permitir iniciar si el estatus es "En proceso" o "En corte"
            if ($estatusLower !== 'en proceso' && $estatusLower !== 'en corte') {
                return $this->response->setStatusCode(400)->setJSON([
                    'error' => 'Solo se puede iniciar el cronómetro cuando la orden está "En proceso" o "En corte". Estatus actual: ' . $estatusOP
                ]);
            }

            // Verificar si ya existe un registro activo (sin finalizar)
            $tiempoModel = new TiempoTrabajoModel();
            $activo = $tiempoModel->obtenerActivo($empleadoId, $ordenProduccionId);
            if ($activo) {
                return $this->response->setJSON([
                    'ok' => true,
                    'id' => (int) $activo['id'],
                    'yaExiste' => true,
                    'inicio' => $activo['inicio'] ?? null,
                ]);
            }

            // Si la orden está en "En proceso" o "En corte", permitir iniciar el cronómetro
            // incluso si hay un tiempo finalizado anterior (permite reactivar cuando la orden vuelve a proceso)
            // Solo bloquear si la orden NO está en proceso/corte Y ya hay un tiempo finalizado
            if ($estatusLower !== 'en proceso' && $estatusLower !== 'en corte') {
                // Si la orden no está en proceso/corte, verificar si ya hay un tiempo finalizado
                if ($tiempoModel->tieneFinalizado($empleadoId, $ordenProduccionId)) {
                    return $this->response->setStatusCode(400)->setJSON([
                        'error' => 'Ya se finalizó el tiempo de trabajo para esta orden. Solo se puede iniciar nuevamente cuando la orden vuelva a estar "En proceso" o "En corte".'
                    ]);
                }
            }
            // Si la orden está en proceso/corte, permitir iniciar incluso si hay tiempos finalizados anteriores

            // Crear nuevo registro
            $id = $tiempoModel->iniciar($empleadoId, $ordenProduccionId);
            if ($id === false) {
                return $this->response->setStatusCode(500)->setJSON(['error' => 'No se pudo iniciar el tiempo de trabajo']);
            }

            $registroNuevo = $tiempoModel->find((int) $id);

            return $this->response->setJSON([
                'ok' => true,
                'id' => $id,
                'empleadoId' => $empleadoId,
                'ordenProduccionId' => $ordenProduccionId,
                'inicio' => $registroNuevo['inicio'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al iniciar tiempo de trabajo',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Finalizar tiempo de trabajo para una orden de producción
     */
    public function tiempo_trabajo_finalizar()
    {
        $method = strtolower($this->request->getMethod());
        if ($method === 'options') {
            return $this->response->setJSON(['ok' => true]);
        }
        if ($method !== 'post') {
            return $this->response->setStatusCode(405)->setJSON(['error' => 'Método no permitido']);
        }

        try {
            // Opción 1: Por ID del registro de tiempo_trabajo
            $tiempoTrabajoId = (int) ($this->request->getPost('tiempoTrabajoId') ?? $this->request->getPost('id') ?? 0);

            // Opción 2: Por empleadoId y ordenProduccionId (buscar el activo)
            if ($tiempoTrabajoId <= 0) {
                $ordenProduccionId = (int) ($this->request->getPost('ordenProduccionId') ?? 0);
                $empleadoId = (int) ($this->request->getPost('empleadoId') ?? 0);

                if ($ordenProduccionId <= 0 || $empleadoId <= 0) {
                    // Intentar obtener empleadoId de la sesión
                    if ($empleadoId <= 0) {
                        $empModel = new EmpleadoModel();
                        $userId = (int) (session()->get('user_id') ?? 0);
                        if ($userId > 0) {
                            $emp = $empModel->where('idusuario', $userId)->select('id')->first();
                            if ($emp && isset($emp['id'])) {
                                $empleadoId = (int) $emp['id'];
                            }
                        }
                    }

                    if ($empleadoId <= 0 || $ordenProduccionId <= 0) {
                        return $this->response->setStatusCode(400)->setJSON(['error' => 'Parámetros inválidos']);
                    }
                }

                $tiempoModel = new TiempoTrabajoModel();
                $activo = $tiempoModel->obtenerActivo($empleadoId, $ordenProduccionId);
                if (!$activo || !isset($activo['id'])) {
                    return $this->response->setStatusCode(404)->setJSON(['error' => 'No se encontró un registro activo para finalizar']);
                }
                $tiempoTrabajoId = (int) $activo['id'];
            }

            if ($tiempoTrabajoId <= 0) {
                return $this->response->setStatusCode(400)->setJSON(['error' => 'ID de tiempo de trabajo inválido']);
            }

            // Obtener el registro antes de finalizar para conocer empleadoId y ordenProduccionId
            $tiempoModel = new TiempoTrabajoModel();
            $registroAntes = $tiempoModel->find($tiempoTrabajoId);
            if (!$registroAntes) {
                return $this->response->setStatusCode(404)->setJSON(['error' => 'Registro de tiempo de trabajo no encontrado']);
            }

            $empleadoId = (int) ($registroAntes['empleadoId'] ?? 0);
            $ordenProduccionId = (int) ($registroAntes['ordenProduccionId'] ?? 0);

            // Finalizar el registro
            try {
                // Verificar que el registro existe antes de intentar finalizarlo
                $registroVerificar = $tiempoModel->find($tiempoTrabajoId);
                if (!$registroVerificar) {
                    log_message('error', "No se encontró el registro tiempo_trabajo ID: {$tiempoTrabajoId} para finalizar");
                    return $this->response->setStatusCode(404)->setJSON([
                        'error' => 'Registro no encontrado',
                        'message' => 'No se encontró el registro de tiempo de trabajo para finalizar.'
                    ]);
                }

                log_message('debug', "Registro antes de finalizar - ID: {$tiempoTrabajoId}, Inicio: " . ($registroVerificar['inicio'] ?? 'N/A') . ", Fin: " . ($registroVerificar['fin'] ?? 'N/A'));

                $resultado = $tiempoModel->finalizar($tiempoTrabajoId);
                if (!$resultado) {
                    log_message('error', "No se pudo finalizar tiempo_trabajo ID: {$tiempoTrabajoId}");
                    return $this->response->setStatusCode(500)->setJSON([
                        'error' => 'No se pudo finalizar el tiempo de trabajo',
                        'message' => 'El registro no pudo ser actualizado. Verifique los logs para más detalles.'
                    ]);
                }
            } catch (\Throwable $e) {
                log_message('error', "Excepción al finalizar tiempo_trabajo ID: {$tiempoTrabajoId} - " . $e->getMessage());
                log_message('error', "Stack trace: " . $e->getTraceAsString());
                return $this->response->setStatusCode(500)->setJSON([
                    'error' => 'Error al finalizar tiempo de trabajo',
                    'message' => $e->getMessage()
                ]);
            }

            // Obtener el registro finalizado para devolver los datos
            $registro = $tiempoModel->find($tiempoTrabajoId);

            // Obtener el puesto del empleado
            $empModel = new EmpleadoModel();
            $empleado = $empModel->find($empleadoId);
            $puesto = $empleado && isset($empleado['puesto']) ? trim((string) $empleado['puesto']) : '';

            // Verificar si todos los empleados de ese tipo han finalizado
            $todosFinalizados = false;
            $nuevoEstatus = null;

            if (!empty($puesto) && $ordenProduccionId > 0) {
                // Normalizar el puesto (comparar sin importar mayúsculas/minúsculas)
                $puestoLower = strtolower(trim($puesto));

                log_message('debug', "Verificando finalización para OP {$ordenProduccionId}, puesto: '{$puesto}' (normalizado: '{$puestoLower}')");

                // Forzar una nueva conexión para evitar caché al verificar
                $db = \Config\Database::connect();
                $db->reconnect();

                if ($puestoLower === 'corte') {
                    $todosFinalizados = $tiempoModel->todosHanFinalizado($ordenProduccionId, 'Corte');
                    log_message('debug', "Corte - todosHanFinalizado para OP {$ordenProduccionId}: " . ($todosFinalizados ? 'true' : 'false'));
                    if ($todosFinalizados) {
                        $nuevoEstatus = 'Corte finalizado';
                    }
                } elseif ($puestoLower === 'empleado') {
                    $todosFinalizados = $tiempoModel->todosHanFinalizado($ordenProduccionId, 'Empleado');
                    log_message('debug', "Empleado - todosHanFinalizado para OP {$ordenProduccionId}: " . ($todosFinalizados ? 'true' : 'false'));
                    if ($todosFinalizados) {
                        $nuevoEstatus = 'Completada';
                    }
                } else {
                    log_message('debug', "Puesto '{$puestoLower}' no coincide con 'corte' ni 'empleado'");
                }
            } else {
                log_message('debug', "No se puede verificar finalización - puesto vacío o ordenProduccionId inválido. Puesto: '{$puesto}', OP: {$ordenProduccionId}");
            }

            // Actualizar el estatus de la OP si todos han finalizado
            $estatusActualizado = false;
            if ($todosFinalizados && $nuevoEstatus !== null && $ordenProduccionId > 0) {
                try {
                    $db = \Config\Database::connect();
                    $db->transStart();

                    // Actualizar el estatus usando el mismo método que actualizarEstatus
                    $okUpd = $db->table('orden_produccion')->where('id', $ordenProduccionId)->update(['status' => $nuevoEstatus]);

                    if (!$okUpd) {
                        $db->transRollback();
                        log_message('error', "No se pudo actualizar el estatus de OP {$ordenProduccionId}");
                        throw new \Exception("No se pudo actualizar el estatus");
                    }

                    // Si el nuevo estatus es "Completada", actualizar también orden_compra a "Finalizada"
                    if (strcasecmp($nuevoEstatus, 'Completada') === 0) {
                        // Obtener ordenCompraId de la OP
                        $ocId = null;
                        try {
                            $rowOP = $db->query('SELECT ordenCompraId FROM orden_produccion WHERE id = ?', [$ordenProduccionId])->getRowArray();
                            if ($rowOP && isset($rowOP['ordenCompraId'])) {
                                $ocId = (int) $rowOP['ordenCompraId'];
                            }
                        } catch (\Throwable $e) {
                            try {
                                $rowOP = $db->query('SELECT ordenCompraId FROM OrdenProduccion WHERE id = ?', [$ordenProduccionId])->getRowArray();
                                if ($rowOP && isset($rowOP['ordenCompraId'])) {
                                    $ocId = (int) $rowOP['ordenCompraId'];
                                }
                            } catch (\Throwable $e2) {
                                // Ignorar si no existe la tabla con mayúsculas
                            }
                        }

                        // Actualizar estatus de orden_compra a "Finalizada" si existe
                        if ($ocId && $ocId > 0) {
                            try {
                                $okOc = $db->table('orden_compra')->where('id', $ocId)->update(['estatus' => 'Finalizada']);
                                if (!$okOc) {
                                    try {
                                        $db->table('OrdenCompra')->where('id', $ocId)->update(['estatus' => 'Finalizada']);
                                    } catch (\Throwable $e) {
                                        log_message('warning', "No se pudo actualizar estatus de orden_compra ID: {$ocId}");
                                    }
                                } else {
                                    log_message('info', "Estatus de orden_compra ID: {$ocId} actualizado a 'Finalizada'");
                                }
                            } catch (\Throwable $e) {
                                log_message('warning', "Error al actualizar estatus de orden_compra ID: {$ocId}: " . $e->getMessage());
                            }
                        }
                    }

                    $db->transComplete();

                    if ($db->transStatus() === false) {
                        log_message('error', "Error en la transacción al actualizar estatus de OP {$ordenProduccionId}");
                        throw new \Exception("Error en la transacción al actualizar estatus");
                    }

                    // Verificar que el estatus se haya actualizado realmente
                    // Forzar una nueva conexión para leer el estatus actualizado
                    $dbVerificar = \Config\Database::connect();
                    $dbVerificar->reconnect();
                    $sqlVerificar = "SELECT status FROM orden_produccion WHERE id = ? LIMIT 1";
                    $opVerificada = $dbVerificar->query($sqlVerificar, [$ordenProduccionId])->getRowArray();
                    $estatusVerificado = $opVerificada && isset($opVerificada['status']) ? trim($opVerificada['status']) : '';

                    if ($estatusVerificado === $nuevoEstatus) {
                        $estatusActualizado = true;
                        log_message('info', "Estatus de OP {$ordenProduccionId} actualizado correctamente a '{$nuevoEstatus}'");
                    } else {
                        log_message('warning', "Estatus de OP {$ordenProduccionId} no se actualizó correctamente. Esperado: '{$nuevoEstatus}', Obtenido: '{$estatusVerificado}'");
                    }
                } catch (\Throwable $e) {
                    // Log el error pero no fallar la respuesta
                    log_message('error', 'Error al actualizar estatus de OP: ' . $e->getMessage());
                }
            }

            return $this->response->setJSON([
                'ok' => true,
                'id' => $tiempoTrabajoId,
                'inicio' => $registro['inicio'] ?? null,
                'fin' => $registro['fin'] ?? null,
                'horas' => $registro['horas'] ?? null,
                'todosFinalizados' => $todosFinalizados,
                'nuevoEstatus' => $nuevoEstatus,
                'estatusActualizado' => $estatusActualizado,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'error' => 'Error al finalizar tiempo de trabajo',
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function orden_comparticion($opId = null)
    {
        $opId = (int) ($opId ?? 0);
        if ($opId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'ID inválido']);
        }

        $db = \Config\Database::connect();
        $maquiladoraSesion = session()->get('maquiladora_id') ?? session()->get('maquiladoraID');
        $maquiladoraSesion = $maquiladoraSesion ? (int) $maquiladoraSesion : null;

        $opRow = null;
        try {
            $opRow = $db->query('SELECT id, folio, cantidadPlan, maquiladoraID, maquiladoraCompartidaID FROM orden_produccion WHERE id = ? LIMIT 1', [$opId])->getRowArray();
        } catch (\Throwable $e) {
            try {
                $opRow = $db->query('SELECT id, folio, cantidadPlan, maquiladoraID, maquiladoraCompartidaID FROM OrdenProduccion WHERE id = ? LIMIT 1', [$opId])->getRowArray();
            } catch (\Throwable $e2) {
                $opRow = null;
            }
        }

        if (!$opRow) {
            return $this->response->setStatusCode(404)->setJSON(['ok' => false, 'message' => 'Orden no encontrada']);
        }

        $ownId = (int) ($opRow['maquiladoraID'] ?? 0);
        $sharedToId = (int) ($opRow['maquiladoraCompartidaID'] ?? 0);

        if ($maquiladoraSesion && $ownId !== $maquiladoraSesion && $sharedToId !== $maquiladoraSesion) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
        }

        $tipo = 'normal';
        if ($maquiladoraSesion && $ownId && $ownId !== $maquiladoraSesion) {
            $tipo = 'entrante';
        } elseif ($sharedToId > 0) {
            $tipo = 'saliente';
        }

        $getMaquiladoraNombre = function (int $id) use ($db): ?string {
            if ($id <= 0) {
                return null;
            }
            try {
                $r = $db->query('SELECT Nombre_Maquila AS nombre FROM maquiladora WHERE idmaquiladora = ? LIMIT 1', [$id])->getRowArray();
                return $r['nombre'] ?? null;
            } catch (\Throwable $e) {
                try {
                    $r = $db->query('SELECT Nombre_Maquila AS nombre FROM Maquiladora WHERE idmaquiladora = ? LIMIT 1', [$id])->getRowArray();
                    return $r['nombre'] ?? null;
                } catch (\Throwable $e2) {
                    return null;
                }
            }
        };

        $origenNombre = $getMaquiladoraNombre($ownId);
        $destinoNombre = $getMaquiladoraNombre($sharedToId);

        $control = null;
        try {
            $control = $db->query('SELECT id, cantidad_total, estado, created_at FROM control_bultos WHERE ordenProduccionId = ? ORDER BY created_at DESC, id DESC LIMIT 1', [$opId])->getRowArray();
        } catch (\Throwable $e) {
            $control = null;
        }

        $avance = null;
        if (!empty($control) && isset($control['id'])) {
            $controlId = (int) $control['id'];
            $progreso = null;
            try {
                $cbModel = new \App\Models\ControlBultosModel();
                $progreso = $cbModel->calcularProgresoGeneral($controlId);
            } catch (\Throwable $e) {
                $progreso = null;
            }
            $avance = [
                'controlBultoId' => $controlId,
                'cantidad_total' => isset($control['cantidad_total']) ? (int) $control['cantidad_total'] : null,
                'estado' => $control['estado'] ?? null,
                'progreso_general' => $progreso,
            ];
        }

        return $this->response->setJSON([
            'ok' => true,
            'op' => [
                'id' => (int) ($opRow['id'] ?? $opId),
                'folio' => $opRow['folio'] ?? '',
                'cantidadPlan' => isset($opRow['cantidadPlan']) ? (int) $opRow['cantidadPlan'] : null,
                'tipo' => $tipo,
            ],
            'origen' => [
                'id' => $ownId,
                'nombre' => $origenNombre,
            ],
            'destino' => [
                'id' => $sharedToId,
                'nombre' => $destinoNombre,
            ],
            'avance' => $avance,
        ]);
    }
}

