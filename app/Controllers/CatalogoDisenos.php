<?php

namespace App\Controllers;

class CatalogoDisenos extends BaseController
{
    /** Helper simple para combinar datos base. */
    private function payload(array $data = []): array
    {
        $base = [
            'notifCount' => $data['notifCount'] ?? 0,
            'userEmail' => session()->get('email') ?: 'admin@fabrica.com',
        ];
        return array_merge($base, $data);
    }

    /** Catálogo: Sexo */
    public function catalogoSexo()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $soloMaquiladora = (string) $this->request->getGet('solo_maquiladora') === '1';
        
        // Solo aplicar filtro de maquiladora si explícitamente se solicita
        $where = '';
        if ($soloMaquiladora && $maquiladoraId) {
            $where = "WHERE maquiladoraID = " . $db->escape($maquiladoraId);
        }

        $rows = [];
        $queries = [
            "SELECT id_sexo AS id, nombre, descripcion FROM sexo $where ORDER BY nombre",
            "SELECT id_sexo AS id, nombre, descripcion FROM Sexo $where ORDER BY nombre",
        ];
        foreach ($queries as $q) {
            try {
                $rows = $db->query($q)->getResultArray();
                if ($rows !== null) break;
            } catch (\Throwable $e) {}
        }
        if (empty($rows) && $maquiladoraId && !$soloMaquiladora) {
            $queriesNoWhere = [
                "SELECT id_sexo AS id, nombre, descripcion FROM sexo ORDER BY nombre",
                "SELECT id_sexo AS id, nombre, descripcion FROM Sexo ORDER BY nombre",
            ];
            foreach ($queriesNoWhere as $q) {
                try {
                    $rows = $db->query($q)->getResultArray();
                    if ($rows !== null) break;
                } catch (\Throwable $e) {}
            }
        }
        return $this->response->setJSON(['items' => $rows]);
    }

    public function catalogoSexoCrear()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');

        $nombre = trim((string) $this->request->getPost('nombre'));
        $descripcion = $this->request->getPost('descripcion');
        if ($descripcion !== null) {
            $descripcion = trim((string) $descripcion) === '' ? null : trim((string) $descripcion);
        }

        if ($nombre === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'El nombre es obligatorio',
            ]);
        }

        $tables = ['sexo', 'Sexo'];
        foreach ($tables as $t) {
            try {
                $data = [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                ];
                try {
                    if ($maquiladoraId) {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $data['maquiladoraID'] = (int) $maquiladoraId;
                        }
                    }
                } catch (\Throwable $e) {}

                $db->table($t)->insert($data);
                $insertId = $db->insertID();
                
                // Enviar notificación de catálogo actualizado
                try {
                    $notificationService = new \App\Services\NotificationService();
                    $notificationService->notifyDisenoAgregado(
                        $maquiladoraId ?? 1,
                        $nombre,
                        'Sexo'
                    );
                } catch (\Exception $e) {
                    log_message('error', 'Error al enviar notificación de sexo: ' . $e->getMessage());
                }
                
                return $this->response->setJSON([
                    'ok' => true,
                    'id' => $insertId,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo crear el registro en sexo',
        ]);
    }

    public function catalogoSexoActualizar($id)
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $id = (int) $id;

        $nombre = trim((string) $this->request->getPost('nombre'));
        $descripcion = $this->request->getPost('descripcion');
        if ($descripcion !== null) {
            $descripcion = trim((string) $descripcion) === '' ? null : trim((string) $descripcion);
        }

        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'ID inválido',
            ]);
        }
        if ($nombre === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'El nombre es obligatorio',
            ]);
        }

        $tables = ['sexo', 'Sexo'];
        foreach ($tables as $t) {
            try {
                $builder = $db->table($t);
                $data = [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                ];
                $builder->where('id_sexo', $id);
                if ($maquiladoraId) {
                    try {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $builder->where('maquiladoraID', (int) $maquiladoraId);
                        }
                    } catch (\Throwable $e) {}
                }
                $builder->update($data);
                return $this->response->setJSON([
                    'ok' => true,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo actualizar el registro de sexo',
        ]);
    }

    public function catalogoSexoEliminar($id)
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $id = (int) $id;

        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'ID inválido',
            ]);
        }

        $tables = ['sexo', 'Sexo'];
        foreach ($tables as $t) {
            try {
                $builder = $db->table($t)->where('id_sexo', $id);
                if ($maquiladoraId) {
                    try {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $builder->where('maquiladoraID', (int) $maquiladoraId);
                        }
                    } catch (\Throwable $e) {}
                }
                $builder->delete();
                return $this->response->setJSON([
                    'ok' => true,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo eliminar el registro de sexo',
        ]);
    }

    /** Catálogo: Tallas */
    public function catalogoTallas()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $soloMaquiladora = (string) $this->request->getGet('solo_maquiladora') === '1';
        
        // Solo aplicar filtro de maquiladora si explícitamente se solicita
        $where = '';
        if ($soloMaquiladora && $maquiladoraId) {
            $where = "WHERE maquiladoraID = " . $db->escape($maquiladoraId);
        }

        $rows = [];
        $queries = [
            "SELECT id_talla AS id, nombre, descripcion FROM tallas $where ORDER BY nombre",
            "SELECT id_talla AS id, nombre, descripcion FROM Tallas $where ORDER BY nombre",
        ];
        foreach ($queries as $q) {
            try {
                $rows = $db->query($q)->getResultArray();
                if ($rows !== null) break;
            } catch (\Throwable $e) {}
        }
        if (empty($rows) && $maquiladoraId && !$soloMaquiladora) {
            $queriesNoWhere = [
                "SELECT id_talla AS id, nombre, descripcion FROM tallas ORDER BY nombre",
                "SELECT id_talla AS id, nombre, descripcion FROM Tallas ORDER BY nombre",
            ];
            foreach ($queriesNoWhere as $q) {
                try {
                    $rows = $db->query($q)->getResultArray();
                    if ($rows !== null) break;
                } catch (\Throwable $e) {}
            }
        }
        return $this->response->setJSON(['items' => $rows]);
    }

    public function catalogoTallasCrear()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');

        $nombre = trim((string) $this->request->getPost('nombre'));
        $descripcion = $this->request->getPost('descripcion');
        if ($descripcion !== null) {
            $descripcion = trim((string) $descripcion) === '' ? null : trim((string) $descripcion);
        }

        if ($nombre === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'El nombre es obligatorio',
            ]);
        }

        $tables = ['tallas', 'Tallas'];
        foreach ($tables as $t) {
            try {
                $data = [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                ];
                try {
                    if ($maquiladoraId) {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $data['maquiladoraID'] = (int) $maquiladoraId;
                        }
                    }
                } catch (\Throwable $e) {}

                $db->table($t)->insert($data);
                $insertId = $db->insertID();
                
                // Enviar notificación de catálogo actualizado
                try {
                    $notificationService = new \App\Services\NotificationService();
                    $notificationService->notifyDisenoAgregado(
                        $maquiladoraId ?? 1,
                        $nombre,
                        'Talla'
                    );
                } catch (\Exception $e) {
                    log_message('error', 'Error al enviar notificación de talla: ' . $e->getMessage());
                }
                
                return $this->response->setJSON([
                    'ok' => true,
                    'id' => $insertId,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo crear el registro en tallas',
        ]);
    }

    public function catalogoTallasActualizar($id)
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $id = (int) $id;

        $nombre = trim((string) $this->request->getPost('nombre'));
        $descripcion = $this->request->getPost('descripcion');
        if ($descripcion !== null) {
            $descripcion = trim((string) $descripcion) === '' ? null : trim((string) $descripcion);
        }

        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'ID inválido',
            ]);
        }
        if ($nombre === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'El nombre es obligatorio',
            ]);
        }

        $tables = ['tallas', 'Tallas'];
        foreach ($tables as $t) {
            try {
                $builder = $db->table($t);
                $data = [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                ];
                $builder->where('id_talla', $id);
                if ($maquiladoraId) {
                    try {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $builder->where('maquiladoraID', (int) $maquiladoraId);
                        }
                    } catch (\Throwable $e) {}
                }
                $builder->update($data);
                return $this->response->setJSON([
                    'ok' => true,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo actualizar el registro de tallas',
        ]);
    }

    public function catalogoTallasEliminar($id)
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $id = (int) $id;

        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'ID inválido',
            ]);
        }

        $tables = ['tallas', 'Tallas'];
        foreach ($tables as $t) {
            try {
                $builder = $db->table($t)->where('id_talla', $id);
                if ($maquiladoraId) {
                    try {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $builder->where('maquiladoraID', (int) $maquiladoraId);
                        }
                    } catch (\Throwable $e) {}
                }
                $builder->delete();
                return $this->response->setJSON([
                    'ok' => true,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo eliminar el registro de tallas',
        ]);
    }

    /** Catálogo: Tipo de Corte */
    public function catalogoTipoCorte()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $soloMaquiladora = (string) $this->request->getGet('solo_maquiladora') === '1';
        $where = $maquiladoraId ? "WHERE maquiladoraID = " . $db->escape($maquiladoraId) : "";

        $rows = [];
        $queries = [
            "SELECT id_tipo_corte AS id, nombre, descripcion FROM tipo_corte $where ORDER BY nombre",
            "SELECT id_tipo_corte AS id, nombre, descripcion FROM Tipo_Corte $where ORDER BY nombre",
            "SELECT id_tipo_corte AS id, nombre, descripcion FROM tipocorte $where ORDER BY nombre",
        ];
        foreach ($queries as $q) {
            try {
                $rows = $db->query($q)->getResultArray();
                if ($rows !== null) break;
            } catch (\Throwable $e) {}
        }
        if (empty($rows) && $maquiladoraId && !$soloMaquiladora) {
            $queriesNoWhere = [
                "SELECT id_tipo_corte AS id, nombre, descripcion FROM tipo_corte ORDER BY nombre",
                "SELECT id_tipo_corte AS id, nombre, descripcion FROM Tipo_Corte ORDER BY nombre",
                "SELECT id_tipo_corte AS id, nombre, descripcion FROM tipocorte ORDER BY nombre",
            ];
            foreach ($queriesNoWhere as $q) {
                try {
                    $rows = $db->query($q)->getResultArray();
                    if ($rows !== null) break;
                } catch (\Throwable $e) {}
            }
        }
        return $this->response->setJSON(['items' => $rows]);
    }

    public function catalogoTipoCorteCrear()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');

        $nombre = trim((string) $this->request->getPost('nombre'));
        $descripcion = $this->request->getPost('descripcion');
        if ($descripcion !== null) {
            $descripcion = trim((string) $descripcion) === '' ? null : trim((string) $descripcion);
        }

        if ($nombre === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'El nombre es obligatorio',
            ]);
        }

        $tables = ['tipo_corte', 'Tipo_Corte', 'tipocorte'];
        foreach ($tables as $t) {
            try {
                $data = [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                ];
                try {
                    if ($maquiladoraId) {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $data['maquiladoraID'] = (int) $maquiladoraId;
                        }
                    }
                } catch (\Throwable $e) {}

                $db->table($t)->insert($data);
                $insertId = $db->insertID();
                
                // Enviar notificación de catálogo actualizado
                try {
                    $notificationService = new \App\Services\NotificationService();
                    $notificationService->notifyDisenoAgregado(
                        $maquiladoraId ?? 1,
                        $nombre,
                        'Tipo de Corte'
                    );
                } catch (\Exception $e) {
                    log_message('error', 'Error al enviar notificación de tipo de corte: ' . $e->getMessage());
                }
                
                return $this->response->setJSON([
                    'ok' => true,
                    'id' => $insertId,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo crear el registro en tipo de corte',
        ]);
    }

    public function catalogoTipoCorteActualizar($id)
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $id = (int) $id;

        $nombre = trim((string) $this->request->getPost('nombre'));
        $descripcion = $this->request->getPost('descripcion');
        if ($descripcion !== null) {
            $descripcion = trim((string) $descripcion) === '' ? null : trim((string) $descripcion);
        }

        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'ID inválido',
            ]);
        }
        if ($nombre === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'El nombre es obligatorio',
            ]);
        }

        $tables = ['tipo_corte', 'Tipo_Corte', 'tipocorte'];
        foreach ($tables as $t) {
            try {
                $builder = $db->table($t);
                $data = [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                ];
                $builder->where('id_tipo_corte', $id);
                if ($maquiladoraId) {
                    try {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $builder->where('maquiladoraID', (int) $maquiladoraId);
                        }
                    } catch (\Throwable $e) {}
                }
                $builder->update($data);
                return $this->response->setJSON([
                    'ok' => true,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo actualizar el registro de tipo de corte',
        ]);
    }

    public function catalogoTipoCorteEliminar($id)
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $id = (int) $id;

        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'ID inválido',
            ]);
        }

        $tables = ['tipo_corte', 'Tipo_Corte', 'tipocorte'];
        foreach ($tables as $t) {
            try {
                $builder = $db->table($t)->where('id_tipo_corte', $id);
                if ($maquiladoraId) {
                    try {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $builder->where('maquiladoraID', (int) $maquiladoraId);
                        }
                    } catch (\Throwable $e) {}
                }
                $builder->delete();
                return $this->response->setJSON([
                    'ok' => true,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo eliminar el registro de tipo de corte',
        ]);
    }

    /** Catálogo: Tipo de Ropa */
    public function catalogoTipoRopa()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $soloMaquiladora = (string) $this->request->getGet('solo_maquiladora') === '1';
        $where = $maquiladoraId ? "WHERE maquiladoraID = " . $db->escape($maquiladoraId) : "";

        $rows = [];
        $queries = [
            "SELECT id_tipo_ropa AS id, nombre, descripcion FROM tipo_ropa $where ORDER BY nombre",
            "SELECT id_tipo_ropa AS id, nombre, descripcion FROM Tipo_Ropa $where ORDER BY nombre",
        ];
        foreach ($queries as $q) {
            try {
                $rows = $db->query($q)->getResultArray();
                if ($rows !== null) break;
            } catch (\Throwable $e) {}
        }
        if (empty($rows) && $maquiladoraId && !$soloMaquiladora) {
            $queriesNoWhere = [
                "SELECT id_tipo_ropa AS id, nombre, descripcion FROM tipo_ropa ORDER BY nombre",
                "SELECT id_tipo_ropa AS id, nombre, descripcion FROM Tipo_Ropa ORDER BY nombre",
            ];
            foreach ($queriesNoWhere as $q) {
                try {
                    $rows = $db->query($q)->getResultArray();
                    if ($rows !== null) break;
                } catch (\Throwable $e) {}
            }
        }
        return $this->response->setJSON(['items' => $rows]);
    }

    public function catalogoTipoRopaCrear()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');

        $nombre = trim((string) $this->request->getPost('nombre'));
        $descripcion = $this->request->getPost('descripcion');
        if ($descripcion !== null) {
            $descripcion = trim((string) $descripcion) === '' ? null : trim((string) $descripcion);
        }

        if ($nombre === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'El nombre es obligatorio',
            ]);
        }

        $tables = ['tipo_ropa', 'Tipo_Ropa'];
        foreach ($tables as $t) {
            try {
                $data = [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                ];
                try {
                    if ($maquiladoraId) {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $data['maquiladoraID'] = (int) $maquiladoraId;
                        }
                    }
                } catch (\Throwable $e) {}

                $db->table($t)->insert($data);
                $insertId = $db->insertID();
                
                // Enviar notificación de catálogo actualizado
                try {
                    $notificationService = new \App\Services\NotificationService();
                    $notificationService->notifyDisenoAgregado(
                        $maquiladoraId ?? 1,
                        $nombre,
                        'Tipo de Ropa'
                    );
                } catch (\Exception $e) {
                    log_message('error', 'Error al enviar notificación de tipo de ropa: ' . $e->getMessage());
                }
                
                return $this->response->setJSON([
                    'ok' => true,
                    'id' => $insertId,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo crear el registro en tipo de ropa',
        ]);
    }

    public function catalogoTipoRopaActualizar($id)
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $id = (int) $id;

        $nombre = trim((string) $this->request->getPost('nombre'));
        $descripcion = $this->request->getPost('descripcion');
        if ($descripcion !== null) {
            $descripcion = trim((string) $descripcion) === '' ? null : trim((string) $descripcion);
        }

        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'ID inválido',
            ]);
        }
        if ($nombre === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'El nombre es obligatorio',
            ]);
        }

        $tables = ['tipo_ropa', 'Tipo_Ropa'];
        foreach ($tables as $t) {
            try {
                $builder = $db->table($t);
                $data = [
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                ];
                $builder->where('id_tipo_ropa', $id);
                if ($maquiladoraId) {
                    try {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $builder->where('maquiladoraID', (int) $maquiladoraId);
                        }
                    } catch (\Throwable $e) {}
                }
                $builder->update($data);
                return $this->response->setJSON([
                    'ok' => true,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo actualizar el registro de tipo de ropa',
        ]);
    }

    public function catalogoTipoRopaEliminar($id)
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $id = (int) $id;

        if ($id <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'ID inválido',
            ]);
        }

        $tables = ['tipo_ropa', 'Tipo_Ropa'];
        foreach ($tables as $t) {
            try {
                $builder = $db->table($t)->where('id_tipo_ropa', $id);
                if ($maquiladoraId) {
                    try {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $builder->where('maquiladoraID', (int) $maquiladoraId);
                        }
                    } catch (\Throwable $e) {}
                }
                $builder->delete();
                return $this->response->setJSON([
                    'ok' => true,
                ]);
            } catch (\Throwable $e) {}
        }

        return $this->response->setStatusCode(500)->setJSON([
            'ok' => false,
            'message' => 'No se pudo eliminar el registro de tipo de ropa',
        ]);
    }

    /** Artículos (para materiales de diseños) */
    public function articulosJson()
    {
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');

        $rows = [];
        $tables = ['articulos', 'Articulos', 'articulo', 'Articulo'];
        foreach ($tables as $t) {
            try {
                $builder = $db->table($t)
                    ->select('id, nombre, unidadMedida, sku');

                // Si la tabla tiene maquiladoraID, filtrar
                try {
                    if ($maquiladoraId) {
                        $fields = $db->getFieldNames($t);
                        if (in_array('maquiladoraID', $fields, true)) {
                            $builder->where('maquiladoraID', (int) $maquiladoraId);
                        }
                    }
                } catch (\Throwable $e) {}

                $rows = $builder->orderBy('nombre', 'ASC')->get()->getResultArray();
                if ($rows !== null) break;
            } catch (\Throwable $e) {
                $rows = [];
            }
        }

        return $this->response->setJSON(['items' => $rows]);
    }
}
