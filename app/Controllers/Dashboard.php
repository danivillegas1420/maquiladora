<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        // Verificar si el usuario está logueado
        if (!session()->get('logged_in')) {
            return redirect()->to('/login');
        }

        $service = new \App\Services\DashboardService();
        $userId = session()->get('user_id');

        // Obtener datos del servicio
        $kpis = $service->getKPIs();
        $notifications = $service->getNotifications($userId);
        
        // Calcular notificaciones no leídas
        $notifCount = count($notifications);

        // Datos para gráficas
        $charts = [
            'produccion' => $service->getProduccionStats(),
            'calidad' => $service->getCalidadStats(),
            'inventario' => $service->getInventarioStats(),
        ];

        // Obtener nombre de la maquiladora
        $db = \Config\Database::connect();
        $maquiladoraId = session()->get('maquiladora_id');
        $maquiladoraName = '';
        
        if ($maquiladoraId) {
            $row = $db->table('maquiladora')->select('Nombre_Maquila')->where('idmaquiladora', $maquiladoraId)->get()->getRow();
            if ($row) {
                $maquiladoraName = $row->Nombre_Maquila;
            }
        }

        return view('modulos/dashboard', [
            'title' => 'Dashboard Principal',
            'kpis' => $kpis,
            'notifications' => $notifications,
            'notifCount' => $notifCount,
            'charts' => $charts,
            'userEmail' => session()->get('email') ?? 'usuario@fabrica.com',
            'userName' => session()->get('user_name') ?? 'Usuario',
            'userRole' => session()->get('user_role') ?? 'empleado',
            'maquiladoraName' => $maquiladoraName
        ]);
    }

    public function api()
    {
        $service = new \App\Services\DashboardService();
        $userId = session()->get('user_id');
        $range = $this->request->getGet('range') ?? 30;

        $data = [
            'kpis' => $service->getKPIs(),
            'produccion' => $service->getProduccionStats(), // Could accept range
            'calidad' => $service->getCalidadStats($range),
            'inventario' => $service->getInventarioStats(),
            'logistica' => $service->getLogisticaStats(),
            'notifications' => $service->getNotifications($userId)
        ];

        return $this->response->setJSON($data);
    }
}
