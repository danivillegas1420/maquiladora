<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EmbarqueModel;
use App\Models\OrdenCompraModel;
use App\Models\ClienteModel;

class EmbarquesController extends BaseController
{
    /**
     * Vista principal de Preparación de Envíos / Packing.
     *
     * URL sugerida: GET /modulo3/logistica_preparacion
     */
    public function packing()
    {
        if (!can('menu.embarques_crud')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        // Maquiladora desde sesión (usa el índice que tengas configurado)
        $maquiladoraId = session('maquiladora_id') ?? session('maquiladoraID') ?? null;
        $maquiladoraId = $maquiladoraId !== null ? (int) $maquiladoraId : null;

        $embarqueModel = new EmbarqueModel();
        $clienteModel  = new ClienteModel();
        $ordenModel    = new OrdenCompraModel();

        // Embarque abierto actual (si existe)
        $embarque = $embarqueModel->getAbiertoActual($maquiladoraId) ?? [];

        // Clientes de la maquiladora
        $clientes = $clienteModel->listado($maquiladoraId);

        // Órdenes pendientes de embarque
        $ordenes  = $ordenModel->listarPendientes($maquiladoraId);

        return view('modulos/logistica_preparacion', [
            'title'    => 'Preparación de Envíos',
            'embarque' => $embarque,
            'clientes' => $clientes,
            'ordenes'  => $ordenes,
        ]);
    }

    /**
     * POST /modulo3/embarques/crear
     * Crea o actualiza el embarque ABIERTO actual.
     */
    public function crear()
    {
        if (!can('menu.embarques_crud')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        $maquiladoraId = session('maquiladora_id') ?? session('maquiladoraID') ?? null;
        $maquiladoraId = $maquiladoraId !== null ? (int) $maquiladoraId : null;

        $embarqueModel = new EmbarqueModel();

        $data = [
            'folio'         => trim((string) $this->request->getPost('folio')),
            'clienteId'     => $this->request->getPost('clienteId') ?: null,
            'fecha'         => date('Y-m-d'),
            'estatus'       => 'abierto',
            'maquiladoraID' => $maquiladoraId,
            // Si en tu tabla EMBARQUE tienes estas columnas, descomenta:
            // 'cajas'      => $this->request->getPost('cajas') ?: 0,
            // 'pesoTotal'  => $this->request->getPost('pesoTotal') ?: 0,
            // 'volumen'    => $this->request->getPost('volumen') ?: 0,
        ];

        if ($data['folio'] === '') {
            return redirect()->back()
                ->withInput()
                ->with('error', 'El folio es obligatorio');
        }

        // Buscar si ya hay un embarque ABIERTO
        $actual = $embarqueModel->getAbiertoActual($maquiladoraId);

        if ($actual) {
            // Actualizamos el embarque abierto
            $embarqueModel->update($actual['id'], $data);
            $id = $actual['id'];
        } else {
            // Creamos nuevo embarque abierto
            $id = $embarqueModel->insert($data);
        }

        if ($id === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No se pudo guardar el embarque');
        }

        return redirect()
            ->to(site_url('modulo3/logistica_preparacion'))
            ->with('ok', 'Embarque guardado correctamente');
    }

    /**
     * POST /modulo3/embarques/{id}/agregar-orden
     *
     * Aquí está el punto CLAVE:
     *   - Insertamos un registro en `embarque_item`
     *   - Después, la consulta listarPendientes usa NOT EXISTS sobre esa tabla,
     *     por eso la orden deja de aparecer en la tabla de "Consolidación".
     */
    public function agregarOrden($embarqueId)
    {
        $embarqueId = (int) $embarqueId;
        $ordenId    = (int) $this->request->getPost('ordenId');

        if (!$embarqueId || !$ordenId) {
            return redirect()->back()->with('error', 'Datos de embarque/orden inválidos');
        }

        $ordenModel    = new OrdenCompraModel();
        $embarqueModel = new EmbarqueModel();

        $orden    = $ordenModel->find($ordenId);
        $embarque = $embarqueModel->find($embarqueId);

        if (!$orden) {
            return redirect()->back()->with('error', 'La orden no existe');
        }
        if (!$embarque) {
            return redirect()->back()->with('error', 'El embarque no existe');
        }

        $db = \Config\Database::connect();

        // Verificar si ya existe relación para no duplicar
        $existe = $db->table('embarque_item')
            ->where('embarqueId', $embarqueId)
            ->where('ordenCompraId', $ordenId)
            ->countAllResults();

        if ($existe == 0) {
            $db->table('embarque_item')->insert([
                'maquiladoraID' => $embarque['maquiladoraID'] ?? null,
                'embarqueId'    => $embarqueId,
                'ordenCompraId' => $ordenId,
                // Deja los demás campos con sus defaults (productoId, cantidad, unidadMedida, etc.)
            ]);
        }

        return redirect()
            ->to(site_url('modulo3/logistica_preparacion'))
            ->with('ok', 'Orden agregada al embarque');
    }

    /**
     * JSON de una orden para el modal "ver".
     *
     * GET /modulo3/ordenes/{id}/json
     */
    public function ordenJson($id)
    {
        $ordenModel = new OrdenCompraModel();
        $data       = $ordenModel->find((int) $id);

        if (!$data) {
            return $this->response
                ->setStatusCode(404)
                ->setJSON(['error' => 'No encontrado']);
        }

        return $this->response->setJSON($data);
    }

    /**
     * POST /modulo3/ordenes/{id}/editar
     * Guarda los cambios de la orden desde el modal "editar".
     */
    public function ordenEditar($id)
    {
        $ordenModel = new OrdenCompraModel();

        $data = [
            'folio'     => $this->request->getPost('folio'),
            'fecha'     => $this->request->getPost('fecha'),
            'clienteId' => $this->request->getPost('clienteId') ?: null,
            'estatus'   => $this->request->getPost('estatus'),
            'moneda'    => $this->request->getPost('moneda'),
            'total'     => $this->request->getPost('total'),
            'op'        => $this->request->getPost('op'),
            'cajas'     => $this->request->getPost('cajas'),
            'peso'      => $this->request->getPost('peso'),
        ];

        if ($ordenModel->update((int) $id, $data) === false) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No se pudo actualizar la orden');
        }

        return redirect()
            ->to(site_url('modulo3/logistica_preparacion'))
            ->with('ok', 'Orden actualizada correctamente');
    }

    // Pendiente implementar: Packing List y Etiquetas
    public function packingList($id)
    {
        return "Packing List para embarque {$id} (pendiente implementar)";
    }

    public function etiquetas($id)
    {
        return "Etiquetas para embarque {$id} (pendiente implementar)";
    }
}
