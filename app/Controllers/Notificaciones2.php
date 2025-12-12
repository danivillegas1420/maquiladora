<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificacionModel;
use App\Models\UsuarioNotificacionModel;
use App\Services\NotificationService;

class Notificaciones2 extends BaseController
{
    protected $notificationModel;
    protected $userNotificationModel;
    protected $notificationService;

    public function __construct()
    {
        $this->notificationModel = new NotificacionModel();
        $this->userNotificationModel = new UsuarioNotificacionModel();
        $this->notificationService = new NotificationService();
    }

    /**
     * Main notifications view
     */
    public function index()
    {
        $userId = (int) (session('user_id') ?? session('id'));
        $maquiladoraId = session('maquiladora_id') ?? session('maquiladoraID');

        // Debug: Log para verificar los IDs de sesión
        log_message('debug', 'Notificaciones2 - Session IDs: maquiladora_id=' . session('maquiladora_id') . 
                   ', maquiladoraID=' . session('maquiladoraID') . 
                   ', user_id=' . session('user_id'));

        // Validar que tengamos IDs válidos
        if (!$maquiladoraId || !$userId) {
            log_message('error', 'Notificaciones2 - Faltan datos de sesión: maquiladoraId=' . $maquiladoraId . ', userId=' . $userId);
            return view('modulos/notificaciones2', [
                'title' => 'Notificaciones (Sistema Nuevo)',
                'notifications' => [],
                'unreadCount' => 0,
                'error' => 'No se pudo identificar tu sesión. Por favor inicia sesión nuevamente.'
            ]);
        }

        // Get notifications with read status FILTERED BY ROLE PERMISSIONS
        log_message('debug', 'Notificaciones2 - Consultando para maquiladoraId: ' . (int) $maquiladoraId . ', userId: ' . (int) $userId);
        $notifications = $this->notificationModel->getWithReadStatusFiltered((int) $maquiladoraId, (int) $userId, 50);

        // Get unread count FILTERED BY ROLE PERMISSIONS
        $unreadCount = $this->notificationModel->getUnreadCountFiltered((int) $maquiladoraId, (int) $userId);

        // Debug: Log resultados
        log_message('debug', 'Notificaciones2 - Encontradas: ' . count($notifications) . ' notificaciones, no leídas: ' . $unreadCount);

        // Add time ago to each notification
        foreach ($notifications as &$notification) {
            $notification['time_ago'] = $this->timeAgo($notification['created_at']);
        }

        return view('modulos/notificaciones2', [
            'title' => 'Notificaciones (Sistema Nuevo)',
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'debug_maquiladora_id' => $maquiladoraId // Para depuración en la vista
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        $userId = (int) (session('user_id') ?? session('id'));
        $maquiladoraId = session('maquiladora_id') ?? session('maquiladoraID');

        if (!$maquiladoraId || !$userId) {
            return redirect()->back()->with('error', 'No se pudo identificar tu sesión');
        }

        $this->userNotificationModel->markAsRead((int) $id, $userId, (int) $maquiladoraId);

        return redirect()->back()->with('success', 'Notificación marcada como leída');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $userId = (int) (session('user_id') ?? session('id'));
        $maquiladoraId = session('maquiladora_id') ?? session('maquiladoraID');

        if (!$maquiladoraId || !$userId) {
            return redirect()->back()->with('error', 'No se pudo identificar tu sesión');
        }

        $this->userNotificationModel->markAllAsRead($userId, (int) $maquiladoraId);

        return redirect()->back()->with('success', 'Todas las notificaciones marcadas como leídas');
    }

    /**
     * Delete notification (remove user link)
     */
    public function delete($id)
    {
        $userId = (int) (session('user_id') ?? session('id'));

        if (!$userId) {
            return redirect()->back()->with('error', 'No se pudo identificar tu sesión');
        }

        $this->userNotificationModel
            ->where(['idUserFK' => $userId, 'idNotificacionFK' => $id])
            ->delete();

        return redirect()->back()->with('success', 'Notificación eliminada');
    }

    /**
     * Generate test notifications (for demo purposes)
     */
    public function generateTestNotifications()
    {
        $maquiladoraId = session('maquiladora_id') ?? session('maquiladoraID');

        if (!$maquiladoraId) {
            return redirect()->back()->with('error', 'No se pudo identificar tu sesión');
        }

        // Create different types of test notifications
        $this->notificationService->createStockAlert($maquiladoraId, 'Tela Algodón 180g', 5.0, 50.0);
        $this->notificationService->createStockAlert($maquiladoraId, 'Hilo 40/2', 0.0, 25.0);
        $this->notificationService->createIncidentNotification($maquiladoraId, 'desecho', 25);
        $this->notificationService->createIncidentNotification($maquiladoraId, 'reproceso', 15);
        $this->notificationService->createClientNotification($maquiladoraId, 'Textiles Premium S.A.');
        $this->notificationService->createSampleNotification($maquiladoraId, 'Diseño Primavera 2025');
        $this->notificationService->createOrderNotification($maquiladoraId, 'OP-2025-0042', 'new');
        $this->notificationService->createMRPNotification($maquiladoraId, 'Etiquetas talla M', 500);
        $this->notificationService->createOCNotification($maquiladoraId, 123, 'Botones metálicos');

        return redirect()->to('/modulo3/notificaciones2')->with('success', '9 notificaciones de prueba generadas');
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
            return date('d/m/Y H:i', $timestamp);
        }
    }
}
