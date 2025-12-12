<?php
namespace App\Controllers;

use App\Models\IncidenciaModel;
use App\Models\EmpleadoModel;
use App\Models\OrdenProduccionModel;
use App\Services\NotificationService;

class Incidencias extends BaseController
{
    public function index()
    {
        if (!can('menu.incidencias')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        $maquiladoraId = session()->get('maquiladora_id');

        // Catálogos para el modal, filtrados por maquiladora si aplica
        $empBuilder = (new EmpleadoModel())
            ->select('id,nombre,apellido')
            ->where('activo', 1);
        if ($maquiladoraId) {
            $empBuilder->where('maquiladoraID', (int)$maquiladoraId);
        }
        $empleados = $empBuilder->orderBy('nombre','ASC')->findAll();

        $opModel = new OrdenProduccionModel();
        $opModel = $opModel->select('id,folio');
        if ($maquiladoraId) {
            $opModel = $opModel->where('maquiladoraID', (int)$maquiladoraId);
        }
        $ops = $opModel->orderBy('folio','DESC')->findAll();

        try {
            $db = db_connect(); // ✅ CI4
            $builder = $db->table('incidencia i')
                ->select(
                    'i.id as Ide,' .
                    'DATE_FORMAT(i.fecha, "%Y-%m-%d") as Fecha,' .
                    'op.folio as OP,' .
                    'i.tipo as Tipo,' .
                    'i.prioridad as Prioridad,' .
                    'CONCAT(COALESCE(e.nombre,""), " ", COALESCE(e.apellido,"")) as Empleado,' .
                    'i.descripcion as Descripcion,' .
                    'i.accion as Accion'
                )
                ->join('orden_produccion op', 'op.id = i.ordenProduccionFK', 'left')
                ->join('empleado e', 'e.id = i.empleadoFK', 'left');

            // Filtrar incidencias por maquiladora de la incidencia, la OP o el empleado
            if ($maquiladoraId) {
                $builder->groupStart()
                    ->where('i.maquiladoraID', (int)$maquiladoraId)
                    ->orWhere('op.maquiladoraID', (int)$maquiladoraId)
                    ->orWhere('e.maquiladoraID', (int)$maquiladoraId)
                ->groupEnd();
            }

            $rows = $builder->orderBy('i.fecha', 'DESC')->get()->getResultArray();
        } catch (\Throwable $e) {
            $rows = [];
            session()->setFlashdata('error', 'No fue posible consultar incidencias ('.$e->getMessage().')');
        }

        return view('modulos/incidencias', [
            'title'     => 'Incidencias',
            'lista'     => $rows,
            'empleados' => $empleados,
            'ops'       => $ops,
        ]);
    }

    public function store()
    {
        if (!can('menu.incidencias')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }
        
        $post = $this->request->getPost();
        $data = [
            'ordenProduccionFK' => (int)($post['ordenProduccionFK'] ?? 0),
            'empleadoFK'        => ($post['empleadoFK'] ?? '') !== '' ? (int)$post['empleadoFK'] : null,
            'tipo'              => trim($post['tipo'] ?? ''),
            'prioridad'         => trim($post['prioridad'] ?? 'Baja'),
            'fecha'             => $post['fecha'] ?? date('Y-m-d'),
            'descripcion'       => trim($post['descripcion'] ?? ''),
            'accion'            => trim($post['accion'] ?? ''),
        ];
        if ($data['ordenProduccionFK'] <= 0 || $data['tipo'] === '' || $data['fecha'] === '') {
            return redirect()->back()->with('error','Faltan campos obligatorios (OP, tipo, fecha).');
        }
        
        // Insertar la incidencia
        $incidenciaModel = new IncidenciaModel();
        $incidenciaId = $incidenciaModel->insert($data);
        
        // Crear notificación si se registró la incidencia correctamente
        if ($incidenciaId) {
            try {
                $notificationService = new NotificationService();
                $ordenProduccionModel = new OrdenProduccionModel();
                
                // Obtener datos para la notificación
                $orden = $ordenProduccionModel->find($data['ordenProduccionFK']);
                
                if ($orden) {
                    $maquiladoraId = session()->get('maquiladoraID') ?? session()->get('maquiladora_id') ?? 1;
                    $ordenFolio = $orden['folio'] ?? 'OP-' . $data['ordenProduccionFK'];
                    $tipoIncidencia = $data['tipo'];
                    $descripcion = $data['descripcion'];
                    
                    // Limitar la descripción para la notificación
                    $descripcionCorta = strlen($descripcion) > 100 ? substr($descripcion, 0, 100) . '...' : $descripcion;
                    
                    $notificationService->notifyIncidenciaRegistrada(
                        $maquiladoraId,
                        $tipoIncidencia,
                        $ordenFolio,
                        $descripcionCorta
                    );
                }
            } catch (\Throwable $e) {
                // No fallar el registro si hay error en la notificación
                log_message('warning', 'Error al crear notificación de incidencia: ' . $e->getMessage());
            }
        }
        
        return redirect()->to(site_url('modulo3/incidencias'))->with('ok','Incidencia registrada.');
    }

    public function delete($id)
    {
        if (!can('menu.incidencias')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }
        
        (new IncidenciaModel())->delete((int)$id);
        return redirect()->back()->with('ok','Incidencia eliminada.');
    }

    public function modal()
    {
        if (!can('menu.incidencias_modal')) {
            return $this->response->setStatusCode(403)->setJSON(['html' => 'Acceso denegado']);
        }
        
        // Catálogos mínimos para el modal de alta
        $empleados = (new EmpleadoModel())
            ->select('id,nombre,apellido')
            ->where('activo', 1)
            ->orderBy('nombre','ASC')->findAll();

        $ops = (new OrdenProduccionModel())
            ->select('id,folio')
            ->orderBy('folio','DESC')->findAll();

        return view('modulos/incidencias_modal', [
            'empleados' => $empleados,
            'ops'       => $ops,
        ]);
    }
}
