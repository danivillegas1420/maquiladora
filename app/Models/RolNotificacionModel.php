<?php

namespace App\Models;

use CodeIgniter\Model;

class RolNotificacionModel extends Model
{
    protected $table = 'rol_notificacion';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;

    protected $allowedFields = [
        'rol_id',
        'tipo_notificacion',
        'maquiladoraID'
    ];

    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all notification types available
     */
    public function getTiposNotificacion(): array
    {
        return [
            'mrp_materiales' => 'MRP Materiales',
            'incidencias' => 'Incidencias',
            'clientes' => 'Clientes',
            'muestras' => 'Muestras',
            'ordenes_produccion' => 'Órdenes de Producción',
            'pedidos' => 'Pedidos',
            'mantenimiento' => 'Mantenimiento',
            'sistema' => 'Sistema',
            'disenos' => 'Diseños',
            'inspeccion' => 'Inspección'
        ];
    }

    /**
     * Get permissions for a specific role
     */
    public function getPermisosByRol(int $rolId, int $maquiladoraId): array
    {
        return $this->where('rol_id', $rolId)
            ->where('maquiladoraID', $maquiladoraId)
            ->find();
    }

    /**
     * Save permissions for a role
     */
    public function savePermisosRol(int $rolId, int $maquiladoraId, array $tiposNotificacion): bool
    {
        // Delete existing permissions
        $this->where('rol_id', $rolId)
            ->where('maquiladoraID', $maquiladoraId)
            ->delete();

        // Insert new permissions
        if (!empty($tiposNotificacion)) {
            $data = [];
            foreach ($tiposNotificacion as $tipo) {
                $data[] = [
                    'rol_id' => $rolId,
                    'tipo_notificacion' => $tipo,
                    'maquiladoraID' => $maquiladoraId,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ];
            }
            return $this->insertBatch($data) !== false;
        }

        return true;
    }

    /**
     * Get roles with their notification permissions
     */
    public function getRolesWithPermisos(int $maquiladoraId): array
    {
        $db = \Config\Database::connect();
        
        $roles = $db->table('rol r')
            ->select('r.id, r.nombre, r.descripcion, GROUP_CONCAT(rn.tipo_notificacion) as tipos_notificacion')
            ->join('rol_notificacion rn', 'rn.rol_id = r.id AND rn.maquiladoraID = ' . $maquiladoraId, 'left')
            ->where('r.maquiladoraID', $maquiladoraId)
            ->groupBy('r.id, r.nombre, r.descripcion')
            ->get()
            ->getResultArray();

        foreach ($roles as &$rol) {
            $rol['tipos_notificacion'] = $rol['tipos_notificacion'] ? explode(',', $rol['tipos_notificacion']) : [];
        }

        return $roles;
    }
}
