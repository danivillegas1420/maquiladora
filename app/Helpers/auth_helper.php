<?php

use CodeIgniter\Database\BaseConnection;

if (!function_exists('current_role_name')) {
    function current_role_name(): ?string {
        $role = session()->get('role_name');
        if ($role) { return $role; }
        
        // Optimización: verificar cache de roles en sesión primero
        $cachedRoles = session()->get('cached_roles');
        if ($cachedRoles && !empty($cachedRoles)) {
            session()->set('role_name', $cachedRoles[0]);
            return $cachedRoles[0];
        }
        
        $userId = session()->get('user_id');
        if (!$userId) { return null; }
        try {
            /** @var BaseConnection $db */
            $db = \Config\Database::connect();
            // Optimización: una sola consulta para obtener todos los roles
            $query = 'SELECT r.nombre FROM usuario_rol ur JOIN rol r ON r.id = ur.rolIdFK WHERE ur.usuarioIdFK = ?';
            $rows = $db->query($query, [$userId])->getResultArray();
            
            $roles = [];
            foreach ($rows as $r) {
                if (isset($r['nombre']) && $r['nombre'] !== '') {
                    $roles[] = (string)$r['nombre'];
                }
            }
            
            if (!empty($roles)) {
                // Cache de roles en sesión
                session()->set([
                    'role_name' => $roles[0],
                    'cached_roles' => $roles,
                    'role_names' => $roles
                ]);
                return $roles[0];
            }
        } catch (\Throwable $e) { /* sin rol */ }
        return null;
    }
}

if (!function_exists('can_menu')) {
    function can_menu(string $perm): bool {
        // Bypass para roles privilegiados (siempre tienen acceso)
        $roleName = current_role_name();
        $roleNorm = $roleName ? mb_strtolower(trim($roleName)) : '';
        
        if (in_array($roleNorm, ['administrador', 'jefe', 'superadmin'])) {
            return true;
        }

        // Bypass específico para menu.maquiladora
        if ($perm === 'menu.maquiladora' && $roleNorm === 'rh') {
            return true;
        }

        // NOTA: No otorgar bypass automático a 'empleado' para que el menú respete
        // los permisos configurados en Roles (rol_permiso).

        // Optimización: usar cache de permisos en sesión
        $cachedPermissions = session()->get('cached_permissions');
        if ($cachedPermissions !== null) {
            return in_array($perm, $cachedPermissions);
        }
        
        $role = current_role_name();
        
        if (!$role) {
            return false;
        }
        
        try {
            $db = \Config\Database::connect();
            // Optimización: una sola consulta para obtener todos los permisos del rol
            $rolRow = $db->table('rol')
                ->select('id')
                ->where('nombre', $role)
                ->get()
                ->getRowArray();
            
            if ($rolRow) {
                $rolId = $rolRow['id'];
                // Obtener todos los permisos del rol en una sola consulta
                $permisos = $db->table('rol_permiso')
                    ->select('permiso')
                    ->where('rol_id', $rolId)
                    ->get()
                    ->getResultArray();
                
                $permissionList = array_column($permisos, 'permiso');
                
                // Cache de permisos en sesión
                session()->set('cached_permissions', $permissionList);
                
                return in_array($perm, $permissionList);
            }
        } catch (\Throwable $e) { 
            // En caso de error, limpiar cache para reintentar en siguiente solicitud
            session()->remove('cached_permissions');
        }
        
        return false;
    }
}

// Alias común
if (!function_exists('can')) {
    function can(string $perm): bool { return can_menu($perm); }
}
