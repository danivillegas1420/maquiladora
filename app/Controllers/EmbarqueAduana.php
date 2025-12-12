<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EmbarqueAduanaModel;
use Config\Database;
use CodeIgniter\Exceptions\PageNotFoundException;

class EmbarqueAduana extends BaseController
{
    /** Lista de aduanas de un embarque */
    public function index(int $embarqueId)
    {
        if (!can('menu.aduanas')) {
            return redirect()->to('/dashboard')->with('error', 'Acceso denegado');
        }
        
        $db = Database::connect();

        // Datos del embarque
        $embarque = $db->table('embarque')
            ->where('id', $embarqueId)
            ->get()
            ->getRowArray();

        if (!$embarque) {
            throw PageNotFoundException::forPageNotFound('Embarque no encontrado');
        }

        // Maquiladora actual (de sesión o del propio embarque)
        $session        = session();
        $maquiladoraID  = $session->get('maquiladora_id')
            ?? $session->get('maquiladoraID')
            ?? ($embarque['maquiladoraID'] ?? $embarque['maquiladoraId'] ?? null);

        $model   = new EmbarqueAduanaModel();
        $aduanas = $model
            ->where('embarqueId', $embarqueId)
            ->orderBy('id', 'DESC')
            ->findAll();

        return view('modulos/embarque_aduanas', [
            'title'         => 'Aduanas – Embarque ' . ($embarque['folio'] ?? ('#' . $embarque['id'])),
            'embarque'      => $embarque,
            'aduanas'       => $aduanas,
            'maquiladoraID' => $maquiladoraID,
        ]);
    }

    /** API opcional: lista JSON */
    public function listar(int $embarqueId)
    {
        if (!can('menu.aduanas')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso denegado']);
        }
        
        $model   = new EmbarqueAduanaModel();
        $aduanas = $model->where('embarqueId', $embarqueId)->orderBy('id', 'DESC')->findAll();

        return $this->response->setJSON(['data' => $aduanas]);
    }

    /** Crear / actualizar una Aduana (AJAX) */
    public function guardar()
    {
        if (!can('menu.aduanas')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso denegado']);
        }
        
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405)
                ->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
        }

        $id = (int) $this->request->getPost('id');

        $data = [
            'maquiladoraID'       => (int) $this->request->getPost('maquiladoraID'),
            'embarqueId'          => (int) $this->request->getPost('embarqueId'),
            'aduana'              => trim((string) $this->request->getPost('aduana')),
            'numeroPedimento'     => trim((string) $this->request->getPost('numeroPedimento')),
            'fraccionArancelaria' => trim((string) $this->request->getPost('fraccionArancelaria')),
            'observaciones'       => trim((string) $this->request->getPost('observaciones')),
        ];

        // Usuario actual (si usas Shield)
        if (function_exists('auth') && auth()->loggedIn()) {
            $data['usuarioId'] = auth()->user()->id;
        }

        if ($id > 0) {
            $data['id'] = $id; // save() actualiza si mandas id
        }

        $model = new EmbarqueAduanaModel();

        if (!$model->save($data)) {
            return $this->response->setStatusCode(400)
                ->setJSON([
                    'status' => 'error',
                    'errors' => $model->errors(),
                ]);
        }

        return $this->response->setJSON(['status' => 'ok']);
    }

    /** Eliminar Aduana (AJAX) */
    public function eliminar()
    {
        if (!can('menu.aduanas')) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Acceso denegado']);
        }
        
        if (!$this->request->is('post')) {
            return $this->response->setStatusCode(405)
                ->setJSON(['status' => 'error', 'message' => 'Método no permitido']);
        }

        $id = (int) $this->request->getPost('id');

        if ($id <= 0) {
            return $this->response->setStatusCode(400)
                ->setJSON(['status' => 'error', 'message' => 'ID inválido']);
        }

        $model = new EmbarqueAduanaModel();
        $model->delete($id);

        return $this->response->setJSON(['status' => 'ok']);
    }
}
