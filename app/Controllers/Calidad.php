<?php
namespace App\Controllers;

use App\Models\InspeccionModel;
use App\Models\ReprocesoModel;

class Calidad extends BaseController
{
    /* =========================================================
     * Helper: resolver ordenProduccionId desde input del usuario
     * Acepta: id o numero. Devuelve id o null si no existe.
     * ========================================================= */
    private function resolveOrdenProduccionId($op): ?int
    {
        $op = trim((string) $op);
        if ($op === '')
            return null;

        $db = \Config\Database::connect();
        $tbl = $db->table('orden_produccion');

        // Busca por id exacto o por numero
        $row = $tbl->select('id')
            ->groupStart()
            ->where('id', (int) $op)
            ->orWhere('numero', $op)
            ->groupEnd()
            ->get(1)->getRowArray();

        return $row['id'] ?? null;
    }

    /** ===================== LISTA ===================== */
    public function desperdicios()
    {
        if (!can('menu.desperdicios')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $mR = new ReprocesoModel();
        $db = \Config\Database::connect();

        // Desechos - todos los registros con resultado 'rechazo'
        $desp = $db->table('reproceso r')
            ->select('r.id, r.cantidad, r.fecha, r.accion AS observaciones, 
                      COALESCE(ordprod.folio, CAST(i.ordenProduccionId AS CHAR), "N/A") AS op')
            ->join('inspeccion i', 'i.id = r.inspeccionId', 'left')
            ->join('orden_produccion ordprod', 'ordprod.id = i.ordenProduccionId', 'left')
            ->where('LOWER(i.resultado)', 'rechazo')
            ->orderBy('r.fecha', 'DESC')
            ->get()
            ->getResultArray();

        // Reprocesos - todos los registros con resultado 'reproceso'
        $rep = $db->table('reproceso r')
            ->select('r.id, r.accion AS tarea, r.cantidad AS pendientes, 
                      r.fecha AS eta, COALESCE(ordprod.folio, CAST(i.ordenProduccionId AS CHAR), "N/A") AS op')
            ->join('inspeccion i', 'i.id = r.inspeccionId', 'left')
            ->join('orden_produccion ordprod', 'ordprod.id = i.ordenProduccionId', 'left')
            ->where('LOWER(i.resultado)', 'reproceso')
            ->orderBy('r.fecha', 'DESC')
            ->get()
            ->getResultArray();

        // Cargar órdenes de producción activas para el dropdown
        $ordenesProduccion = $db->table('orden_produccion op')
            ->select('op.id, op.folio, c.nombre as cliente_nombre')
            ->join('orden_compra oc', 'oc.id = op.ordenCompraId', 'left')
            ->join('cliente c', 'c.id = oc.clienteId', 'left')
            ->whereIn('op.status', ['pendiente', 'en_proceso', 'activa'])
            ->orderBy('op.folio', 'DESC')
            ->limit(100) // Limitar a las últimas 100 órdenes activas
            ->get()
            ->getResultArray();

        return view('modulos/desperdicios', [
            'title' => 'Desperdicios & Reprocesos',
            'desp' => $desp,
            'rep' => $rep,
            'ordenesProduccion' => $ordenesProduccion, // NUEVO
            'todo' => false,
        ]);
    }

    /** ===================== CREAR ===================== */

    public function guardarDesecho()
    {
        if (!can('menu.desperdicios')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $post = $this->request->getPost();
        $db = \Config\Database::connect();

        try {
            // Insertar inspección con ordenProduccionId = NULL para evitar FK constraint
            $insertInspeccion = [
                'ordenProduccionId' => null, // Siempre NULL para evitar FK error
                'fecha' => $post['fecha'] ?? date('Y-m-d'),
                'resultado' => 'rechazo',
                'observaciones' => $post['motivo'] ?? null,
            ];

            $result = $db->table('inspeccion')->insert($insertInspeccion);
            if (!$result) {
                $error = $db->error();
                throw new \RuntimeException('Error insertando inspección: ' . json_encode($error));
            }
            $insId = $db->insertID();

            // Insertar reproceso directamente
            $insertReproceso = [
                'inspeccionId' => $insId,
                'accion' => $post['motivo'] ?: 'Desecho',
                'cantidad' => $post['cantidad'],
                'fecha' => $post['fecha'] ?? date('Y-m-d'),
            ];

            $result2 = $db->table('reproceso')->insert($insertReproceso);
            if (!$result2) {
                $error = $db->error();
                throw new \RuntimeException('Error insertando reproceso: ' . json_encode($error));
            }

            return $this->respOkRedirect('Desecho registrado');
        } catch (\Throwable $e) {
            log_message('error', '[Desecho] ' . $e->getMessage());
            return $this->respErrRedirect($e->getMessage());
        }
    }

    public function guardarReproceso()
    {
        if (!can('menu.desperdicios')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $post = $this->request->getPost();
        $db = \Config\Database::connect();

        try {
            // Insertar inspección con ordenProduccionId = NULL para evitar FK constraint
            $db->table('inspeccion')->insert([
                'ordenProduccionId' => null, // Siempre NULL para evitar FK error
                'fecha' => $post['eta'] ?? date('Y-m-d'),
                'resultado' => 'reproceso',
                'observaciones' => $post['tarea'] ?? null,
            ]);
            $insId = $db->insertID();


            // Insertar reproceso directamente
            $db->table('reproceso')->insert([
                'inspeccionId' => $insId,
                'accion' => $post['tarea'] ?: 'Reproceso',
                'cantidad' => $post['pendientes'],
                'fecha' => $post['eta'] ?? date('Y-m-d'),
            ]);

            // Create notification
            $notifService = new \App\Services\NotificationService();
            $maquiladoraId = session('maquiladoraID') ?? 1;
            $notifService->createIncidentNotification($maquiladoraId, 'reproceso', (int) $post['pendientes']);

            return $this->respOkRedirect('Reproceso registrado');
        } catch (\Throwable $e) {
            log_message('error', '[Reproceso] ' . $e->getMessage());
            return $this->respErrRedirect($e->getMessage());
        }
    }

    /** ===================== EDITAR ===================== */

    public function editarDesecho($id)
    {
        if (!can('menu.desperdicios')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $post = $this->request->getPost();
        $mR = new ReprocesoModel();
        $r = $mR->find($id);
        if (!$r)
            return $this->respErrRedirect('Registro no existe');

        $db = \Config\Database::connect();

        try {
            $db->transStart();

            $mR->update($id, [
                'accion' => $post['motivo'] ?: 'Desecho',
                'cantidad' => $post['cantidad'],
                'fecha' => $post['fecha'],
            ]);

            $ordenId = $this->resolveOrdenProduccionId($post['op'] ?? null);

            (new InspeccionModel())->update($r['inspeccionId'], [
                'ordenProduccionId' => $ordenId,
                'fecha' => $post['fecha'],
                'resultado' => 'rechazo',
                'observaciones' => $post['motivo'] ?? null,
            ]);

            $db->transComplete();
            if (!$db->transStatus())
                throw new \RuntimeException('DB transaction failed');

            return $this->respOkRedirect('Actualizado');
        } catch (\Throwable $e) {
            log_message('error', '[EditarDesecho] ' . $e->getMessage());
            return $this->respErrRedirect('No se pudo editar');
        }
    }

    public function editarReproceso($id)
    {
        if (!can('menu.desperdicios')) {
            return $this->response->setStatusCode(403)->setJSON(['success' => false, 'message' => 'Acceso denegado']);
        }

        $post = $this->request->getPost();
        $mR = new ReprocesoModel();
        $r = $mR->find($id);
        if (!$r)
            return $this->respErrRedirect('Registro no existe');

        $db = \Config\Database::connect();

        try {
            $db->transStart();

            $mR->update($id, [
                'accion' => $post['tarea'] ?: 'Reproceso',
                'cantidad' => $post['pendientes'],
                'fecha' => $post['eta'],
            ]);

            $ordenId = $this->resolveOrdenProduccionId($post['op'] ?? null);

            (new InspeccionModel())->update($r['inspeccionId'], [
                'ordenProduccionId' => $ordenId,
                'fecha' => $post['eta'],
                'resultado' => 'reproceso',
                'observaciones' => $post['tarea'] ?? null,
            ]);

            $db->transComplete();
            if (!$db->transStatus())
                throw new \RuntimeException('DB transaction failed');

            return $this->respOkRedirect('Actualizado');
        } catch (\Throwable $e) {
            log_message('error', '[EditarReproceso] ' . $e->getMessage());
            return $this->respErrRedirect('No se pudo editar');
        }
    }

    /** ===================== ELIMINAR ===================== */

    public function eliminarDesecho($id)
    {
        $reqAjax = $this->request->isAJAX();
        $mR = new ReprocesoModel();
        $row = $mR->find($id);

        if (!$row) {
            $msg = ['ok' => false, 'message' => 'Registro no encontrado', 'csrf' => csrf_hash()];
            return $reqAjax ? $this->response->setJSON($msg)->setStatusCode(404)
                : redirect()->back()->with('error', $msg['message']);
        }

        $db = \Config\Database::connect();
        try {
            $db->transStart();
            $mR->delete($id);
            if (!empty($row['inspeccionId'])) {
                (new InspeccionModel())->delete((int) $row['inspeccionId']);
            }
            $db->transComplete();
            if (!$db->transStatus())
                throw new \RuntimeException('DB transaction failed');

            if ($reqAjax) {
                return $this->response->setJSON([
                    'ok' => true,
                    'message' => 'Eliminado correctamente',
                    'csrf' => csrf_hash(),
                ]);
            }
            return redirect()->to('/calidad/desperdicios')->with('success', 'Eliminado');
        } catch (\Throwable $e) {
            if ($reqAjax) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'No se pudo eliminar',
                    'csrf' => csrf_hash(),
                ], 500);
            }
            return redirect()->back()->with('error', 'No se pudo eliminar');
        }
    }

    public function eliminarReproceso($id)
    {
        $reqAjax = $this->request->isAJAX();
        $mR = new ReprocesoModel();
        $row = $mR->find($id);

        if (!$row) {
            $msg = ['ok' => false, 'message' => 'Registro no encontrado', 'csrf' => csrf_hash()];
            return $reqAjax ? $this->response->setJSON($msg)->setStatusCode(404)
                : redirect()->back()->with('error', $msg['message']);
        }

        $db = \Config\Database::connect();
        try {
            $db->transStart();
            $mR->delete($id);
            if (!empty($row['inspeccionId'])) {
                (new InspeccionModel())->delete((int) $row['inspeccionId']);
            }
            $db->transComplete();
            if (!$db->transStatus())
                throw new \RuntimeException('DB transaction failed');

            if ($reqAjax) {
                return $this->response->setJSON([
                    'ok' => true,
                    'message' => 'Eliminado correctamente',
                    'csrf' => csrf_hash(),
                ]);
            }
            return redirect()->to('/calidad/desperdicios')->with('success', 'Eliminado');
        } catch (\Throwable $e) {
            if ($reqAjax) {
                return $this->response->setJSON([
                    'ok' => false,
                    'message' => 'No se pudo eliminar',
                    'csrf' => csrf_hash(),
                ], 500);
            }
            return redirect()->back()->with('error', 'No se pudo eliminar');
        }
    }

    /** ===================== JSON VISTA DETALLE ===================== */

    public function verDesecho($id)
    {
        if (!can('menu.desperdicios')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $row = (new ReprocesoModel())
            ->select('reproceso.*, inspeccion.ordenProduccionId AS op, inspeccion.resultado, inspeccion.observaciones')
            ->join('inspeccion', 'inspeccion.id = reproceso.inspeccionId')
            ->where('reproceso.id', $id)
            ->groupBy('reproceso.id')
            ->first();

        return $row ? $this->response->setJSON($row)
            : $this->response->setStatusCode(404)->setBody('No encontrado');
    }

    public function verReproceso($id)
    {
        if (!can('menu.desperdicios')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }

        $row = (new ReprocesoModel())
            ->select('reproceso.*, inspeccion.ordenProduccionId AS op')
            ->join('inspeccion', 'inspeccion.id = reproceso.inspeccionId')
            ->where('reproceso.id', $id)
            ->groupBy('reproceso.id')
            ->first();

        return $row ? $this->response->setJSON($row)
            : $this->response->setStatusCode(404)->setBody('No encontrado');
    }

    /** ===================== HELPERS RESPUESTA ===================== */

    private function respOkRedirect(string $msg)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => true, 'message' => $msg, 'csrf' => csrf_hash()]);
        }
        return redirect()->to('/calidad/desperdicios')->with('success', $msg);
    }

    private function respErrRedirect(string $msg)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['ok' => false, 'message' => $msg, 'csrf' => csrf_hash()], 422);
        }
        return redirect()->back()->with('error', $msg);
    }
}
