<?php

namespace App\Services;

use App\Models\NotificacionModel;
use App\Models\UsuarioNotificacionModel;

class NotificationService
{
    protected $notificationModel;
    protected $userNotificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificacionModel();
        $this->userNotificationModel = new UsuarioNotificacionModel();
    }

    /**
     * Create a notification
     * 
     * @param int $maquiladoraId
     * @param string $titulo
     * @param string $mensaje
     * @param string|null $sub
     * @param string $nivel (info, success, warning, danger)
     * @param string|null $color
     * @return int|false Notification ID or false on failure
     */
    protected function createNotification(
        int $maquiladoraId,
        string $titulo,
        string $mensaje,
        ?string $sub = null,
        string $nivel = 'info',
        ?string $color = null,
        ?string $tipoNotificacion = null
    ) {
        log_message('debug', "NOTIFICATION DEBUG - createNotification llamado con: maquiladora={$maquiladoraId}, titulo='{$titulo}', mensaje='{$mensaje}', tipo='{$tipoNotificacion}'");
        
        $db = \Config\Database::connect();
        $db->transStart();
        
        try {
            // Insertar la notificación
            $data = [
                'maquiladoraID' => $maquiladoraId,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'sub' => $sub,
                'nivel' => $nivel,
                'color' => $color ?? $this->getColorForLevel($nivel),
                'tipo_notificacion' => $tipoNotificacion
            ];

            log_message('debug', "NOTIFICATION DEBUG - Insertando notificación con datos: " . json_encode($data));

            $notificationId = $this->notificationModel->insert($data);
            
            if (!$notificationId) {
                throw new \Exception('No se pudo crear la notificación');
            }

            log_message('debug', "NOTIFICATION DEBUG - Notificación insertada con ID: {$notificationId}");

            // Obtener usuarios que deben recibir esta notificación según su rol
            log_message('debug', "NOTIFICATION DEBUG - Llamando a getUsuariosPorTipoNotificacion con maquiladora: {$maquiladoraId}, tipo: " . ($tipoNotificacion ?? 'NULL'));
            $usuarios = $this->getUsuariosPorTipoNotificacion($maquiladoraId, $tipoNotificacion);

            log_message('debug', "NOTIFICATION DEBUG - Usuarios encontrados: " . count($usuarios));
            
            // Mostrar IDs de usuarios que recibirán la notificación
            $usuarioIds = array_map(function($u) { return $u['id']; }, $usuarios);
            log_message('debug', "NOTIFICATION DEBUG - IDs de usuarios que recibirán notificación: " . implode(', ', $usuarioIds));

            // Asignar la notificación a cada usuario
            foreach ($usuarios as $usuario) {
                $userNotificationData = [
                    'maquiladoraID' => $maquiladoraId,
                    'idNotificacionFK' => $notificationId,
                    'idUserFK' => $usuario['id'],
                    'is_leida' => 0
                ];
                
                $this->userNotificationModel->insert($userNotificationData);
            }

            $db->transComplete();
            
            if ($db->transStatus() === false) {
                throw new \Exception('Error en la transacción de notificación');
            }

            log_message('debug', "NOTIFICATION DEBUG - Transacción completada exitosamente");
            return $notificationId;
            
        } catch (\Throwable $e) {
            $db->transRollback();
            log_message('error', 'NOTIFICATION DEBUG - Error al crear notificación: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtiene los usuarios que deben recibir una notificación según su rol
     */
    private function getUsuariosPorTipoNotificacion(int $maquiladoraId, ?string $tipoNotificacion): array
    {
        $db = \Config\Database::connect();
        
        // Si no hay tipo de notificación, enviar a todos (comportamiento anterior)
        if (!$tipoNotificacion) {
            log_message('debug', "NOTIFICATION DEBUG - Sin tipo de notificación, enviando a todos");
            return $db->table('users')
                ->where('maquiladoraIdFK', $maquiladoraId)
                ->get()
                ->getResultArray();
        }

        // Verificar si existe la tabla rol_notificacion
        if (!$db->tableExists('rol_notificacion')) {
            log_message('warning', "NOTIFICATION DEBUG - La tabla rol_notificacion no existe, enviando a todos");
            return $db->table('users')
                ->where('maquiladoraIdFK', $maquiladoraId)
                ->get()
                ->getResultArray();
        }

        // Verificar si hay configuraciones para este tipo de notificación
        $configCount = $db->table('rol_notificacion')
            ->where('tipo_notificacion', $tipoNotificacion)
            ->countAllResults();
        
        log_message('debug', "NOTIFICATION DEBUG - Configuraciones encontradas para '{$tipoNotificacion}': {$configCount}");
        
        if ($configCount == 0) {
            log_message('warning', "NOTIFICATION DEBUG - No hay roles configurados para '{$tipoNotificacion}', no enviando notificación");
            return []; // Devolver array vacío en lugar de enviar a todos
        }

        // Obtener usuarios cuyos roles tienen permiso para este tipo de notificación
        $usuarios = $db->table('users u')
            ->select('u.id')
            ->join('usuario_rol ur', 'ur.usuarioIdFK = u.id')
            ->join('rol_notificacion rn', 'rn.rol_id = ur.rolIdFK')
            ->where('u.maquiladoraIdFK', $maquiladoraId)
            ->where('rn.tipo_notificacion', $tipoNotificacion)
            ->get()
            ->getResultArray();
        
        log_message('debug', "NOTIFICATION DEBUG - Usuarios filtrados para '{$tipoNotificacion}': " . count($usuarios));
        
        return $usuarios;
    }

    /**
     * Get color based on level
     */
    protected function getColorForLevel(string $nivel): string
    {
        return match ($nivel) {
            'success' => '#28a745',
            'warning' => '#ffc107',
            'danger' => '#dc3545',
            'info' => '#17a2b8',
            default => '#6c757d'
        };
    }

    /**
     * Create stock alert notification
     */
    public function createStockAlert(int $maquiladoraId, string $material, float $stock, float $reorderPoint): int|false
    {
        $percentage = ($stock / $reorderPoint) * 100;

        if ($stock <= 0) {
            return $this->createNotification(
                $maquiladoraId,
                'Stock Agotado',
                "El material '{$material}' está agotado",
                'Requiere atención inmediata',
                'danger',
                null,
                'mrp_materiales'
            );
        } elseif ($percentage < 20) {
            return $this->createNotification(
                $maquiladoraId,
                'Stock Bajo',
                "El material '{$material}' tiene stock bajo ({$stock} unidades)",
                'Considere realizar un pedido',
                'warning',
                null,
                'mrp_materiales'
            );
        }

        return false;
    }

    /**
     * Create incident notification
     */
    public function createIncidentNotification(int $maquiladoraId, string $type, int $cantidad): int|false
    {
        $typeText = $type === 'desecho' ? 'Desecho' : 'Reproceso';

        return $this->createNotification(
            $maquiladoraId,
            "Nuevo {$typeText} Registrado",
            "Se ha registrado un nuevo {$typeText} de {$cantidad} unidades",
            'Revisar en módulo de Calidad',
            'warning',
            null,
            'incidencias'
        );
    }

    /**
     * Create client notification
     */
    public function createClientNotification(int $maquiladoraId, string $clientName): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nuevo Cliente',
            "Se ha agregado el cliente '{$clientName}'",
            null,
            'success',
            null,
            'clientes'
        );
    }

    /**
     * Create sample/design notification
     */
    public function createSampleNotification(int $maquiladoraId, string $sampleName, ?string $status = null): int|false
    {
        if ($status) {
            return $this->createNotification(
                $maquiladoraId,
                'Cambio de Estado en Muestra',
                "La muestra '{$sampleName}' cambió a: {$status}",
                null,
                'info',
                null,
                'muestras'
            );
        } else {
            return $this->createNotification(
                $maquiladoraId,
                'Nueva Muestra',
                "Se ha creado la muestra '{$sampleName}'",
                null,
                'success',
                null,
                'muestras'
            );
        }
    }

    /**
     * Create work order notification
     */
    public function createOrderNotification(int $maquiladoraId, string $orderNumber, string $type = 'new'): int|false
    {
        if ($type === 'new') {
            return $this->createNotification(
                $maquiladoraId,
                'Nueva Orden de Trabajo',
                "Se ha creado la orden #{$orderNumber}",
                null,
                'info',
                null,
                'ordenes_produccion'
            );
        } else {
            return $this->createNotification(
                $maquiladoraId,
                'Cambio en Orden',
                "La orden #{$orderNumber} ha cambiado de estado",
                null,
                'info',
                null,
                'ordenes_produccion'
            );
        }
    }

    /**
     * Create MRP notification
     */
    public function createMRPNotification(int $maquiladoraId, string $material, float $cantidad): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Materiales Necesarios',
            "Se requieren {$cantidad} unidades de '{$material}'",
            'Revisar en módulo MRP',
            'warning',
            null,
            'mrp_materiales'
        );
    }

    /**
     * Create OC generated notification
     */
    public function createOCNotification(int $maquiladoraId, int $ocId, string $material): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Orden de Compra Generada',
            "Se generó la OC #{$ocId} para '{$material}'",
            'Ver detalles en MRP',
            'success',
            null,
            'mrp_materiales'
        );
    }

    /**
     * Notificación cuando se agrega un cliente
     */
    public function notifyClienteAgregado(int $maquiladoraId, string $clienteNombre): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nuevo Cliente Agregado',
            "Se ha registrado el cliente: {$clienteNombre}",
            null,
            'success',
            null,
            'clientes'
        );
    }

    /**
     * Notificación cuando se actualiza un cliente
     */
    public function notifyClienteActualizado(int $maquiladoraId, string $clienteNombre): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Cliente Actualizado',
            "Se han actualizado los datos del cliente: {$clienteNombre}",
            null,
            'info',
            null,
            'clientes'
        );
    }

    /**
     * Notificación cuando se elimina un cliente
     */
    public function notifyClienteEliminado(int $maquiladoraId, string $clienteNombre): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Cliente Eliminado',
            "Se ha eliminado el cliente: {$clienteNombre}",
            null,
            'warning',
            null,
            'clientes'
        );
    }

    /**
     * Notificación cuando se agrega un diseño
     */
    public function notifyDisenoAgregado(int $maquiladoraId, string $disenoNombre, string $codigo): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nuevo Diseño Agregado',
            "Se ha creado el diseño: {$codigo} - {$disenoNombre}",
            null,
            'success',
            null,
            'disenos'
        );
    }

    /**
     * Notificación cuando se actualiza un diseño
     */
    public function notifyDisenoActualizado(int $maquiladoraId, string $disenoNombre, string $codigo): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Diseño Actualizado',
            "Se ha actualizado el diseño: {$codigo} - {$disenoNombre}",
            null,
            'info',
            null,
            'disenos'
        );
    }

    /**
     * Notificación cuando se elimina un diseño
     */
    public function notifyDisenoEliminado(int $maquiladoraId, string $disenoNombre, string $codigo): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Diseño Eliminado',
            "Se ha eliminado el diseño: {$codigo} - {$disenoNombre}",
            null,
            'warning',
            null,
            'disenos'
        );
    }

    /**
     * Notificación cuando se agrega un pedido
     */
    public function notifyPedidoAgregado(int $maquiladoraId, string $pedidoCodigo, string $clienteNombre): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nuevo Pedido Creado',
            "Se ha creado el pedido {$pedidoCodigo} para el cliente {$clienteNombre}",
            null,
            'info',
            null,
            'pedidos'
        );
    }

    public function notifyOrdenEstatusActualizado(int $maquiladoraId, string $ordenFolio, string $nuevoEstatus, string $clienteNombre): int|false
    {
        // Determinar el nivel y color según el estatus
        $nivel = 'info';
        $estatusLower = strtolower($nuevoEstatus);
        
        if (strpos($estatusLower, 'completada') !== false || strpos($estatusLower, 'finalizada') !== false) {
            $nivel = 'success';
        } elseif (strpos($estatusLower, 'en proceso') !== false || strpos($estatusLower, 'corte') !== false) {
            $nivel = 'primary';
        } elseif (strpos($estatusLower, 'pausada') !== false || strpos($estatusLower, 'detenida') !== false) {
            $nivel = 'warning';
        } elseif (strpos($estatusLower, 'cancelada') !== false) {
            $nivel = 'danger';
        }

        return $this->createNotification(
            $maquiladoraId,
            'Estatus de Orden Actualizado',
            "La orden {$ordenFolio} ha cambiado a estatus: {$nuevoEstatus}",
            "Cliente: {$clienteNombre}",
            $nivel,
            null,
            'pedidos'
        );
    }

    /**
     * Notificación cuando se actualiza el estado de un pedido
     */
    public function notifyPedidoEstadoActualizado(int $maquiladoraId, string $pedidoCodigo, string $estado): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Estado de Pedido Actualizado',
            "El pedido {$pedidoCodigo} ha cambiado a estado: {$estado}",
            null,
            'info',
            null,
            'pedidos'
        );
    }

    /**
     * Notificación cuando se agrega una orden de producción
     */
    public function notifyOrdenProduccionAgregada(int $maquiladoraId, string $ordenCodigo): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nueva Orden de Producción',
            "Se ha creado la orden de producción: {$ordenCodigo}",
            null,
            'info',
            null,
            'ordenes_produccion'
        );
    }

    /**
     * Notificación de mantenimiento programado
     */
    public function notifyMantenimientoProgramado(int $maquiladoraId, string $maquinaNombre, string $fecha): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Mantenimiento Programado',
            "Se ha programado mantenimiento para {$maquinaNombre} el {$fecha}",
            null,
            'warning',
            null,
            'mantenimiento'
        );
    }

    /**
     * Notificación de incidencia reportada
     */
    public function notifyIncidenciaReportada(int $maquiladoraId, string $tipoIncidencia, string $descripcion): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Incidencia Reportada',
            "Se ha reportado una incidencia: {$tipoIncidencia} - {$descripcion}",
            null,
            'danger',
            null,
            'incidencias'
        );
    }

    /**
     * Notificación cuando se asigna un empleado a una orden de producción
     */
    public function notifyEmpleadoAsignado(int $maquiladoraId, string $ordenFolio, string $empleadoNombre, string $empleadoPuesto): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Empleado Asignado a Orden',
            "Se ha asignado a {$empleadoNombre} ({$empleadoPuesto}) a la orden {$ordenFolio}",
            null,
            'info',
            null,
            'ordenes_produccion'
        );
    }

    /**
     * Notificación cuando se asignan múltiples empleados a una orden de producción
     */
    public function notifyEmpleadosAsignadosMultiple(int $maquiladoraId, string $ordenFolio, int $cantidadEmpleados): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Empleados Asignados a Orden',
            "Se han asignado {$cantidadEmpleados} empleados a la orden {$ordenFolio}",
            null,
            'info',
            null,
            'ordenes_produccion'
        );
    }

    /**
     * Notificación cuando una muestra es aprobada
     */
    public function notifyMuestraAprobada(int $maquiladoraId, string $prototipoId, string $clienteNombre): int|false
    {
        log_message('debug', "NOTIFICATION DEBUG - notifyMuestraAprobada llamado con: maquiladora={$maquiladoraId}, prototipo={$prototipoId}, cliente={$clienteNombre}");
        
        $result = $this->createNotification(
            $maquiladoraId,
            'Muestra Aprobada',
            "La muestra del prototipo {$prototipoId} ha sido aprobada",
            "Cliente: {$clienteNombre}",
            'success',
            null,
            'muestras'
        );
        
        log_message('debug', "NOTIFICATION DEBUG - notifyMuestraAprobada resultado: {$result}");
        return $result;
    }

    /**
     * Notificación cuando una muestra es rechazada
     */
    public function notifyMuestraRechazada(int $maquiladoraId, string $prototipoId, string $clienteNombre): int|false
    {
        log_message('debug', "NOTIFICATION DEBUG - notifyMuestraRechazada llamado con: maquiladora={$maquiladoraId}, prototipo={$prototipoId}, cliente={$clienteNombre}");
        
        $result = $this->createNotification(
            $maquiladoraId,
            'Muestra Rechazada',
            "La muestra del prototipo {$prototipoId} ha sido rechazada",
            "Cliente: {$clienteNombre}",
            'warning',
            null,
            'muestras'
        );
        
        log_message('debug', "NOTIFICATION DEBUG - notifyMuestraRechazada resultado: {$result}");
        return $result;
    }

    /**
     * Notificación cuando se registra una incidencia en producción
     */
    public function notifyIncidenciaRegistrada(int $maquiladoraId, string $tipoIncidencia, string $ordenFolio, string $descripcion): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Incidencia Registrada',
            "Se ha reportado una incidencia tipo: {$tipoIncidencia} en la orden {$ordenFolio}",
            "Descripción: {$descripcion}",
            'danger',
            null,
            'incidencias'
        );
    }

    /**
     * Notificación cuando una inspección es aprobada
     */
    public function notifyInspeccionAprobada(int $maquiladoraId, string $numeroInspeccion, string $ordenFolio, string $puntoInspeccion): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Inspección Aprobada',
            "La inspección {$numeroInspeccion} ha sido aprobada",
            "Orden: {$ordenFolio} - Punto: {$puntoInspeccion}",
            'success',
            null,
            'inspeccion'
        );
    }

    /**
     * Notificación cuando una inspección es rechazada
     */
    public function notifyInspeccionRechazada(int $maquiladoraId, string $numeroInspeccion, string $ordenFolio, string $puntoInspeccion): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Inspección Rechazada',
            "La inspección {$numeroInspeccion} ha sido rechazada",
            "Orden: {$ordenFolio} - Punto: {$puntoInspeccion}",
            'danger',
            null,
            'inspeccion'
        );
    }

    /**
     * Notificación cuando se crea un nuevo rol
     */
    public function notifyRolCreado(int $maquiladoraId, string $rolNombre, string $rolDescripcion): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nuevo Rol Creado',
            "Se ha creado el rol: {$rolNombre}",
            $rolDescripcion,
            'success',
            null,
            'sistema'
        );
    }

    /**
     * Notificación cuando se actualiza un rol
     */
    public function notifyRolActualizado(int $maquiladoraId, string $rolNombre, string $rolDescripcion): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Rol Actualizado',
            "Se ha actualizado el rol: {$rolNombre}",
            $rolDescripcion,
            'info',
            null,
            'sistema'
        );
    }

    /**
     * Notificación cuando se actualizan los permisos de un rol
     */
    public function notifyPermisosRolActualizados(int $maquiladoraId, string $rolNombre, int $cantidadPermisos): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Permisos de Rol Actualizados',
            "Se han actualizado los permisos del rol: {$rolNombre}",
            "Total de permisos: {$cantidadPermisos}",
            'info',
            null,
            'sistema'
        );
    }

    /**
     * Notificación cuando se actualiza un usuario
     */
    public function notifyUsuarioActualizado(int $maquiladoraId, string $nombreUsuario, string $email): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Usuario Actualizado',
            "Se ha actualizado el usuario: {$nombreUsuario}",
            "Email: {$email}",
            'warning',
            null,
            'sistema'
        );
    }

    /**
     * Notificación cuando se registra un nuevo usuario
     */
    public function notifyUsuarioRegistrado(int $maquiladoraId, string $nombreUsuario, string $email): int|false
    {
        return $this->createNotification(
            $maquiladoraId,
            'Nuevo Usuario Registrado',
            "Se ha registrado un nuevo usuario: {$nombreUsuario}",
            "Email: {$email}",
            'info',
            null,
            'sistema'
        );
    }
}
