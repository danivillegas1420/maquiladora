<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificacionModel extends Model
{
    protected $table = 'notificaciones';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'maquiladoraID',
        'mensaje',
        'titulo',
        'sub',
        'nivel',
        'color',
        'tipo_notificacion'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get recent notifications for a maquiladora
     * 
     * @param int $maquiladoraId
     * @param int $limit
     * @return array
     */
    public function getRecent(int $maquiladoraId, int $limit = 10): array
    {
        return $this->where('maquiladoraID', $maquiladoraId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->find();
    }

    /**
     * Get notifications with user read status
     * 
     * @param int $maquiladoraId
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getWithReadStatus(int $maquiladoraId, int $userId, int $limit = 10): array
    {
        $db = $this->db;

        return $db->table('notificaciones n')
            ->select('n.*, un.is_leida, un.id as user_notification_id')
            ->join('usuarioNotificacion un', 'un.idNotificacionFK = n.id AND un.idUserFK = ' . $userId, 'left')
            ->where('n.maquiladoraID', $maquiladoraId)
            ->orderBy('n.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get notifications with user read status filtered by role permissions
     * 
     * @param int $maquiladoraId
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getWithReadStatusFiltered(int $maquiladoraId, int $userId, int $limit = 10): array
    {
        $db = $this->db;

        // Get user's notification permissions based on their role
        $permittedTypes = $this->getUserNotificationPermissions($userId, $maquiladoraId);
        
        if (empty($permittedTypes)) {
            // User has no permissions, return empty array
            return [];
        }

        return $db->table('notificaciones n')
            ->select('n.*, un.is_leida, un.id as user_notification_id')
            ->join('usuarioNotificacion un', 'un.idNotificacionFK = n.id AND un.idUserFK = ' . $userId, 'left')
            ->where('n.maquiladoraID', $maquiladoraId)
            ->whereIn('n.tipo_notificacion', $permittedTypes)
            ->orderBy('n.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get unread count for a user filtered by role permissions
     * 
     * @param int $maquiladoraId
     * @param int $userId
     * @return int
     */
    public function getUnreadCountFiltered(int $maquiladoraId, int $userId): int
    {
        $db = $this->db;

        // Get user's notification permissions based on their role
        $permittedTypes = $this->getUserNotificationPermissions($userId, $maquiladoraId);
        
        if (empty($permittedTypes)) {
            // User has no permissions, return 0
            return 0;
        }

        // Count notifications that don't have a read record for this user and user has permission
        $result = $db->query("
            SELECT COUNT(*) as count
            FROM notificaciones n
            LEFT JOIN usuarioNotificacion un ON un.idNotificacionFK = n.id AND un.idUserFK = ?
            WHERE n.maquiladoraID = ? 
            AND (un.is_leida IS NULL OR un.is_leida = 0)
            AND n.tipo_notificacion IN (" . implode(',', array_fill(0, count($permittedTypes), '?')) . ")
        ", array_merge([$userId, $maquiladoraId], $permittedTypes))->getRowArray();

        return (int) ($result['count'] ?? 0);
    }

    /**
     * Get notification types that user has permission to see based on their role
     * 
     * @param int $userId
     * @param int $maquiladoraId
     * @return array
     */
    public function getUserNotificationPermissions(int $userId, int $maquiladoraId): array
    {
        $db = $this->db;

        // Check if rol_notificacion table exists
        if (!$db->tableExists('rol_notificacion')) {
            // If table doesn't exist, return all types (backward compatibility)
            return [
                'mrp_materiales', 'incidencias', 'clientes', 'muestras', 
                'ordenes_produccion', 'pedidos', 'mantenimiento', 'sistema', 
                'disenos', 'inspeccion'
            ];
        }

        // Get user's role and their notification permissions
        $permissions = $db->table('rol_notificacion rn')
            ->select('rn.tipo_notificacion')
            ->join('usuario_rol ur', 'ur.rolIdFK = rn.rol_id')
            ->where('ur.usuarioIdFK', $userId)
            ->where('rn.maquiladoraID', $maquiladoraId)
            ->get()
            ->getResultArray();

        return array_column($permissions, 'tipo_notificacion');
    }
}
