<?php
namespace App\Controllers;

use App\Models\InspeccionModel;
use App\Services\NotificationService;
use CodeIgniter\Exceptions\PageNotFoundException;

class Inspeccion extends BaseController
{
    /**
     * Muestra la lista de inspecciones registradas.
     */
    protected $inspeccionModel;

    public function __construct()
    {
        $this->inspeccionModel = new InspeccionModel();
        helper(['form', 'url']);
    }

    private function canAccessOrdenProduccion(?int $opId): bool
    {
        if (!$opId || $opId <= 0) {
            return false;
        }

        $maquiladoraId = session()->get('maquiladora_id') ?? session()->get('maquiladoraID');
        if (!$maquiladoraId) {
            return true;
        }

        $db = \Config\Database::connect();
        $row = $db->table('orden_produccion')
            ->select('maquiladoraID, maquiladoraCompartidaID')
            ->where('id', (int) $opId)
            ->get()
            ->getRowArray();

        if (!$row) {
            return false;
        }

        return ((int) ($row['maquiladoraID'] ?? 0) === (int) $maquiladoraId)
            || ((int) ($row['maquiladoraCompartidaID'] ?? 0) === (int) $maquiladoraId);
    }

    private function canAccessInspeccion(int $inspeccionId): bool
    {
        if ($inspeccionId <= 0) {
            return false;
        }

        $db = \Config\Database::connect();
        $row = $db->table('inspeccion')
            ->select('ordenProduccionId')
            ->where('id', $inspeccionId)
            ->get()
            ->getRowArray();

        $opId = (int) ($row['ordenProduccionId'] ?? 0);
        return $this->canAccessOrdenProduccion($opId);
    }
    public function index()
    {
        if (!can('menu.inspeccion')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        $db = db_connect();
        $builder = $db->table('inspeccion i')
            ->select('i.id AS inspeccionId, i.ordenProduccionId, i.puntoInspeccionId, i.resultado, i.observaciones, i.inspectorId, i.fecha,')
            ->select('pi.tipo as punto_inspeccion, r.id AS reprocesoId, r.accion, r.cantidad, r.fecha AS fechaReproceso, op.folio')
            ->join('punto_inspeccion pi', 'pi.id = i.puntoInspeccionId', 'left')
            ->join('reproceso r', 'i.id = r.inspeccionId', 'left')
            ->join('orden_produccion op', 'op.id = i.ordenProduccionId', 'left')
            ->orderBy('i.id', 'DESC')
            ->orderBy('r.fecha', 'DESC');

        // Filtrar por maquiladora si está en sesión
        $maquiladoraId = session()->get('maquiladora_id');
        if ($maquiladoraId) {
            $builder->groupStart()
                ->where('op.maquiladoraID', (int)$maquiladoraId)
                ->orWhere('op.maquiladoraCompartidaID', (int)$maquiladoraId)
                ->groupEnd();
        }

        $inspecciones = $builder->get()->getResultArray();

        $lista = [];
        $n = 1;
        $processedIds = [];

        foreach ($inspecciones as $row) {
            // Si ya procesamos esta inspección, la saltamos
            if (in_array($row['inspeccionId'], $processedIds)) {
                continue;
            }

            $item = [
                'num' => $n++,
                'id' => $row['inspeccionId'] ?? '',
                'inspeccionId' => $row['inspeccionId'] ?? '',
                'numero_inspeccion' => 'INSP-' . str_pad($row['inspeccionId'], 5, '0', STR_PAD_LEFT),
                'ordenProduccionId' => $row['ordenProduccionId'] ?? 'N/A',
                'ordenFolio' => $row['folio'] ?? 'N/A',
                // Usar el ID real del punto de inspección (no el texto)
                'puntoInspeccionId' => $row['puntoInspeccionId'] ?? null,
                'inspectorId' => $row['inspectorId'] ?? 'N/A',
                'fecha' => $row['fecha'] ?? null,
                'resultado' => $row['resultado'] ?? 'Pendiente',
                'observaciones' => $row['observaciones'] ?? ''
            ];

            // Si hay información de reproceso, la agregamos
            if (!empty($row['reprocesoId'])) {
                $item['reprocesoId'] = $row['reprocesoId'];
                $item['accion'] = $row['accion'] ?? '';
                $item['cantidad'] = $row['cantidad'] ?? 0;
                $item['fechaReproceso'] = $row['fechaReproceso'] ?? null;
            }

            $lista[] = $item;
            $processedIds[] = $row['inspeccionId'];
        }

        // Obtener la lista de puntos de inspección para el dropdown (filtrado por maquiladora)
        $puntosBuilder = $db->table('punto_inspeccion')
            ->select('id, tipo')
            ->orderBy('tipo', 'ASC');
        
        // Filtrar solo puntos de la maquiladora actual
        if ($maquiladoraId) {
            $puntosBuilder->where('maquiladoraID', (int)$maquiladoraId);
        }
        
        $puntosInspeccion = $puntosBuilder->get()->getResultArray();

        return view('modulos/inspeccion', [
            'title' => 'Inspecciones',
            'lista' => $lista,
            'puntosInspeccion' => $puntosInspeccion
        ]);
    }

    public function nueva()
    {
        return view('modulos/inspeccion_form', [
            'title' => 'Nueva Inspección',
            'validation' => \Config\Services::validation()
        ]);
    }

    public function ver($id)
    {
        $inspeccion = $this->inspeccionModel->getDetalle($id);

        if (!$inspeccion) {
            throw new PageNotFoundException('No se encontró la inspección solicitada');
        }

        return view('modulos/inspeccion_ver', [
            'title' => 'Detalles de Inspección',
            'inspeccion' => $inspeccion
        ]);
    }

    public function editar($id)
    {
        $inspeccion = $this->inspeccionModel->find($id);

        if (!$inspeccion) {
            throw new PageNotFoundException('No se encontró la inspección solicitada');
        }

        return view('modulos/inspeccion_form', [
            'title' => 'Editar Inspección',
            'inspeccion' => $inspeccion,
            'validation' => \Config\Services::validation()
        ]);
    }

    public function actualizarPunto()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setStatusCode(405)->setJSON([
                    'success' => false,
                    'message' => 'Solicitud no válida'
                ]);
            }

