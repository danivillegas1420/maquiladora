<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RolNotificacionEmpleadoSeeder extends Seeder
{
    public function run()
    {
        // Eliminar configuraciones existentes del rol 8 para evitar duplicados
        $this->db->table('rol_notificacion')
            ->where('rol_id', 8)
            ->delete();
        
        // Configurar solo notificaciones esenciales para empleados
        $data = [
            // Empleados solo reciben notificaciones directamente relacionadas con su trabajo
            ['rol_id' => 8, 'tipo_notificacion' => 'ordenes_produccion', 'maquiladoraID' => 1],
            ['rol_id' => 8, 'tipo_notificacion' => 'inspeccion', 'maquiladoraID' => 1],
            ['rol_id' => 8, 'tipo_notificacion' => 'incidencias', 'maquiladoraID' => 1],
            // NO reciben notificaciones de sistema, roles, clientes, etc.
        ];

        foreach ($data as &$item) {
            $item['created_at'] = date('Y-m-d H:i:s');
            $item['updated_at'] = date('Y-m-d H:i:s');
        }

        $this->db->table('rol_notificacion')->insertBatch($data);
        
        echo "Configuración de notificaciones para empleados actualizada correctamente.\n";
        echo "Los empleados ahora solo recibirán notificaciones de producción, inspección e incidencias.\n";
    }
}
