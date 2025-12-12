<?php

namespace App\Controllers;

use App\Models\NotificacionModel;
use App\Models\UsuarioNotificacionModel;
use CodeIgniter\RESTful\ResourceController;

class NotificationController extends ResourceController
{
    protected $notificationModel;
    protected $userNotificationModel;
    protected $format = 'json';

    public function __construct()
    {
        $this->notificationModel = new NotificacionModel();
        $this->userNotificationModel = new UsuarioNotificacionModel();
    }

    /**
     * Get current user and maquiladora from session
     */
    protected function getCurrentUserData(): array
    {
        $session = session();
        $userId = $session->get('user_id') ?? $session->get('userId');
        $maquiladoraId = $session->get('maquiladora_id') ?? $session->get('maquiladoraID');
        
        if (!$userId || !$maquiladoraId) {
            log_message('error', 'NotificationController - Faltan datos de sesión: userId=' . $userId . ', maquiladoraId=' . $maquiladoraId);
            return [
                'userId' => null,
                'maquiladoraId' => null,
                'error' => 'No se pudo identificar tu sesión'
            ];
        }
        
        return [
            'userId' => $userId,
            'maquiladoraId' => $maquiladoraId
        ];
    }

    /**
     * GET /api/notifications
     * Get recent notifications with read status filtered by role permissions
     */
    public function index()
    {
        $userData = $this->getCurrentUserData();
        
        if (isset($userData['error'])) {
            return $this->respond([
                'success' => false,
                'error' => $userData['error']
            ], 401);
        }
        
        $limit = $this->request->getVar('limit') ?? 10;

        $notifications = $this->notificationModel->getWithReadStatusFiltered(
            $userData['maquiladoraId'],
            $userData['userId'],
            (int) $limit
        );

        // Add time ago for each notification
        foreach ($notifications as &$notification) {
            $notification['time_ago'] = $this->timeAgo($notification['created_at']);
        }

        return $this->respond([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * GET /api/notifications/unread-count
     * Get count of unread notifications filtered by role permissions
     */
    public function unreadCount()
    {
        $userData = $this->getCurrentUserData();
        
        if (isset($userData['error'])) {
            return $this->respond([
                'success' => false,
                'error' => $userData['error']
            ], 401);
        }

        $count = $this->notificationModel->getUnreadCountFiltered(
            $userData['maquiladoraId'],
            $userData['userId']
        );

        return $this->respond([
            'success' => true,
            'count' => $count
        ]);
    }

    /**
     * GET /api/notifications/all
     * Get all notifications without filtering (for admin/debug purposes)
     */
    public function indexAll()
    {
        $userData = $this->getCurrentUserData();
        
        if (isset($userData['error'])) {
            return $this->respond([
                'success' => false,
                'error' => $userData['error']
            ], 401);
        }
        
        $limit = $this->request->getVar('limit') ?? 10;

        $notifications = $this->notificationModel->getWithReadStatus(
            $userData['maquiladoraId'],
            $userData['userId'],
            (int) $limit
        );

        // Add time ago for each notification
        foreach ($notifications as &$notification) {
            $notification['time_ago'] = $this->timeAgo($notification['created_at']);
        }

        return $this->respond([
            'success' => true,
            'data' => $notifications,
            'filtered' => false
        ]);
    }

    /**
     * POST /api/notifications/:id/read
     * Mark notification as read
     */
    public function markAsRead($id = null)
    {
        if (!$id) {
            return $this->fail('ID de notificación requerido', 400);
        }

        $userData = $this->getCurrentUserData();

        $success = $this->userNotificationModel->markAsRead(
            (int) $id,
            $userData['userId'],
            $userData['maquiladoraId']
        );

        if ($success) {
            return $this->respond([
                'success' => true,
                'message' => 'Notificación marcada como leída'
            ]);
        } else {
            return $this->failServerError('Error al marcar notificación');
        }
    }

    /**
     * POST /api/notifications/read-all
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $userData = $this->getCurrentUserData();

        $success = $this->userNotificationModel->markAllAsRead(
            $userData['userId'],
            $userData['maquiladoraId']
        );

        if ($success) {
            return $this->respond([
                'success' => true,
                'message' => 'Todas las notificaciones marcadas como leídas'
            ]);
        } else {
            return $this->failServerError('Error al marcar notificaciones');
        }
    }

    /**
     * Helper: Convert timestamp to "time ago" format
     */
    protected function timeAgo($datetime): string
    {
        $timestamp = strtotime($datetime);
        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Justo ahora';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return "Hace {$mins} " . ($mins == 1 ? 'minuto' : 'minutos');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "Hace {$hours} " . ($hours == 1 ? 'hora' : 'horas');
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return "Hace {$days} " . ($days == 1 ? 'día' : 'días');
        } else {
            return date('d/m/Y', $timestamp);
        }
    }

    /**
     * GET /api/notifications/permissions
     * Get current user's notification permissions
     */
    public function getUserPermissions()
    {
        $userData = $this->getCurrentUserData();
        
        if (isset($userData['error'])) {
            return $this->respond([
                'success' => false,
                'error' => $userData['error']
            ], 401);
        }

        $permissions = $this->notificationModel->getUserNotificationPermissions(
            $userData['userId'],
            $userData['maquiladoraId']
        );

        return $this->respond([
            'success' => true,
            'permissions' => $permissions,
            'count' => count($permissions)
        ]);
    }
}