            $idRaw = $this->request->getPost('id');
            $puntoRaw = $this->request->getPost('puntoInspeccionId');
            $id = (int)$idRaw;
            $puntoInspeccionId = (int)$puntoRaw;

            if ($id <= 0 || $puntoInspeccionId <= 0) {
                return $this->response->setStatusCode(400)->setJSON([
                    'success' => false,
                    'message' => 'Datos inválidos'
                ]);
            }

            if (!$this->canAccessInspeccion($id)) {
                return $this->response->setStatusCode(403)->setJSON([
                    'success' => false,
                    'message' => 'Acceso denegado'
                ]);
            }

            $db = \Config\Database::connect();
            $punto = $db->table('punto_inspeccion')
                ->select('tipo')
                ->where('id', $puntoInspeccionId)
                ->get()
                ->getRowArray();

            if (!$punto) {
                return $this->response->setStatusCode(404)->setJSON([
                    'success' => false,
                    'message' => 'Punto de inspección no encontrado'
                ]);
            }

            $ok = $db->table('inspeccion')
                ->where('id', $id)
                ->update([
                    'puntoInspeccionId' => $puntoInspeccionId,
                ]);

            if (!$ok) {
                $err = $db->error();
                $msg = $err['message'] ?? 'Error al actualizar';
                return $this->response->setStatusCode(500)->setJSON([
                    'success' => false,
                    'message' => $msg
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'punto_tipo' => $punto['tipo']
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON([
                'success' => false,
                'message' => 'Excepción: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * ===== CRUD PUNTOS DE INSPECCIÓN =====
     */
    public function puntosJson()
    {
        $db = \Config\Database::connect();
        try {
            $rows = $db->table('punto_inspeccion pi')
                ->select('pi.id, pi.tipo, pi.criterio, pi.maquiladoraID, m.Nombre_Maquila as maquiladoraNombre')
                ->join('maquiladora m', 'pi.maquiladoraID = m.idmaquiladora', 'left')
                ->orderBy('pi.id','ASC')
                ->get()
                ->getResultArray();
            return $this->response->setJSON(['success'=>true, 'data'=>$rows]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success'=>false, 'message'=>$e->getMessage()]);
        }
    }

    public function puntoCrear()
    {
        if (!$this->request->isAJAX()) { return $this->response->setStatusCode(405)->setJSON(['success'=>false,'message'=>'Método no permitido']); }
        $tipo = trim((string)($this->request->getPost('tipo') ?? ''));
        $criterio = trim((string)($this->request->getPost('criterio') ?? '')) ?: null;
        $maquiladoraID = $this->request->getPost('maquiladoraID') ? (int)$this->request->getPost('maquiladoraID') : null;
        
        if ($tipo === '') { return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'El campo tipo es requerido']); }
        
        $db = \Config\Database::connect();
        try {
            $data = [
                'tipo' => $tipo, 
                'criterio' => $criterio,
                'maquiladoraID' => $maquiladoraID
            ];
            $ok = $db->table('punto_inspeccion')->insert($data);
            if (!$ok) { $err = $db->error(); throw new \Exception($err['message'] ?? 'No se pudo crear'); }
            $id = (int)$db->insertID();
            
            // Obtener nombre de maquiladora si existe
            $maquiladoraNombre = null;
            if ($maquiladoraID) {
                $maq = $db->table('maquiladora')->select('Nombre_Maquila')->where('idmaquiladora', $maquiladoraID)->get()->getRowArray();
                $maquiladoraNombre = $maq['Nombre_Maquila'] ?? null;
            }
            
            return $this->response->setJSON([
                'success'=>true, 
                'data'=>[
                    'id'=>$id,
                    'tipo'=>$tipo,
                    'criterio'=>$criterio,
                    'maquiladoraID'=>$maquiladoraID,
                    'maquiladoraNombre'=>$maquiladoraNombre
                ]
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success'=>false, 'message'=>$e->getMessage()]);
        }
    }

    public function puntoEditar()
    {
        if (!$this->request->isAJAX()) { return $this->response->setStatusCode(405)->setJSON(['success'=>false,'message'=>'Método no permitido']); }
        $id = (int)($this->request->getPost('id') ?? 0);
        $tipo = trim((string)($this->request->getPost('tipo') ?? ''));
        $criterio = trim((string)($this->request->getPost('criterio') ?? '')) ?: null;
        $maquiladoraID = $this->request->getPost('maquiladoraID') ? (int)$this->request->getPost('maquiladoraID') : null;
        
        if ($id<=0 || $tipo==='') { return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'Datos inválidos']); }
        
        $db = \Config\Database::connect();
        try {
            $data = [
                'tipo' => $tipo,
                'criterio' => $criterio,
                'maquiladoraID' => $maquiladoraID
            ];
            $ok = $db->table('punto_inspeccion')->where('id',$id)->update($data);
            if (!$ok) { $err = $db->error(); throw new \Exception($err['message'] ?? 'No se pudo actualizar'); }
            return $this->response->setJSON(['success'=>true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success'=>false, 'message'=>$e->getMessage()]);
        }
    }

    public function puntoEliminar()
    {
        if (!$this->request->isAJAX()) { return $this->response->setStatusCode(405)->setJSON(['success'=>false,'message'=>'Método no permitido']); }
        $id = (int)($this->request->getPost('id') ?? 0);
        if ($id<=0) { return $this->response->setStatusCode(422)->setJSON(['success'=>false,'message'=>'ID inválido']); }
        $db = \Config\Database::connect();
        try {
            $ok = $db->table('punto_inspeccion')->where('id',$id)->delete();
            if (!$ok) { $err = $db->error(); throw new \Exception($err['message'] ?? 'No se pudo eliminar'); }
            return $this->response->setJSON(['success'=>true]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['success'=>false, 'message'=>$e->getMessage()]);
        }
    }

    public function guardar()
    {
        $rules = [
            'orden_produccion_id' => 'required',
            'punto_inspeccion_id' => 'required',
            'fecha' => 'required|valid_date',
            'inspector_id' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'ordenProduccionId' => $this->request->getPost('orden_produccion_id'),
            'puntoInspeccionId' => $this->request->getPost('punto_inspeccion_id'),
            'inspectorId' => $this->request->getPost('inspector_id'),
            'fecha' => $this->request->getPost('fecha'),
            'resultado' => $this->request->getPost('resultado') ?? 'Pendiente',
            'observaciones' => $this->request->getPost('observaciones'),
            'fecha_creacion' => date('Y-m-d H:i:s')
        ];

        $opId = (int) ($data['ordenProduccionId'] ?? 0);
        if (!$this->canAccessOrdenProduccion($opId)) {
            return redirect()->back()->withInput()->with('error', 'Acceso denegado');
        }

        $this->inspeccionModel->insert($data);

        return redirect()->to(base_url('inspeccion'))
            ->with('success', 'Inspección registrada correctamente.');
    }

    public function actualizar($id)
    {
        if (!$this->inspeccionModel->find($id)) {
            throw new PageNotFoundException('No se encontró la inspección solicitada');
        }

        if (!$this->canAccessInspeccion((int) $id)) {
            return redirect()->back()->with('error', 'Acceso denegado');
        }

        $rules = [
            'orden_produccion_id' => 'required',
            'punto_inspeccion_id' => 'required',
            'fecha' => 'required|valid_date',
            'inspector_id' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'ordenProduccionId' => $this->request->getPost('orden_produccion_id'),
            'puntoInspeccionId' => $this->request->getPost('punto_inspeccion_id'),
            'inspectorId' => $this->request->getPost('inspector_id'),
            'fecha' => $this->request->getPost('fecha'),
            'resultado' => $this->request->getPost('resultado') ?? 'Pendiente',
            'observaciones' => $this->request->getPost('observaciones'),
            'fecha_actualizacion' => date('Y-m-d H:i:s')
        ];

        $this->inspeccionModel->update($id, $data);

        return redirect()->to(base_url('inspeccion'))
            ->with('success', 'Inspección actualizada correctamente.');
    }

    public function eliminar($id)
    {
        if (!$this->inspeccionModel->find($id)) {
            throw new PageNotFoundException('No se encontró la inspección solicitada');
        }

        if (!$this->canAccessInspeccion((int) $id)) {
            return redirect()->back()->with('error', 'Acceso denegado');
        }

        $this->inspeccionModel->delete($id);

        return redirect()->to(base_url('inspeccion'))
            ->with('success', 'Inspección eliminada correctamente.');
    }

    /**
     * Muestra el detalle y formulario de evaluación de una inspección.
     */
    public function evaluar(int $id)
    {
        $model = new InspeccionModel();
        $inspeccion = $model->getDetalle($id);

        if (!$inspeccion) {
            throw new PageNotFoundException('Inspección no encontrada');
        }

        if (!$this->canAccessOrdenProduccion((int) ($inspeccion['ordenProduccionId'] ?? 0))) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        return view('modulos/inspeccion_evaluar', [
            'title' => 'Evaluación de inspección',
            'i'     => $inspeccion,
        ]);
    }

    /**
     * Guarda la evaluación de una inspección, incluyendo sus defectos
     */
    public function guardarEvaluacion(int $id)
    {
        // Solo permitir peticiones AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Método no permitido'
            ])->setStatusCode(405);
        }

        $model = new InspeccionModel();
        $inspeccion = $model->find($id);

        if (!$inspeccion) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Inspección no encontrada'
            ])->setStatusCode(404);
        }

        if (!$this->canAccessOrdenProduccion((int) ($inspeccion['ordenProduccionId'] ?? 0))) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Acceso denegado'
            ])->setStatusCode(403);
        }

        // Obtener los datos del formulario
        $data = $this->request->getPost();
        $defectos = json_decode($this->request->getPost('defectos') ?? '[]', true);
        $conReproceso = (int)($this->request->getPost('con_reproceso') ?? 0) === 1;
        $accionReproceso = trim((string)($this->request->getPost('accion_reproceso') ?? '')) ?: null;
        $cantidadReproceso = $this->request->getPost('cantidad_reproceso');
        $cantidadReproceso = ($cantidadReproceso === '' || $cantidadReproceso === null) ? null : (int)$cantidadReproceso;
        $fechaReproceso = trim((string)($this->request->getPost('fecha_reproceso') ?? '')) ?: null;
        
        // Obtener el resultado anterior para verificar si hubo cambio
        $resultadoAnterior = $inspeccion['resultado'] ?? '';
        $resultadoNuevo = $data['resultado'] ?? '';

        // Validar los datos
        $rules = [
            'resultado' => 'required|in_list[aprobado,rechazado,pendiente]',
            'fecha'     => 'required|valid_date',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $this->validator->getErrors()
            ])->setStatusCode(400);
        }

        // Defectos opcionales: no bloquear guardado si están vacíos

        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // Procesar la evidencia fotográfica si viene en base64
            $evidenciaBlob = null;
            if (!empty($data['evidencia'])) {
                // Remover el prefijo data:image/jpeg;base64, si existe
                $evidenciaBase64 = preg_replace('/^data:image\/\w+;base64,/', '', $data['evidencia']);
                $evidenciaBlob = base64_decode($evidenciaBase64);
            }

            // Actualizar la inspección (estatus, observaciones, fecha, evidencia)
            $updateData = [
                'resultado'     => $data['resultado'],
                'observaciones' => $data['observaciones'] ?? null,
                'fecha'         => $data['fecha'],
            ];

            // Solo agregar evidencia si hay una foto
            if ($evidenciaBlob !== null) {
                $updateData['evidencia'] = $evidenciaBlob;
            }

            $model->update($id, $updateData);

            // Guardar/actualizar reproceso según toggle
            if ($conReproceso) {
                // upsert reproceso por inspeccionId
                $rowRep = $db->table('reproceso')->where('inspeccionId', $id)->get()->getRowArray();
                $payloadRep = [
                    'accion' => $accionReproceso,
                    'cantidad' => $cantidadReproceso ?? 0,
                    'fecha' => $fechaReproceso,
                ];
                if ($rowRep) {
                    $db->table('reproceso')->where('inspeccionId', $id)->update($payloadRep);
                } else {
                    $payloadRep['inspeccionId'] = $id;
                    $db->table('reproceso')->insert($payloadRep);
                }
            } else {
                // Si no hay reproceso, limpiar valores
                $db->table('reproceso')->where('inspeccionId', $id)->update([
                    'accion' => null,
                    'cantidad' => 0,
                    'fecha' => null,
                ]);
            }

            // Si hay defectos (opcionales), guardarlos cuando venga rechazado
            if ($conReproceso && !empty($defectos) && $data['resultado'] === 'rechazado') {
                // Eliminar defectos existentes
                $db->table('inspeccion_defecto')->where('inspeccion_id', $id)->delete();

                // Insertar nuevos defectos
                foreach ($defectos as $defecto) {
                    $db->table('inspeccion_defecto')->insert([
                        'inspeccion_id'    => $id,
                        'defecto_id'       => $this->getDefectoIdByTipo($defecto['tipo']),
                        'descripcion'      => $defecto['descripcion'],
                        'cantidad'         => $defecto['cantidad'],
                        'accion_correctiva' => $defecto['accion_correctiva'],
                        'fecha_registro'   => date('Y-m-d H:i:s')
                    ]);
                }
            }

            // Si el resultado es "rechazado", actualizar el estatus de la orden de producción a "Pausada"
            if ($data['resultado'] === 'rechazado') {
                // Obtener el ordenProduccionId de la inspección
                $inspeccionData = $db->table('inspeccion')
                    ->select('ordenProduccionId')
                    ->where('id', $id)
                    ->get()
                    ->getRowArray();

                if ($inspeccionData && !empty($inspeccionData['ordenProduccionId'])) {
                    // Actualizar el estatus de la orden de producción a "Pausada"
                    $db->table('orden_produccion')
                        ->where('id', $inspeccionData['ordenProduccionId'])
                        ->update(['status' => 'Pausada']);
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Error al guardar los datos');
            }

            // Crear notificaciones si el resultado cambió a "aprobado" o "rechazado"
            if ($resultadoAnterior !== $resultadoNuevo && ($resultadoNuevo === 'aprobado' || $resultadoNuevo === 'rechazado')) {
                try {
                    $notificationService = new NotificationService();
                    
                    // Obtener datos para la notificación
                    $inspeccionData = $db->query("
                        SELECT i.id, op.folio, pi.tipo as puntoInspeccion
                        FROM inspeccion i
                        LEFT JOIN orden_produccion op ON op.id = i.ordenProduccionId
                        LEFT JOIN punto_inspeccion pi ON pi.id = i.puntoInspeccionId
                        WHERE i.id = ?
                    ", [$id])->getRowArray();

                    if ($inspeccionData) {
                        $maquiladoraId = session()->get('maquiladoraID') ?? session()->get('maquiladora_id') ?? 1;
                        $numeroInspeccion = 'INSP-' . str_pad($id, 5, '0', STR_PAD_LEFT);
                        $ordenFolio = $inspeccionData['folio'] ?? 'OP-' . ($inspeccionData['ordenProduccionId'] ?? 'Desconocido');
                        $puntoInspeccion = $inspeccionData['puntoInspeccion'] ?? 'Punto no especificado';

                        if ($resultadoNuevo === 'aprobado') {
                            $notificationService->notifyInspeccionAprobada(
                                $maquiladoraId,
                                $numeroInspeccion,
                                $ordenFolio,
                                $puntoInspeccion
                            );
                        } elseif ($resultadoNuevo === 'rechazado') {
                            $notificationService->notifyInspeccionRechazada(
                                $maquiladoraId,
                                $numeroInspeccion,
                                $ordenFolio,
                                $puntoInspeccion
                            );
                        }
                    }
                } catch (\Throwable $e) {
                    // No fallar el guardado si hay error en la notificación
                    log_message('warning', 'Error al crear notificación de inspección: ' . $e->getMessage());
                }
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Evaluación guardada correctamente'
            ]);

        } catch (\Exception $e) {
            $db->transRollback();

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error al guardar la evaluación: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }

    /**
     * Obtiene el ID de un defecto por su tipo
     */
    private function getDefectoIdByTipo(string $tipo): ?int
    {
        $db = \Config\Database::connect();
        $defecto = $db->table('defecto')
            ->select('id')
            ->where('nombre', $tipo)
            ->get()
            ->getRowArray();

        return $defecto ? (int)$defecto['id'] : null;
    }

    /**
     * Endpoint para obtener los datos de inspecciones en formato JSON
     * Si se proporciona un ID, devuelve los detalles de esa inspección con sus defectos
     */
    public function json($id = null)
    {
        $model = new InspeccionModel();

        if ($id === null) {
            // Obtener todas las inspecciones con información relacionada
            $inspecciones = $model->getListadoCompleto();

            return $this->response->setJSON([
                'success' => true,
                'data' => $inspecciones
            ]);
        } else {
            // Obtener los detalles de la inspección específica con información relacionada
            $inspeccion = $model->getDetalle($id);

            if (!$inspeccion) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Inspección no encontrada'
                ])->setStatusCode(404);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $inspeccion
            ]);
        }
    }

    /**
     * Endpoint para obtener la evidencia fotográfica de una inspección
     */
    public function evidencia($id = null)
    {
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID de inspección no proporcionado'
            ])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $inspeccion = $db->table('inspeccion')
            ->select('evidencia')
            ->where('id', (int)$id)
            ->get()
            ->getRowArray();

        if (!$inspeccion || empty($inspeccion['evidencia'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'No hay evidencia fotográfica para esta inspección'
            ]);
        }

        // Convertir BLOB a base64
        $evidenciaBase64 = 'data:image/jpeg;base64,' . base64_encode($inspeccion['evidencia']);

        return $this->response->setJSON([
            'success' => true,
            'evidencia' => $evidenciaBase64
        ]);
    }
}
