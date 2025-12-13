<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ControlBultosModel;
use App\Models\PlantillaOperacionModel;
use App\Models\OperacionControlModel;
use App\Models\OrdenProduccionModel;
use App\Models\EmpleadoModel;
use App\Models\RegistroProduccionModel;

class ControlBultosController extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    /**
     * Vista principal
     */
    public function index()
    {
        if (!can('menu.inspeccion')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');
        // Usar el mismo key que auth_helper (user_id) como prioridad, con compatibilidad hacia atrás
        $usuarioId = $session->get('usuario_id')
            ?? $session->get('user_id')
            ?? $session->get('id');

        $controlModel = new ControlBultosModel();
        $plantillaModel = new PlantillaOperacionModel();
        $ordenModel = new OrdenProduccionModel();
        $empleadoModel = new EmpleadoModel();

        $empleadoActual = null;
        if ($usuarioId) {
            $empleadoActual = $empleadoModel
                ->where('idusuario', (int) $usuarioId)
                ->where('activo', 1)
                ->first();
        }

        $data = [
            'controles' => $controlModel->getConMaquiladora($maquiladoraId),
            'plantillas' => $plantillaModel->getPlantillasPorMaquiladora($maquiladoraId),
            // Solo órdenes de producción que aún no tienen control de bultos
            'ordenes' => $ordenModel->getListadoSinControl($maquiladoraId),
            'empleados' => $empleadoModel->getEmpleadosActivos(),
            'empleadoActual' => $empleadoActual,
        ];

        return view('modulos/control_bultos', $data);
    }

    /**
     * API: Listar controles
     */
    public function listar()
    {
        if (!can('menu.inspeccion')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
        }

        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');

        $controlModel = new ControlBultosModel();
        $controles = $controlModel->getConMaquiladora($maquiladoraId);

        return $this->response->setJSON([
            'ok' => true,
            'data' => $controles
        ]);
    }

    /**
     * API: Detalle de control
     */
    public function detalle($id)
    {
        if (!can('menu.inspeccion')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
        }

        $controlModel = new ControlBultosModel();
        $control = $controlModel->getDetallado($id);

        if (!$control) {
            return $this->response->setStatusCode(404)->setJSON([
                'ok' => false,
                'message' => 'Control no encontrado'
            ]);
        }

        return $this->response->setJSON([
            'ok' => true,
            'data' => $control
        ]);
    }

    
    /**
     * API: Registrar rendimiento individual del empleado
     */
    public function registrarRendimientoEmpleado()
    {
        try {
            // Temporalmente sin validación de permisos para permitir acceso
            // if (!can('menu.empleados')) {
            //     return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
            // }

            $ordenProduccionId = $this->request->getPost('ordenProduccionId');
            $operacionControlId = $this->request->getPost('operacionControlId');
            $empleadoId = $this->request->getPost('empleadoId');
            $cantidad = $this->request->getPost('cantidad');
            $fechaRegistro = $this->request->getPost('fecha_registro');
            $horaInicio = $this->request->getPost('hora_inicio');
            $horaFin = $this->request->getPost('hora_fin');
            $notas = $this->request->getPost('notas');
            $tallaInfo = trim((string) $this->request->getPost('talla_info'));

            // Validaciones básicas
            if (!$ordenProduccionId || !$operacionControlId || !$empleadoId || !$cantidad || !$fechaRegistro) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'Faltan datos requeridos'
                ]);
            }

            if ($cantidad <= 0) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'La cantidad debe ser mayor a 0'
                ]);
            }

            // Validar que la operación pertenezca al control correcto
            $operacionModel = new OperacionControlModel();
            $operacion = $operacionModel->find($operacionControlId);
            
            if (!$operacion) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'Operación no encontrada'
                ]);
            }

            // Obtener control de bultos para verificar la orden de producción
            $controlModel = new ControlBultosModel();
            $control = $controlModel->find($operacion['controlBultoId']);
            
            if (!$control || $control['ordenProduccionId'] != $ordenProduccionId) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'La operación no corresponde a la orden de producción especificada'
                ]);
            }

            // Validar que el empleado esté asignado a la orden de producción
            $empleadoModel = new EmpleadoModel();
            $empleadosAsignados = $empleadoModel->getEmpleadosPorOrden($ordenProduccionId);
            
            $empleadoAsignado = false;
            foreach ($empleadosAsignados as $emp) {
                if ($emp['id'] == $empleadoId) {
                    $empleadoAsignado = true;
                    break;
                }
            }

            if (!$empleadoAsignado) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'El empleado no está asignado a esta orden de producción'
                ]);
            }

            // Registrar la producción usando el método existente
            $datosProduccion = [
                'empleadoId' => $empleadoId,
                'cantidad' => $cantidad,
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'notas' => $notas
            ];

            $resultado = $this->registrarProduccionIndividual($operacionControlId, $datosProduccion);

            if ($resultado['success']) {
                if ($tallaInfo !== '') {
                    $this->aplicarCantidadATallaEnBultos((int) $operacion['controlBultoId'], (int) $operacionControlId, (int) $empleadoId, (int) $cantidad, $tallaInfo);
                }
                return $this->response->setJSON([
                    'ok' => true,
                    'message' => 'Rendimiento registrado correctamente',
                    'data' => [
                        'registro_id' => $resultado['registro_id'],
                        'cantidad_registrada' => $cantidad,
                        'operacion' => $operacion['nombre_operacion']
                    ]
                ]);
            } else {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => $resultado['message'] ?? 'Error al registrar el rendimiento'
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en registrarRendimientoEmpleado: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Error interno del servidor'
            ]);
        }
    }

    private function aplicarCantidadATallaEnBultos(int $controlBultoId, int $operacionControlId, int $empleadoId, int $cantidad, string $tallaKey): void
    {
        if ($cantidad <= 0 || $tallaKey === '') {
            return;
        }

        try {
            // Si aún no existen bultos, crear 1 bulto por talla según pedido_tallas_detalle.
            // Esto evita que el progreso por talla se "divida" por prorrateo.
            $totalBultos = (int) $this->db->table('bultos')
                ->where('controlBultoId', $controlBultoId)
                ->countAllResults();

            if ($totalBultos === 0) {
                $controlRow = $this->db->table('control_bultos')
                    ->select('ordenProduccionId')
                    ->where('id', $controlBultoId)
                    ->get()
                    ->getRowArray();

                $ordenProduccionId = (int) ($controlRow['ordenProduccionId'] ?? 0);
                if ($ordenProduccionId > 0) {
                    try {
                        $tallas = $this->db->query(
                            "SELECT ptd.cantidad, t.nombre AS nombre_talla\n" .
                            "FROM pedido_tallas_detalle ptd\n" .
                            "LEFT JOIN tallas t ON t.id_talla = ptd.id_talla\n" .
                            "WHERE ptd.ordenProduccionId = ?",
                            [$ordenProduccionId]
                        )->getResultArray();

                        $num = 1;
                        foreach ($tallas as $t) {
                            $nombreTalla = trim((string) ($t['nombre_talla'] ?? ''));
                            $cantTalla = (int) ($t['cantidad'] ?? 0);
                            if ($nombreTalla === '' || $cantTalla <= 0) {
                                continue;
                            }
                            $this->db->table('bultos')->insert([
                                'controlBultoId' => $controlBultoId,
                                'numero_bulto' => (string) $num,
                                'talla' => $nombreTalla,
                                'cantidad' => $cantTalla,
                                'observaciones' => null,
                                'created_at' => date('Y-m-d H:i:s'),
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);
                            $num++;
                        }
                    } catch (\Throwable $e) {
                        // si falla, seguimos sin bloquear registro
                    }
                }
            }

            $bultos = $this->db->table('bultos')
                ->select('id, cantidad')
                ->where('controlBultoId', $controlBultoId)
                ->where('talla', $tallaKey)
                ->orderBy('numero_bulto', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($bultos)) {
                return;
            }

            $this->db->transStart();

            $restante = $cantidad;
            foreach ($bultos as $b) {
                if ($restante <= 0) {
                    break;
                }

                $bultoId = (int) ($b['id'] ?? 0);
                $capacidadTotal = (int) ($b['cantidad'] ?? 0);
                if ($bultoId <= 0 || $capacidadTotal <= 0) {
                    continue;
                }

                $row = $this->db->table('progreso_bulto_operacion')
                    ->where('bultoId', $bultoId)
                    ->where('operacionControlId', $operacionControlId)
                    ->get()
                    ->getRowArray();

                $actual = (int) ($row['cantidad_completada'] ?? 0);
                $disponible = max(0, $capacidadTotal - $actual);
                if ($disponible <= 0) {
                    continue;
                }

                $agregar = min($restante, $disponible);
                $nuevo = $actual + $agregar;
                $completado = $nuevo >= $capacidadTotal ? 1 : 0;
                $payload = [
                    'bultoId' => $bultoId,
                    'operacionControlId' => $operacionControlId,
                    'completado' => $completado,
                    'cantidad_completada' => $nuevo,
                    'empleadoId' => $empleadoId,
                    'fecha_completado' => date('Y-m-d H:i:s'),
                ];

                if ($row) {
                    $this->db->table('progreso_bulto_operacion')
                        ->where('id', $row['id'])
                        ->update($payload);
                } else {
                    $this->db->table('progreso_bulto_operacion')->insert($payload);
                }

                $restante -= $agregar;
            }

            $this->db->transComplete();
        } catch (\Throwable $e) {
            // silencioso: no bloquea el registro principal
        }
    }

    /**
     * Registrar producción individual de un empleado
     */
    private function registrarProduccionIndividual($operacionControlId, $datosProduccion)
    {
        try {
            $empleadoId = $datosProduccion['empleadoId'];
            $cantidad = $datosProduccion['cantidad'];
            $horaInicio = $datosProduccion['hora_inicio'];
            $horaFin = $datosProduccion['hora_fin'];
            $notas = $datosProduccion['notas'] ?? '';

            // Validar que la operación exista
            $operacionModel = new OperacionControlModel();
            $operacion = $operacionModel->find($operacionControlId);
            
            if (!$operacion) {
                return ['success' => false, 'message' => 'Operación no encontrada'];
            }

            // Usar el modelo existente para registrar la producción
            $registroModel = new \App\Models\RegistroProduccionModel();
            
            $data = [
                'operacionControlId' => $operacionControlId,
                'empleadoId' => $empleadoId,
                'cantidad_producida' => $cantidad,
                'fecha_registro' => date('Y-m-d'),
                'hora_inicio' => $horaInicio,
                'hora_fin' => $horaFin,
                'observaciones' => $notas
            ];

            $resultado = $registroModel->registrarProduccion($data);
            
            if ($resultado['ok']) {
                return [
                    'success' => true, 
                    'registro_id' => $resultado['id'],
                    'message' => 'Producción registrada correctamente'
                ];
            } else {
                return [
                    'success' => false, 
                    'message' => 'Error al registrar la producción: ' . ($resultado['message'] ?? 'Error desconocido')
                ];
            }

        } catch (\Exception $e) {
            log_message('error', 'Error en registrarProduccionIndividual: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error interno: ' . $e->getMessage()];
        }
    }

    /**
     * API: Obtener controles de bultos por orden de producción
     */
    public function obtenerControlesPorOP($opId)
    {
        try {
            // Temporalmente sin validación de permisos para permitir acceso
            // if (!can('menu.empleados')) {
            //     return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
            // }

            $controlModel = new ControlBultosModel();
            $operacionModel = new OperacionControlModel();

            // Obtener controles de bultos para esta OP
            $controles = $controlModel->where('ordenProduccionId', $opId)->findAll();

            $controlesConOperaciones = [];
            foreach ($controles as $control) {
                // Obtener operaciones de cada control
                $operaciones = $operacionModel->where('controlBultoId', $control['id'])->orderBy('orden', 'ASC')->findAll();
                
                $controlesConOperaciones[] = [
                    'id' => $control['id'],
                    'estilo' => $control['estilo'],
                    'cantidad_total' => $control['cantidad_total'],
                    'estado' => $control['estado'],
                    'operaciones' => $operaciones
                ];
            }

            return $this->response->setJSON([
                'ok' => true,
                'data' => $controlesConOperaciones
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error en obtenerControlesPorOP: ' . $e->getMessage());
            
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Error interno del servidor'
            ]);
        }
    }
    public function progreso($id)
    {
        try {
            $roleName = function_exists('current_role_name') ? (string) current_role_name() : '';
            $roleNorm = mb_strtolower(trim($roleName));
            $permitido = can('menu.inspeccion') || can('menu.produccion') || $roleNorm === 'corte';
            if (!$permitido) {
                return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
            }

            $controlModel = new ControlBultosModel();
            $operacionModel = new OperacionControlModel();
            $empleadoModel = new EmpleadoModel();

            $control = $controlModel->find($id);

            if (!$control) {
                return $this->response->setStatusCode(404)->setJSON([
                    'ok' => false,
                    'message' => 'Control no encontrado'
                ]);
            }

            $progresoGeneral = $controlModel->calcularProgresoGeneral($id);
            $listoParaArmado = $controlModel->verificarListoParaArmado($id);
            $estadisticas = $operacionModel->getEstadisticas($id);

            // Obtener lista de operaciones para la vista (versión simple original)
            $operaciones = $operacionModel->where('controlBultoId', $id)->orderBy('orden', 'ASC')->findAll();

            // Obtener empleados asignados a la orden de producción
            $empleadosAsignados = [];
            if (!empty($control['ordenProduccionId'])) {
                try {
                    // Obtener empleados de la orden de producción
                    $empleadosAsignados = $empleadoModel->getEmpleadosPorOrden($control['ordenProduccionId']);
                } catch (\Exception $e) {
                    log_message('error', 'Error obteniendo empleados para orden ' . $control['ordenProduccionId'] . ': ' . $e->getMessage());
                    $empleadosAsignados = [];
                }
            }

            // Obtener tallas del control si aplica
            $tallasControl = [];
            if (!empty($control['ordenProduccionId'])) {
                try {
                    // Obtener detalles de tallas con nombres de sexo y talla
                    $tallasControl = $this->db->query(
                        "SELECT ptd.*, s.nombre AS nombre_sexo, t.nombre AS nombre_talla 
                         FROM pedido_tallas_detalle ptd
                         LEFT JOIN sexo s ON s.id_sexo = ptd.id_sexo
                         LEFT JOIN tallas t ON t.id_talla = ptd.id_talla
                         WHERE ptd.ordenProduccionId = ?",
                        [$control['ordenProduccionId']]
                    )->getResultArray();
                } catch (\Exception $e) {
                    log_message('error', 'Error obteniendo tallas de pedido para orden ' . $control['ordenProduccionId'] . ': ' . $e->getMessage());
                    $tallasControl = [];
                }
            }

            // Progreso por talla (solo si hay múltiples tallas)
            $progresoPorTalla = [];
            if (count($tallasControl) > 1) {
                $totalCantidadTallas = 0;
                foreach ($tallasControl as $t) {
                    $totalCantidadTallas += (int) ($t['cantidad'] ?? 0);
                }

                $bultosExistentes = (int) $this->db->table('bultos')
                    ->where('controlBultoId', $id)
                    ->countAllResults();

                // completadas reales por talla/operación desde matriz (si existen bultos)
                $completadasPorTallaOperacion = [];
                if ($bultosExistentes > 0) {
                    try {
                        $rows = $this->db->query(
                            "SELECT b.talla, oc.id as operacionId, SUM(COALESCE(pbo.cantidad_completada, 0)) as completadas\n" .
                            "FROM bultos b\n" .
                            "CROSS JOIN operaciones_control oc\n" .
                            "LEFT JOIN progreso_bulto_operacion pbo ON pbo.bultoId = b.id AND pbo.operacionControlId = oc.id\n" .
                            "WHERE b.controlBultoId = ? AND oc.controlBultoId = ?\n" .
                            "GROUP BY b.talla, oc.id\n" .
                            "ORDER BY b.talla ASC, oc.orden ASC",
                            [$id, $id]
                        )->getResultArray();

                        foreach ($rows as $r) {
                            $tallaKey = (string) ($r['talla'] ?? '');
                            $opKey = (string) ($r['operacionId'] ?? '');
                            if ($tallaKey === '' || $opKey === '') {
                                continue;
                            }
                            $completadasPorTallaOperacion[$tallaKey][$opKey] = (int) ($r['completadas'] ?? 0);
                        }
                    } catch (\Throwable $e) {
                        $completadasPorTallaOperacion = [];
                    }
                }

                foreach ($tallasControl as $t) {
                    $nombreSexo = (string) ($t['nombre_sexo'] ?? '');
                    $nombreTalla = (string) ($t['nombre_talla'] ?? '');
                    $cantidadTalla = (int) ($t['cantidad'] ?? 0);

                    $titulo = trim($nombreSexo . ' ' . $nombreTalla);
                    if ($titulo === '') {
                        $titulo = 'Talla';
                    }

                    // bultos.talla normalmente es VARCHAR; intentamos matchear con nombre_talla
                    $tallaKey = $nombreTalla;

                    $ops = [];
                    foreach ($operaciones as $op) {
                        $opId = (int) ($op['id'] ?? 0);
                        $piezasReqTotal = (int) ($op['piezas_requeridas'] ?? 0);
                        $piezasReqTalla = $totalCantidadTallas > 0
                            ? (int) round(($piezasReqTotal * $cantidadTalla) / $totalCantidadTallas)
                            : $piezasReqTotal;

                        $completadasReal = (int) ($completadasPorTallaOperacion[$tallaKey][(string) $opId] ?? 0);

                        // Fallback: prorratear del total completado SOLO cuando no existen bultos.
                        // Si existen bultos, el progreso por talla debe salir únicamente de progreso_bulto_operacion
                        // para evitar "dividir" la producción registrada entre tallas.
                        if ($bultosExistentes === 0) {
                            $piezasCompTotal = (int) ($op['piezas_completadas'] ?? 0);
                            $completadasReal = $totalCantidadTallas > 0
                                ? (int) round(($piezasCompTotal * $cantidadTalla) / $totalCantidadTallas)
                                : $piezasCompTotal;
                        }

                        $completadasReal = max(0, min($piezasReqTalla, $completadasReal));
                        $porcentaje = $piezasReqTalla > 0 ? round(($completadasReal / $piezasReqTalla) * 100, 2) : 0;

                        $ops[] = [
                            'id' => $opId,
                            'nombre_operacion' => $op['nombre_operacion'] ?? '',
                            'es_componente' => $op['es_componente'] ?? 1,
                            'piezas_requeridas' => $piezasReqTalla,
                            'piezas_completadas' => $completadasReal,
                            'porcentaje_completado' => $porcentaje,
                        ];
                    }

                    $progresoPorTalla[] = [
                        'titulo' => $titulo,
                        'talla_key' => $nombreTalla,
                        'cantidad' => $cantidadTalla,
                        'operaciones' => $ops,
                    ];
                }
            }

            return $this->response->setJSON([
                'ok' => true,
                'data' => [
                    'estado' => $control['estado'],
                    'progreso_general' => $progresoGeneral,
                    'listo_para_armado' => $listoParaArmado,
                    'estadisticas' => $estadisticas,
                    'operaciones' => $operaciones,
                    'empleados' => $empleadosAsignados,
                    'tallas' => $tallasControl,
                    'con_tallas' => count($tallasControl) > 1 ? 1 : 0,
                    'progreso_por_talla' => $progresoPorTalla
                ]
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error en progreso para control ID ' . $id . ': ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
                'debug_info' => [
                    'control_id' => $id,
                    'error_line' => $e->getLine(),
                    'error_file' => $e->getFile()
                ]
            ]);
        }
    }

    /**
     * API: Crear control desde plantilla
     */
    public function crear()
    {
        if (!can('menu.inspeccion')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
        }

        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');
        $usuarioId = $session->get('usuario_id') ?? $session->get('id');

        $data = [
            'idmaquiladora' => $maquiladoraId,
            'ordenProduccionId' => $this->request->getPost('ordenProduccionId'),
            'inspeccionId' => $this->request->getPost('inspeccionId'),
            'estilo' => $this->request->getPost('estilo'),
            'orden' => $this->request->getPost('orden'),
            'cantidad_total' => $this->request->getPost('cantidad_total'),
            'plantillaId' => $this->request->getPost('plantillaId'),
            'usuario_creacion' => $usuarioId,
        ];

        // Capturar tallas si se envían (control con tallas)
        $tallas = [];
        foreach ($this->request->getPost() as $key => $value) {
            if (strpos($key, 'talla_') === 0 && !empty($value)) {
                $tallaId = str_replace('talla_', '', $key);
                $tallas[] = [
                    'id_talla' => $tallaId,
                    'cantidad' => intval($value)
                ];
            }
        }

        // Marcar si es control con tallas
        $data['con_tallas'] = !empty($tallas) ? 1 : 0;
        
        // Agregar tallas al data si hay
        if (!empty($tallas)) {
            $data['tallas'] = $tallas;
        }

        // Validaciones
        if (empty($data['ordenProduccionId']) || empty($data['cantidad_total'])) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'La orden de producción y cantidad total son requeridas'
            ]);
        }

        // Si no viene el folio de la orden, buscarlo
        if (empty($data['orden'])) {
            $ordenModel = new OrdenProduccionModel();
            $orden = $ordenModel->find($data['ordenProduccionId']);
            if ($orden) {
                $data['orden'] = $orden['folio'];
            } else {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'Orden de producción no válida'
                ]);
            }
        }

        $controlModel = new ControlBultosModel();
        $controlId = $controlModel->crearControl($data);

        if ($controlId) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Control creado correctamente',
                'id' => $controlId
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo crear el control'
        ]);
    }

    /**
     * API: Editar control
     */
    public function editar($id)
    {
        if (!can('menu.inspeccion')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
        }

        $controlModel = new ControlBultosModel();

        $data = [
            'estilo' => $this->request->getPost('estilo'),
            'orden' => $this->request->getPost('orden'),
            'cantidad_total' => $this->request->getPost('cantidad_total'),
        ];

        if ($controlModel->update($id, $data)) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Control actualizado correctamente'
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo actualizar el control'
        ]);
    }

    /**
     * API: Eliminar control
     */
    public function eliminar($id)
    {
        if (!can('menu.inspeccion')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
        }

        $controlModel = new ControlBultosModel();

        if ($controlModel->delete($id)) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Control eliminado correctamente'
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo eliminar el control'
        ]);
    }

    /**
     * API: Registrar producción de empleado(s) con cantidades individuales
     */
    public function registrarProduccion()
    {
        $session = session();
        $usuarioId = $session->get('usuario_id') ?? $session->get('id');

        // Obtener datos del formulario
        $operacionControlId = $this->request->getPost('operacionControlId');
        $fecha_registro = $this->request->getPost('fecha_registro') ?? date('Y-m-d');
        $hora_inicio = $this->request->getPost('hora_inicio');
        $hora_fin = $this->request->getPost('hora_fin');
        $observaciones = $this->request->getPost('observaciones');

        // Obtener cantidades por empleado (JSON)
        $empleadosCantidadesJson = $this->request->getPost('empleados_cantidades');
        $cantidadesPorEmpleado = [];

        if ($empleadosCantidadesJson) {
            $cantidadesPorEmpleado = json_decode($empleadosCantidadesJson, true) ?: [];
        }

        // Validaciones básicas
        if (empty($operacionControlId) || empty($cantidadesPorEmpleado)) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Operación y cantidades por empleado son requeridos'
            ]);
        }

        // Validar que no se exceda la cantidad requerida
        $operacionModel = new OperacionControlModel();
        $operacion = $operacionModel->find($operacionControlId);

        if (!$operacion) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Operación no encontrada'
            ]);
        }

        // Calcular total de cantidades a registrar
        $totalCantidad = 0;
        foreach ($cantidadesPorEmpleado as $item) {
            $totalCantidad += intval($item['cantidad']);
        }

        // Validar que el total no exceda las piezas requeridas
        $nuevaCantidadTotal = $operacion['piezas_completadas'] + $totalCantidad;
        if ($nuevaCantidadTotal > $operacion['piezas_requeridas']) {
            $maximoPermitido = $operacion['piezas_requeridas'] - $operacion['piezas_completadas'];
            return $this->response->setJSON([
                'ok' => false,
                'message' => "El total ($totalCantidad) excede las piezas requeridas. Máximo permitido: $maximoPermitido"
            ]);
        }

        $registroModel = new RegistroProduccionModel();
        $resultados = [];
        $errores = [];

        // Registrar producción para cada empleado con su cantidad específica
        foreach ($cantidadesPorEmpleado as $item) {
            $empleadoId = $item['empleadoId'];
            $cantidad = $item['cantidad'];

            // Solo registrar si la cantidad es mayor a 0
            if ($cantidad > 0) {
                $data = [
                    'operacionControlId' => $operacionControlId,
                    'empleadoId' => $empleadoId,
                    'cantidad_producida' => $cantidad,
                    'fecha_registro' => $fecha_registro,
                    'hora_inicio' => $hora_inicio,
                    'hora_fin' => $hora_fin,
                    'observaciones' => $observaciones,
                    'registrado_por' => $usuarioId,
                ];

                $resultado = $registroModel->registrarProduccion($data);

                if (is_array($resultado) && isset($resultado['ok']) && $resultado['ok']) {
                    $resultados[] = [
                        'empleadoId' => $empleadoId,
                        'cantidad' => $cantidad,
                        'registroId' => $resultado['id']
                    ];
                } else {
                    $errores[] = [
                        'empleadoId' => $empleadoId,
                        'cantidad' => $cantidad,
                        'error' => $resultado
                    ];
                }
            }
        }

        // Verificar si al menos una operación fue exitosa
        if (!empty($resultados)) {
            // Obtener estado actualizado
            $controlModel = new ControlBultosModel();
            $nuevoEstado = $controlModel->actualizarEstado($operacion['controlBultoId']);

            $response = [
                'ok' => true,
                'message' => 'Producción registrada correctamente para ' . count($resultados) . ' empleado(s)',
                'total_registrado' => $totalCantidad,
                'registros' => $resultados,
                'nuevo_estado' => $nuevoEstado
            ];

            if (!empty($errores)) {
                $response['message'] .= '. Hubo errores con ' . count($errores) . ' empleado(s).';
                $response['errores'] = $errores;
            }

            return $this->response->setJSON($response);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo registrar la producción para ningún empleado',
            'errores' => $errores
        ]);
    }

    /**
     * API: Ver registros de producción
     */
    public function registrosProduccion($controlId)
    {
        try {
            $registroModel = new RegistroProduccionModel();
            $registros = $registroModel->getRegistrosPorControl($controlId);

            return $this->response->setJSON([
                'ok' => true,
                'data' => $registros
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Error obteniendo registros de producción',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function resumenProduccionOperacion($operacionId)
    {
        if (!can('menu.inspeccion')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
        }

        $registroModel = new RegistroProduccionModel();
        $resumen = $registroModel->getResumenPorOperacion($operacionId);

        $total = 0;
        foreach ($resumen as $row) {
            $total += (int) ($row['total_cantidad'] ?? 0);
        }

        return $this->response->setJSON([
            'ok' => true,
            'data' => $resumen,
            'total' => $total
        ]);
    }

    public function bultos($id)
    {
        try {
            $roleName = function_exists('current_role_name') ? (string) current_role_name() : '';
            $roleNorm = mb_strtolower(trim($roleName));
            $permitido = can('menu.inspeccion') || can('menu.produccion') || $roleNorm === 'corte';
            if (!$permitido) {
                return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
            }

            $rows = $this->db->table('bultos')
                ->select('id, numero_bulto, talla, cantidad')
                ->where('controlBultoId', (int) $id)
                ->orderBy('numero_bulto', 'ASC')
                ->get()
                ->getResultArray();

            return $this->response->setJSON([
                'ok' => true,
                'data' => $rows,
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'Error al cargar bultos',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * API: Exportar a Excel
     */
    public function exportExcel($id)
    {
        $controlModel = new ControlBultosModel();
        $control = $controlModel->getDetallado($id);

        if (!$control) {
            return redirect()->back()->with('error', 'Control no encontrado');
        }

        // Aquí implementarías la lógica de exportación a Excel
        // usando PhpSpreadsheet o similar

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'Exportación a Excel en desarrollo'
        ]);
    }

    /**
     * API: Listar plantillas
     */
    public function listarPlantillas()
    {
        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');

        $plantillaModel = new PlantillaOperacionModel();
        $plantillas = $plantillaModel->getPlantillasPorMaquiladora($maquiladoraId);

        return $this->response->setJSON([
            'ok' => true,
            'data' => $plantillas
        ]);
    }

    /**
     * API: Crear plantilla
     */
    public function crearPlantilla()
    {
        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');

        $operaciones = $this->request->getPost('operaciones');

        // Si viene como JSON string, decodificar
        if (is_string($operaciones)) {
            $operaciones = json_decode($operaciones, true);
        }

        $data = [
            'idmaquiladora' => $maquiladoraId,
            'tipo_prenda' => $this->request->getPost('tipo_prenda'),
            'nombre_plantilla' => $this->request->getPost('nombre_plantilla'),
            'operaciones' => $operaciones,
            'activo' => 1,
        ];

        $plantillaModel = new PlantillaOperacionModel();
        $plantillaId = $plantillaModel->crearPlantilla($data);

        if ($plantillaId) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Plantilla creada correctamente',
                'id' => $plantillaId
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo crear la plantilla'
        ]);
    }

    /**
     * API: Editar plantilla
     */
    public function editarPlantilla($id)
    {
        $operaciones = $this->request->getPost('operaciones');

        // Si viene como JSON string, decodificar
        if (is_string($operaciones)) {
            $operaciones = json_decode($operaciones, true);
        }

        $data = [
            'tipo_prenda' => $this->request->getPost('tipo_prenda'),
            'nombre_plantilla' => $this->request->getPost('nombre_plantilla'),
            'operaciones' => $operaciones,
        ];

        $plantillaModel = new PlantillaOperacionModel();

        if ($plantillaModel->actualizarPlantilla($id, $data)) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Plantilla actualizada correctamente'
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo actualizar la plantilla'
        ]);
    }
    /**
     * Vista: Editor de Plantilla (Nueva)
     */
    public function nuevaPlantilla()
    {
        $plantillaModel = new PlantillaOperacionModel();
        $maquiladoraId = session()->get('maquiladora_id') ?? session()->get('maquiladoraID');
        $operacionesUnicas = $plantillaModel->getOperacionesUnicas($maquiladoraId);

        return view('modulos/plantilla_editor', [
            'plantilla' => [],
            'operacionesUnicas' => $operacionesUnicas
        ]);
    }

    /**
     * Vista: Editor de Plantilla (Existente)
     */
    public function editorPlantilla($id)
    {
        $plantillaModel = new PlantillaOperacionModel();
        $plantilla = $plantillaModel->find($id);

        if (!$plantilla) {
            return redirect()->back()->with('error', 'Plantilla no encontrada');
        }

        // Decodificar operaciones si es string
        if (is_string($plantilla['operaciones'])) {
            $plantilla['operaciones'] = json_decode($plantilla['operaciones'], true);
        }

        $maquiladoraId = session()->get('maquiladora_id') ?? session()->get('maquiladoraID');
        $operacionesUnicas = $plantillaModel->getOperacionesUnicas($maquiladoraId);

        return view('modulos/plantilla_editor', [
            'plantilla' => $plantilla,
            'operacionesUnicas' => $operacionesUnicas
        ]);
    }

    /**
     * API: Guardar Plantilla Completa
     */
    public function guardarPlantillaCompleta()
    {
        $plantillaModel = new PlantillaOperacionModel();

        $id = $this->request->getPost('id');
        $data = [
            'nombre_plantilla' => $this->request->getPost('nombre_plantilla'),
            'tipo_prenda' => $this->request->getPost('tipo_prenda'),
            'operaciones' => $this->request->getPost('operaciones'), // Ya viene como JSON string del frontend o array
            'idmaquiladora' => session()->get('maquiladora_id') ?? session()->get('maquiladoraID')
        ];

        if (empty($id)) {
            $plantillaModel->insert($data);
        } else {
            $plantillaModel->update($id, $data);
        }

        return $this->response->setJSON(['ok' => true, 'message' => 'Plantilla guardada correctamente']);
    }

    /**
     * Vista de Matriz
     */
    public function vistaMatriz($id)
    {
        if (!can('menu.inspeccion')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $session = session();
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');

        $controlModel = new ControlBultosModel();
        $progresoModel = new \App\Models\ProgresoBultoOperacionModel();
        $empleadoModel = new EmpleadoModel();

        $control = $controlModel->getDetallado($id);

        if (!$control) {
            return redirect()->back()->with('error', 'Control no encontrado');
        }

        $matriz = $progresoModel->getMatrizProgreso($id);
        $progresoOperaciones = $progresoModel->getProgresoPorOperacion($id);

        // Obtener empleados activos
        $usuarioId = $session->get('usuario_id') ?? $session->get('user_id') ?? $session->get('id');
        $empleadoActual = null;
        if ($usuarioId) {
            $empleadoActual = $empleadoModel
                ->where('idusuario', (int) $usuarioId)
                ->where('activo', 1)
                ->first();
        }

        // Organizar datos para la vista
        $bultos = [];
        $operaciones = [];

        foreach ($matriz as $row) {
            // Agregar bulto si no existe
            if (!isset($bultos[$row['bultoId']])) {
                $bultos[$row['bultoId']] = [
                    'id' => $row['bultoId'],
                    'numero_bulto' => $row['numero_bulto'],
                    'talla' => $row['talla'],
                    'cantidad' => $row['cantidad'],
                    'operaciones' => []
                ];
            }

            // Agregar operación si no existe
            if (!isset($operaciones[$row['operacionId']])) {
                $operaciones[$row['operacionId']] = [
                    'id' => $row['operacionId'],
                    'nombre' => $row['nombre_operacion'],
                    'orden' => $row['operacion_orden']
                ];
            }

            // Agregar progreso de esta operación para este bulto
            $bultos[$row['bultoId']]['operaciones'][$row['operacionId']] = [
                'completado' => $row['completado'],
                'cantidad_completada' => $row['cantidad_completada'],
                'empleadoId' => $row['empleadoId'],
                'fecha_completado' => $row['fecha_completado']
            ];
        }

        // Ordenar operaciones por orden
        usort($operaciones, function ($a, $b) {
            return $a['orden'] <=> $b['orden'];
        });

        $data = [
            'control' => $control,
            'bultos' => array_values($bultos),
            'operaciones' => $operaciones,
            'progresoOperaciones' => $progresoOperaciones,
            'empleados' => $empleadoModel->getEmpleadosActivos(),
            'empleadoActual' => $empleadoActual
        ];

        return view('modulos/control_bultos_matriz', $data);
    }

    /**
     * API: Registrar producción desde matriz (con bulto específico)
     */
    public function registrarProduccionMatriz()
    {
        $session = session();
        $usuarioId = $session->get('usuario_id') ?? $session->get('id');

        $bultoId = $this->request->getPost('bultoId');
        $operacionControlId = $this->request->getPost('operacionControlId');
        $empleadoId = $this->request->getPost('empleadoId');
        $cantidadProducida = $this->request->getPost('cantidad_producida');
        $fechaRegistro = $this->request->getPost('fecha_registro') ?? date('Y-m-d');
        $horaInicio = $this->request->getPost('hora_inicio');
        $horaFin = $this->request->getPost('hora_fin');
        $observaciones = $this->request->getPost('observaciones');

        if (empty($observaciones) && !empty($bultoId)) {
            try {
                $b = $this->db->table('bultos')
                    ->select('numero_bulto')
                    ->where('id', (int) $bultoId)
                    ->get()
                    ->getRowArray();
                if ($b && isset($b['numero_bulto']) && $b['numero_bulto'] !== '') {
                    $observaciones = 'Bulto ' . $b['numero_bulto'];
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        // Validaciones
        if (empty($operacionControlId) || empty($empleadoId) || empty($cantidadProducida)) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Operación, empleado y cantidad son requeridos'
            ]);
        }

        // Registrar en la tabla de registros_produccion (sistema existente)
        $registroModel = new RegistroProduccionModel();
        $dataRegistro = [
            'operacionControlId' => $operacionControlId,
            'empleadoId' => $empleadoId,
            'cantidad_producida' => $cantidadProducida,
            'fecha_registro' => $fechaRegistro,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'observaciones' => $observaciones,
            'registrado_por' => $usuarioId,
        ];

        $resultado = $registroModel->registrarProduccion($dataRegistro);

        if (is_array($resultado) && isset($resultado['ok']) && $resultado['ok']) {
            // Marcar el bulto específico como completado
            if ($bultoId) {
                $progresoModel = new \App\Models\ProgresoBultoOperacionModel();
                $progresoModel->marcarCompletado($bultoId, $operacionControlId, $empleadoId, $cantidadProducida);
            }

            // Actualizar estado del control
            $operacionModel = new OperacionControlModel();
            $operacion = $operacionModel->find($operacionControlId);

            if ($operacion) {
                $controlModel = new ControlBultosModel();
                $nuevoEstado = $controlModel->actualizarEstado($operacion['controlBultoId']);

                return $this->response->setJSON([
                    'ok' => true,
                    'message' => 'Producción registrada correctamente',
                    'id' => $resultado['id'],
                    'nuevo_estado' => $nuevoEstado
                ]);
            }
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo registrar la producción',
            'debug' => $resultado
        ]);
    }

    /**
     * API: Crear bultos automáticamente
     */
    public function crearBultosAuto()
    {
        if (!can('menu.inspeccion')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
        }

        $controlBultoId = $this->request->getPost('controlBultoId');
        $numeroBultos = (int) $this->request->getPost('numeroBultos');
        $talla = $this->request->getPost('talla') ?? 'M';

        if (empty($controlBultoId) || $numeroBultos < 1) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Datos inválidos'
            ]);
        }

        // Obtener el control
        $controlModel = new ControlBultosModel();
        $control = $controlModel->find($controlBultoId);

        if (!$control) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Control no encontrado'
            ]);
        }

        // Verificar si ya existen bultos
        $bultosExistentes = $this->db->table('bultos')
            ->where('controlBultoId', $controlBultoId)
            ->countAllResults();

        if ($bultosExistentes > 0) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Ya existen bultos para este control. Elimínalos primero si deseas recrearlos.'
            ]);
        }

        // Calcular cantidad por bulto
        $cantidadTotal = $control['cantidad_total'];
        $cantidadPorBulto = (int) ceil($cantidadTotal / $numeroBultos);

        // Crear bultos
        $this->db->transStart();

        for ($i = 1; $i <= $numeroBultos; $i++) {
            // Calcular cantidad para este bulto (el último puede tener menos)
            $cantidadRestante = $cantidadTotal - (($i - 1) * $cantidadPorBulto);
            $cantidadBulto = min($cantidadPorBulto, $cantidadRestante);

            $this->db->table('bultos')->insert([
                'controlBultoId' => $controlBultoId,
                'numero_bulto' => str_pad($i, 3, '0', STR_PAD_LEFT),
                'talla' => $talla,
                'cantidad' => $cantidadBulto,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }

        $this->db->transComplete();

        if ($this->db->transStatus()) {
            return $this->response->setJSON([
                'ok' => true,
                'message' => "Se crearon $numeroBultos bultos correctamente"
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'Error al crear los bultos'
        ]);
    }

    /**
     * Descargar matriz en PDF
     */
    public function descargarMatrizPDF($id)
    {
        try {
            // Cargar datos del control
            $control = $this->db->table('control_bultos')->where('id', $id)->get()->getRow();
            if (!$control) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException();
            }

            // Cargar registros de producción para la matriz
            $registroModel = new \App\Models\RegistroProduccionModel();
            $registros = $registroModel->getRegistrosPorControl($id);

            // Construir matriz
            $idx = [];
            $colsSet = [];
            foreach ($registros as $r) {
                $opNombre = trim($r['nombre_operacion'] ?? '');
                $colKey = trim($r['observaciones'] ?? '');
                if ($opNombre && $colKey) {
                    $emp = trim(($r['empleadoNombre'] ?? '') . ' ' . ($r['empleadoApellido'] ?? ''));
                    $cant = (int)($r['cantidad_producida'] ?? 0);
                    if (!isset($idx[$opNombre])) $idx[$opNombre] = [];
                    if (!isset($idx[$opNombre][$colKey])) $idx[$opNombre][$colKey] = [];
                    if (!isset($idx[$opNombre][$colKey][$emp])) $idx[$opNombre][$colKey][$emp] = 0;
                    $idx[$opNombre][$colKey][$emp] += $cant;
                    $colsSet[$colKey] = true;
                }
            }
            $cols = array_keys($colsSet);
            sort($cols);

            // Generar HTML para PDF
            $html = '<style>
                table { border-collapse: collapse; width: 100%; font-size: 10px; }
                th, td { border: 1px solid #000; padding: 4px; text-align: left; vertical-align: top; }
                th { background: #f2f2f2; font-weight: bold; }
                .op-col { width: 200px; }
            </style>';
            $html .= '<h3>Bitácora Matriz - Control #' . $id . ' (' . htmlspecialchars($control->estilo ?? '') . ')</h3>';
            $html .= '<table>';
            $html .= '<thead><tr><th class="op-col">Operación</th>';
            foreach ($cols as $c) {
                $html .= '<th>' . htmlspecialchars($c) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($idx as $op => $row) {
                $html .= '<tr><td class="op-col">' . htmlspecialchars($op) . '</td>';
                foreach ($cols as $c) {
                    $cell = $row[$c] ?? [];
                    $cellHtml = '';
                    foreach ($cell as $emp => $qty) {
                        $cellHtml .= htmlspecialchars($emp) . ': ' . $qty . '<br>';
                    }
                    $html .= '<td>' . $cellHtml . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';

            // Usar DOMPDF si está disponible, sino salida simple
            if (class_exists('\Dompdf\Dompdf')) {
                $dompdf = new \Dompdf\Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'landscape');
                $dompdf->render();
                $dompdf->stream('matriz_control_' . $id . '.pdf', ['Attachment' => true]);
            } else {
                // Fallback: devolver HTML como descarga
                return $this->response
                    ->setHeader('Content-Type', 'text/html')
                    ->setHeader('Content-Disposition', 'attachment; filename="matriz_control_' . $id . '.html"')
                    ->setBody($html);
            }
        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
                ->setBody('Error generando PDF: ' . $e->getMessage());
        }
    }

    /**
     * Descargar matriz en Excel
     */
    public function descargarMatrizExcel($id)
    {
        try {
            // Cargar datos del control
            $control = $this->db->table('control_bultos')->where('id', $id)->get()->getRow();
            if (!$control) {
                throw new \CodeIgniter\Exceptions\PageNotFoundException();
            }

            // Cargar registros de producción para la matriz
            $registroModel = new \App\Models\RegistroProduccionModel();
            $registros = $registroModel->getRegistrosPorControl($id);

            // Construir matriz
            $idx = [];
            $colsSet = [];
            foreach ($registros as $r) {
                $opNombre = trim($r['nombre_operacion'] ?? '');
                $colKey = trim($r['observaciones'] ?? '');
                if ($opNombre && $colKey) {
                    $emp = trim(($r['empleadoNombre'] ?? '') . ' ' . ($r['empleadoApellido'] ?? ''));
                    $cant = (int)($r['cantidad_producida'] ?? 0);
                    if (!isset($idx[$opNombre])) $idx[$opNombre] = [];
                    if (!isset($idx[$opNombre][$colKey])) $idx[$opNombre][$colKey] = [];
                    if (!isset($idx[$opNombre][$colKey][$emp])) $idx[$opNombre][$colKey][$emp] = 0;
                    $idx[$opNombre][$colKey][$emp] += $cant;
                    $colsSet[$colKey] = true;
                }
            }
            $cols = array_keys($colsSet);
            sort($cols);

            // Generar CSV simple (Excel compatible)
            $csv = "\xEF\xBB\xBF"; // BOM for UTF-8
            $csv .= "Operación";
            foreach ($cols as $c) {
                $csv .= "," . $c;
            }
            $csv .= "\n";
            foreach ($idx as $op => $row) {
                $csv .= '"' . str_replace('"', '""', $op) . '"';
                foreach ($cols as $c) {
                    $cell = $row[$c] ?? [];
                    $cellLines = [];
                    foreach ($cell as $emp => $qty) {
                        $cellLines[] = $emp . ': ' . $qty;
                    }
                    $csv .= ',"' . str_replace('"', '""', implode("\n", $cellLines)) . '"';
                }
                $csv .= "\n";
            }

            return $this->response
                ->setHeader('Content-Type', 'text/csv')
                ->setHeader('Content-Disposition', 'attachment; filename="matriz_control_' . $id . '.csv"')
                ->setBody($csv);
        } catch (\Throwable $e) {
            return $this->response
                ->setStatusCode(500)
                ->setHeader('Content-Type', 'text/plain; charset=UTF-8')
                ->setBody('Error generando Excel: ' . $e->getMessage());
        }
    }
}


