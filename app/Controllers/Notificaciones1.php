<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NotificacionModel;
use App\Models\UsuarioNotificacionModel;

class Notificaciones1 extends BaseController
{
    public function index()
    {
        $userId = (int) (session('user_id') ?? session('id'));
        $maquiladoraId = session('maquiladora_id') ?? session('maquiladoraID');

        // Debug: Log para verificar los IDs de sesión
        log_message('debug', 'Notificaciones1 - Session IDs: maquiladora_id=' . session('maquiladora_id') . 
                   ', maquiladoraID=' . session('maquiladoraID') . 
                   ', user_id=' . session('user_id'));

        // Validar que tengamos IDs válidos
        if (!$maquiladoraId || !$userId) {
            log_message('error', 'Notificaciones1 - Faltan datos de sesión: maquiladoraId=' . $maquiladoraId . ', userId=' . $userId);
            return view('modulos/notificaciones1', [
                'title'      => 'Notificaciones',
                'items'      => [],
                'notifCount' => 0,
                'error'      => 'No se pudo identificar tu sesión. Por favor inicia sesión nuevamente.'
            ]);
        }

        // Use the new filtered method from NotificacionModel
        $notificationModel = new NotificacionModel();
        
        // Get notifications filtered by role permissions
        log_message('debug', 'Notificaciones1 - Consultando para maquiladoraId: ' . (int) $maquiladoraId . ', userId: ' . (int) $userId);
        $items = $notificationModel->getWithReadStatusFiltered((int) $maquiladoraId, (int) $userId, 50);

        // Get unread count filtered by role permissions
        $notifCount = $notificationModel->getUnreadCountFiltered((int) $maquiladoraId, (int) $userId);

        log_message('debug', 'Notificaciones1 - Encontradas: ' . count($items) . ' notificaciones, no leídas: ' . $notifCount);

        return view('modulos/notificaciones1', [
            'title'      => 'Notificaciones',
            'items'      => $items,
            'notifCount' => $notifCount,
            'debug_maquiladora_id' => $maquiladoraId // Para depuración en la vista
        ]);
    }

    // Marca UNA notificación como leída para el usuario
    public function leer(int $notifId)
    {
        $userId = (int) (session('user_id') ?? session('id'));
        $maquiladoraId = session('maquiladora_id') ?? session('maquiladoraID');

        if (!$maquiladoraId || !$userId) {
            return redirect()->back()->with('error', 'No se pudo identificar tu sesión');
        }

        $un = new UsuarioNotificacionModel();
        $row = $un->where([
            'idUserFK'        => $userId,
            'idNotificacionFK'=> $notifId
        ])->first();

        if ($row) {
            $un->update($row['id'], ['is_leida' => 1]);
        } else {
            $un->insert([
                'idUserFK'         => $userId,
                'idNotificacionFK' => $notifId,
                'is_leida'         => 1
            ]);
        }
        return redirect()->back();
    }

    // Marca TODAS como leídas
    public function leerTodas()
    {
        $userId = (int) (session('user_id') ?? session('id'));
        $maquiladoraId = session('maquiladora_id') ?? session('maquiladoraID');

        if (!$maquiladoraId || !$userId) {
            return redirect()->back()->with('error', 'No se pudo identificar tu sesión');
        }

        $db = \Config\Database::connect();
        // marca las que ya tengan enlace
        $db->table('usuarioNotificacion')
            ->where('idUserFK', $userId)
            ->set('is_leida', 1)
            ->update();

        // crea enlaces leídos para las que no existan aún
        $sub = $db->table('usuarioNotificacion')
            ->select('idNotificacionFK')
            ->where('idUserFK', $userId);

        $faltantes = $db->table('notificaciones n')
            ->select('n.id')
            ->whereNotIn('n.id', $sub, false)
            ->get()->getResultArray();

        if ($faltantes) {
            $rows = array_map(fn($r) => [
                'idUserFK'         => $userId,
                'idNotificacionFK' => (int)$r['id'],
                'is_leida'         => 1
            ], $faltantes);
            $db->table('usuarioNotificacion')->insertBatch($rows);
        }

        return redirect()->back();
    }

    // Elimina SOLO el enlace usuario-notificación (no borra la notificación maestra)
    public function eliminar(int $notifId)
    {
        $userId = (int) (session('user_id') ?? session('id') ?? 1);

        (new UsuarioNotificacionModel())
            ->where(['idUserFK'=>$userId, 'idNotificacionFK'=>$notifId])
            ->delete();

        return redirect()->back();
    }
}
