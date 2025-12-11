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
     * API: Obtener progreso y estado
     */
    public function progreso($id)
    {
        if (!can('menu.inspeccion')) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Acceso denegado']);
        }

        $controlModel = new ControlBultosModel();
        $operacionModel = new OperacionControlModel();

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

        // Obtener lista de operaciones para la vista
        $operaciones = $operacionModel->where('controlBultoId', $id)->orderBy('orden', 'ASC')->findAll();

        return $this->response->setJSON([
            'ok' => true,
            'data' => [
                'estado' => $control['estado'],
                'progreso_general' => $progresoGeneral,
                'listo_para_armado' => $listoParaArmado,
                'estadisticas' => $estadisticas,
                'operaciones' => $operaciones
            ]
        ]);
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
     * API: Registrar producción de empleado
     */
    public function registrarProduccion()
    {
        $session = session();
        $usuarioId = $session->get('usuario_id') ?? $session->get('id');

        $data = [
            'operacionControlId' => $this->request->getPost('operacionControlId'),
            'empleadoId' => $this->request->getPost('empleadoId'),
            'cantidad_producida' => $this->request->getPost('cantidad_producida'),
            'fecha_registro' => $this->request->getPost('fecha_registro') ?? date('Y-m-d'),
            'hora_inicio' => $this->request->getPost('hora_inicio'),
            'hora_fin' => $this->request->getPost('hora_fin'),
            'observaciones' => $this->request->getPost('observaciones'),
            'registrado_por' => $usuarioId,
        ];

        // Validaciones
        if (empty($data['operacionControlId']) || empty($data['empleadoId']) || empty($data['cantidad_producida'])) {
            return $this->response->setJSON([
                'ok' => false,
                'message' => 'Operación, empleado y cantidad son requeridos'
            ]);
        }

        // Validar que no se exceda la cantidad requerida
        $operacionModel = new OperacionControlModel();
        $operacion = $operacionModel->find($data['operacionControlId']);

        if ($operacion) {
            $nuevasCantidad = $operacion['piezas_completadas'] + $data['cantidad_producida'];
            if ($nuevasCantidad > $operacion['piezas_requeridas']) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'La cantidad excede las piezas requeridas. Máximo permitido: ' .
                        ($operacion['piezas_requeridas'] - $operacion['piezas_completadas'])
                ]);
            }
        }

        $registroModel = new RegistroProduccionModel();
        $resultado = $registroModel->registrarProduccion($data);

        if (is_array($resultado) && isset($resultado['ok']) && $resultado['ok']) {
            // Obtener estado actualizado
            $controlModel = new ControlBultosModel();
            $nuevoEstado = $controlModel->actualizarEstado($operacion['controlBultoId']);

            return $this->response->setJSON([
                'ok' => true,
                'message' => 'Producción registrada correctamente',
                'id' => $resultado['id'],
                'nuevo_estado' => $nuevoEstado
            ]);
        }

        return $this->response->setJSON([
            'ok' => false,
            'message' => 'No se pudo registrar la producción',
            'debug' => $resultado // Return full debug info
        ]);
    }

    /**
     * API: Ver registros de producción
     */
    public function registrosProduccion($controlId)
    {
        $registroModel = new RegistroProduccionModel();
        $registros = $registroModel->getRegistrosPorControl($controlId);

        return $this->response->setJSON([
            'ok' => true,
            'data' => $registros
        ]);
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
}


