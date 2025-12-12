<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class DatabaseFixController extends Controller
{
    /**
     * Add tipo_notificacion column to notificaciones table if it doesn't exist
     */
    public function addTipoNotificacionColumn()
    {
        $db = \Config\Database::connect();
        
        try {
            // Check if table exists
            if (!$db->tableExists('notificaciones')) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'La tabla notificaciones no existe'
                ]);
            }
            
            // Check if column already exists
            $query = $db->query("SHOW COLUMNS FROM notificaciones LIKE 'tipo_notificacion'");
            if ($query->getNumRows() > 0) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'La columna tipo_notificacion ya existe en la tabla notificaciones'
                ]);
            }
            
            // Add the column
            $sql = "ALTER TABLE notificaciones ADD COLUMN tipo_notificacion VARCHAR(50) NULL AFTER color";
            $db->query($sql);
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Columna tipo_notificacion agregada exitosamente a la tabla notificaciones'
            ]);
            
        } catch (\Throwable $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
